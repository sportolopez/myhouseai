<?php

namespace App\Entity;

use App\Doctrine\Types\EstadoCompra;
use App\Repository\UsuarioComprasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsuarioComprasRepository::class)]
class UsuarioCompras
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $Usuario = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $Fecha = null;

    #[ORM\Column]
    private ?int $Cantidad = null;

    #[ORM\Column]
    private ?float $Monto = null;

    #[ORM\Column(length: 255)]
    private ?string $moneda = null;

    #[ORM\Column(length: 255)]
    private ?string $medioPago = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preferenceId = null;

    #[ORM\Column(length: 50)]
    private ?string $estado = null;

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->Fecha;
    }

    public function setFecha(\DateTimeInterface $Fecha): static
    {
        $this->Fecha = $Fecha;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->Cantidad;
    }

    public function setCantidad(int $Cantidad): static
    {
        $this->Cantidad = $Cantidad;

        return $this;
    }

    public function getMonto(): ?float
    {
        return $this->Monto;
    }

    public function setMonto(float $Monto): static
    {
        $this->Monto = $Monto;

        return $this;
    }

    public function getMoneda(): ?string
    {
        return $this->moneda;
    }

    public function setMoneda(string $moneda): static
    {
        $this->moneda = $moneda;

        return $this;
    }

    public function getMedioPago(): ?string
    {
        return $this->medioPago;
    }

    public function setMedioPago(string $medioPago): static
    {
        $this->medioPago = $medioPago;

        return $this;
    }

    public function getPreferenceId(): ?string
    {
        return $this->preferenceId;
    }

    public function setPreferenceId(?string $preferenceId): static
    {
        $this->preferenceId = $preferenceId;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }
}
