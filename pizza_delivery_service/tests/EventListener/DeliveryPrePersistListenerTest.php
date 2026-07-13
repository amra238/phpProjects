<?php

namespace App\Tests\EventListener;

use App\Entity\Delivery;
use App\Entity\Point;
use App\EventListener\DeliveryPrePersistListener;
use App\Service\DeliveryService;
use PHPUnit\Framework\TestCase;

class DeliveryPrePersistListenerTest extends TestCase
{
    public function testPrePersistCallsFillDeliveryDataWhenPriceIsNull(): void
    {
        $service = $this->createMock(DeliveryService::class);
        $listener = new DeliveryPrePersistListener($service);

        $delivery = new Delivery();
        $delivery->setPointOfDelivery(new Point(55.75, 37.61));

        $service->expects($this->once())
            ->method('fillDeliveryData')
            ->with($delivery);

        $listener->prePersist($delivery);
    }

    public function testPrePersistSkipsWhenPriceAlreadySet(): void
    {
        $service = $this->createMock(DeliveryService::class);
        $listener = new DeliveryPrePersistListener($service);

        $delivery = new Delivery();
        $delivery->setPointOfDelivery(new Point(55.75, 37.61));
        $delivery->setTotalPrice('100.00');

        $service->expects($this->never())->method('fillDeliveryData');

        $listener->prePersist($delivery);
    }
}
