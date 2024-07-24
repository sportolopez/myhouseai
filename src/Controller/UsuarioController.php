<?php

namespace App\Controller;
use App\Repository\UsuarioRepository;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Exceptions\MPApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UsuarioController extends AbstractController{

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