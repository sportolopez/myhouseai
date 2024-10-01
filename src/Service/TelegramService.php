<?php
namespace App\Service;

class TelegramService
{
    private const BOT_ID = "7293637587:AAF9cQYXsPlLl5ufJ8YgARydPbuGeTcLhyk";
    private string $apiUrl;
    private $chatID;
    

    public function __construct()
    {
        $this->apiUrl = "https://api.telegram.org/bot". self::BOT_ID. "/sendMessage";
        $this->chatID  = $_ENV['CHAT_ID'];
    }

    public function sendMessage(string $message)
    {
        // Datos a enviar
        $data = [
            'chat_id' => $this->chatID,
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
            error_log("Telegram service: Error al enviar mensaje: " . $message);
        } 
    }
}
