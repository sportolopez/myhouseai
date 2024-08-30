<?php

namespace App\Service;

use App\Entity\Imagen;
use App\Entity\Variacion;
use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiClientService

{
    //const URL_API = "https://api.virtualstagingai.app/";
    const URL_API = "https://7607b2e4-b983-4b42-9a22-052496954763.mock.pstmn.io/";
    
    const URL_IMG = "https://myhouseai.com/api/consultar/";
    const API_KEY = "vsai-pk-4865cd6f-9460-412c-8200-5bf1c9e95843";

    private function executeCurlRequest($url, $method, $postFields = null, $headers = [])
    {
        $headers = ['Authorization: Api-Key ' . self::API_KEY, 'Content-Type: application/json'];

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

        // Capturar el tiempo inicial
        $startTime = microtime(true);

        $response = curl_exec($curl);

        // Capturar el tiempo final
        $endTime = microtime(true);

        // Calcular el tiempo de respuesta
        $responseTime = $endTime - $startTime;

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception("cURL Error: " . $error);
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Registrar la solicitud, la respuesta y el tiempo de respuesta
        $this->logRequestResponse($url, $method, $postFields, $headers, $httpCode, $response, $responseTime);

        curl_close($curl);

        // Verificar si el código de respuesta HTTP es 200
        if ($httpCode !== 200) {
            throw new HttpException($httpCode, $response);
        }

        $responseObject = json_decode($response);

        // Verificar si hubo un error al decodificar el JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }

        return $responseObject;
    }


    public function generarImagen(Imagen $imagen, String $declutter_mode)
    {
        $imageUrl = self::URL_IMG . $imagen->getId() . ".png";
        $postFields = json_encode([
            'image_url' => $imageUrl,
            'room_type' => $imagen->getTipoHabitacion(),
            'wait_for_completion' => false,
            'declutter_mode' => $declutter_mode,
            'style' => $imagen->getEstilo()
        ], JSON_UNESCAPED_SLASHES);

        $responseObject = $this->executeCurlRequest(self::URL_API . 'v1/render/create', 'POST', $postFields);

        if(!$responseObject->render_id)
            throw new Exception('No tiene render ID: ' . print_r($responseObject, true));

        return $responseObject->render_id;
    }

    public function getRender(Imagen $imagen)
    {
        $queryParams = [
            'render_id' => $imagen->getRenderId()
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



        return $responseObject;
    }

    public function crearVariacionParaRender(String $renderId, String $roomType, String $style, bool $waitForCompletion = false, bool $addWatermark = false, bool $switchToQueuedImmediately = true)
    {
        $postFields = json_encode([
            'wait_for_completion' => $waitForCompletion,
            'roomType' => $roomType,
            'style' => $style,
            'add_virtually_staged_watermark' => $addWatermark,
            'switch_to_queued_immediately' => $switchToQueuedImmediately,
        ]);

        $url = self::URL_API . 'v1/render/create-variation?render_id=' . urlencode($renderId);
        $headers = ['Authorization: Api-Key ' .self::API_KEY];

        $responseObject = $this->executeCurlRequest($url, 'POST', $postFields, $headers);

        return $responseObject;

    }

    private function logRequestResponse($url, $method, $postFields, $headers, $httpCode, $response, $responseTime)
    {
        // Crear el mensaje de log
        $logMessage = sprintf(
            "URL: %s\nMétodo: %s\nEncabezados: %s\nDatos de Solicitud: %s\nCódigo HTTP: %d\nRespuesta: %s\nTiempo de Respuesta API: %f segundos\n\n",
            $url,
            $method,
            json_encode($headers),
            json_encode($postFields),
            $httpCode,
            $response,
            $responseTime
        );
    
        // Escribir el mensaje en el archivo de log de errores
        error_log($logMessage);
    }
}
