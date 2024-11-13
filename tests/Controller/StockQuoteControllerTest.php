<?php

namespace App\Tests\Controller;

use App\Controller\StockQuoteController;
use App\Service\StockQuoteService;
use App\Util\CsvGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StockQuoteControllerTest extends WebTestCase
{
    private $stockQuoteService;
    private $validator;
    private $mailer;
    private $controller;

    protected function setUp(): void
    {
        $this->stockQuoteService = $this->createMock(StockQuoteService::class);
        $this->validator = Validation::createValidator();
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->controller = new StockQuoteController($this->stockQuoteService, $this->validator, $this->mailer);
    }

    public function testGetStockQuotesValidInput()
    {
        $request = new Request([], [
            'companySymbol' => 'AAPL',
            'startDate' => '2023-01-01',
            'endDate' => '2023-01-31',
            'email' => 'example@example.com',
        ]);

        $historicalData = [
            ['date' => strtotime('2023-01-01'), 'open' => 150, 'high' => 155, 'low' => 145, 'close' => 152, 'volume' => 1000],
            ['date' => strtotime('2023-01-02'), 'open' => 153, 'high' => 158, 'low' => 149, 'close' => 157, 'volume' => 1100],
        ];

        $this->stockQuoteService->expects($this->once())
            ->method('fetchHistoricalData')
            ->with('AAPL', '2023-01-01', '2023-01-31')
            ->willReturn($historicalData);

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                return $email->getTo()[0]->getAddress() === 'example@example.com';
            }));

        $response = $this->controller->getStockQuotes($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('Email sent successfully!', $response->getContent());
    }

    public function testGetStockQuotesValidationErrors()
    {
        $request = new Request([], [
            'companySymbol' => '',
            'startDate' => '',
            'endDate' => '',
            'email' => 'invalid-email',
        ]);

        $response = $this->controller->getStockQuotes($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('errors', $response->getContent());
    }

    public function testGetStockQuotesServiceFailure()
    {
        $request = new Request([], [
            'companySymbol' => 'AAPL',
            'startDate' => '2023-01-01',
            'endDate' => '2023-01-31',
            'email' => 'example@example.com',
        ]);

        $this->stockQuoteService->expects($this->once())
            ->method('fetchHistoricalData')
            ->will($this->throwException(new \Exception('Service failure')));

        $response = $this->controller->getStockQuotes($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('Failed to fetch historical data', $response->getContent());
    }

    public function testGetStockQuotesEmailFailure()
    {
        $request = new Request([], [
            'companySymbol' => 'AAPL',
            'startDate' => '2023-01-01',
            'endDate' => '2023-01-31',
            'email' => 'example@example.com',
        ]);

        $historicalData = [
            ['date' => strtotime('2023-01-01'), 'open' => 150, 'high' => 155, 'low' => 145, 'close' => 152, 'volume' => 1000],
            ['date' => strtotime('2023-01-02'), 'open' => 153, 'high' => 158, 'low' => 149, 'close' => 157, 'volume' => 1100],
        ];

        $this->stockQuoteService->expects($this->once())
            ->method('fetchHistoricalData')
            ->willReturn($historicalData);

        $this->mailer->expects($this->once())
            ->method('send')
            ->will($this->throwException(new \Exception('Email failure')));

        $response = $this->controller->getStockQuotes($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('Failed to send email: Email failure', $response->getContent());
    }
}
