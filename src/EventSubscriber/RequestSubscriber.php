<?php

namespace App\EventSubscriber;

use App\Controller\UsuarioController;
use App\Service\TelegramService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RequestSubscriber implements EventSubscriberInterface
{

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
            ResponseEvent::class => 'onKernelResponse', // Aquí añadimos el evento ResponseEvent
            TerminateEvent::class => 'onKernelTerminate',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route'); // Obtiene el nombre de la ruta
   
        error_log("Request: URL: {$request->getRequestUri()} / QueryParameters: {$request->getQueryString()} / Body {$request->getContent()} ");

        // Guardar tiempo de inicio
        $request->attributes->set('request_start_time', microtime(true));

        // Proteger solo ciertas rutas
        $protectedRoutes = ['generar','historial','process_payment','perfil','payment_controller','create_preference'];
        if (!in_array($routeName, $protectedRoutes)) {
            error_log("La request no esta protegida: " .  $routeName);
            return;
        }
        error_log("La request esta protegida: " .  $routeName);

        $authHeader = $request->headers->get('Token');

        if (!$authHeader) {
            throw new AccessDeniedHttpException('No se encontró el encabezado de autorización.');
        }

        $tokenParts = explode(' ', $authHeader);
        if (count($tokenParts) != 2 || strtolower($tokenParts[0]) != 'bearer') {
            throw new AccessDeniedHttpException('Encabezado de autorización inválido.');
        }

        $tokenJwt = $tokenParts[1];

        // Decodificar el JWT
        try {
            $payload = JWT::decode($tokenJwt, new Key(UsuarioController::SECRET_KEY, 'HS256'));

            // Agregar el payload a la solicitud para que esté disponible en el controlador
            $request->attributes->set('jwt_payload', $payload);
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException('Token inválido o expirado.');
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        $statusCode = $response->getStatusCode();

        // Loguear detalles de la respuesta
        error_log(sprintf(
            "Response para la ruta %s: Status: %d, Content: %s",
            $request->attributes->get('_route'),
            $response->getStatusCode(),
            substr($response->getContent(), 0, 200) // Limitar el contenido para no llenar el log
        ));

            // Si el código de estado es diferente de 200, enviar el log a Telegram
        if ($statusCode !== 200) {
            $message = sprintf(
                "⚠️ Error en la ruta %s: Status: %d, Content: %s",
                $request->attributes->get('_route'),
                $statusCode,
                substr($response->getContent(), 0, 200)
            );

            $this->telegramService->sendMessage($message);
        }
    }
    public function onKernelTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();
        $startTime = $request->attributes->get('request_start_time');
        if ($startTime) {
            $endTime = microtime(true);
            $duration = $endTime - $startTime;
            error_log(sprintf("Tiempo de respuesta para la ruta %s: %f segundos", $request->attributes->get('_route'), $duration));
        }
    }
}
