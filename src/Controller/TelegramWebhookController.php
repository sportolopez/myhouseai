<?php
namespace App\Controller;

use App\Service\TelegramService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TelegramWebhookController extends AbstractController
{
    private $telegramService;
    private $logger;

    public function __construct(TelegramService $tc, LoggerInterface $logger)
    {
        $this->telegramService = $tc;
        $this->logger = $logger;
    }

    #[Route(path: '/webhook/telegram', name: 'webhook_telegram', methods: ['POST'])]
    public function receiveTelegramMessage(Request $request): JsonResponse
    {
        // Obtener el contenido JSON del webhook
        $content = json_decode($request->getContent(), true);

        // Validar que se haya recibido el mensaje correctamente
        if (isset($content['message'])) {
            $chatId = $content['message']['chat']['id'];
            $text = $content['message']['text'] ?? 'Mensaje sin texto';
            $username = $content['message']['from']['username'] ?? 'Usuario desconocido';

            // Escribir en el log
            $this->logger->info('Mensaje recibido de Telegram', [
                'chat_id' => $chatId,
                'username' => $username,
                'text' => $text,
            ]);

            // Aquí podrías agregar lógica para manejar el mensaje, responder, etc.

            return new JsonResponse(['status' => 'success'], 200);
        }

        // En caso de que no haya un mensaje válido en el webhook
        $this->logger->error('Webhook recibido sin mensaje válido');
        return new JsonResponse(['status' => 'error', 'message' => 'Invalid request'], 400);
    }
}
