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




    #[Route('/historial', name: 'app_historial', methods: ['GET'])]
    public function historial(Request $request,ImagenRepository $imagenRepository): Response
    {
        
        $jwtPayload = $request->attributes->get('jwt_payload');
        $imagenes = $imagenRepository->findByUsuarioEmail($jwtPayload->token_info->email);

        usort($imagenes, function($a, $b) {
            return $b->getFecha()->getTimestamp() - $a->getFecha()->getTimestamp();
        });
    
        // Tomar las primeras 20 imágenes
        $imagenes = array_slice($imagenes, 0, 20);


        $url = "/api/consultar/";
        $imagenesArray = [];
        foreach ($imagenes as $imagen) {
            //$variaciones = $entityManager->getRepository(Variacion::class)->findByImagen($imagen);
            $variaciones = $imagen->getVariaciones()->toArray();
            
            $variacionesIds = array_map(function($variacion) {
                return  "/api/variacion/" . $variacion->getId() . ".png";
            }, $variaciones);
            $imagenesArray[] = ['imagen' => $url . $imagen->getId() . ".png",
                                'render_id' => $imagen->getId(),
                                'fecha' => $imagen->getFecha()->format('d/m/Y H:i:s'), "variaciones" => $variacionesIds];
        }
        
        $jsonResponse = json_encode($imagenesArray, JSON_UNESCAPED_SLASHES);
        
        return new JsonResponse($imagenesArray, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

 
    #[Route('/generar', name: 'app_generar', methods: ['POST'])]
    public function generar(ManagerRegistry $doctrine,Request $request, UsuarioRepository $usuarioRepository, ImagenRepository $imagenRepository, ApiClientService $apiClientService, VariacionRepository $variacionRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $jwtPayload = $request->attributes->get('jwt_payload');
        $usuario = $usuarioRepository->findOneByEmail($jwtPayload->token_info->email);

        if($usuario->getCantidadImagenesDisponibles()<1)
            return new JsonResponse(['error' => 'Te quedaste sin imagenes'],Response::HTTP_FORBIDDEN);
        // Obtener los datos de la solicitud
        $data = json_decode($request->getContent(), true);

        if (!isset($data['image']) && !isset($data['generation_id'])) {
            return new JsonResponse(['error' => 'Se tiene que subir una imagen o un generation_id'],
            Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if(!$data['roomType'] || !$data['style'])
            return new JsonResponse(['error' => 'Se tiene que enviar roomType y style']);



            
        if(isset($data['generation_id'])){
            $imagen = $imagenRepository->find($data['generation_id']);

            if(!$imagen)
                return new JsonResponse(['error' => 'No se encontro una imagen con ese generation_id']);


            
            $variacion = $apiClientService->generarVariacion($imagen,$data['generation_id'],$data['roomType'],$data['style']);
            $entityManager->persist($variacion);
            $entityManager->flush();
            return new JsonResponse(['generation_id' => $imagen->getId(),'cantidad_imagenes_disponibles' => $usuario->getCantidadImagenesDisponibles()], JsonResponse::HTTP_OK);
        }

        $base64Image = $data['image'];
        
        // Decodificar la imagen base64
        $imageData = base64_decode($base64Image);
        if ($imageData === false) {
            return new JsonResponse(['error' => 'Invalid base64 image data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Generar un UUID para el nombre de la imagen
        $uuid = Uuid::uuid4()->toString();
        


        
        $imagen = new Imagen();
        $imagen->setId($uuid);
        $imagen->setImgOrigen($imageData);
        $imagen->setUsuario($usuario);
        $imagen->setEstilo($data['style']);
        $imagen->setTipoHabitacion($data['roomType']);
        $imagen->setFecha( new DateTime());
        $entityManager->persist($imagen);
        $entityManager->flush();

        $variacion = $apiClientService->generarImagen($imagen);

        
        $entityManager->persist($variacion);
       
        $usuario->setCantidadImagenesDisponibles($usuario->getCantidadImagenesDisponibles()-1);

        $entityManager->persist($usuario);

        $entityManager->flush();

        return new JsonResponse(['generation_id' => $uuid,'cantidad_imagenes_disponibles' => $usuario->getCantidadImagenesDisponibles()], JsonResponse::HTTP_OK);
    }

    #[Route('/status/{uuid}', name: 'homepage')]
    public function status(string $uuid, ImagenRepository $imagenRepository): JsonResponse
    {
        $imagen = $imagenRepository->find($uuid);
    
        if (!$imagen) {
            return new Response('Image not found', Response::HTTP_NOT_FOUND);
        }

        
    
        $fechaGeneracion = $imagen->getFecha();
        $fechaActual = new \DateTime();
        $diferenciaSegundos = $fechaActual->getTimestamp() - $fechaGeneracion->getTimestamp();
    
        // Calcula el progreso en función de la diferencia de tiempo
        $progreso = min($diferenciaSegundos / 4, 1); // Máximo 1 después de 1 minuto
        
        $status = "rendering";
        if($progreso == 1)
            $status = "done";


        $variaciones = $imagen->getVariaciones()->toArray();

        usort($variaciones, function($a, $b) {
            // Invertir el operador de comparación para obtener un orden descendente
            return $b->getFecha() <=> $a->getFecha();
        });
        
        $variacionesIds = array_map(function(Variacion $variacion) {
            return [
                "url" => "/api/variacion/" . $variacion->getId() . ".png",
                "variacion_id" => $variacion->getId(),
                "fecha" => $variacion->getFecha()->format('Y-m-d H:i:s'),
                "room_type" => $variacion->getRoomType(),
                "style" => $variacion->getStyle(),
            ];
        }, $variaciones);
        
        $response = [
            "render_id" => $uuid,
            "status" => $status,
            "fecha_creacion" => $fechaGeneracion->format('Y-m-d H:i:s'), // epoch timestamp en milisegundos
            "outputs" => $variacionesIds, // Contendrá URLs de imágenes si status == "done". Habrá una entrada para cada nueva variación.
            "progress" => $progreso
        ];
        return new JsonResponse($response, 200);
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

    #[Route('/variacion/{uuid}.png', name: 'app_variacion', methods: ['GET'])]
    public function getVariacion(string $uuid, ManagerRegistry $doctrine): Response
    {
        $variacion = $doctrine->getRepository(Variacion::class)->find($uuid);

        if (!$variacion) {
            return new Response('Image not found', Response::HTTP_NOT_FOUND);
        }

        $imageResource = $variacion->getImg();
        $imageData = stream_get_contents($imageResource);
        $response = new Response($imageData);
        $response->headers->set('Content-Type', 'image/jpeg'); // Ajusta el tipo MIME según el formato de tu imagen

        return $response;
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