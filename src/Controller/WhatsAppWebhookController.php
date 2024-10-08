<?php

namespace App\Controller;
use App\Service\TelegramService;
use App\Service\WhatsAppService;
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
        // Obtén el contenido JSON del webhook
        $content = json_decode($request->getContent(), true);

    
        // Verifica si el contenido tiene un mensaje o un estado de mensaje
        if ($this->isValidMessage($content)) {
            $this->processIncomingMessage($content);
            return new JsonResponse(['message' => 'Mensaje recibido y procesado correctamente'], 200);
        } elseif ($this->isValidStatus($content)) {
            $this->processMessageStatus($content);
            return new JsonResponse(['message' => 'Estado del mensaje procesado correctamente'], 200);
        }
        $this->telegramService->notificaCionWhatsapp("CasoNoContemplado: " . $request->getContent());
        // Loggea un error si no se recibió el contenido esperado
        return new JsonResponse(['error' => 'Estructura inválida'], 200);
    }
    
    // Verifica si el contenido contiene un mensaje válido
    private function isValidMessage(array $content): bool
    {
        return isset($content['entry'][0]['changes'][0]['value']['messages'][0]);
    }
    
    // Verifica si el contenido contiene un estado de mensaje válido
    private function isValidStatus(array $content): bool
    {
        return isset($content['entry'][0]['changes'][0]['value']['statuses'][0]);
    }
    
    // Procesa el mensaje entrante y notifica a Telegram
    private function processIncomingMessage(array $content): void
    {
        $message = $content['entry'][0]['changes'][0]['value']['messages'][0];
        $contact = $content['entry'][0]['changes'][0]['value']['contacts'][0];
    
        $from = $message['from'] ?? 'N/A';
        $text = $message['text']['body'] ?? 'N/A';
        $contactName = $contact['profile']['name'] ?? 'N/A';
        $contactNumber = $contact['wa_id'] ?? 'N/A';
    
        // Notifica el mensaje recibido por Telegram
        $this->notifyTelegram("📩 Nuevo mensaje de $contactName ($contactNumber): \"$text\"");
    }
    
    // Procesa el estado de mensaje y notifica a Telegram
    private function processMessageStatus(array $content): void
    {
        $status = $content['entry'][0]['changes'][0]['value']['statuses'][0];
        $statusText = $status['status'] ?? 'N/A';
        $recipientId = $status['recipient_id'] ?? 'N/A';
    
        // Define emojis para cada acción
        $emojis = [
            'sent' => '📤',
            'delivered' => '✅',
            'read' => '👀',
            'failed' => '❌',
            'unknown' => '❓'
        ];
    
        // Realiza diferentes acciones según el estado del mensaje
        $message = match ($statusText) {
            'sent' => null, // No se envía notificación para "sent"
            'delivered' => "{$emojis['delivered']} Mensaje entregado a $recipientId.",
            'read' => "{$emojis['read']} Mensaje leído por $recipientId.",
            'failed' => "{$emojis['failed']} Error en el envío del mensaje a $recipientId.",
            default => "{$emojis['unknown']} Estado del mensaje desconocido: $statusText.",
        };
    
        if ($message) {
            $this->notifyTelegram($message);
        }
    }
    
    // Notifica a Telegram
    private function notifyTelegram(string $message): void
    {
        $this->telegramService->notificaCionWhatsapp($message);
    }
}