<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class StockQuoteService
{
    private $httpClient;
    private $rapidApiKey;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->rapidApiKey = $_ENV['RAPIDAPI_KEY'];
    }

    public function fetchHistoricalData(string $symbol, string $startDate, string $endDate): ?array
    {
        $response = $this->httpClient->request('GET', $_ENV['RAPIDAPI_URL'], [
            'headers' => [
                'X-RapidAPI-Key' => $this->rapidApiKey,
                'X-RapidAPI-Host' => 'yh-finance.p.rapidapi.com',
            ],
            'query' => [
                'symbol' => $symbol,
                'region' => 'US',
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data = $response->toArray();
        return array_filter($data['prices'], function ($item) use ($startDate, $endDate) {
            $date = date('Y-m-d', $item['date']);
            return $date >= $startDate && $date <= $endDate;
        });
    }
}
