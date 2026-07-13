<?php

namespace App\Service;

use App\Entity\Delivery;
use App\Entity\Point;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class DeliveryService
{
    private ClientInterface $httpClient;

    public function __construct(
        private string $apiKey,
        private EntityManagerInterface $entityManager,
        ?ClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => 'https://api.openrouteservice.org/',
            'timeout' => 7.0,
            'verify' => false,
            'headers' => [
                'Authorization' => $this->apiKey,
                'Accept' => 'application/geo+json',
            ],
        ]);
    }

    public function fillDeliveryData(Delivery $delivery): void
    {
        $deliveryPoint = $delivery->getPointOfDelivery();

        if (!$deliveryPoint) {
            throw new RuntimeException('Точка доставки не указана');
        }

        $restaurants = $this->getAllRestaurants();

        if (empty($restaurants)) {
            throw new RuntimeException('Рестораны не найдены');
        }

        $route = $this->getClosestRoute($restaurants, $deliveryPoint);
        $price = $this->calculateTotalPrice($route['distance'], 100, 0.06);

        $delivery->setTotalPrice((string) $price);
        $delivery->setDistance((string) $route['distance']);
        $delivery->setSenderRestaurant($route['restaurant']);
    }

    public function getClosestRoute(array $restaurants, Point $targetPoint): array
    {
        $minDistance = PHP_INT_MAX;
        $closestRestaurant = null;

        foreach ($restaurants as $restaurant) {
            $location = $restaurant->getLocation();
            $url = sprintf(
                '/v2/directions/driving-car?start=%s,%s&end=%s,%s',
                $location->getLongitude(),
                $location->getLatitude(),
                $targetPoint->getLongitude(),
                $targetPoint->getLatitude()
            );

            try {
                $response = $this->httpClient->request('GET', $url);
                $data = json_decode($response->getBody()->getContents(), true);
                $statusCode = $response->getStatusCode();

                $distanceInMetre = $data['features'][0]['properties']['summary']['distance'] ?? null;

                if ($distanceInMetre !== null && $statusCode == 200 && $distanceInMetre < $minDistance) {
                    $minDistance = (float)$distanceInMetre;
                    $closestRestaurant = $restaurant;
                }
            } catch (GuzzleException $e) {
                error_log('ORS ERROR: ' . $e->getMessage());
                continue;
            }
        }

        if ($closestRestaurant == null) {
            throw new RuntimeException('Определить кратчайший путь не удалось');
        }

        return [
            'restaurant' => $closestRestaurant,
            'distance' => $minDistance,
        ];
    }

    public function calculateTotalPrice(float $distance, float $basePrice, float $ratio): float
    {
        return $basePrice + ($distance * $ratio);
    }

    public function getAllRestaurants(): array
    {
        return $this->entityManager->getRepository(Restaurant::class)->findAll();
    }
}
