<?php

namespace App\Entity;

use App\Repository\VariacionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VariacionRepository::class)]
class Variacion
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private ?string $id = null;


    #[ORM\ManyToOne(inversedBy: 'variaciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Imagen $imagen = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(type: Types::BLOB)]
    private $img = null;

    #[ORM\Column(length: 255)]
    private ?string $roomType = null;

    #[ORM\Column(length: 255)]
    private ?string $style = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {   
        $this->id = $id;

        return $this;
    }

    public function getImagen(): ?Imagen
    {
        return $this->imagen;
    }

    public function setImagen(?Imagen $imagen): static
    {
        $this->imagen = $imagen;

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

    public function getRoomType(): ?string
    {
        return $this->roomType;
    }

    public function setRoomType(string $roomType): static
    {
        $this->roomType = $roomType;

        return $this;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setStyle(string $style): static
    {
        $this->style = $style;

        return $this;
    }
}
