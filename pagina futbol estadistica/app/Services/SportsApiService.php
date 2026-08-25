<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SportsApiService
{
    protected Client $client;

    public function __construct()
    {
        // For demonstration, we point to our internal mock route.
        // In a real scenario, this would be an external domain from env()
        $this->client = new Client([
            'base_uri' => 'http://127.0.0.1:8000',
            'timeout'  => 10.0,
        ]);
    }

    /**
     * Fetch mock data from the external API via Guzzle
     * Returns an array with tournaments, matchdays, teams and matches.
     */
    public function fetchAllData(): array
    {
        try {
            $response = $this->client->request('GET', '/api/external-mock', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer FAKE_API_KEY_123'
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (GuzzleException $e) {
            Log::error('API Sync Error: ' . $e->getMessage());
            throw new \Exception('Failed to fetch sports data from API');
        }
    }
}
