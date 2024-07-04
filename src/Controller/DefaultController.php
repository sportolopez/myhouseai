<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        error_reporting(E_ALL ^ E_DEPRECATED);
        return new Response('<html><body>Hola Mundoaaaaaaaaaa</body></html>');
    }

    #[Route('/hola', name: 'homepage2')]
    public function index2(): Response
    {
        return new Response('<html><body>Hola Mundo222222   </body></html>');
    }

    #[Route('/historial', name: 'app_historial', methods: ['GET'])]
    public function historial(): JsonResponse
    {
        // Implementa la lógica de obtención del historial aquí

        return new JsonResponse([
            'images_url' => [
                'https://example.com/image1.png',
                'https://example.com/image2.png',
                'https://example.com/image3.png'
            ]
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $accessToken = $data['access_token'] ?? null;

        // Implementa la lógica de validación del token aquí

        if ($accessToken === 'valid_token') {
            return new JsonResponse(['message' => 'Token válido.'], JsonResponse::HTTP_OK);
        } else {
            return new JsonResponse(['message' => 'Token inválido.'], JsonResponse::HTTP_UNAUTHORIZED);
        }
    }

    #[Route('/generar', name: 'app_generar', methods: ['POST'])]
    public function generar(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Implementa la lógica de generación aquí

        return new JsonResponse(['generation_id' => 'some_generated_id'], JsonResponse::HTTP_OK);
    }

    #[Route('/consultar', name: 'app_consultar', methods: ['POST'])]
    public function consultar(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Implementa la lógica de consulta aquí

        return new JsonResponse([
            'image_url' => 'https://example.com/image.png',
            'remaining_photos' => 5
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/process_payment', name: 'create_payment', methods: ['POST'])]
    public function createPayment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validar y procesar los datos recibidos
        $transactionAmount = $data['transaction_amount'] ?? null;
        $token = $data['token'] ?? null;
        $installments = $data['installments'] ?? null;
        $paymentMethodId = $data['payment_method_id'] ?? null;
        $issuerId = $data['issuer_id'] ?? null;
        $payerEmail = $data['payer']['email'] ?? null;

        if (!$transactionAmount || !$token || !$installments || !$paymentMethodId || !$issuerId || !$payerEmail) {
            return new JsonResponse(['error' => 'Invalid input'], 400);
        }

        // Simular la creación del pago (en un caso real, aquí se llamaría a la API de MercadoPago)
        $paymentResponse = [
            'id' => '123456789',
            'status' => 'approved'
        ];

        return new JsonResponse($paymentResponse, 200);
    }
}