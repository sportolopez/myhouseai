<?php

namespace App\Entity;

use App\Repository\InmobiliariaRepository;
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

    #[ORM\Column(type: Types::BINARY)]
    private $visto = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha_ultimo_visto = null;

    #[ORM\Column(type: Types::BLOB)]
    private $imagen_ejemplo = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

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

    public function getVisto()
    {
        return $this->visto;
    }

    public function setVisto($visto): static
    {
        $this->visto = $visto;

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
}
