<?php

namespace App\Controller;

use App\Repository\InmobiliariaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class ScrapperController extends AbstractController
{

    private $inmobiliariaRepository;

    public function __construct(InmobiliariaRepository $inmobiliariaRepository)
    {
        $this->inmobiliariaRepository = $inmobiliariaRepository;
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

            $result .= "Nombre: $nombre<br>";
            $result .= "Teléfono: $telefono<br>";
            $result .= "Email: $email<br>";
            $result .= "WhatsApp: $whatsapp<br>";
            $result .= "-----------------------------------<br>";

            $inmobiliaria = $this->inmobiliariaRepository->findOneByEmail($email);
            if($inmobiliaria)
                print_r("Inmobiliaria ya existe");
            
        });

        return new Response($result);
    }
}
