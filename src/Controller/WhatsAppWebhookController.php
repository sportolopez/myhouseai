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
        // Obtén el contenido JSON del webhook
        $content = json_decode($request->getContent(), true);
        $this->telegramService->notificaCionWhatsapp("DEBUG: $content.");
        // Verifica si se recibió un objeto con el campo esperado
        if (isset($content['entry'][0]['changes'][0]['value']['statuses'][0])) {
            $status = $content['entry'][0]['changes'][0]['value']['statuses'][0];

            // Extrae los datos importantes
            $messageId = $status['id'] ?? 'N/A';
            $statusText = $status['status'] ?? 'N/A';
            $timestamp = $status['timestamp'] ?? 'N/A';
            $recipientId = $status['recipient_id'] ?? 'N/A';

            // Define emojis para cada acción
            $emojis = [
                'sent' => '📤',     // Ícono de mensaje enviado
                'delivered' => '✅', // Ícono de mensaje entregado
                'read' => '👀',      // Ícono de mensaje leído
                'failed' => '❌',    // Ícono de error en el envío
                'unknown' => '❓'    // Ícono de estado desconocido
            ];

            // Realiza diferentes acciones según el estado del mensaje
            switch ($statusText) {
                case 'delivered':
                    $this->telegramService->notificaCionWhatsapp("{$emojis['delivered']} Mensaje entregado a $recipientId.");
                    break;

                case 'read':
                    $this->telegramService->notificaCionWhatsapp("{$emojis['read']} Mensaje leído por $recipientId.");
                    break;

                case 'failed':
                    $this->telegramService->notificaCionWhatsapp("{$emojis['failed']} Error en el envío del mensaje a $recipientId.");
                    break;

                default:
                    $this->telegramService->notificaCionWhatsapp("{$emojis['unknown']} Estado del mensaje desconocido: $statusText.");
                    break;
            }

            return new JsonResponse(['message' => 'Webhook procesado correctamente'], 200);
        } else {
            // Loggea un error si no se recibió el contenido esperado
            return new JsonResponse(['error' => 'Estructura inválida'], 400);
        }
    }
}