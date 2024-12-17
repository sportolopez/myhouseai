<?php
namespace App\Service;

class TelegramService
{
    private const BOT_ID = "7293637587:AAF9cQYXsPlLl5ufJ8YgARydPbuGeTcLhyk";
    private string $apiUrl;
    private $chatID;
    
    private const CHAT_ID_WHATSAPP = "-4590360358";
    private const CHAT_ID_LECTURA = "-4574469813";
    private const CHAT_DEV_ID = "-4585899489";

    public function __construct()
    {
        $this->apiUrl = "https://api.telegram.org/bot". self::BOT_ID. "/sendMessage";
        $this->chatID  = $_ENV['CHAT_ID'];
    }

    public function sendMessage(string $message)
    {
        $this->sendMessageAgrupo($message,$this->chatID);
    }

    private function sendMessageAgrupo(string $message, string $idGrupo){
        $data = [
            'chat_id' => $idGrupo,
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

    public function notificaLectura(string $message)
    {
        $this->sendMessageAgrupo($message,self::CHAT_ID_LECTURA);
    }

    public function notificaLecturaDev(string $message)
    {
        $this->sendMessageAgrupo($message,self::CHAT_DEV_ID);
    }

    public function notificaCionWhatsapp(string $message)
    {
        $this->sendMessageAgrupo($message,self::CHAT_ID_WHATSAPP);
    }
}
