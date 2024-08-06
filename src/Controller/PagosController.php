<?php

namespace App\Controller;
use App\Repository\UsuarioRepository;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Exceptions\MPApiException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PagosController extends AbstractController{

    #[Route('/process_payment', name: 'create_payment', methods: ['POST'])]
    public function processPayment(ManagerRegistry $doctrine,Request $request, UsuarioRepository $usuarioRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $jwtPayload = $request->attributes->get('jwt_payload');
        $usuario = $usuarioRepository->findOneByEmail($jwtPayload->token_info->email);

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
                "description" => 'Compra de fotos',
                "installments" => 1,
                "payment_method_id" => $data['payment_method_id'],
                "issuer_id" => $data['issuer_id'],
                "payer" => [
                    "email" => $data['payer']['email'],
                    "identification"=> [
                        "type"=> "DNI",
                        "number"=> "12312312"
                    ]
                ]
            ];
    
            // Crear las opciones de solicitud, estableciendo X-Idempotency-Key
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Idempotency-Key: " . uniqid()]);
    
            $payment = $client->create($request, $request_options);
    
            $usuario->setCantidadImagenesDisponibles($usuario->getCantidadImagenesDisponibles()+10);
            $entityManager->persist($usuario);
            $entityManager->flush();
            
            return new JsonResponse(['cantidad_imagenes_disponibles' => $usuario->getCantidadImagenesDisponibles()]);
    
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