<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ChessComApiClient
{
    private HttpClientInterface $client;
    private string $baseUrl = 'https://api.chess.com/pub/player';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetchUserProfile(string $username): ?array
    {
        try {
            $response = $this->client->request('GET', "{$this->baseUrl}/$username");
            if ($response->getStatusCode() === 200) {
                return $response->toArray();
            }
        } catch (TransportExceptionInterface $e) {
            // Handle network/API errors gracefully
            return null;
        }

        return null;
    }
    public function fetchMonthlyGames(string $username, string $year, string $month): array
    {
        $url = "{$this->baseUrl}/{$username}/games/{$year}/{$month}";

        try {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                return $data['games'] ?? [];
            }
        } catch (TransportExceptionInterface $e) {
            return [];
        }

        return [];
    }

}
