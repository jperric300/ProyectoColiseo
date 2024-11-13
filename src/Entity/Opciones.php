<?php

namespace App\Entity;

use App\Repository\OpcionesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OpcionesRepository::class)]
class Opciones
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Usuarios $id_usuario = null;

    // Campo 'objetivo' solo puede tener valores 1, 2 o 3. Por defecto es 1.
    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private ?int $objetivo = 1;

    // Campo 'disponibilidad' solo puede tener valores 3, 4 o 5. Por defecto es 3.
    #[ORM\Column(type: 'integer', options: ['default' => 3])]
    private ?int $disponibilidad = 3;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getIdUsuario(): ?Usuarios
    {
        return $this->id_usuario;
    }

    public function setIdUsuario(?Usuarios $id_usuario): static
    {
        $this->id_usuario = $id_usuario;

        return $this;
    }

    public function getObjetivo(): ?int
    {
        return $this->objetivo;
    }

    public function setObjetivo(int $objetivo): static
    {
        if (!in_array($objetivo, [1, 2, 3])) {
            throw new \InvalidArgumentException('El objetivo debe ser 1, 2 o 3.');
        }
        $this->objetivo = $objetivo;

        return $this;
    }

    public function getDisponibilidad(): ?int
    {
        return $this->disponibilidad;
    }

    public function setDisponibilidad(int $disponibilidad): static
    {
        if (!in_array($disponibilidad, [3, 4, 5])) {
            throw new \InvalidArgumentException('La disponibilidad debe ser 3, 4 o 5.');
        }
        $this->disponibilidad = $disponibilidad;

        return $this;
    }
}
