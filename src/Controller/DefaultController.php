<?php

namespace App\Controller;

use App\Entity\Variacion;
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
            echo json_encode(['error' => 'Encabezado de autorización inválido.']);
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
        $accessToken = $data['access_token'] ?? null;

        // Implementa la lógica de validación del token aquí
        $user_info = validate_access_token($accessToken);
        print_r("user info obtenido" . $user_info);
        if ($user_info !== null) {
            print_r($user_info);
        } else {
            throw new AccessDeniedHttpException('Encabezado de autorización inválido.');
            #return new JsonResponse($token, JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Ejemplo de uso
        $token_info = array(
            'user_id' => 123,
            "name" => "example_user"
        );

        $secret_key = 'secret_key';
        $payload = array(
            "token_info" => $token_info
        );
    
        $jwt = JWT::encode($payload, $secret_key, 'HS256');
    
        $token =  array(
            'jwt_token' => $jwt,
            'userInfo' => $token_info
        );


        return new JsonResponse($token, JsonResponse::HTTP_OK);
        

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




    

}