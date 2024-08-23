<?php

namespace App\Entity;

use App\Repository\EmailEnviadoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailEnviadoRepository::class)]
class EmailEnviado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'emailEnviados')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Inmobiliaria $inmobiliaria = null;

    #[ORM\Column(length: 255)]
    private ?string $email_version = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column]
    private ?bool $visto = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $visto_fecha = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInmobiliaria(): ?Inmobiliaria
    {
        return $this->inmobiliaria;
    }

    public function setInmobiliaria(?Inmobiliaria $inmobiliaria): static
    {
        $this->inmobiliaria = $inmobiliaria;

        return $this;
    }

    public function getEmailVersion(): ?string
    {
        return $this->email_version;
    }

    public function setEmailVersion(string $email_version): static
    {
        $this->email_version = $email_version;

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

    public function isVisto(): ?bool
    {
        return $this->visto;
    }

    public function setVisto(bool $visto): static
    {
        $this->visto = $visto;

        return $this;
    }

    public function getVistoFecha(): ?\DateTimeInterface
    {
        return $this->visto_fecha;
    }

    public function setVistoFecha(?\DateTimeInterface $visto_fecha): static
    {
        $this->visto_fecha = $visto_fecha;

        return $this;
    }
}
