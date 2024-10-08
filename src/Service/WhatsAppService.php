<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;  // Importa el Logger

class WhatsAppService
{
    private $apiVersion;
    private $accessToken;
    private $phoneNumberId;
    private $telegramService;
    
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
        $this->apiVersion = $_ENV['API_VERSION'];
        $this->accessToken = $_ENV['WP_ACCESS_TOKEN'];
        $this->phoneNumberId = $_ENV['ID_PHONE_NUMB'];
    }

    /**
     * Envía un mensaje de texto a través de la API de WhatsApp.
     *
     * @param string $phoneNumber Número de teléfono del destinatario en formato internacional (Ej: 541162198358).
     * @param string $messageText Texto del mensaje a enviar.
     * @return JsonResponse
     */
    public function sendWhatsAppMessage(string $phoneNumber, string $messageText): JsonResponse
    {
        // URL de la API de WhatsApp con la versión parametrizada
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";
        if (preg_match('/^5491162198358|5491157634406$/', $phoneNumber)) {
            $phoneNumber = substr($phoneNumber, 0, 2) . substr($phoneNumber, 3);
        }
        // Cuerpo de la solicitud
        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'text',
            'text' => [
                'body' => $messageText,
            ],
        ];

        // Headers de la solicitud
        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ];

        // Loguear headers y body antes de hacer la solicitud
        error_log('Enviando solicitud a WhatsApp API' . json_encode([
            'url' => $url,
            'headers' => $headers,
            'body' => $body
        ]));

        // Crea un cliente HTTP
        $client = HttpClient::create();

        try {
            // Realiza la solicitud POST
            $response = $client->request('POST', $url, [
                'headers' => $headers,
                'json' => $body,
            ]);

            // Obtener el contenido de la respuesta
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false); // Evita que se lance la excepción
            $decodedContent = json_decode($content, true); // Decodificar JSON de la respuesta

            // Loguear la respuesta
            error_log('Respuesta de WhatsApp API: ' . json_encode([
                'status_code' => $statusCode,
                'response' => $decodedContent
            ]));

            // Verifica si la respuesta fue exitosa
            if ($statusCode === 200) {
                return new JsonResponse(['message' => 'Mensaje enviado correctamente a WhatsApp'], 200);
            } else {
                $this->telegramService->sendMessage('Error enviando mensaje a WhatsApp:' . $content);
                return new JsonResponse(['error' => 'Error enviando mensaje a WhatsApp', 'details' => $content], 200);
            }
        } catch (\Exception $e) {
            // Captura errores en la solicitud
            error_log('Error en la solicitud a WhatsApp API' . $decodedContent);
            $this->telegramService->sendMessage('Error enviando mensaje a WhatsApp: ' . $content);
            return new JsonResponse(['error' => 'Error enviando mensaje a WhatsApp', 'details' => $e->getMessage()], 200);
        }
    }
}
