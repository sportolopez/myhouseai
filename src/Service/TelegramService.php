<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class TelegramService
{
    private const BOT_ID = "7293637587:AAF9cQYXsPlLl5ufJ8YgARydPbuGeTcLhyk";
    private string $apiUrl;
    private const CHAT_ID = '-4539412661'; // Define el chatId como una constante
    

    public function __construct()
    {
        $this->apiUrl = "https://api.telegram.org/bot". self::BOT_ID. "/sendMessage";
    }

    public function sendMessage(string $message): JsonResponse
    {
        // Datos a enviar
        $data = [
            'chat_id' => self::CHAT_ID,
            'text' => $message,
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $context  = stream_context_create($options);

        // Realizar la solicitud a la API de Telegram
        $result = file_get_contents($this->apiUrl, false, $context);

        // Verificar el resultado
        if ($result === FALSE) {
            return new JsonResponse("Error al enviar el mensaje", 500);
        } else {
            return new JsonResponse("Mensaje enviado correctamente", 200);
        }
    }
}