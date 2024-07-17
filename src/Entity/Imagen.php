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

    #[ORM\OneToMany(mappedBy: 'imagen', targetEntity: Variacion::class)]
    private Collection $variaciones;

    public function __construct()
    {
        $this->variaciones = new ArrayCollection();
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

    /**
     * @return Collection<int, Variacion>
     */
    public function getVariaciones(): Collection
    {
        return $this->variaciones;
    }

    public function addVariacione(Variacion $variacione): static
    {
        if (!$this->variaciones->contains($variacione)) {
            $this->variaciones->add($variacione);
            $variacione->setImagen($this);
        }

        return $this;
    }

    public function removeVariacione(Variacion $variacione): static
    {
        if ($this->variaciones->removeElement($variacione)) {
            // set the owning side to null (unless already changed)
            if ($variacione->getImagen() === $this) {
                $variacione->setImagen(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        $variacionesStr = array_map(function($variacion) {
            return $variacion->getId();
        }, $this->variaciones->toArray());

        return sprintf(
            "ID: %s, Imagen Origen: %s, Usuario: %s, Fecha: %s, Estilo: %s, Tipo Habitación: %s, Variaciones: [%s]",
            $this->id,
            $this->imgOrigen,
            $this->Usuario ? $this->Usuario->getId() : 'N/A',
            $this->fecha ? $this->fecha->format('Y-m-d H:i:s') : 'N/A',
            $this->estilo,
            $this->tipoHabitacion,
            implode(', ', $variacionesStr)
        );
    }
}
