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