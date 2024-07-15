<?php

namespace App\Controller;

use App\Entity\Variacion;
use App\Repository\ImagenRepository;
use App\Repository\UsuarioRepository;
use App\Repository\VariacionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Firebase\JWT\JWT;
use Ramsey\Uuid\Uuid;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Imagen;
use DateTime;
use App\Entity\Usuario;
use App\Service\ApiClientService;
use App\Service\Utils;
class DefaultController extends AbstractController
{

    #[Route('/{any}', name: 'app_options', requirements: ['any' => '.*'], methods: ['OPTIONS'])]
    public function options(): JsonResponse
    {
        $response = new JsonResponse();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        return $response;
    }
    

    

    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        error_reporting(E_ALL ^ E_DEPRECATED);
        return new Response('<html><body>Hola Mundoaaaaaaaaaa</body></html>');
    }

    #[Route('/historial', name: 'app_historial', methods: ['GET'])]
    public function historial(ManagerRegistry $doctrine): Response
    {
        // Implementa la lógica de obtención del historial aquí
        
        $entityManager = $doctrine->getManager();
        
        $imagenes = $entityManager->getRepository(Imagen::class)->findByUsuarioId(1);
        $url = "https://comomequeda.com.ar/myhouseai/public/consultar/";

        $imagenesArray = [];
        foreach ($imagenes as $imagen) {
            $imagenesArray[] = [
                'id' => $url . $imagen->getId() . ".png"
            ];
        }
        
        $jsonResponse = json_encode($imagenesArray, JSON_UNESCAPED_SLASHES);
        
        return new Response($jsonResponse, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        
        $data = json_decode($request->getContent(), true);
        error_log("entro al login");
        $accessToken = $data['access_token'] ?? null;

        // Implementa la lógica de validación del token aquí
        
        $user_info = Utils::validateAccessToken($accessToken);


        // Ejemplo de uso
        $token_info = $user_info;

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

    #[Route('/generar', name: 'app_generar', methods: ['POST'])]
    public function generar(ManagerRegistry $doctrine,Request $request, UsuarioRepository $usuarioRepository, ApiClientService $apiClientService, VariacionRepository $variacionRepository): JsonResponse
    {
       
        // Obtener los datos de la solicitud
        $data = json_decode($request->getContent(), true);

        if (!isset($data['image'])) {
            return new JsonResponse(['error' => 'No image provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $base64Image = $data['image'];
        
        // Decodificar la imagen base64
        $imageData = base64_decode($base64Image);
        if ($imageData === false) {
            return new JsonResponse(['error' => 'Invalid base64 image data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Generar un UUID para el nombre de la imagen
        $uuid = Uuid::uuid4()->toString();
        
        $entityManager = $doctrine->getManager();
   

        $usuario = $usuarioRepository->find(1);

        
        $imagen = new Imagen();
        $imagen->setId($uuid);
        $imagen->setImgOrigen($imageData);
        $imagen->setUsuario($usuario);
        $imagen->setEstilo($data['theme']);
        $imagen->setTipoHabitacion($data['room_type']);
        $imagen->setFecha( new DateTime());
        $entityManager->persist($imagen);
        $entityManager->flush();

        $variacion = $apiClientService->generarImagen($imagen);

        
        $entityManager->persist($variacion);
       
        $usuario->setCantidadImagenesDisponibles($usuario->getCantidadImagenesDisponibles()-1);

        $entityManager->persist($usuario);

        $entityManager->flush();

        return new JsonResponse(['generation_id' => $variacion->getId(),'cantidad_imagenes_disponibles' => $usuario->getCantidadImagenesDisponibles()], JsonResponse::HTTP_OK);
    }

    #[Route('/status/{uuid}', name: 'homepage')]
    public function status(string $uuid, ImagenRepository $imagenRepository): JsonResponse
    {
        /*
        $imagen = $imagenRepository->find($uuid);

        if (!$imagen) {
            return new Response('Image not found', Response::HTTP_NOT_FOUND);
        }
        */
        $response = [
            "render_id" => $uuid,
            "status" => "rendering",
            "created_at" => 1685742540902, // epoch timestamp
            "outputs" => [], // will contain output image urls if status == "done". Will an entry for each new variation.
            "progress" => 0.7432000000000001, // number 0-1
            "outputs_room_types" => [],
            "outputs_styles"=> []
        ];
        return new JsonResponse($response, 200);;
    }

    #[Route('/consultar/{uuid}.png', name: 'app_consultar', methods: ['GET'])]
    public function consultar(string $uuid, ManagerRegistry $doctrine): Response
    {
        $imagen = $doctrine->getRepository(Imagen::class)->find($uuid);

        if (!$imagen) {
            return new Response('Image not found', Response::HTTP_NOT_FOUND);
        }

        $imageResource = $imagen->getImgOrigen();
        $imageData = stream_get_contents($imageResource);
        $response = new Response($imageData);
        $response->headers->set('Content-Type', 'image/jpeg'); // Ajusta el tipo MIME según el formato de tu imagen

        return $response;
    }

    #[Route('/process_payment', name: 'create_payment', methods: ['POST'])]
    public function createPayment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validar y procesar los datos recibidos
        $transactionAmount = $data['transaction_amount'] ?? null;
        $token = $data['token'] ?? null;
        $installments = $data['installments'] ?? null;
        $paymentMethodId = $data['payment_method_id'] ?? null;
        $issuerId = $data['issuer_id'] ?? null;
        $payerEmail = $data['payer']['email'] ?? null;

        if (!$transactionAmount || !$token || !$installments || !$paymentMethodId || !$issuerId || !$payerEmail) {
            return new JsonResponse(['error' => 'Invalid input'], 400);
        }

        // Simular la creación del pago (en un caso real, aquí se llamaría a la API de MercadoPago)
        $paymentResponse = [
            'id' => '123456789',
            'status' => 'approved'
        ];

        return new JsonResponse($paymentResponse, 200);
    }


    #[Route('/parametria', name: 'parametria', methods: ['GET'])]
    public function parametria(Request $request): JsonResponse
    {
        $data = [
            "styles" => [
                "modern",
                "scandinavian",
                "industrial",
                "midcentury",
                "luxury",
                "farmhouse",
                "coastal",
                "standard"
            ],
            "roomTypes" => [
                "living",
                "bed",
                "kitchen",
                "dining",
                "bathroom",
                "home_office"
            ],
            "precios" => [
                "10 imagenes: 50",
                "100 imagenes: 50",
                "500 imagenes: 50"
            ]

        ];
        

        return new JsonResponse($data, 200);
    }



    

}