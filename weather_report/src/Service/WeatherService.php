<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Psr\Cache\CacheItemPoolInterface;

class WeatherService
{
    private string $baseUrl = 'https://api.openweathermap.org/data/2.5/weather';

    public function __construct(private Client $client, private string $apiKey, private CacheItemPoolInterface $cache)
    {}

    public function getWeather(string $city): array
    {
        $cacheKey = 'weather_' . mb_strtolower(trim($city));
        $item = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            $resultItem = $item->get();
            $resultItem['message'] = 'данные из кеша';
            return $resultItem;
        }

        $data = $this->fetchApi($city);
        $item->set($data);
        $item->expiresAfter(300);
        $this->cache->save($item);

        return $data;
    }

    private function fetchApi(string $city): array
    {
        try {
            $response = $this->client->get($this->baseUrl, [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'ru',
                ]
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Некорректный ответ от API: невалидный JSON');
            }
        } catch (ClientException $e) {
            $message = $this->extractApiError($e);
            throw new \RuntimeException($message, $e->getCode(), $e);
        } catch (ServerException $e) {
            throw new \RuntimeException('Ошибка сервера погодного API. Попробуйте позже.', $e->getCode(), $e);
        } catch (ConnectException $e) {
            throw new \RuntimeException('Не удалось подключиться к серверу погоды. Проверьте интернет-соединение.', 0, $e);
        } catch (RequestException $e) {
            throw new \RuntimeException('Ошибка при запросе к API погоды.', 0, $e);
        }

        // API может вернуть 200, но с ошибкой внутри (редко, но бывает)
        if (isset($data['cod']) && (string) $data['cod'] !== '200') {
            $apiMessage = $data['message'] ?? 'Неизвестная ошибка API';
            throw new \RuntimeException("Ошибка API: {$apiMessage}");
        }

        if (!isset($data['main'], $data['weather'][0])) {
            throw new \RuntimeException('Некорректный ответ от API: отсутствуют необходимые данные');
        }

        return [
            'city' => $data['name'],
            'country' => $data['sys']['country'] ?? '',
            'weather' => $data['weather'][0]['main'] ?? 'Clear',
            'temp' => $data['main']['temp'],
            'feels_like' => $data['main']['feels_like'] ?? $data['main']['temp'],
            'humidity' => (int) $data['main']['humidity'],
            'description' => $data['weather'][0]['description'] ?? 'нет данных',
            'wind_speed' => round((float) ($data['wind']['speed'] ?? 0), 1),
            'message' => 'данные из нового запроса'
        ];
    }

    /**
     * Извлекает понятное сообщение об ошибке из тела ответа API
     */
    private function extractApiError(ClientException $e): string
    {
        $response = $e->getResponse();
        if ($response === null) {
            return 'Ошибка запроса к API погоды.';
        }

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($data['message'])) {
            $apiMessage = $data['message'];

            // Переводим стандартные сообщения OWM на русский
            $translations = [
                'city not found' => 'Город не найден. Проверьте название.',
                'Nothing to geocode' => 'Пустое название города.',
            ];

            return $translations[$apiMessage] ?? "Ошибка API: {$apiMessage}";
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode === 401) {
            return 'Ошибка авторизации: неверный API-ключ.';
        }
        if ($statusCode === 404) {
            return 'Город не найден. Проверьте название.';
        }

        return "Ошибка запроса (HTTP {$statusCode}).";
    }
}
