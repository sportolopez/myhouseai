<?php

namespace App\Controller;
use App\Entity\Planes;
use App\Repository\PlanesRepository;
use App\Repository\UsuarioRepository;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use Doctrine\Persistence\ManagerRegistry;
use MercadoPago\Resources\MerchantOrder\Item;
use MercadoPago\Resources\Preference;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class PagosController extends AbstractController{

    protected function authenticate()
    {
        // Getting the access token from .env file (create your own function)
        // $mpAccessToken = getVariableFromEnv('mercado_pago_access_token');
        // Set the token the SDK's config
        MercadoPagoConfig::setAccessToken("TEST-4972941314108448-060716-d04a68936cfd0dea91f921cba2cdb2ee-22465532");
        // (Optional) Set the runtime enviroment to LOCAL if you want to test on localhost
        // Default value is set to SERVER
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
    }


/*
    #[Route('/payment', name: 'payment', methods: ['GET'])]
    public function processPayment(ManagerRegistry $doctrine,Request $request, UsuarioRepository $usuarioRepository, PlanesRepository  $planesRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $jwtPayload = $request->attributes->get('jwt_payload');
        $usuario = $usuarioRepository->findOneByEmail($jwtPayload->token_info->email);


        // Obtener datos de la solicitud
        $data = $request->toArray();
    
        self::authenticate();
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
                    "email" => $data['payer']['email']
                ]
            ];
    
            // Crear las opciones de solicitud, estableciendo X-Idempotency-Key
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Idempotency-Key: " . uniqid()]);
    
    
            $planComprado = $planesRepository->findOneByMonto($data['transaction_amount']);
            if(!$planComprado){
                return new JsonResponse(['error' => 'El monto no corresponde con un plan '], 500);
            }

            $payment = $client->create($request, $request_options);


            $usuario->setCantidadImagenesDisponibles($usuario->getCantidadImagenesDisponibles()+$planComprado->getCantidad());
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
*/


   


    #[Route('/payment/notification', name: 'payment_notification', methods: ['POST'])]
    public function handleNotification(Request $request): Response
    {
        // Verifica si la solicitud es una notificación IPN de MercadoPago
        $topic = $request->query->get('topic');
        $id = $request->query->get('id');

        if ($topic === 'payment') {
            // Inicializa el SDK de MercadoPago con tu Access Token
            SDK::setAccessToken('TU_ACCESS_TOKEN');
            
            // Obtiene el pago utilizando el ID recibido
            $payment = Payment::find_by_id($id);

            if ($payment) {
                // Procesa el estado del pago y actualiza tu sistema
                switch ($payment->status) {
                    case 'approved':
                        // El pago fue aprobado
                        // Aquí registrarías el pago en tu base de datos
                        // Por ejemplo, usando Doctrine para guardar la información
                        // $this->savePayment($payment);
                        break;
                    case 'pending':
                        // El pago está pendiente
                        break;
                    case 'rejected':
                        // El pago fue rechazado
                        break;
                    // Maneja otros estados si es necesario
                    default:
                        break;
                }
            }
        }

        return new Response('OK', Response::HTTP_OK);
    }

    // Método opcional para guardar el pago en la base de datos
    private function savePayment($payment)
    {
        // Implementa la lógica para guardar el pago en la base de datos utilizando Doctrine
    }

}