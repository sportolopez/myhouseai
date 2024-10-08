<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;

class WhatsAppService
{
    private $apiVersion;
    private $accessToken;
    private $phoneNumberId;

    public function __construct()
    {
        $this->apiVersion =  $_ENV['API_VERSION'];
        $this->accessToken =  $_ENV['WP_ACCESS_TOKEN'];
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
            $content = $response->toArray();

            // Verifica si la respuesta fue exitosa
            if ($statusCode === 200) {
                return new JsonResponse(['message' => 'Mensaje enviado correctamente a WhatsApp'], 200);
            } else {
                return new JsonResponse(['error' => 'Error enviando mensaje a WhatsApp', 'details' => $content], $statusCode);
            }
        } catch (\Exception $e) {
            // Captura errores en la solicitud
            return new JsonResponse(['error' => 'Error enviando mensaje a WhatsApp', 'details' => $e->getMessage()], 500);
        }
    }
}
