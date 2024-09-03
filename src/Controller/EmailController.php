<?php
namespace App\Controller;

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
    private $smtpHost = 'c1802222.ferozo.com';
    private $smtpPort = 465;
    private $smtpUser = 'ventas@myhouseai.com';
    private $smtpPassword = '@9JhcWsLVismDUcU4';
    private $smtpFrom = 'martin@myhouseai.com.ar';
    private $smtpFromName = 'Martin';

    public function __construct(Environment $twig, EntityManagerInterface $em)
    {
        $this->twig = $twig;
        $this->em = $em;
    }

    #[Route('/send-emails', name: 'send_emails', methods: ['GET'])]
    public function sendEmails(Request $request, InmobiliariaRepository $inmobiliariaRepository): JsonResponse
    {
        // Obtener los parámetros de la consulta
        $startId = (int) $request->query->get('start_id');
        $endId = (int) $request->query->get('end_id');
        $asunto = $request->query->get('asunto');
        $template = $request->query->get('template');

        // Validar que todos los parámetros estén presentes
        if (!$startId || !$endId || !$asunto || !$template) {
            return new JsonResponse(['error' => 'Faltan parametros start_id, end_id , asunto , template'], 400);
        }

        // Obtener las inmobiliarias dentro del rango de IDs
        $inmobiliarias = $inmobiliariaRepository->findBy([
            'id' => ['BETWEEN', $startId, $endId]
        ]);

        if (empty($inmobiliarias)) {
            return new JsonResponse(['message' => 'No inmobiliarias found in the given range'], 404);
        }

        // Enviar correos electrónicos a las inmobiliarias
        foreach ($inmobiliarias as $inmobiliaria) {
            $this->processEmail($inmobiliaria, $asunto, $template);
        }

        return new JsonResponse(['message' => 'Emails sent successfully']);
    }


    #[Route('/track-email/{id}', name: 'track_email', methods: ['GET'])]
    public function trackEmail($id, EntityManagerInterface $entityManager, TelegramService $telegramService): Response
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

        $telegramService->sendMessage("📧: Se confirma lectura de {$emailEnviado->getInmobiliaria()->getNombre()}.");

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

        $this->processEmail($inmobiliarium, $template, $asunto . " " . $inmobiliarium->getDireccion());

        return new JsonResponse(['message' => 'Emails sent successfully']);
    }

    private function processEmail($inmobiliaria, $subject, $template)
    {
        $emailEnviado = new EmailEnviado();
        $emailEnviado->setInmobiliaria($inmobiliaria);
        $emailEnviado->setEmailVersion($template);
        $emailEnviado->setFecha(new \DateTime());
        $emailEnviado->setVisto(0);
        $emailEnviado->setVistoFecha(null);

        $this->em->persist($emailEnviado);
        $this->em->flush();

        $pixelUrl = $this->generateUrl('track_email', ['id' => $emailEnviado->getId()], true);

        $htmlContent = $this->twig->render($template . '.html.twig', [
            'ruta_imagen_original' => 'https://myhouseai.com/api/inmobiliaria/' . $inmobiliaria->getId() . '/imagenOriginal.png',
            'ruta_imagen_generada' => 'https://myhouseai.com/api/inmobiliaria/' . $inmobiliaria->getId() . '/imagenGenerada.png',
            'pixel_url' => $pixelUrl
        ]);

        $this->sendPHPMailerEmail($inmobiliaria->getEmail(), $subject, $htmlContent);
    }

    private function sendPHPMailerEmail($to, $subject, $htmlContent)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->Port = $this->smtpPort;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUser;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = 'ssl';
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($this->smtpFrom, $this->smtpFromName);
            //$mail->addAddress($to);
            $mail->addAddress("sebaporto@gmail.com");
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlContent;

            $mail->send();
        } catch (\Exception $e) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
}
