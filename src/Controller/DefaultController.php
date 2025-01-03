<?php

namespace App\Controller;

use App\Entity\Variacion;
use App\Repository\ImagenRepository;
use App\Repository\InmobiliariaRepository;
use App\Repository\PlanesRepository;
use App\Repository\UsuarioRepository;
use App\Repository\VariacionRepository;
use App\Service\EncryptionService;
use App\Service\TelegramService;
use Doctrine\ORM\EntityManager;
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




    #[Route('/historial', name: 'historial', methods: ['GET'])]
    public function historial(Request $request,ImagenRepository $imagenRepository,VariacionRepository $variacionRepository): Response
    {
        
        $jwtPayload = $request->attributes->get('jwt_payload');
        $imagenes = $imagenRepository->findByUsuarioEmail($jwtPayload->token_info->email);
        $imagenesArray = [];

        if (empty($imagenes)) {

            return new JsonResponse($imagenesArray, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        } 

        usort($imagenes, function($a, $b) {
            return $b->getFecha()->getTimestamp() - $a->getFecha()->getTimestamp();
        });
    
        // Tomar las primeras 20 imágenes
        $imagenes = array_slice($imagenes, 0, 20);


        $url = "/api/consultar/";
        
        foreach ($imagenes as $imagen) {
            $variaciones = $variacionRepository->findByImagenSinBlob($imagen->getId());
            

            usort($variaciones, function($a, $b) {
                // Invertir el operador de comparación para obtener un orden descendente
                return $b['fecha'] <=> $a['fecha'];
            });
            
            $variacionesIds = array_map(function(Array $variacion) {
                return [
                    "url" => "/api/variacion/" . $variacion['id'] . ".jpg",
                    "variacion_id" =>$variacion['id'] ,
                    "fecha" => $variacion['fecha']->format('Y-m-d H:i:s'),
                    "room_type" => $variacion['roomType'],
                    "style" => $variacion['style'],
                ];
            }, $variaciones);
            


            $imagenesArray[] = ['imagen' => $url . $imagen->getId() . ".jpg",
                                'render_id' => $imagen->getId(),
                                'fecha' => $imagen->getFecha()->format('d/m/Y H:i:s'), "variaciones" => $variacionesIds];
        }
        
        
        return new JsonResponse($imagenesArray, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/generar', name: 'generar_get', methods: ['GET'])]
    public function generarGet(TelegramService $telegramService): Response{
        $telegramService->sendMessage("Se hizo click en generar");
        return new Response("registrado");
    }
 
    #[Route('/generar', name: 'generar', methods: ['POST'])]
    public function generar(
        ManagerRegistry $doctrine,
        Request $request,
        UsuarioRepository $usuarioRepository,
        ImagenRepository $imagenRepository,
        ApiClientService $apiClientService,
        TelegramService $telegramService
    ): JsonResponse {
        $entityManager = $doctrine->getManager();
        $jwtPayload = $request->attributes->get('jwt_payload');
        $usuario = $usuarioRepository->findOneByEmail($jwtPayload->token_info->email);
    
        // Obtener los datos de la solicitud
        $data = json_decode($request->getContent(), true);
    
        if ($usuario->getCantidadImagenesDisponibles() < 1 && isset($data['image'])) {
            return new JsonResponse(['error' => 'Te quedaste sin imagenes'], Response::HTTP_FORBIDDEN);
        }
    
        if (!isset($data['image']) && !isset($data['generation_id'])) {
            return new JsonResponse(['error' => 'Se tiene que subir una imagen o un generation_id'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        
        if (!isset($data['roomType']) || !isset($data['style'])) {
            return new JsonResponse(['error' => 'Se tiene que enviar roomType y style'], Response::HTTP_BAD_REQUEST);
        }
    
        // Generar variación
        if (isset($data['generation_id'])) {
            $imagen = $imagenRepository->findOneById($data['generation_id']);
    
            if (!$imagen) {
                return new JsonResponse(['error' => 'No se encontró una imagen con ese generation_id']);
            }
    
            $apiClientService->crearVariacionParaRender($imagen->getRenderId(), $data['roomType'], $data['style']);
            $telegramService->sendMessage("📷 Se ejecutó generar variacion para el usuario {$usuario->getEmail()} render id {$imagen->getRenderId()}");
    
            return new JsonResponse(['generation_id' => $imagen->getId(), 'cantidad_imagenes_disponibles' => $usuario->getCantidadImagenesDisponibles()]);
        }
    
        $base64Image = $data['image'];
    
        // Decodificar la imagen base64
        $imageData = base64_decode($base64Image);
        if ($imageData === false) {
            return new JsonResponse(['error' => 'Invalid base64 image data'], Response::HTTP_BAD_REQUEST);
        }

        // Generar un UUID para el nombre de la imagen
        $uuid = Uuid::uuid4()->toString();
    
        $imagen = new Imagen();
        $imagen->setId($uuid);
        $imagen->setImgOrigen($imageData);
        $imagen->setUsuario($usuario);
        $imagen->setEstilo($data['style']);
        $imagen->setTipoHabitacion($data['roomType']);
        $imagen->setFecha(new DateTime());
        $entityManager->persist($imagen);
        $entityManager->flush();
        if (!array_key_exists('declutter_mode', $data))
            $data['declutter_mode'] = "off";
        $renderId = $apiClientService->generarImagen($imagen, $data['declutter_mode']);
    
        $imagen->setRenderId($renderId);
    
        $entityManager->persist($imagen);
        $entityManager->flush();
    
        $usuario->setCantidadImagenesDisponibles($usuario->getCantidadImagenesDisponibles() - 1);
        $entityManager->persist($usuario);
        $entityManager->flush();
    
        $telegramService->sendMessage("📷 Se ejecuto generar: {$usuario->getEmail()}  \r\n render id {$imagen->getRenderId()} \r\n Imagen: https://myhouseai.com/api/consultar/{$uuid}.jpg");
           
        return new JsonResponse(['generation_id' => $uuid, 'cantidad_imagenes_disponibles' => $usuario->getCantidadImagenesDisponibles()], JsonResponse::HTTP_OK);
    }
    
    #[Route('/generar_free', name: 'generar_free', methods: ['POST'])]
    public function generarFree(
        ManagerRegistry $doctrine,
        Request $request,
        UsuarioRepository $usuarioRepository,
        ImagenRepository $imagenRepository,
        ApiClientService $apiClientService,
        EncryptionService $encryptionService,
        InmobiliariaRepository $inmobiliariaRepository,
        TelegramService $telegramService
    ): JsonResponse {
        $entityManager = $doctrine->getManager();
    
        // Obtener la IP del cliente
        $clientIp = $request->getClientIp();

        $data = json_decode($request->getContent(), true);



        if (isset($data['generation_id'])) {
            $imagen = $imagenRepository->findOneById($data['generation_id']);
    
            if (!$imagen) {
                return new JsonResponse(['error' => 'No se encontró una imagen con ese generation_id']);
            }
    
            $apiClientService->crearVariacionParaRender($imagen->getRenderId(), $data['roomType'], $data['style']);
    
            return new JsonResponse(['generation_id' => $imagen->getId(), 'cantidad_imagenes_disponibles' =>100]);
        }

        if (!isset($data['idgenerar'])) {
            $telegramService->sendMessage("SE INTENTO GENERAR FREE sin idGnerarar");
            return new JsonResponse(['error' => 'SE INTENTO GENERAR FREE sin idGnerarar'], Response::HTTP_FORBIDDEN);
        }

        $sessionHash = $data['idgenerar'];
        // Desencriptar el hash usando el servicio de encriptación
        $userEmail = $encryptionService->decrypt($sessionHash);

        if (filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            error_log ("La direccion de correo es valida." . $userEmail);
        } else {
            error_log("La direccion de correo no es valida." . $userEmail);
        }

        if (!$userEmail) {
            $telegramService->sendMessage("No se pudo obtener el mail de idgenerar");
            return new JsonResponse(['error' => 'No se obtuvo el email del idgenerar'], Response::HTTP_FORBIDDEN);
        }

        $inmo = $inmobiliariaRepository->findOneBy(['email' => $userEmail]);

        if (!$inmo) {
            $telegramService->sendMessage("Se intento generar free con una inmobiliaria que no existe");
            return new JsonResponse(['error' => 'Se intento generar free con una inmobiliaria que no existe'], Response::HTTP_FORBIDDEN);
        }
        
        $usuario = $usuarioRepository->findOneBy(['email' => $userEmail]);
    
        if (!$usuario) {
            // Crear el usuario "Usuario Free" si no existe
            $usuario = new Usuario();
            $usuario->setEmail($userEmail);
            $usuario->setNombre($userEmail); // POdria buscar el nombre de la inmo
            $usuario->setCantidadImagenesDisponibles(1);  // No permitimos más imágenes para este usuario, es solo para tracking
            $entityManager->persist($usuario);
            $entityManager->flush();
        }


        $telegramService->sendMessage("El usuario {$userEmail} entro en generar free con ip {$clientIp}");
        


        if (!isset($data['image'])) {
            return new JsonResponse(['error' => 'Se debe subir una imagen'], Response::HTTP_BAD_REQUEST);
        }
    
        if (!$data['roomType'] || !$data['style']) {
            return new JsonResponse(['error' => 'Se debe enviar roomType y style']);
        }
    
        $base64Image = $data['image'];
    
        // Decodificar la imagen base64
        $imageData = base64_decode($base64Image);
        if ($imageData === false) {
            return new JsonResponse(['error' => 'Datos de imagen base64 inválidos'], Response::HTTP_BAD_REQUEST);
        }
    

        if ($usuario->getCantidadImagenesDisponibles() < 1 && isset($data['image'])) {
            $telegramService->sendMessage("El usuario  {$userEmail} se quedo sin imagenes ");
            return new JsonResponse(['error' => 'Te quedaste sin imagenes'], Response::HTTP_FORBIDDEN);
        }


        // Generar un UUID para el nombre de la imagen
        $uuid = Uuid::uuid4()->toString();
    
        // Crear y asociar la imagen al "Usuario Free"
        $imagen = new Imagen();
        $imagen->setId($uuid);
        $imagen->setImgOrigen($imageData);
        $imagen->setUsuario($usuario);  // Asociar la imagen al "Usuario Free"
        $imagen->setEstilo($data['style']);
        $imagen->setTipoHabitacion($data['roomType']);
        $imagen->setFecha(new DateTime());
    
        // Guardar la IP remota
        $imagen->setIpRemota($clientIp);
    
        $entityManager->persist($imagen);
        $entityManager->flush();
    
        if (!array_key_exists('declutter_mode', $data)) {
            $data['declutter_mode'] = "off";
        }
    
        // Generar la imagen
        $renderId = $apiClientService->generarImagen($imagen, $data['declutter_mode']);
        $imagen->setRenderId($renderId);
    
        $entityManager->persist($imagen);
        $entityManager->flush();
    
        // Notificación a Telegram
        $telegramService->sendMessage("📷 Se ejecutó generar_free desde la IP: {$clientIp} para el usuario {$userEmail}");
    

        $usuario->setCantidadImagenesDisponibles($usuario->getCantidadImagenesDisponibles() - 1);
        $entityManager->persist($usuario);
        $entityManager->flush();

        
        return new JsonResponse(['generation_id' => $uuid], JsonResponse::HTTP_OK);
    }
    


    #[Route('/status/{uuid}', name: 'status')]
    public function status(
        string $uuid, 
        ImagenRepository $imagenRepository, 
        VariacionRepository $variacionRepository, 
        ApiClientService $apiClientService,
        ManagerRegistry $doctrine,
    ): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $imagen = $imagenRepository->findOneById($uuid);
    
        if (!$imagen) {
            return new JsonResponse('Image not found', Response::HTTP_NOT_FOUND);
        }
       // $variacionesImagen = $variacionRepository->findByImagenSinBlob($uuid);
        
        $variaciones = [];

        $response = $apiClientService->getRender($imagen);
   
        /*if ($response->status != "done"){
            foreach( $variacionesImagen as $unaVariacion){
                $variacion = [
                    "url" => "/api/variacion/".$unaVariacion['id'].".png",
                    "room_type" => $unaVariacion['roomType'],
                    "style" => $unaVariacion['style'],
                    "fecha" => $unaVariacion['fecha']->format('Y-m-d H:i:s'),
                ];

                $variaciones[] = $variacion;
            }
        }

        if ($response->status == "done") {
            
            
            // Guardar imágenes solo si no existen
            foreach ($response->outputs as $index => $outputUrl) {
                
                // Obtener el nombre del archivo sin la extensión para usarlo como id de variación
                $pathInfo = pathinfo($outputUrl);
                $fileNameWithoutExtension = $pathInfo['filename'];  // Esto da el nombre sin extensión
                $fileNameWithoutExtension = strtok($fileNameWithoutExtension, '?');
                $fileNameWithoutExtension = strtok($fileNameWithoutExtension, '.');
                // Comprobar si la variación ya existe en la base de datos
                $existingVariacion = $variacionRepository->findOneByIdSinImagen($fileNameWithoutExtension);
    
                if ($existingVariacion) {
                    $variacion = [
                        "url" => "/api/variacion/".$existingVariacion->getId().".png",
                        "room_type" => $existingVariacion->getRoomType(),
                        "style" => $existingVariacion->getStyle(),
                        "fecha" => $existingVariacion->getFecha()->format('d/m/Y H:i:s')
                    ];
    
                    $variaciones[] = $variacion;
                    continue; // Si la variación ya existe, omitir
                }

                if (strpos($outputUrl, 'furniture_removed') !== false) {
                    if($imagen->getDeclutteredImage() == null){
                        $imageContent = file_get_contents($outputUrl);
                        $imagen->setDeclutteredImage($imageContent); // Guardar la imagen como BLOB
                        $entityManager->persist($imagen); // Persistir la entidad
                        $entityManager->flush(); // Guardar los cambios en la base de datos
                    }
                    continue;
                }
    
                // Descargar la imagen desde la URL
                $imageContent = file_get_contents($outputUrl);
    
                if ($imageContent === false) {
                    continue; // Si no se pudo descargar la imagen, omitir
                }
    
                // Omitir la URL que tiene "furniture_removed"

    
                // Crear y guardar una nueva variación
  
                $unaVariacion = new Variacion();
                $unaVariacion->setImagenId($imagen->getId());
                $unaVariacion->setFecha(new DateTime());
                $unaVariacion->setRoomType($response->outputs_room_types[$index] ?? null);
                $unaVariacion->setStyle($response->outputs_styles[$index] ?? null);
                $unaVariacion->setId($fileNameWithoutExtension);  // Asignar el nombre sin extensión como id
                $unaVariacion->setImg($imageContent); // Guardar la imagen descargada como BLOB
    
                // Persistir la variación en la base de datos
                $entityManager->persist($unaVariacion);

                $variacion = [
                    "url" => "/api/variacion/".$unaVariacion->getId().".png",
                    "room_type" => $unaVariacion->getRoomType(),
                    "style" => $unaVariacion->getStyle(),
                    "fecha" => $unaVariacion->getFecha()->format('d/m/Y H:i:s')
                ];

                $variaciones[] = $variacion;

            }
    
            // Guardar todas las variaciones en la base de datos de una sola vez
            $entityManager->flush();
        }
    
        // Modificar la respuesta para incluir las variaciones
        
        $response->outputs = $variaciones;*/
        $response->render_id = $uuid;
        unset($response->outputs_styles);
        unset($response->outputs_room_types);
    
        return new JsonResponse($response, 200);
    }
    

    #[Route('/consultar/{uuid}.jpg', name: 'app_consultar', methods: ['GET'])]
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

    #[Route('/variacion/{uuid}.jpg', name: 'app_variacion', methods: ['GET'])]
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
    public function parametria(Request $request, PlanesRepository $planesRepository): JsonResponse
    {
        $listaPlanes = $planesRepository->findAll();
        
        $response = [
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
        'planes' => $listaPlanes];

        return new JsonResponse($response, 200);
    }


    #[Route('/ping', name: 'ping', methods: ['GET'])]
    public function ping(ApiClientService $apiClientService): JsonResponse
    {
        return new JsonResponse( $apiClientService->getPing(), 200);
       
    }

    

}