<?php
namespace App\Controller;

use App\Service\TelegramService;
use App\Service\WhatsAppService;
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
    #[Route(path: '/webhook/telegram', name: 'webhook_telegram_GET', methods: ['GET'])]
    public function telegramGet(Request $request): JsonResponse
    { 
        return new JsonResponse(['msj' => 'Funciona'], 200);
    }
    #[Route(path: '/webhook/telegram', name: 'webhook_telegram', methods: ['POST'])]
    public function receiveTelegramResponse(Request $request, WhatsAppService $whatsAppService): JsonResponse
    {
        $content = json_decode($request->getContent(), associative: true);
        try{
        // Verifica si es una respuesta a un mensaje previo
            if (isset($content['message']['reply_to_message'])) {
                $responseText = $content['message']['text'] ?? '';
                $originalMessage = $content['message']['reply_to_message']['text'] ?? '';
        
                // Extraer el número de teléfono del mensaje original
                $matches = [];
                preg_match('/\((\d+)\)/', $originalMessage, $matches);
                $whatsappNumber = $matches[1] ?? null;
        
                if ($whatsappNumber) {
                    // Enviar el mensaje de respuesta a través de WhatsApp
                    
                    $this->telegramService->notificaCionWhatsapp("Se intenta enviar a {$whatsappNumber} el mensaje {$responseText} ");
                    return $whatsAppService->sendWhatsAppMessage($whatsappNumber,$responseText);
                } else {
                    return new JsonResponse(['error' => 'No se pudo extraer el número de teléfono del mensaje original'], status: 200);
                }
            }
        }catch(\Exception $e){
            $this->telegramService->notificaCionWhatsapp("Error al enviar a {$whatsappNumber} el mensaje {$responseText} : " . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], status: 200);
        }
        $this->telegramService->notificaCionWhatsapp("CasoNoContemplado: " . $request->getContent());
        return new JsonResponse(['error' => 'Estructura de mensaje inválida o no es una respuesta'], 200);
    }
}
