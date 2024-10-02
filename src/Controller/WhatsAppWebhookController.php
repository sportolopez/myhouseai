<?php
namespace App\Controller;
use App\Service\TelegramService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WhatsAppWebhookController extends AbstractController
{

    private $telegramService;

    public function __construct(TelegramService $tc)
    {
        $this->telegramService = $tc;
    }

    #[Route(path: '/webhook/whatsapp', name: 'webhook_whatsapp_get', methods: ['GET'])]
    public function verifyWebhook(Request $request): JsonResponse
    {
        // Parámetros enviados por WhatsApp para la verificación
        $hubMode = $request->query->get('hub.mode');
        $hubChallenge = $request->query->get('hub.challenge');
        $hubVerifyToken = $request->query->get('hub.verify_token');

        // Verifica el token de verificación
        if ($hubVerifyToken === 'EAAPDdEmdFW0BO3EMbEVFoQAJZC10S82UZBYBFOX9EF9Eb9MLWmHXqfDy1tgPPtFgmU6CDQHLLsODizqSlP6uFI17YJMlbQyBX98UYpt4k7oMlutzZCYzJ2KXtkcXuHXxYisVhVFjkse6QWRZCV4DL7aQS5gYpRB680o3ZB3tZAzuMMjSudZAoCRAYWsrNdTv6E1') {
            // Responde con el desafío para confirmar la verificación
            return new JsonResponse(['challenge' => $hubChallenge]);
        }

        // Si el token no coincide, responde con un error
        return new JsonResponse(['error' => 'Token de verificación inválido'], 403);
    }

    #[Route(path: '/webhook/whatsapp', name: 'webhook_whatsapp', methods: ['POST'])]
    public function receiveWhatsAppMessage(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        // Verifica si el payload contiene un mensaje
        if (isset($content['messages']) && count($content['messages']) > 0) {
            $messageData = $content['messages'][0];
            $messageText = $messageData['text']['body'] ?? '';
            $from = $messageData['from'] ?? '';

            // Enviar mensaje a Telegram
            $this->telegramService->sendMessage("💬 Nuevo mensaje de WhatsApp de {$from}: {$messageText}");
        }

        return new JsonResponse(['status' => 'ok']);
    }
}