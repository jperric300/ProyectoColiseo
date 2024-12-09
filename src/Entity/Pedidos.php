<?php

namespace App\Entity;

use App\Repository\PedidosRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PedidosRepository::class)]
class Pedidos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private ?int $userId = null;

    #[ORM\Column(type: 'json')]
    private array $productos = [];

    #[ORM\Column(type: 'json')]
    private array $cantidades = [];

    #[ORM\Column(type: 'float')]
    private ?float $precioTotal = null;

    public function getId(): ?int
    {
        return $id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getProductos(): array
    {
        return $this->productos;
    }

    public function setProductos(array $productos): self
    {
        $this->productos = $productos;

        return $this;
    }

    public function getCantidades(): array
    {
        return $this->cantidades;
    }

    public function setCantidades(array $cantidades): self
    {
        $this->cantidades = $cantidades;

        return $this;
    }

    public function getPrecioTotal(): ?float
    {
        return $this->precioTotal;
    }

    public function setPrecioTotal(float $precioTotal): self
    {
        $this->precioTotal = $precioTotal;

        return $this;
    }
}
