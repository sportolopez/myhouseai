<?php
namespace App\Controller;

use App\Service\EmailService;
use App\Service\EncryptionService;
use App\Service\TelegramService;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\InmobiliariaRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EmailEnviado;
use Twig\Environment;


class EmailController extends AbstractController
{
    private $twig;
    private $em;
    private $emailService;


    public function __construct(Environment $twig, EntityManagerInterface $em, EmailService $emailService)
    {
        $this->twig = $twig;
        $this->em = $em;
        $this->emailService = $emailService;
    }

    #[Route('/send-emails', name: 'send_emails', methods: ['GET'])]
    public function sendEmails(Request $request, InmobiliariaRepository $inmobiliariaRepository, EntityManagerInterface $entityManager,TelegramService $telegramService): JsonResponse
    {
        // Obtener los parámetros de la consulta
        $ids = $request->query->get('ids'); // Los IDs deben venir como una lista, por ejemplo: ?ids=1,2,3
        $asunto = $request->query->get('asunto');
        $template = $request->query->get('template');
        $limit = $request->query->get('limit', 30);

        if($ids)
            $idArray = array_map('intval', explode(',', $ids));

        if(!$ids && $limit){
            $connection = $entityManager->getConnection();
            $sql = '
                SELECT i.id 
                FROM inmobiliaria i
                LEFT JOIN email_enviado ee ON i.id = ee.inmobiliaria_id
                WHERE ee.inmobiliaria_id IS NULL
                LIMIT '. $limit;
            
            $stmt = $connection->prepare($sql);
            $idArray = array_column($stmt->executeQuery()->fetchAll(), 'id');
            // Convertir los IDs en un array
            
        }

        // Validar que todos los parámetros estén presentes
        if (!$idArray || !$asunto || !$template) {
            return new JsonResponse(['error' => 'Faltan parametros ids, asunto, template'], 400);
        }
    
        if (empty($idArray)) {
            return new JsonResponse(['error' => 'La lista de ids está vacía'], 400);
        }
    
        // Obtener las inmobiliarias con los IDs proporcionados
        $inmobiliarias = $inmobiliariaRepository->findBy(['id' => $idArray]);
    
        if (empty($inmobiliarias)) {
            return new JsonResponse(['message' => 'No se encontraron inmobiliarias con los IDs proporcionados'], 404);
        }
    
        // Enviar correos electrónicos a las inmobiliarias
        foreach ($inmobiliarias as $inmobiliaria) {
            if (filter_var($inmobiliaria->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $this->emailService->processEmail($inmobiliaria, $asunto, $template);
            } else {
                $telegramService->notificaLectura("⚠️: Email invalido {$inmobiliaria->getEmail()}");

            }
            
        }
    
        return new JsonResponse(['message' => 'Correos enviados exitosamente']);
    }



    #[Route('/track-email/{id}', name: 'track_email', methods: ['GET'])]
    public function trackEmail($id, EntityManagerInterface $entityManager, TelegramService $telegramService, Request $request): Response
    {
        $emailEnviado = $entityManager->getRepository(EmailEnviado::class)->find($id);

        if (!$emailEnviado) {
            return new Response(404);
        }

        
        $emailEnviado->setVisto(1);
        $emailEnviado->setVistoFecha(new \DateTime());
        $entityManager->flush();

        // Retornar una imagen en blanco de 1x1 píxel
        $response = new Response();
        $response->headers->set('Content-Type', 'image/gif');
        $response->setContent(base64_decode(
            'R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='
        ));

        $telegramService->notificaLectura("📧: Se confirma lectura de {$emailEnviado->getInmobiliaria()->getNombre()} {$emailEnviado->getInmobiliaria()->getEmail()}.");

        return $response;
    }

    #[Route('/test-emails', name: 'test_emails', methods: ['GET'])]
    public function testSendMail(Request $request, InmobiliariaRepository $inmobiliariaRepository): JsonResponse
    {
        $inmobiliaria_id = $request->query->get('inmobiliaria_id');
        $asunto = $request->query->get('asunto');
        $template = $request->query->get('template');

        $inmobiliarium = $inmobiliariaRepository->find($inmobiliaria_id);

        if (!$inmobiliarium) {
            throw $this->createNotFoundException('No se encontró la inmobiliaria con id ' . $inmobiliaria_id);
        }

        $this->emailService->processEmail($inmobiliarium, $template, $asunto . " " . $inmobiliarium->getDireccion());

        return new JsonResponse(['message' => 'Emails sent successfully']);
    }

    #[Route('/sinenvios', name: 'sinenvios', methods: ['GET'])]
    public function sinenvios(Request $request, InmobiliariaRepository $inmobiliariaRepository): JsonResponse
    {
        $dominio_email = $request->query->get(key: 'dominio');
        $notdominio = $request->query->get(key: 'notdominio');
        // Obtener todas las inmobiliarias sin correos enviados
        $inmobiliariasSinEmail = $inmobiliariaRepository->findAllSinEnvios($dominio_email, $notdominio);
    
        // Obtener la cantidad de inmobiliarias
        $cantidadInmobiliarias = count($inmobiliariasSinEmail);
    
        // Extraer los IDs y unirlos en una cadena separada por comas
        $listaIds = implode(',', array_column($inmobiliariasSinEmail, 'id'));
    
        // Retornar un JSON con las propiedades solicitadas
        return new JsonResponse([
            'cantidad_inmobiliarias_sin_email' => $cantidadInmobiliarias,
            'lista_ids' => $listaIds
        ]);
    }

  

}
