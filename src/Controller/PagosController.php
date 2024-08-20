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



    #[Route('/process_payment', name: 'create_payment', methods: ['POST'])]
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



    #[Route('/create_preference', name: 'create_preference', methods: ['POST'])]
    public function createPreference(Request $request,ManagerRegistry $doctrine, UsuarioRepository $usuarioRepository): Response
    {
        self::authenticate();

        $entityManager = $doctrine->getManager();
        $jwtPayload = $request->attributes->get('jwt_payload');
        $usuario = $usuarioRepository->findOneByEmail($jwtPayload->token_info->email);


        $data = json_decode($request->getContent(), true);

        // Fill the data about the product(s) being pruchased
        $product1 = array(
            "id" => "1234567890",
            "title" => "Fotos en MyHouseAi",
            "description" => "Fotos en MyHouseAi",
            "currency_id" => "ARS",
            "quantity" => $data['quantity'],
            "unit_price" => $data['unit-price']
        );



        // Mount the array of products that will integrate the purchase amount
        $items = array($product1);

        // Retrieve information about the user (use your own function)


        $payer = array(
            "name" => $usuario->getNombre(),
            "surname" => $usuario->getNombre(),
            "email" => $usuario->getEmail(),
        );

        // Create the request object to be sent to the API when the preference is created
        $request = self::createPreferenceRequest($items, $payer);

        // Instantiate a new Preference Client
        $client = new PreferenceClient();

        try {
            // Send the request that will create the new preference for user's checkout flow
            $preference = $client->create($request);

            // Useful props you could use from this object is 'init_point' (URL to Checkout Pro) or the 'id'
            return new JsonResponse($preference);
        } catch (MPApiException $error) {
            // Here you might return whatever your app needs.
            // We are returning null here as an example.
            return null;
        }
    }


    function createPreferenceRequest($items, $payer): array
    {
        $paymentMethods = [
            "excluded_payment_methods" => [],
            "installments" => 1,
            "default_installments" => 1
        ];

        $backUrls = array(
            'success' => 'https://myhouseai.com.ar/',
            'failure' => 'https://myhouseai.com.ar/'
        );

        $request = [
            "items" => $items,
            "payer" => $payer,
            "payment_methods" => $paymentMethods,
            "back_urls" => $backUrls,
            "statement_descriptor" => "NAME_DISPLAYED_IN_USER_BILLING",
            "external_reference" => "1234567890",
            "expires" => false,
            "auto_return" => 'approved',
        ];

        return $request;
    }
    
}