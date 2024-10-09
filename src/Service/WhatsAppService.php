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
     * Envía un mensaje de texto o template a través de la API de WhatsApp.
     *
     * @param string $phoneNumber Número de teléfono del destinatario en formato internacional.
     * @param array $body Cuerpo de la solicitud.
     * @return JsonResponse
     */
    private function sendRequest(string $phoneNumber, array $body): JsonResponse
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";
        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ];

        // Quitar "9" si el número coincide con los patrones definidos
        if ($this->shouldRemoveNine($phoneNumber)) {
            $phoneNumber = $this->removeNineFromPhoneNumber($phoneNumber);
        }

        // Loguear la solicitud
        error_log('Enviando solicitud a WhatsApp API' . json_encode([
            'url' => $url,
            'headers' => $headers,
            'body' => $body
        ]));

        $client = HttpClient::create();

        try {
            $response = $client->request('POST', $url, [
                'headers' => $headers,
                'json' => $body,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);  // Obtener contenido sin lanzar excepción
            $decodedContent = json_decode($content, true);  // Decodificar respuesta

            // Loguear la respuesta
            error_log('Respuesta de WhatsApp API: ' . json_encode([
                'status_code' => $statusCode,
                'response' => $decodedContent
            ]));

            if ($statusCode === 200) {
                return new JsonResponse(['message' => 'Mensaje enviado correctamente a WhatsApp'], 200);
            } else {
                $this->logAndNotifyError($content);
                throw new \RuntimeException('Error enviando mensaje a WhatsApp: ' . $content);
            }

        } catch (\Exception $e) {
            // Captura y maneja errores
            $this->logAndNotifyError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Envía un mensaje de texto a través de la API de WhatsApp.
     *
     * @param string $phoneNumber Número de teléfono del destinatario.
     * @param string $message Texto del mensaje.
     * @return JsonResponse
     */
    public function sendWhatsAppMessage(string $phoneNumber, string $message): JsonResponse
    {
        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];

        return $this->sendRequest($phoneNumber, $body);
    }

    /**
     * Envía un mensaje con un template a través de la API de WhatsApp.
     *
     * @param string $phoneNumber Número de teléfono del destinatario.
     * @return JsonResponse
     */
    public function sendWhatsAppTemplate(string $phoneNumber): JsonResponse
    {
        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'template',
            'template' => [
                'name' => 'hello_world',  // Nombre del template
                'language' => [
                    'code' => 'en_US'  // Código de idioma
                ]
            ]
        ];

        return $this->sendRequest($phoneNumber, $body);
    }

    /**
     * Verifica si se debe eliminar el "9" del número de teléfono.
     *
     * @param string $phoneNumber Número de teléfono.
     * @return bool
     */
    private function shouldRemoveNine(string $phoneNumber): bool
    {
        return preg_match('/^5491162198358|5491157634406$/', $phoneNumber);
    }

    /**
     * Elimina el "9" en la tercera posición del número de teléfono.
     *
     * @param string $phoneNumber Número de teléfono.
     * @return string
     */
    private function removeNineFromPhoneNumber(string $phoneNumber): string
    {
        return substr($phoneNumber, 0, 2) . substr($phoneNumber, 3);
    }

    /**
     * Loguea y envía un mensaje de error a través del servicio de Telegram.
     *
     * @param string $errorMessage Mensaje de error.
     */
    private function logAndNotifyError(string $errorMessage): void
    {
        error_log('Error en la solicitud a WhatsApp API: ' . $errorMessage);
        $this->telegramService->sendMessage('Error enviando mensaje a WhatsApp: ' . $errorMessage);
    }
}
