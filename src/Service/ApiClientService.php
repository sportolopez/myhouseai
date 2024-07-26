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
        $unaVariacion->setRoomType($imagen->getTipoHabitacion());
        $unaVariacion->setStyle($imagen->getEstilo());
        //El id que viene del servicio
        $unaVariacion->setId(Uuid::uuid4()->toString());
        //Imagen obtenida
        $unaVariacion->setImg($imagen->getImgOrigen());

        return $unaVariacion;
    }

    public function generarVariacion(Imagen $imagen,String $generation_id, String $roomType, String $style)
    {
        $unaVariacion = new Variacion();
        $unaVariacion->setImagen($imagen);
        $unaVariacion->setFecha(new \DateTime());
        $unaVariacion->setRoomType($roomType );
        $unaVariacion->setStyle($style);
        //El id que viene del servicio
        $unaVariacion->setId(Uuid::uuid4()->toString());
        //Imagen obtenida
        $unaVariacion->setImg($imagen->getImgOrigen());

        return $unaVariacion;
    }
}

