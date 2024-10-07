<?php
namespace App\Controller;
use App\Service\TelegramService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WhatsAppWebhookController extends AbstractController
{

    private $telegramService;

    public function __construct(TelegramService $tc)
    {
        $this->telegramService = $tc;
    }

    #[Route(path: '/webhook/whatsapp', name: 'webhook_whatsapp_get', methods: ['GET'])]
    public function verifyWebhook(Request $request): Response
    {
        $verifyToken = 'mitocken';

        $mode = $request->query->get('hub_mode');
        $token = $request->query->get('hub_verify_token');
        $challenge = $request->query->get('hub_challenge');

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === $verifyToken) {
                // Verificación exitosa
                return new Response($challenge, 200);
            } else {
                // Verificación fallida
                return new Response('Invalid token', 403);
            }
        }

        return new Response('Bad request mode:'. $mode, 400);
    }

    #[Route(path: '/webhook/whatsapp', name: 'webhook_whatsapp', methods: ['POST'])]
    public function receiveWhatsAppMessage(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $this->telegramService->notificaCionWhatsapp("DEBUG: WebHook WP recibido: " . $request->getContent());
    
        // Verificar si es un mensaje nuevo o enviado
        if (isset($content['messages']) && count($content['messages']) > 0) {
            foreach ($content['messages'] as $messageData) {
                $messageText = $messageData['text']['body'] ?? '';
                $from = $messageData['from'] ?? '';
    
                // Detectar si el mensaje fue enviado por ti
                if ($messageData['type'] === 'sent') {
                    $this->telegramService->notificaCionWhatsapp("📤 Mensaje enviado por ti a {$from}: {$messageText}");
                } else {
                    // Enviar mensaje a Telegram cuando es recibido
                    $this->telegramService->notificaCionWhatsapp("💬 Nuevo mensaje de WhatsApp de {$from}: {$messageText}");
                }
            }
        }
    
        // Verificar si el mensaje fue leído
        if (isset($content['statuses']) && count($content['statuses']) > 0) {
            foreach ($content['statuses'] as $statusData) {
                if ($statusData['status'] === 'read') {
                    $this->telegramService->notificaCionWhatsapp("👁️ Mensaje leído por {$statusData['recipient_id']}");
                }
            }
        }
    
        return new JsonResponse(['status' => 'ok']);
    }
}