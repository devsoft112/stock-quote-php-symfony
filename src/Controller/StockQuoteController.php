<?php

namespace App\Controller;

use App\Service\StockQuoteService;
use App\Util\CsvGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use OpenApi\Annotations as OA;

class StockQuoteController extends AbstractController
{
    private $stockQuoteService;
    private $validator;
    private $mailer;

    public function __construct(StockQuoteService $stockQuoteService, ValidatorInterface $validator, MailerInterface $mailer)
    {
        $this->stockQuoteService = $stockQuoteService;
        $this->validator = $validator;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/api/stock-quotes", name="stock_quotes", methods={"POST"})
     * @OA\Post(
     *     path="/api/stock-quotes",
     *     summary="Get historical stock quotes and send via email",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="companySymbol",
     *                     type="string",
     *                     example="AAPL"
     *                 ),
     *                 @OA\Property(
     *                     property="startDate",
     *                     type="string",
     *                     format="date",
     *                     example="2023-01-01"
     *                 ),
     *                 @OA\Property(
     *                     property="endDate",
     *                     type="string",
     *                     format="date",
     *                     example="2023-01-31"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     example="example@example.com"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully!",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Email sent successfully!"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Failed to fetch historical data"
     *             )
     *         )
     *     )
     * )
     */
    public function getStockQuotes(Request $request): JsonResponse
    {
        $data = $request->request->all();

        $constraints = new Assert\Collection([
            'companySymbol' => [new Assert\NotBlank(), new Assert\Type('string')],
            'startDate' => [new Assert\NotBlank(), new Assert\Date(), new Assert\LessThanOrEqual('today')],
            'endDate' => [new Assert\NotBlank(), new Assert\Date(), new Assert\GreaterThanOrEqual($data['startDate'] ?? ''), new Assert\LessThanOrEqual('today')],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
        ]);

        $violations = $this->validator->validate($data, $constraints);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $historicalData = $this->stockQuoteService->fetchHistoricalData($data['companySymbol'], $data['startDate'], $data['endDate']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to fetch historical data'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $csvContent = CsvGenerator::generateCsv($historicalData);

        try {
            $email = (new Email())
                ->from('mantaskreivenas11@gmail.com')
                ->to($data['email'])
                ->subject('Stock Quotes for ' . $data['companySymbol'])
                ->text('From ' . $data['startDate'] . ' to ' . $data['endDate'])
                ->attach($csvContent, 'historical_data.csv', 'text/csv');
            print_r($email);
            $this->mailer->send($email);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to send email: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'Email sent successfully!'], Response::HTTP_OK);
    }
}
