<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`delivery`')]
class Delivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4, nullable: true)]
    private ?string $totalPrice = null;

    #[ORM\ManyToOne(targetEntity: Point::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Point $pointOfDelivery = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $distance = null;

    #[ORM\ManyToOne(targetEntity: Restaurant::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Restaurant $senderRestaurant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(?string $totalPrice): static
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    public function getPointOfDelivery(): ?Point
    {
        return $this->pointOfDelivery;
    }

    public function setPointOfDelivery(Point $pointOfDelivery): static
    {
        $this->pointOfDelivery = $pointOfDelivery;
        return $this;
    }

    public function getDistance(): ?string
    {
        return $this->distance;
    }

    public function setDistance(?string $distance): static
    {
        $this->distance = $distance;
        return $this;
    }

    public function getSenderRestaurant(): ?Restaurant
    {
        return $this->senderRestaurant;
    }

    public function setSenderRestaurant(?Restaurant $senderRestaurant): static
    {
        $this->senderRestaurant = $senderRestaurant;
        return $this;
    }
}
