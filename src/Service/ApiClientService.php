<?php

namespace App\Service;

use App\Entity\Imagen;
use App\Entity\Variacion;
use Ramsey\Uuid\Uuid;

class ApiClientService
{
    public function generarImagen(Imagen $imagen)
    {
        $unaVariacion = new Variacion();
        $unaVariacion->setImagen($imagen);
        $unaVariacion->setFecha(new \DateTime());

        //El id que viene del servicio
        $unaVariacion->setId(Uuid::uuid4()->toString());
        //Imagen obtenida
        $unaVariacion->setImg($imagen->getImgOrigen());

        return $unaVariacion;
    }

    public function generarVariacion(string $to, string $subject, string $body)
    {
        // Lógica para enviar correo electrónico
    }
}

