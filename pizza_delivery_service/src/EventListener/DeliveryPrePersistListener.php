<?php

namespace App\EventListener;

use App\Entity\Delivery;
use App\Service\DeliveryService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Delivery::class)]
class DeliveryPrePersistListener
{
    public function __construct(
        private DeliveryService $deliveryService,
    ) {}

    public function prePersist(Delivery $delivery): void
    {
        if ($delivery->getTotalPrice() === null) {
            $this->deliveryService->fillDeliveryData($delivery);
        }
    }
}
