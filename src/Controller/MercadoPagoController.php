<?php
namespace App\Controller;

use App\Entity\EstadoCompra;
use App\Entity\UsuarioCompras;
use App\Repository\PlanesRepository;
use App\Repository\UsuarioComprasRepository;
use App\Repository\UsuarioRepository;
use App\Service\TelegramService;
use DateTime;
use Exception;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Invoice\Payment;
use MercadoPago\Resources\PreferenceSearch;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use MercadoPago\SDK;
use MercadoPago\Preference;

#[Route('/payment')]
class MercadoPagoController extends AbstractController
{
    private $usuarioRepository;
    
    private $usuarioComprasRepository;
    private $entityManager;

    public function __construct(UsuarioRepository $usuarioRepository, ManagerRegistry $doctrine, UsuarioComprasRepository $usuarioComprasRepository   )
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->usuarioComprasRepository = $usuarioComprasRepository;
        $this->entityManager = $doctrine->getManager();
        MercadoPagoConfig::setAccessToken("TEST-4972941314108448-060716-d04a68936cfd0dea91f921cba2cdb2ee-22465532");
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
    }

    #[Route('/success', name: 'mercadopago_success', methods: ['GET'])]
    public function success(Request $request,TelegramService $telegramService): Response
    {
        try{
            $preferenceId = $request->query->get('preference_id');
            $usuarioCompra =  $this->usuarioComprasRepository->findOneBy(['preferenceId' => $preferenceId]);
            if (!$usuarioCompra) {
                throw new NotFoundHttpException('No se encontró la compra con el preferenceId: ' . $preferenceId);
            }
            
            if ($usuarioCompra->getEstado() === EstadoCompra::SUCCESS ) {
                throw new NotFoundHttpException('Esta compra ya fue utilizada preferenceId: ' . $preferenceId);
            }
            
            $usuarioPagador = $usuarioCompra->getUsuario();
            error_log("mercadopago_success: Se confirma compra de {$usuarioPagador->getEmail()}. Cantidad: " . $usuarioCompra->getCantidad());
            $telegramService->sendMessage("🤑: Se confirma compra de {$usuarioPagador->getEmail()}. Cantidad: " . $usuarioCompra->getCantidad());
            $usuarioPagador->setCantidadImagenesDisponibles($usuarioPagador->getCantidadImagenesDisponibles()+$usuarioCompra->getCantidad());

            $usuarioCompra->setEstado(EstadoCompra::SUCCESS);
            $this->entityManager->persist($usuarioCompra);
            $this->entityManager->persist($usuarioPagador);
            $this->entityManager->flush();

            return new RedirectResponse('https://myhouseai.com.ar/?status=approved');
        }catch (Exception $exception){
            error_log($exception->getTraceAsString());
            return new RedirectResponse('https://myhouseai.com.ar/?status=failure');
        }
        
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
        try{
            $preferenceId = $request->query->get('preference_id');
            $usuarioCompra =  $this->usuarioComprasRepository->findOneBy(['preferenceId' => $preferenceId]);
            if (!$usuarioCompra) {
                throw new NotFoundHttpException('No se encontró la compra con el preferenceId: ' . $preferenceId);
            }
            
            if ($usuarioCompra->getEstado() === EstadoCompra::SUCCESS ) {
                throw new NotFoundHttpException('Esta compra ya fue utilizada preferenceId: ' . $preferenceId);
            }
            
            $usuarioPagador = $usuarioCompra->getUsuario();
            error_log("mercadopago_success: Se cambia el estado a pending {$usuarioPagador->getEmail()}. Cantidad: " . $usuarioCompra->getCantidad());
            $usuarioPagador->setCantidadImagenesDisponibles($usuarioPagador->getCantidadImagenesDisponibles()+$usuarioCompra->getCantidad());

            $usuarioCompra->setEstado(EstadoCompra::PENDING);
            $this->entityManager->persist($usuarioCompra);
            $this->entityManager->persist($usuarioPagador);
            $this->entityManager->flush();

            return new RedirectResponse('https://myhouseai.com.ar/?status=pending');
        }catch (Exception $exception){
            error_log($exception->getTraceAsString());
            return new RedirectResponse('https://myhouseai.com.ar/?status=failure');
        }
        
    }

    
    #[Route('/webhook', name: 'webhook', methods: ['POST'])]
    public function webhook(Request $request,TelegramService $telegramService): Response
    {
        // Obtén el contenido de la solicitud
        $data = json_decode($request->getContent(), true);

        // Registra los datos para depuración
        $telegramService->sendMessage("WebHook recibido:" . $request->getContent() );
            

        // Procesa la notificación según tus necesidades
        // Ejemplo: verificar el estado del pago, actualizar la base de datos, etc.

        // Responde con un 200 OK para confirmar que recibiste la notificación
        return new Response('Webhook received', Response::HTTP_OK);
    }

    #[Route('/create_preference', name: 'create_preference', methods: ['POST'])]
    public function createPreference(Request $request,ManagerRegistry $doctrine, UsuarioRepository $usuarioRepository,PlanesRepository $planesRepository): Response
    {
        $entityManager = $doctrine->getManager();
        $jwtPayload = $request->attributes->get('jwt_payload');
        $usuario = $usuarioRepository->findOneByEmail($jwtPayload->token_info->email);
    

        $data = json_decode($request->getContent(), true);

        $unPlan = $planesRepository->findOneBy(['cantidad'=> $data['quantity']]);

        if(!$unPlan)
            return new JsonResponse("Plan no encontrado",404);

        $valor = $unPlan->getValor();
        $cantidad = $unPlan->getCantidad();

        // Dividir y redondear a 2 decimales
        $resultado = round($valor / $cantidad, 2);
        
        // Fill the data about the product(s) being pruchased
        $product1 = array(
            //"id" => "1234567890",
            "title" => "Fotos en MyHouseAI",
            "description" => "Fotos en MyHouseAI",
            "currency_id" => "ARS",
            "quantity" => $data['quantity'],
            "unit_price" => $resultado
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

            $usuarioCompra = new UsuarioCompras();
            $usuarioCompra->setUsuario($usuario);
            $usuarioCompra->setCantidad($data['quantity']);
            $usuarioCompra->setMonto($data['unit-price']);
            $usuarioCompra->setMoneda("ARS");
            $usuarioCompra->setFecha(new DateTime());
            $usuarioCompra->setPreferenceId($preference->id);
            $usuarioCompra->setEstado(EstadoCompra::NUEVO);
            $usuarioCompra->setMedioPago("TC");
            $entityManager->persist($usuarioCompra);
            
            $entityManager->flush();            
            // Useful props you could use from this object is 'init_point' (URL to Checkout Pro) or the 'id'



            return new JsonResponse(['id'=>$preference->id]);
        } catch (MPApiException $error) {
            // Here you might return whatever your app needs.
            // We are returning null here as an example.
            return new JsonResponse($error,500);
        }
    }

    #[Route('/notification', name: 'payment_notification', methods: ['POST'])]
    public function handleNotification(Request $request): Response
    {
        // Verifica si la solicitud es una notificación IPN de MercadoPago
        $topic = $request->query->get('topic');
        $id = $request->query->get('id');

        if ($topic === 'payment') {

            $payment = new PaymentClient();
            // Obtiene el pago utilizando el ID recibido
            $payment = $payment->get($id);

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
            "notification_url": "https://myhouseai.com/api/payment/webhook",
            "external_reference" => "FOTOSENMY",
            "expires" => false,
            "auto_return" => 'approved',
        ];

        return $request;
    }
}
