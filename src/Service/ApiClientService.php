<?php

namespace App\Service;

use App\Entity\Imagen;
use App\Entity\Variacion;
use Exception;
use Ramsey\Uuid\Uuid;

class ApiClientService

{
    //const URL_API = "https://api.virtualstagingai.app/";
    const URL_API = "https://7607b2e4-b983-4b42-9a22-052496954763.mock.pstmn.io/";
    
    const URL_IMG = "http://myhouseai.com/api/consultar/";
    const API_KEY = "vsai-pk-4865cd6f-9460-412c-8200-5bf1c9e95843";

    private function executeCurlRequest($url, $method, $postFields = null, $headers = [])
    {

        $headers = ['Authorization: Api-Key ' .self::API_KEY];

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
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception("cURL Error: " . $error );
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        // Verificar si el código de respuesta HTTP es 200
        if ($httpCode !== 200) {
            throw new Exception("Error HTTP: Código de respuesta " . $httpCode . " Url:" . $url);
        }

        $responseObject = json_decode($response);

        // Verificar si hubo un error al decodificar el JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }

        return $responseObject;
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

    public function getPing()
    {
        $url = self::URL_API . 'v1/ping';

        // Ejecuta la solicitud cURL y obtiene la respuesta cruda
        $responseObject = $this->executeCurlRequest($url, 'GET');

        
        // Verifica si hubo un error al decodificar el JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Obtiene el mensaje de error y el JSON crudo que se intentó decodificar
            $errorMsg = 'Error al decodificar JSON: ' . json_last_error_msg();
            $debugInfo = 'JSON que se intentó decodificar: ' . $responseRaw;

            // Lanza una excepción con ambos detalles
            throw new Exception($errorMsg . ' - ' . $debugInfo);
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
