<?php

namespace App\Tests\Command;

use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AddRestaurantCommandTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testExecuteSuccessfully(): void
    {
        $application = new Application(self::$kernel);

        $command = $application->find('restaurant:add');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'name' => 'Тестовый ресторан',
            'lat'  => '55.7558',
            'lng'  => '37.6173',
        ]);

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Тестовый ресторан добавлен', $commandTester->getDisplay());

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $restaurant = $em->getRepository(Restaurant::class)
            ->findOneBy(['name' => 'Тестовый ресторан']);

        $this->assertNotNull($restaurant);
        $this->assertSame(55.7558, (float) $restaurant->getLocation()->getLatitude());
        $this->assertSame(37.6173, (float) $restaurant->getLocation()->getLongitude());
    }
}
