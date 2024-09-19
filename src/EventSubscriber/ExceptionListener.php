<?php

namespace App\EventSubscriber;

use App\Service\TelegramService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener implements EventSubscriberInterface
{
    private $telegramService;

    // Inyecta el servicio TelegramService a través del constructor
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

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
            'stack' => explode("\n", $exception->getTraceAsString()), // Divide el stack trace en un array de líneas
        ];

        // Codifica manualmente el array de respuesta como JSON
        $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE);

        $this->telegramService->sendMessage("ERROR:" . $jsonResponse );
    
        // Establece la respuesta JSON
        $event->setResponse(new JsonResponse($jsonResponse, $response['code'], [], true));
    }
}