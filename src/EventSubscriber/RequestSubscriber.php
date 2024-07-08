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
        $routeName = basename($request->getUri());
    

        // Proteger solo ciertas rutas
        $unProtectedRoutes = ['login','public','generar','historial'];
        if (in_array($routeName, $unProtectedRoutes)) {
            error_log("La request no esta protegida: " .  $routeName);
            return;
        }

        error_log("Entro el onKernelRequest.");

        $request = $event->getRequest();
        //print_r($request->headers->all());
        $authHeader = $request->headers->get('Token');

        if (!$authHeader) {
            throw new AccessDeniedHttpException('No se encontró el encabezado de autorización.' . $routeName);
        }

        $tokenParts = explode(' ', $authHeader);
        if (count($tokenParts) != 2 || strtolower($tokenParts[0]) != 'bearer') {
            throw new AccessDeniedHttpException('Encabezado de autorización inválido.');
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