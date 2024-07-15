<?php

namespace App\Service;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Utils
{
    public static function validateAccessToken($accessToken)
    {
        // URL para obtener la información del usuario
        $url = 'https://www.googleapis.com/oauth2/v3/userinfo';

        // Construir el encabezado con el token de acceso
        $headers = [
            'Authorization: Bearer ' . $accessToken
        ];

        // Iniciar una sesión cURL
        $ch = curl_init();

        // Configurar las opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desactivar la verificación del certificado (solo para pruebas)

        // Ejecutar la solicitud GET
        $response = curl_exec($ch);

        // Obtener el código de estado HTTP de la respuesta
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Capturar el error de cURL si ocurre
        if (curl_errno($ch)) {
            $curlError = curl_error($ch);
        }

        // Cerrar la sesión cURL
        curl_close($ch);

        // Verificar si ocurrió un error de cURL
        if (isset($curlError)) {
            throw new \RuntimeException('Error en cURL: ' . $curlError);
        }

        // Verificar si la solicitud fue exitosa
        if ($httpCode == 200) {
            // La solicitud fue exitosa, devolver la información del usuario
            $userInfo = json_decode($response, true);
            return $userInfo;
        } else {
            // Lanzar una excepción adecuada
            throw new UnauthorizedHttpException('Bearer', 'Encabezado de autorización de Google inválido.');
        }
    }

    function check_auth_header($auth_header) {
    
        if (!$auth_header) {
            error_log("No se encontró el encabezado de autorización.");
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'No se encontró el encabezado de autorización.']);
            exit;
        }
    
        $token_parts = explode(' ', $auth_header);
        if (count($token_parts) != 2 || strtolower($token_parts[0]) != 'bearer') {
            error_log("Encabezado de autorización inválido.");
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Encabezado de autorización inválido.....']);
            exit;
        }
    
        $token_jwt = $token_parts[1];
    
        // Decodificar el JWT
        try {
            $payload = JWT::decode($token_jwt, new Key('secret_key', 'HS256'));
            error_log("Token válido: " . json_encode($payload));
            return $payload;
        } catch (Firebase\JWT\ExpiredSignatureException $e) {
            error_log("Token expirado.");
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Token expirado']);
            exit;
        } catch (Firebase\JWT\SignatureInvalidException $e) {
            error_log("Token inválido.");
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }
    }
}