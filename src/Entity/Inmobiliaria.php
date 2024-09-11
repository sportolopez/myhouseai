<?php

namespace App\Entity;

use App\Repository\InmobiliariaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InmobiliariaRepository::class)]
class Inmobiliaria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    private ?int $vistos = null;

    private ?int $enviados = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha_ultimo_visto = null;

    #[ORM\Column(type: Types::BLOB)]
    private $imagen_ejemplo = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $whatsapp = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $telefono = null;

    #[ORM\Column(length: 255)]
    private ?string $link_venta = null;

    #[ORM\Column(length: 255)]
    private ?string $link_alquiler = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $imagen_generada = null;

    #[ORM\Column(length: 255)]
    private ?string $ultimo_envio_version = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ultimo_envio_fecha = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $direccion = null;

    
    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getVistos()
    {
        return $this->vistos;
    }

    public function setVistos($vistos): static
    {
        $this->vistos = $vistos;

        return $this;
    }

    
    public function getEnviados()
    {
        return $this->enviados;
    }

    public function setEnviados($enviados): static
    {
        $this->enviados = $enviados;

        return $this;
    }
    public function getFechaUltimoVisto(): ?\DateTimeInterface
    {
        return $this->fecha_ultimo_visto;
    }

    public function setFechaUltimoVisto(\DateTimeInterface $fecha_ultimo_visto): static
    {
        $this->fecha_ultimo_visto = $fecha_ultimo_visto;

        return $this;
    }

    public function getImagenEjemplo()
    {
        return $this->imagen_ejemplo;
    }

    public function setImagenEjemplo($imagen_ejemplo): static
    {
        $this->imagen_ejemplo = $imagen_ejemplo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    public function setWhatsapp(?string $whatsapp): static
    {
        $this->whatsapp = $whatsapp;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getLinkVenta(): ?string
    {
        return $this->link_venta;
    }

    public function setLinkVenta(string $link_venta): static
    {
        $this->link_venta = $link_venta;

        return $this;
    }

    public function getLinkAlquiler(): ?string
    {
        return $this->link_alquiler;
    }

    public function setLinkAlquiler(string $link_alquiler): static
    {
        $this->link_alquiler = $link_alquiler;

        return $this;
    }

    public function getImagenGenerada()
    {
        return $this->imagen_generada;
    }

    public function setImagenGenerada($imagen_generada): static
    {
        $this->imagen_generada = $imagen_generada;

        return $this;
    }

    public function getUltimoEnvioVersion(): ?string
    {
        return $this->ultimo_envio_version;
    }

    public function setUltimoEnvioVersion(string $ultimo_envio_version): static
    {
        $this->ultimo_envio_version = $ultimo_envio_version;

        return $this;
    }

    public function getUltimoEnvioFecha(): ?string
    {
        return $this->ultimo_envio_fecha;
    }

    public function setUltimoEnvioFecha(?string $ultimo_envio_fecha): static
    {
        $this->ultimo_envio_fecha = $ultimo_envio_fecha;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }

}
