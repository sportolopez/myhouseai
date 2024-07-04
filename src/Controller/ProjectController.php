<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class ProjectController extends AbstractController
{
    #[Route('/status', name: 'project_index', methods:['GET'] )]
    public function index(): JsonResponse
    {
        // Retorna un JSON con el mensaje "hola mundo"
        return $this->json(['message' => 'hola mundo']);
    }
}