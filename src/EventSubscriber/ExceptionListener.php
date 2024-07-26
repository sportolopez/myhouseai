<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener 
{
    public static function getSubscribedEvents()
    {
        return [
            ExceptionEvent::class => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        error_log("Entro el onKernelException.");
        // Obtiene la excepción lanzada
        $exception = $event->getThrowable();

        // Construye el array de respuesta
        $response = [
            'error' => $exception->getMessage(),
            'code' => $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500,
        ];

        // Codifica manualmente el array de respuesta como JSON
        $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE);

        // Establece la respuesta JSON
        $event->setResponse(new JsonResponse($jsonResponse, $response['code'], [], true));
    }
}