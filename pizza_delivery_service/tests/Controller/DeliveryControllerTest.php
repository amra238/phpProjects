<?php

namespace App\Tests\Controller;

use App\Entity\Point;
use App\Entity\Restaurant;
use App\Service\DeliveryService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DeliveryControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $cacheDir = dirname(__DIR__, 2) . '/var/cache/test';
        if (is_dir($cacheDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
            }
            rmdir($cacheDir);
        }

        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testNewFormIsDisplayed(): void
    {
        $this->client->request('GET', '/delivery/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorTextContains('h1', 'Оформление доставки пиццы');
    }

    public function testNewSubmitWithoutRestaurantsShowsError(): void
    {
        $crawler = $this->client->request('GET', '/delivery/new');
        $token = $crawler->filter('form input[name="point[_token]"]')->attr('value');

        $this->client->request('POST', '/delivery/new', [
            'point' => [
                'latitude'  => '55.7558',
                'longitude' => '37.6173',
                '_token'    => $token,
            ],
        ]);

        self::assertResponseRedirects('/delivery/new');

        $this->client->followRedirect();
        self::assertSelectorExists('.alert.alert-danger');
    }

    public function testNewSubmitWithRestaurant(): void
    {
        $restaurantPoint = new Point(55.7582, 37.6173);
        $restaurant = new Restaurant();
        $restaurant->setName('Тестовая пицца');
        $restaurant->setLocation($restaurantPoint);

        $this->em->persist($restaurantPoint);
        $this->em->persist($restaurant);
        $this->em->flush();

        $responseData = json_encode([
            'features' => [['properties' => ['summary' => ['distance' => 1500]]]]
        ]);

        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->method('request')->willReturn(
            new Response(200, [], $responseData)
        );

        $mockService = new DeliveryService('fake-key', $this->em, $clientMock);
        static::getContainer()->set(DeliveryService::class, $mockService);

        $crawler = $this->client->request('GET', '/delivery/new');
        $token = $crawler->filter('form input[name="point[_token]"]')->attr('value');

        $this->client->request('POST', '/delivery/new', [
            'point' => [
                'latitude'  => '55.7558',
                'longitude' => '37.6173',
                '_token'    => $token,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Заказ оформлен');

        self::assertSelectorExists('p:contains("ID:")');
        self::assertSelectorExists('p:contains("Цена:")');
        self::assertSelectorExists('p:contains("Расстояние:")');
        self::assertSelectorExists('p:contains("Ресторан:")');
    }
}
