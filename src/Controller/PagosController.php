<?php

namespace App\Controller;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Exceptions\MPApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PagosController extends AbstractController{

    #[Route('/', name: '_holamundo_', methods: ['GET'])]
    public function holamundo(): Response
    {
        error_reporting(E_ALL ^ E_DEPRECATED);
        return new Response('<html><body>Hola Mundoaaaaaaaaaa</body></html>');
    }

    #[Route('/process_payment', name: 'create_payment', methods: ['POST'])]
    public function processPayment(Request $request): JsonResponse
    {
        // Obtener datos de la solicitud
        $data = $request->toArray();
    
        // Configurar MercadoPago
        MercadoPagoConfig::setAccessToken("TEST-4972941314108448-060716-d04a68936cfd0dea91f921cba2cdb2ee-22465532");
        // Step 2.1 (optional - default is SERVER): Set your runtime enviroment from MercadoPagoConfig::RUNTIME_ENVIROMENTS
        // In case you want to test in your local machine first, set runtime enviroment to LOCAL
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
        // Inicializar el cliente de API
        $client = new PaymentClient();
        try {
            // Crear el array de solicitud usando los datos de la solicitud
            $request = [
                "transaction_amount" => $data['transaction_amount'],
                "token" => $data['token'],
                "description" => $data['description'],
                "installments" => $data['installments'],
                "payment_method_id" => $data['payment_method_id'],
                "payer" => [
                    "email" => $data['payer_email'],
                ]
            ];
    
            // Crear las opciones de solicitud, estableciendo X-Idempotency-Key
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Idempotency-Key: " . uniqid()]);
    
            // Realizar la solicitud
            $payment = $client->create($request, $request_options);
    
            return new JsonResponse($request);
    
        } catch (MPApiException $e) {
            return new JsonResponse([
                'status_code' => $e->getApiResponse()->getStatusCode(),
                'content' => $e->getApiResponse()->getContent(),
            ], $e->getApiResponse()->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
}