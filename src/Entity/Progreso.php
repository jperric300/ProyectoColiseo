<?php
namespace App\Entity;

use App\Repository\ProgresoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProgresoRepository::class)]
class Progreso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Relación con la entidad Usuarios
    #[ORM\ManyToOne(targetEntity: Usuarios::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id', nullable: false)]
    private ?Usuarios $usuario = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)] // Cambiado a DATETIME_MUTABLE
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $peso = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuarios
    {
        return $this->usuario;
    }

    public function setUsuario(Usuarios $usuario): static
    {
        $this->usuario = $usuario;

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

    public function getPeso(): ?string
    {
        return $this->peso;
    }

    public function setPeso(?string $peso): static
    {
        $this->peso = $peso;

        return $this;
    }
}
