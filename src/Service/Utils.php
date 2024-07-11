<?php

namespace App\Service;
class Utils
{
    function validate_access_token($access_token)
    {
        // Construir la URL para obtener la información del usuario
        $url = 'https://www.googleapis.com/oauth2/v3/userinfo';

        // Construir el encabezado con el token de acceso
        $headers = [
            'Authorization: Bearer ' . $access_token
        ];

        // Iniciar una sesión cURL
        $ch = curl_init();

        // Configurar las opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud GET
        $response = curl_exec($ch);

        // Obtener el código de estado HTTP de la respuesta
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Cerrar la sesión cURL
        curl_close($ch);

        // Verificar si la solicitud fue exitosa
        if ($http_code == 200) {
            // La solicitud fue exitosa, devolver la información del usuario
            $user_info = json_decode($response, true);
            print ($user_info);
            return $user_info;
        } else {
            // La solicitud no fue exitosa, imprimir el mensaje de error
            echo 'Error al validar el token: ' . $response;
            return null;
        }
    }
}