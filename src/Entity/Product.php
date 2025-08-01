<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $unitPrice = null;

    #[ORM\Column]
    private ?bool $withConsigne = false;

    #[ORM\OneToOne(mappedBy: 'product', cascade: ['persist', 'remove'])]
    private ?Stock $stock = null;

    #[ORM\Column(nullable: true)]
    private ?float $saleUnit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $picto = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $color = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUnitPrice(): ?float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function isWithConsigne(): ?bool
    {
        return $this->withConsigne;
    }

    public function setWithConsigne(bool $withConsigne): static
    {
        $this->withConsigne = $withConsigne;

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(Stock $stock): static
    {
        // set the owning side of the relation if necessary
        if ($stock->getProduct() !== $this) {
            $stock->setProduct($this);
        }

        $this->stock = $stock;

        return $this;
    }

    public function getSaleUnit(): ?float
    {
        return $this->saleUnit;
    }

    public function setSaleUnit(?float $saleUnit): static
    {
        $this->saleUnit = $saleUnit;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'unitPrice' => $this->getUnitPrice(),
            'withConsigne' => $this->isWithConsigne(),
            'saleUnit' => 0.25,
            'picto' => $this->getPicto(),
            'color' => $this->getColor()
        ];
    }

    public function getPicto(): ?string
    {
        return $this->picto;
    }

    public function setPicto(?string $picto): static
    {
        $this->picto = $picto;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }
}
