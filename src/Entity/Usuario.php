<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, unique:true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?int $cantidadImagenesDisponibles = null;

    public function __construct()
    {
        $this->imagens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCantidadImagenesDisponibles(): ?int
    {
        return $this->cantidadImagenesDisponibles;
    }

    public function setCantidadImagenesDisponibles(int $cantidadImagenesDisponibles): static
    {
        $this->cantidadImagenesDisponibles = $cantidadImagenesDisponibles;

        return $this;
    }

        // Método para convertir la clase a cadena
        public function __toString()
        {
            return "Id: {$this->id}, Nombre: {$this->nombre}";
        }
}
