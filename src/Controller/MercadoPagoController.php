<?php
namespace App\Controller;

use App\Repository\UsuarioRepository;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\PreferenceSearch;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MercadoPago\SDK;
use MercadoPago\Preference;

#[Route('/payment')]
class MercadoPagoController extends AbstractController
{
    private $usuarioRepository;
    private $entityManager;

    public function __construct(UsuarioRepository $usuarioRepository, ManagerRegistry $doctrine   )
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->entityManager = $doctrine->getManager();
        MercadoPagoConfig::setAccessToken("TEST-4972941314108448-060716-d04a68936cfd0dea91f921cba2cdb2ee-22465532");
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
    }

    #[Route('/success', name: 'mercadopago_success', methods: ['GET'])]
    public function success(Request $request): Response
    {
        $preferenceId = $request->query->get('preference_id');
        return $this->handlePaymentResponse($preferenceId, 'success');
    }

    #[Route('/failure', name: 'mercadopago_failure', methods: ['GET'])]
    public function failure(Request $request): Response
    {
        $preferenceId = $request->query->get('preference_id');
        return new RedirectResponse('https://myhouseai.com.ar/?status=failure');
    }

    #[Route('/pending', name: 'mercadopago_pending', methods: ['GET'])]
    public function pending(Request $request): Response
    {
        $preferenceId = $request->query->get('preference_id');
        return $this->handlePaymentResponse($preferenceId, 'pending');
    }

    private function handlePaymentResponse(string $preferenceId, string $status): Response
    {
        // Obtener los detalles de la preferencia
        $client = new PreferenceClient();
        $preference = $client->get($preferenceId);

        if ($preference) {
            // Obtener los detalles del pagador
            $payer = $preference->payer;
            $usuarioPagador = $this->usuarioRepository->findOneByEmail($payer->email);
            if(!$usuarioPagador)
                return new Response('No se encontró al usuario .' . $payer->email, Response::HTTP_NOT_FOUND);
            $cantidadImagenesCompradas = $preference->items[0]->quantity;
            
            $usuarioPagador->setCantidadImagenesDisponibles($usuarioPagador->getCantidadImagenesDisponibles()+$cantidadImagenesCompradas);
            $this->entityManager->persist($usuarioPagador);
            $this->entityManager->flush();

            /*$usuarioPagador->setCantidadImagenesDisponibles($usuarioPagador->getCantidadImagenesDisponibles()+$planComprado->getCantidad());
            $entityManager->persist($usuario);
            $entityManager->flush();*/
            // Aquí puedes procesar la información del pagador o actualizar el estado del pago en tu base de datos
            // Por ejemplo, registrar la compra en función del estado y los detalles del pagador

            return new RedirectResponse('https://myhouseai.com.ar/?status=approved');
        }

        // Redirigir en caso de error
        return new RedirectResponse('https://myhouseai.com.ar/?status=failure');
    }

    #[Route('/create_preference', name: 'create_preference', methods: ['POST'])]
    public function createPreference(Request $request,ManagerRegistry $doctrine, UsuarioRepository $usuarioRepository): Response
    {
        $entityManager = $doctrine->getManager();
        $jwtPayload = $request->attributes->get('jwt_payload');
        $usuario = $usuarioRepository->findOneByEmail($jwtPayload->token_info->email);


        $data = json_decode($request->getContent(), true);

        // Fill the data about the product(s) being pruchased
        $product1 = array(
            //"id" => "1234567890",
            "title" => "Fotos en MyHouseAi",
            "description" => "Fotos en MyHouseAi",
            "currency_id" => "ARS",
            "quantity" => $data['quantity'],
            "unit_price" => $data['unit-price']
        );

        // Mount the array of products that will integrate the purchase amount
        $items = array($product1);

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
            return new JsonResponse($error,500);
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
            'success' => 'https://myhouseai.com.ar/api/payment/success',
            'failure' => 'https://myhouseai.com.ar/api/payment/failure',
            'pending' => 'https://myhouseai.com.ar/api/payment/pending'
        );

        $request = [
            "items" => $items,
            "payer" => $payer,
            "payment_methods" => $paymentMethods,
            "back_urls" => $backUrls,
            //"statement_descriptor" => "NAME_DISPLAYED_IN_USER_BILLING",
            //"external_reference" => "1234567890",
            "expires" => false,
            "auto_return" => 'approved',
        ];

        return $request;
    }
}
