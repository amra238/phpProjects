<?php

namespace App\Tests\Service;

use App\Entity\Delivery;
use App\Entity\Point;
use App\Entity\Restaurant;
use App\Service\DeliveryService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class DeliveryServiceTest extends TestCase
{
    private function createEntityManagerMock(array $restaurants): EntityManagerInterface
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $repo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findAll'])
            ->getMock();
        $repo->method('findAll')->willReturn($restaurants);

        $em->method('getRepository')
            ->with(Restaurant::class)
            ->willReturn($repo);

        return $em;
    }

    private function createService(array $responses, ?EntityManagerInterface $em = null): DeliveryService
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        return new DeliveryService(
            'fake-api-key',
            $em ?? $this->createMock(EntityManagerInterface::class),
            $client
        );
    }

    public function testCalculateTotalPrice(): void
    {
        $service = new DeliveryService('key', $this->createMock(EntityManagerInterface::class));

        $this->assertSame(160.0, $service->calculateTotalPrice(1000.0, 100.0, 0.06));
        $this->assertSame(100.0,  $service->calculateTotalPrice(0.0, 100.0, 0.06));
    }

    public function testGetAllRestaurants(): void
    {
        $expected = [new Restaurant(), new Restaurant()];
        $em = $this->createEntityManagerMock($expected);

        $service = new DeliveryService('key', $em);

        $this->assertSame($expected, $service->getAllRestaurants());
    }

    public function testGetClosestRouteReturnsNearestRestaurant(): void
    {
        $r1 = new Restaurant();
        $r1->setName('R1');
        $r1->setLocation(new Point(55.75, 37.61));
        $r2 = new Restaurant();
        $r2->setName('R2');
        $r2->setLocation(new Point(55.76, 37.62));

        $target = new Point(55.7601, 37.6201);

        $service = $this->createService([
            new Response(200, [], json_encode(['features' => [['properties' => ['summary' => ['distance' => 5000]]]]])),
            new Response(200, [], json_encode(['features' => [['properties' => ['summary' => ['distance' => 200]]]]])),
        ]);

        $result = $service->getClosestRoute([$r1, $r2], $target);

        $this->assertSame($r2, $result['restaurant']);
        $this->assertSame(200.0, $result['distance']);
    }

    public function testFillDeliveryDataSuccessfully(): void
    {
        $r1 = new Restaurant();
        $r1->setName('R1');
        $r1->setLocation(new Point(55.75, 37.61));
        $r2 = new Restaurant();
        $r2->setName('R2');
        $r2->setLocation(new Point(55.76, 37.62));

        $em = $this->createEntityManagerMock([$r1, $r2]);

        $service = $this->createService([
            new Response(200, [], json_encode(['features' => [['properties' => ['summary' => ['distance' => 5000]]]]])),
            new Response(200, [], json_encode(['features' => [['properties' => ['summary' => ['distance' => 200]]]]])),
        ], $em);

        $delivery = new Delivery();
        $delivery->setPointOfDelivery(new Point(55.7601, 37.6201));

        $service->fillDeliveryData($delivery);

        $this->assertSame('112', $delivery->getTotalPrice());
        $this->assertSame('200', $delivery->getDistance());
        $this->assertSame($r2, $delivery->getSenderRestaurant());
    }

    public function testFillDeliveryDataThrowsWhenNoDeliveryPoint(): void
    {
        $service = new DeliveryService('key', $this->createMock(EntityManagerInterface::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Точка доставки не указана');

        $service->fillDeliveryData(new Delivery());
    }

    public function testFillDeliveryDataThrowsWhenNoRestaurants(): void
    {
        $em = $this->createEntityManagerMock([]);

        $service = new DeliveryService('key', $em);
        $delivery = new Delivery();
        $delivery->setPointOfDelivery(new Point(0, 0));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Рестораны не найдены');

        $service->fillDeliveryData($delivery);
    }

    public function testFillDeliveryDataThrowsWhenApiFailsForAll(): void
    {
        $r = new Restaurant();
        $r->setName('R1');
        $r->setLocation(new Point(55.75, 37.61));

        $em = $this->createEntityManagerMock([$r]);

        $service = $this->createService([
            new Response(500, [], 'Server Error'),
        ], $em);

        $delivery = new Delivery();
        $delivery->setPointOfDelivery(new Point(55.76, 37.62));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Определить кратчайший путь не удалось');

        $service->fillDeliveryData($delivery);
    }
}
