<?php

namespace App\Entity;

use App\Repository\VariacionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VariacionRepository::class)]
class Variacion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'variaciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Imagen $Imagen = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(type: Types::BLOB)]
    private $img = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImagen(): ?Imagen
    {
        return $this->Imagen;
    }

    public function setImagen(?Imagen $Imagen): static
    {
        $this->Imagen = $Imagen;

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

    public function getImg()
    {
        return $this->img;
    }

    public function setImg($img): static
    {
        $this->img = $img;

        return $this;
    }
}
