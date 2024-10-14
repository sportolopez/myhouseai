<?php

namespace App\Service;

use App\Entity\EmailEnviado;
use App\Entity\UsuarioCompras;
use App\Service\EncryptionService;
use PHPMailer\PHPMailer\PHPMailer;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class EmailService
{
    private $em;
    private $twig;
    private $encryptionService;
    private $smtpHost = 'c1802222.ferozo.com';
    private $smtpPort = 465;
    private $smtpUser = 'ventas@myhouseai.com';
    private $smtpPassword = '1@2z5NT0xY';
    private $smtpFrom = 'ventas@myhouseai.com';
    private $smtpFromName = 'Martin';

    public function __construct(
        EntityManagerInterface $em, 
        Environment $twig, 
        EncryptionService $encryptionService
    ) {
        $this->em = $em;
        $this->twig = $twig;
        $this->encryptionService = $encryptionService;
    }

    public function processEmail($inmobiliaria, $subject, $template, $conAdjuntos)
    {
        // Crear y persistir el registro del email enviado
        $emailEnviado = new EmailEnviado();
        $emailEnviado->setInmobiliaria($inmobiliaria);
        $emailEnviado->setEmailVersion($template);
        $emailEnviado->setFecha(new \DateTime());
        $emailEnviado->setVisto(0);
        $emailEnviado->setVistoFecha(null);

        $this->em->persist($emailEnviado);
        $this->em->flush();


        // URL del pixel de seguimiento
        $pixelUrl = 'https://myhouseai.com/api/track-email/' . $emailEnviado->getId();

        // Generar contenido HTML del correo utilizando Twig
        $htmlContent = $this->twig->render($template . '.html.twig', [
            'ruta_imagen_original' => 'https://myhouseai.com/api/inmobiliaria/' . $inmobiliaria->getId() . '/imagenOriginal.png',
            'ruta_imagen_generada' => 'https://myhouseai.com/api/inmobiliaria/' . $inmobiliaria->getId() . '/imagenGenerada.png',
            'pixel_url' => $pixelUrl
        ]);



        // Obtener la dirección de la inmobiliaria y encriptar el email
        $domicilio = $inmobiliaria->getDireccion();
        error_log("Domicilio: $domicilio");

        $sessionId = $this->encryptionService->encrypt($inmobiliaria->getEmail());

        // Reemplazar placeholders en el asunto y en el contenido del email
        $subject = str_replace('{domicilio}', $domicilio, $subject);
        $htmlContent = str_replace('{session}', $sessionId, $htmlContent);
        error_log("Asunto modificado: $subject");


        $imagePaths = [];

        // Si se indican adjuntos, agregar las URLs de las imágenes
        // Si se indican adjuntos, agregar las rutas de las imágenes desde la carpeta local
        if ($conAdjuntos) {
            $imagePaths = [
                __DIR__ . '/../../public/images/decorar.png',
                __DIR__ . '/../../public/images/limpiar.jpg'
            ];
        }


        // Enviar el correo utilizando PHPMailer
        $this->sendPHPMailerEmail($inmobiliaria->getEmail(), $subject, $htmlContent,$imagePaths);
    }

    public function emailCompra(UsuarioCompras $usuarioCompra)
    {
        // Generar contenido HTML del correo utilizando Twig
        $htmlContent = $this->twig->render('Email_compra.html.twig', [
            'fecha' => $usuarioCompra->getFecha()->format('d/m/Y H:i'),
            'cantidad' => $usuarioCompra->getCantidad(),
            'valor' => '$ ' . $usuarioCompra->getMonto()
        ]);

        // Reemplazar placeholders en el asunto y en el contenido del email
        $subject = "MyHouseAi :: Gracias por tu compra!";
        
        $this->sendPHPMailerEmail($usuarioCompra->getUsuario()->getEmail(), $subject, $htmlContent);
    }

    
    private function sendPHPMailerEmail($to, $subject, $htmlContent, array $imagePaths = [])
    {
    
        $mail = new PHPMailer(true);

        try {
            // Configurar PHPMailer
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->Port = $this->smtpPort;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUser;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = 'ssl';
            $mail->CharSet = 'UTF-8';

            // Configurar remitente y destinatario
            $mail->setFrom($this->smtpFrom, $this->smtpFromName);
            $mail->addAddress($to);
            //$mail->addAddress("correo1@example.com"); // Para agregar más destinatarios si es necesario

            // Configurar el contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlContent;

            if (!empty($imagePaths)) {
                foreach ($imagePaths as $imagePath) {
                    if (file_exists($imagePath)) {
                        $mail->addAttachment($imagePath);
                    } else {

                        error_log("No se encontró la imagen en la ruta: $imagePath");
                        throw new \RuntimeException("No se encontro la ruta: $imagePath");
                    }
                }
            }
            // Enviar el correo
            $mail->send();
        } catch (\Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            throw $e;
        }
    }
}
