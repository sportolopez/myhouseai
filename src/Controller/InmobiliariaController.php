<?php

namespace App\Controller;

use App\Entity\Inmobiliaria;
use App\Form\InmobiliariaType;
use App\Repository\InmobiliariaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/inmobiliaria')]
class InmobiliariaController extends AbstractController
{
    #[Route('/', name: 'app_inmobiliaria_index', methods: ['GET'])]
    public function index(InmobiliariaRepository $inmobiliariaRepository): Response
    {
        return $this->render('inmobiliaria/index.html.twig', [
            'inmobiliarias' => $inmobiliariaRepository->findAllOrderedByImagenEjemplo(),
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
            throw $this->createNotFoundException('Inmobiliaria no encontrada.');
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
            // Leer el contenido del recurso y convertirlo en una cadena
            $imageContent = stream_get_contents($imageContent);
        }

        if ($imageContent === false || $imageContent === null) {
            throw $this->createNotFoundException('Imagen no encontrada.');
        }

        // Determinar el tipo MIME (aquí asumimos que es PNG)
        $mimeType = 'image/png';

        // Crear la respuesta con el contenido de la imagen
        $response = new Response($imageContent);
        
        // Establecer el Content-Type correctamente
        $response->headers->set('Content-Type', $mimeType);

        // Retornar la respuesta con el contenido de la imagen
        return $response;
    }


    
}
