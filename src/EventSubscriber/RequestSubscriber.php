<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class RequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
            ControllerEvent::class => 'onKernelController'
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route'); // Obtiene el nombre de la ruta


        // Proteger solo ciertas rutas
        $protectedRoutes = ['generar','historial','process_payment','perfil','payment_controller','create_preference','status'];
        if (!in_array($routeName, $protectedRoutes)) {
            error_log("La request no esta protegida: " .  $routeName);
            return;
        }
        error_log("La request esta protegida: " .  $routeName);
        error_log("Entro el onKernelRequest.");

        $request = $event->getRequest();
        //print_r($request->headers->all());
        $authHeader = $request->headers->get('Token');

        if (!$authHeader) {
            throw new AccessDeniedHttpException('No se encontró el encabezado de autorización.');
        }

        $tokenParts = explode(' ', $authHeader);
        if (count($tokenParts) != 2 || strtolower($tokenParts[0]) != 'bearer') {
            throw new AccessDeniedHttpException('Encabezado de autorización inválid.');
        }

        $tokenJwt = $tokenParts[1];

        // Decodificar el JWT
        try {
            $payload = JWT::decode($tokenJwt, new Key('secret_key', 'HS256'));

            // Agregar el payload a la solicitud para que esté disponible en el controlador
            $request->attributes->set('jwt_payload', $payload);
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException('Token inválido o expirado.');
        }

    }

    public function onKernelController(ControllerEvent $event)
    {
        error_log("Entro el onKernelController.");



    }

}