<?php

namespace App\Entity;

use App\Repository\PlanesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanesRepository::class)]
class Planes  implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column]
    private ?float $valor = null;

    #[ORM\Column(length: 255)]
    private ?string $preferenceId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getValor(): ?float
    {
        return $this->valor;
    }

    public function setValor(float $valor): static
    {
        $this->valor = $valor;

        return $this;
    }

    public function jsonSerialize() {
        return [
            'cantidad' => $this->cantidad,
            'valor' => $this->valor,
            'preferenceId' => $this->preferenceId
        ];
    }

    public function getPreferenceId(): ?string
    {
        return $this->preferenceId;
    }

    public function setPreferenceId(string $preferenceId): static
    {
        $this->preferenceId = $preferenceId;

        return $this;
    }
}
