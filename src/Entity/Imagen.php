<?php

namespace App\Entity;

use App\Repository\ImagenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImagenRepository::class)]
class Imagen
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private ?string $id = null;

    #[ORM\Column(type: Types::BLOB, nullable: false)]
    private $imgOrigen = null;

    #[ORM\ManyToOne(inversedBy: 'imagens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $Usuario = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(length: 255)]
    private ?string $estilo = null;

    #[ORM\Column(length: 255)]
    private ?string $tipoHabitacion = null;

    #[ORM\Column(length: 255)]
    private ?string $renderId = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $declutteredImage = null;

    #[ORM\Column(length: 255)]
    private ?string $ip_remota = null;

    public function __construct()
    {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {   
        $this->id = $id;

        return $this;
    }


    public function getImgOrigen()
    {
        return $this->imgOrigen;
    }

    public function setImgOrigen($imgOrigen): static
    {
        $this->imgOrigen = $imgOrigen;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->Usuario;
    }

    public function setUsuario(?Usuario $Usuario): static
    {
        $this->Usuario = $Usuario;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getEstilo(): ?string
    {
        return $this->estilo;
    }

    public function setEstilo(string $estilo): static
    {
        $this->estilo = $estilo;

        return $this;
    }

    public function getTipoHabitacion(): ?string
    {
        return $this->tipoHabitacion;
    }

    public function setTipoHabitacion(string $tipoHabitacion): static
    {
        $this->tipoHabitacion = $tipoHabitacion;

        return $this;
    }


    public function getRenderId(): ?string
    {
        return $this->renderId;
    }

    public function setRenderId(string $renderId): static
    {
        $this->renderId = $renderId;

        return $this;
    }

    public function getDeclutteredImage()
    {
        return $this->declutteredImage;
    }

    public function setDeclutteredImage($declutteredImage): static
    {
        $this->declutteredImage = $declutteredImage;

        return $this;
    }

    public function getIpRemota(): ?string
    {
        return $this->ip_remota;
    }

    public function setIpRemota(string $ip_remota): static
    {
        $this->ip_remota = $ip_remota;

        return $this;
    }
}
