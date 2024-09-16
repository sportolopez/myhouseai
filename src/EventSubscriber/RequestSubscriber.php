<?php

namespace App\EventSubscriber;

use App\Controller\UsuarioController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
            TerminateEvent::class => 'onKernelTerminate',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route'); // Obtiene el nombre de la ruta

        // Guardar tiempo de inicio
        $request->attributes->set('request_start_time', microtime(true));

        // Proteger solo ciertas rutas
        $protectedRoutes = ['generar','historial','process_payment','perfil','payment_controller','create_preference','send_emails'];
        if (!in_array($routeName, $protectedRoutes)) {
            error_log("La request no esta protegida: " .  $routeName);
            return;
        }
        error_log("La request esta protegida: " .  $routeName);
        error_log("Entro el onKernelRequest.");

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


    public function onKernelTerminate(TerminateEvent $event)
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
