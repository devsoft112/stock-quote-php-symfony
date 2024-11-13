<?php

namespace App\Tests\Service;

use App\Service\StockQuoteService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class StockQuoteServiceTest extends TestCase
{
    private $httpClient;
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new StockQuoteService($this->httpClient);
    }

    public function testFetchHistoricalData()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn([
            'prices' => [
                ['date' => strtotime('2023-01-01'), 'open' => 150, 'high' => 155, 'low' => 145, 'close' => 152, 'volume' => 1000],
                ['date' => strtotime('2023-01-02'), 'open' => 153, 'high' => 158, 'low' => 149, 'close' => 157, 'volume' => 1100],
            ]
        ]);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $data = $this->service->fetchHistoricalData('AAPL', '2023-01-01', '2023-01-31');
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
    }

    public function testFetchHistoricalDataFailure()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $data = $this->service->fetchHistoricalData('AAPL', '2023-01-01', '2023-01-31');
        $this->assertNull($data);
    }
}
