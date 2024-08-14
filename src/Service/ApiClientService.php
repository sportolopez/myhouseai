<?php

namespace App\Service;

use App\Entity\Imagen;
use App\Entity\Variacion;
use Exception;
use Ramsey\Uuid\Uuid;

class ApiClientService
{
    const URL_API = "https://7607b2e4-b983-4b42-9a22-052496954763.mock.pstmn.io/";
    const URL_IMG = "http://myhouseai.com/api/consultar/";
    const API_KEY = "23423423423";

    private function executeCurlRequest($url, $method, $postFields = null, $headers = [])
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $headers),
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception("cURL Error: " . $error);
        }

        curl_close($curl);
        return json_decode($response);
    }

    public function generarImagen(Imagen $imagen)
    {
        $imageUrl = self::URL_IMG . $imagen->getId();
        $postFields = json_encode([
            'image_url' => $imageUrl,
            'room_type' => $imagen->getTipoHabitacion(),
            'style' => $imagen->getEstilo()
        ]);

        $responseObject = $this->executeCurlRequest(self::URL_API . 'v1/render/create', 'POST', $postFields);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }

        return $responseObject->render_id ?? null;
    }

    public function getRender(Imagen $imagen)
    {
        $queryParams = [
            'room_type' => $imagen->getRenderId()
        ];
        $url = self::URL_API . 'v1/render?' . http_build_query($queryParams);

        $responseObject = $this->executeCurlRequest($url, 'GET');

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }

        return $responseObject;
    }

    public function crearVariacionParaRender(String $renderId, String $roomType, String $style, bool $waitForCompletion = false, bool $addWatermark = false, bool $switchToQueuedImmediately = true)
    {
        $postFields = json_encode([
            'wait_for_completion' => '$waitForCompletion',
            'roomType' => $roomType,
            'style' => $style,
            'add_virtually_staged_watermark' => $addWatermark,
            'switch_to_queued_immediately' => $switchToQueuedImmediately,
        ]);

        $url = self::URL_API . 'v1/render/create-variation?render_id=' . urlencode($renderId);
        $headers = ['Authorization: Api-Key ' .self::API_KEY];

        $responseObject = $this->executeCurlRequest($url, 'POST', $postFields, $headers);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }

    }

}
