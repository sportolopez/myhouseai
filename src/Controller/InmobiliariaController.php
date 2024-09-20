<?php

namespace App\Controller;

use App\Entity\Inmobiliaria;
use App\Form\InmobiliariaType;
use App\Repository\InmobiliariaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/inmobiliaria')]
class InmobiliariaController extends AbstractController
{
    #[Route('/', name: 'app_inmobiliaria_index', methods: ['GET'])]
    public function index(InmobiliariaRepository $inmobiliariaRepository): Response
    {
        $inmobiliarias = $inmobiliariaRepository->findAllOrderedByImagenEjemplo();
        /*
        foreach ($inmobiliarias as &$inmobiliaria) {
            $inmobiliaria->['imagenEjemploUrl'] = $this->generateUrl('app_inmobiliaria_original', ['id' => $inmobiliaria['id']]);
            $inmobiliaria['imagenGeneradaUrl'] = $this->generateUrl('app_inmobiliaria_generada', ['id' => $inmobiliaria['id']]);
        }*/

        return $this->render('inmobiliaria/index.html.twig', [
            'inmobiliarias' => $inmobiliarias,
        ]);
    }

    #[Route('/new', name: 'app_inmobiliaria_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $inmobiliarium = new Inmobiliaria();
        $form = $this->createForm(InmobiliariaType::class, $inmobiliarium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inmobiliarium);
            $entityManager->flush();

            return $this->redirectToRoute('app_inmobiliaria_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('inmobiliaria/new.html.twig', [
            'inmobiliarium' => $inmobiliarium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_inmobiliaria_show', methods: ['GET'])]
    public function show(Inmobiliaria $inmobiliarium): Response
    {
        return $this->render('inmobiliaria/show.html.twig', [
            'inmobiliarium' => $inmobiliarium,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_inmobiliaria_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Inmobiliaria $inmobiliarium, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InmobiliariaType::class, $inmobiliarium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_inmobiliaria_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('inmobiliaria/edit.html.twig', [
            'inmobiliarium' => $inmobiliarium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_inmobiliaria_delete', methods: ['POST'])]
    public function delete(Request $request, Inmobiliaria $inmobiliarium, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$inmobiliarium->getId(), $request->request->get('_token'))) {
            $entityManager->remove($inmobiliarium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_inmobiliaria_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/upload/{id}/{tipoImagen}', name: 'app_inmobiliaria_upload_imagen', methods: ['POST'])]
    public function uploadImagen(Request $request, InmobiliariaRepository $inmobiliariaRepository, EntityManagerInterface $entityManager, $id, $tipoImagen): Response
    {
        $inmobiliarium = $inmobiliariaRepository->find($id);
    
        if (!$inmobiliarium) {
            throw $this->createNotFoundException('No se encontró la inmobiliaria con id ' . $id);
        }
    
        $file = $request->files->get($tipoImagen);
    
        if ($file) {
            // Leer el contenido del archivo y guardarlo en la columna binaria
            if ($tipoImagen === 'imagenEjemplo') {
                $inmobiliarium->setImagenEjemplo(file_get_contents($file->getPathname()));
            } elseif ($tipoImagen === 'imagenGenerada') {
                $inmobiliarium->setImagenGenerada(file_get_contents($file->getPathname()));
            }
    
            // Guardar en la base de datos
            $entityManager->persist($inmobiliarium);
            $entityManager->flush();
    
            // Redirigir o devolver una respuesta
            return $this->redirectToRoute('app_inmobiliaria_index');
        }
    
        return $this->redirectToRoute('app_inmobiliaria_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/imagenOriginal.png', name: 'app_inmobiliaria_original', methods: ['GET'])]
    #[Route('/{id}/imagenGenerada.png', name: 'app_inmobiliaria_generada', methods: ['GET'])]
    public function imagenOriginal(Request $request, InmobiliariaRepository $inmobiliariaRepository, $id): Response
    {
        // Obtener la entidad inmobiliaria usando el ID
        $inmobiliarium = $inmobiliariaRepository->find($id);
        
        if (!$inmobiliarium) {
            throw $this->createNotFoundException('Inmobiliaria no encontrada.' . $id);
        }
    
        // Determinar qué imagen devolver
        $imageType = $request->get('_route') === 'app_inmobiliaria_original' ? 'original' : 'generada';
    
        if ($imageType === 'original') {
            $imageContent = $inmobiliarium->getImagenEjemplo();
        } else {
            $imageContent = $inmobiliarium->getImagenGenerada();
        }
    
        // Verificar si el contenido es válido y convertir el recurso a una cadena si es necesario
        if (is_resource($imageContent)) {
            $imageContent = stream_get_contents($imageContent);
        }
    
        if ($imageContent === false || $imageContent === null) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
        /*
        // Crear la imagen desde el string
        $image = imagecreatefromstring($imageContent);
        if ($image === false) {
            throw new \Exception('Error al crear la imagen desde los datos.');
        }
        
        // Corregir la orientación usando los datos EXIF si es una imagen JPEG
        if ($imageType === 'original' && function_exists('exif_read_data')) {
            $stream = fopen('data://text/plain;base64,' . base64_encode($imageContent), 'rb');
            $exif = exif_read_data($stream);
            fclose($stream);
    
            if (isset($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $image = imagerotate($image, 180, 0); // Rotar 180 grados
                        break;
                    case 6:
                        $image = imagerotate($image, -90, 0); // Rotar 90 grados hacia la izquierda
                        break;
                    case 8:
                        $image = imagerotate($image, 90, 0); // Rotar 90 grados hacia la derecha
                        break;
                }
            }
        }
    
        // Redimensionar la imagen (por ejemplo, a 800x600)
        $newWidth = 800;
        $newHeight = 600;
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = min($newWidth / $width, $newHeight / $height);
        $resizedWidth = (int)($width * $ratio);
        $resizedHeight = (int)($height * $ratio);
    
        // Crear la nueva imagen redimensionada
        $resizedImage = imagecreatetruecolor($resizedWidth, $resizedHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $resizedWidth, $resizedHeight, $width, $height);
    
        // Capturar la salida de la imagen redimensionada en JPG
        ob_start();
        imagejpeg($resizedImage, null, 90); // Convertir la imagen a JPG con calidad 90
        $imageContent = ob_get_clean();
    
        // Liberar memoria
        imagedestroy($image);
        imagedestroy($resizedImage);
    */
        // Crear la respuesta con el contenido de la imagen
        $response = new Response($imageContent);
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setPublic();
        $response->setMaxAge(604800); // Cachear en el navegador por 7 días
        $response->headers->set('Cache-Control', 'public, max-age=604800');
        $lastModified = new \DateTime();
        $response->setLastModified($lastModified);
    
        // Definir el nombre del archivo cuando el usuario lo descargue (opcional)
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $id . '_' . $imageType . '.jpg'
        );
        $response->headers->set('Content-Disposition', $disposition);
        $etag = md5($imageContent); // Puedes usar un hash del contenido o alguna otra lógica
        $response->headers->set('ETag', $etag);
        // Comprobar si el contenido no ha cambiado para devolver 304 Not Modified
        if ($response->isNotModified($request)) {
            return $response;
        }
    
        return $response;
    }
    
    
    
}
