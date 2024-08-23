<?php
namespace App\Controller;

use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\InmobiliariaRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EmailEnviado;

class EmailController extends AbstractController
{
    #[Route('/send-emails', name: 'send_emails', methods: ['POST'])]
    public function sendEmails(
        Request $request,
        MailerInterface $mailer,
        InmobiliariaRepository $inmobiliariaRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validar que los parámetros 'start_id' y 'end_id' estén presentes
        if (!isset($data['start_id']) || !isset($data['end_id'])) {
            return new JsonResponse(['error' => 'Missing parameters'], 400);
        }

        $startId = (int) $data['start_id'];
        $endId = (int) $data['end_id'];

        // Obtener las inmobiliarias dentro del rango de IDs
        $inmobiliarias = $inmobiliariaRepository->findInRange($startId, $endId);

        if (empty($inmobiliarias)) {
            return new JsonResponse(['message' => 'No inmobiliarias found in the given range'], 404);
        }

        // Leer el contenido del archivo HTML
        $htmlTemplate = file_get_contents(__DIR__ . '/../../templates/email/template.html');

        foreach ($inmobiliarias as $inmobiliaria) {
            // Crear un nuevo registro de EmailEnviado
            $emailEnviado = new EmailEnviado();
            $emailEnviado->setInmobiliariaId($inmobiliaria->getId());
            $emailEnviado->setEmailVersion('v1'); // Puedes ajustar según sea necesario
            $emailEnviado->setFecha(new \DateTime());
            $emailEnviado->setVisto(0); // Inicialmente no visto
            $emailEnviado->setVistoFecha(null);

            $entityManager->persist($emailEnviado);
            $entityManager->flush(); // Esto nos da el ID del nuevo correo enviado

            // Crear la URL del píxel con el ID único
            $pixelUrl = $this->generateUrl('track_email', ['id' => $emailEnviado->getId()], true);

            // Reemplazar placeholders en el HTML
            $htmlContent = str_replace(
                ['{{nombre}}', '{{direccion}}', '{{ruta_imagen}}', '{{pixel_url}}'],
                [$inmobiliaria->getNombre(), $inmobiliaria->getDireccion(), $inmobiliaria->getRutaImagen(), $pixelUrl],
                $htmlTemplate
            );

            // Crear el correo electrónico
            $email = (new Email())
                ->from('tu_email@ejemplo.com')
                ->to($inmobiliaria->getEmail())
                ->subject('Información Importante')
                ->html($htmlContent);

            // Enviar el correo
            $mailer->send($email);
        }

        return new JsonResponse(['message' => 'Emails sent successfully']);
    }

    #[Route('/track-email/{id}', name: 'track_email', methods: ['GET'])]
    public function trackEmail($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $emailEnviado = $entityManager->getRepository(EmailEnviado::class)->find($id);

        if (!$emailEnviado) {
            return new JsonResponse(['message' => 'Email not found'], 404);
        }

        if ($emailEnviado->getVisto() === 0) {
            $emailEnviado->setVisto(1);
            $emailEnviado->setVistoFecha(new \DateTime());
            $entityManager->flush();
        }

        // Retornar una imagen en blanco de 1x1 píxel
        $response = new Response();
        $response->headers->set('Content-Type', 'image/gif');
        $response->setContent(base64_decode(
            'R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='
        ));

        return $response;
    }


    #[Route('/test-emails', name: 'test_emails', methods: ['GET'])]
    public function testSendMail(
        Request $request
    ): JsonResponse {
        $para = 'sebaporto@gmail.com';
        $asunto = 'Prueba de correo con PHPMailer';
        $mensaje = '<html><body><h1>Hola!</h1><p>Este es un correo de prueba usando PHPMailer.</p></body></html>';
        
        self::enviarCorreo($para, $asunto, $mensaje);

        return new JsonResponse(['message' => 'Emails sent successfully']);
    }
    function enviarCorreo($para, $asunto, $mensaje) {
        $mail = new PHPMailer(true);
    
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'c1802222.ferozo.com'; // Servidor SMTP
            $mail->Port = 465; // Puerto SMTP (587 para TLS, 465 para SSL)
            $mail->SMTPAuth = true;
            $mail->Username = 'ventas@myhouseai.com'; // Usuario SMTP
            $mail->Password = '@9JhcWsLVismDUcU4'; // Contraseña SMTP
            $mail->SMTPSecure = 'tls'; // 'ssl' o 'tls'
    
            // Remitente
            $mail->setFrom('ventas@myhouseai.com', 'Ventassss');
            $mail->addAddress($para); // Destinatario
    
            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $mensaje;
    
            $mail->send();
            echo 'Correo enviado exitosamente.';
        } catch (\Exception $e) {
            echo "El correo no pudo ser enviado. Error: {$mail->ErrorInfo}";
        }
    }
}
