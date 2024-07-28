<?php

namespace App\Controller;
use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
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

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, UsuarioRepository $usuarioRepository,ManagerRegistry $doctrine): JsonResponse
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
            $usuarioLogueado->setCantidadImagenesDisponibles(10);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($usuarioLogueado);
            $entityManager->flush();
        }
       
        // Ejemplo de uso
        $token_info = $user_info;
        $token_info['userId'] = $usuarioLogueado->getId();
        $secret_key = 'secret_key';
        $payload = array(
            "token_info" => $token_info
        );
    
        $jwt = JWT::encode($payload, $secret_key, 'HS256');
    
        $token =  array(
            'jwt_token' => $jwt,
            'userInfo' => $token_info
        );
        error_log(json_encode($token, JSON_UNESCAPED_SLASHES));

        return new JsonResponse($token,200);
        

    }

    
    #[Route('/perfil', name: '_holamundo_', methods: ['GET'])]
    public function perfil(Request $request, UsuarioRepository $usuarioRepository): JsonResponse
    {
        $jwtPayload = $request->attributes->get('jwt_payload');
        $usuario = $usuarioRepository->findOneBy(['id' => $jwtPayload->token_info->userId]);

        return new JsonResponse([
            'nombre' => $usuario->getNombre(),
            'CantidadImagenesDisponibles' => $usuario->getCantidadImagenesDisponibles(),
        ],200);
    }

    
}