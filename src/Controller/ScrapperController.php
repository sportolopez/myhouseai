<?php

namespace App\Controller;

use App\Entity\Inmobiliaria;
use App\Repository\InmobiliariaRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class ScrapperController extends AbstractController
{
    private $inmobiliariaRepository;
    private $doctrine;

    public function __construct(InmobiliariaRepository $inmobiliariaRepository,ManagerRegistry $doctrine)
    {
        $this->inmobiliariaRepository = $inmobiliariaRepository;
        $this->doctrine = $doctrine;
    }

    #[Route('/scrape/buscadorprop', name: 'buscadorprop', methods: ['GET'])]
    public function scrape(): Response
    {
        $client = new HttpBrowser(HttpClient::create());
        $crawler = $client->request('GET', 'https://www.buscadorprop.com.ar/inmobiliarias/provincia-de-buenos-aires');

        $result = '';

        // Seleccionar todas las inmobiliarias
        $crawler->filter('.inmobiliarias__ficha')->each(function (Crawler $node) use (&$result) {
            $nombre = $node->filter('.inmobiliarias__ficha__title')->text();
            $telefono = $node->filter('.telefono')->count() ? $node->filter('.telefono')->text() : 'No disponible';

            // Buscar email en enlaces con mailto
            $email = $node->filter('a[href^="mailto"]')->count() ? $node->filter('a[href^="mailto"]')->attr('href') : 'No disponible';
            $email = str_replace('mailto:', '', $email);

            // Extraer número de WhatsApp
            $whatsapp = $node->filter('a[href*="api.whatsapp.com/send"]')->count() ? $node->filter('a[href*="api.whatsapp.com/send"]')->text() : 'No disponible';

            // Verificar si ya existe
            $inmobiliaria = $this->inmobiliariaRepository->findOneByEmail($email);

            if ($inmobiliaria) {
                $result .= "Inmobiliaria ya existe: $nombre<br>";
            } else {
                // Crear y guardar una nueva inmobiliaria
                $inmobiliaria = new Inmobiliaria();
                $inmobiliaria->setNombre($nombre);
                $inmobiliaria->setTelefono($telefono);
                $inmobiliaria->setEmail($email);
                $inmobiliaria->setWhatsapp($whatsapp);

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($inmobiliaria);
                $entityManager->flush();

                $result .= "Nueva inmobiliaria agregada: $nombre<br>";
            }

           
        });

        return new Response($result);
    }
}
