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

    public function __construct(InmobiliariaRepository $inmobiliariaRepository, ManagerRegistry $doctrine)
    {
        $this->inmobiliariaRepository = $inmobiliariaRepository;
        $this->doctrine = $doctrine;
    }

    #[Route('/scrape/buscadorprop', name: 'buscadorprop', methods: ['GET'])]
    public function scrape(): Response
    {
        $client = new HttpBrowser(HttpClient::create());
        $baseUrl = 'https://www.buscadorprop.com.ar';
        // Step 1: Scrape the main page to get the links
        $crawler = $client->request('GET', $baseUrl . "/inmobiliarias");
        $links = $crawler->filter('.inmobiliarias__item__sublist__item a')->each(function (Crawler $node) {
            return $node->attr('href');
        });

        // Initialize counters
        $insertedCount = 0;
        $result = '';

        // Step 2: Process each link
        foreach ($links as $relativeUrl) {
            $url = preg_match('#^https?://#', $relativeUrl) ? $relativeUrl : $baseUrl . $relativeUrl;
            $crawler = $client->request('GET', $url);

            // Scrape data from each page
            $crawler->filter('.inmobiliarias__ficha')->each(function (Crawler $node) use (&$result, &$insertedCount) {
                $nombre = $node->filter('.inmobiliarias__ficha__title')->text();
                $telefono = $node->filter('.telefono')->count() ? $node->filter('.telefono')->text() : 'No disponible';

                $email = $node->filter('a[href^="mailto"]')->count() ? $node->filter('a[href^="mailto"]')->attr('href') : 'No disponible';
                $email = str_replace('mailto:', '', $email);

                $whatsapp = $node->filter('a[href*="api.whatsapp.com/send"]')->count() ? $node->filter('a[href*="api.whatsapp.com/send"]')->text() : 'No disponible';

                // Check if the inmobiliaria already exists
                $inmobiliaria = $this->inmobiliariaRepository->findOneByEmail($email);

                if ($inmobiliaria) {
                    $result .= "Inmobiliaria ya existe: $nombre<br>";
                } else {
                    // Create and save a new inmobiliaria
                    $inmobiliaria = new Inmobiliaria();
                    $inmobiliaria->setNombre($nombre);
                    $inmobiliaria->setTelefono($telefono);
                    $inmobiliaria->setEmail($email);
                    $inmobiliaria->setWhatsapp($whatsapp);
/*
                    $entityManager = $this->doctrine->getManager();
                    $entityManager->persist($inmobiliaria);
                    $entityManager->flush();*/

                    $insertedCount++; // Increment the counter
                    $result .= "Nueva inmobiliaria agregada: $nombre<br>";
                }

                $result .= "Teléfono: $telefono<br>";
                $result .= "Email: $email<br>";
                $result .= "WhatsApp: $whatsapp<br>";
                $result .= "-----------------------------------<br>";
            });
        }

        $result .= "<br>Total inmobiliarias insertadas: $insertedCount";

        return new Response($result);
    }
}
