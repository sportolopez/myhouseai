<?php

namespace App\Controller;
use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use App\Service\EncryptionService;
use App\Service\TelegramService;
use App\Service\Utils;
use Firebase\JWT\JWT;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Exceptions\MPApiException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UsuarioController extends AbstractController{

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, UsuarioRepository $usuarioRepository,ManagerRegistry $doctrine, TelegramService $telegramService): JsonResponse
    {
        
        $data = json_decode($request->getContent(), true);
        $accessToken = $data['access_token'] ?? null;

        // Implementa la lógica de validación del token aquí
        
        $user_info = Utils::validateAccessToken($accessToken);

        $usuarioLogueado = $usuarioRepository->findOneByEmail($user_info['email']);

        if(!$usuarioLogueado){
            $usuarioLogueado = new Usuario();
            $usuarioLogueado->setEmail($user_info['email']);
            $usuarioLogueado->setNombre($user_info['name']);
            $usuarioLogueado->setCantidadImagenesDisponibles(0);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($usuarioLogueado);
            $entityManager->flush();
        }
       
        // Ejemplo de uso
        $token_info = $user_info;
        $token_info['userId'] = $usuarioLogueado->getId();
        $token_info['cantidadImagenesDisponibles'] = $usuarioLogueado->getCantidadImagenesDisponibles();
        $secret_key = 'secret_key';
        $payload = array(
            "token_info" => $token_info
        );
    
        $jwt = JWT::encode($payload, $secret_key, 'HS256');
    
        $token =  array(
            'jwt_token' => $jwt,
            'userInfo' => $token_info
        );
        //error_log(json_encode($token, JSON_UNESCAPED_SLASHES));
        $telegramService->sendMessage("Login exitoso: {$user_info['email']}");
                
        return new JsonResponse($token,200);
        

    }

    #[Route('/encriptar', name: 'encriptar_get', methods: ['GET'])]
    public function encriptar(Request $request, EncryptionService $encryptionService): JsonResponse
    {
        $sessionHash = $request->query->get('session');

        if (!$sessionHash) {
            return new JsonResponse(['error' => 'Session hash missing'], 400);
        }

        $decryptedData = $encryptionService->encrypt($sessionHash);
        error_log("Encrip $decryptedData");
        return new JsonResponse($decryptedData, 200);

    }

    #[Route('/login_mail', name: 'login_mail', methods: ['POST'])]
public function loginGet(Request $request, UsuarioRepository $usuarioRepository, ManagerRegistry $doctrine, TelegramService $telegramService, EncryptionService $encryptionService): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $sessionHash = $data['session'];
    
        
    if (!$sessionHash) {
        return new JsonResponse(['error' => 'Session hash missing'], 400);
    }

    // Desencriptar el hash usando el servicio de encriptación
    $userEmail = $encryptionService->decrypt($sessionHash);

    if (!$userEmail) {
        return new JsonResponse(['error' => 'Invalid session hash'], 400);
    }

    // Buscar al usuario en la base de datos
    $usuarioLogueado = $usuarioRepository->findOneByEmail($userEmail);

    if (!$usuarioLogueado || $usuarioLogueado->getEmail() !== $userEmail) {
        return new JsonResponse(['error' => 'Invalid user'], 404);
    }

    // Preparar el token JWT como en el método original
    $token_info = [
        'userId' => $usuarioLogueado->getId(),
        'email' => $usuarioLogueado->getEmail(),
        'cantidadImagenesDisponibles' => $usuarioLogueado->getCantidadImagenesDisponibles(),
    ];

    $payload = [
        'token_info' => $token_info,
    ];

    $jwt = JWT::encode($payload, $encryptionService->encrypt('some_jwt_secret_key'), 'HS256');

    $token = [
        'jwt_token' => $jwt,
        'userInfo' => $token_info,
    ];

    // Enviar notificación por Telegram
    $telegramService->sendMessage("Login exitoso por GET: {$usuarioLogueado->getEmail()}");

    // Retornar el token
    return new JsonResponse($token, 200);
}

    
    #[Route('/perfil', name: 'perfil', methods: ['GET'])]
    public function perfil(Request $request, UsuarioRepository $usuarioRepository): JsonResponse
    {
        $jwtPayload = $request->attributes->get('jwt_payload');
        $usuario = $usuarioRepository->findOneByEmail($jwtPayload->token_info->email);

        return new JsonResponse([
            'nombre' => $usuario->getNombre(),
            'CantidadImagenesDisponibles' => $usuario->getCantidadImagenesDisponibles(),
        ],200);
    }

    
}