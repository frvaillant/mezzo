<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, PurchaseLine>
     */
    #[ORM\OneToMany(targetEntity: PurchaseLine::class, mappedBy: 'purchase', orphanRemoval: true)]
    private Collection $line;

    public function __construct()
    {
        $this->line = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, PurchaseLine>
     */
    public function getLine(): Collection
    {
        return $this->line;
    }

    public function addLine(PurchaseLine $line): static
    {
        if (!$this->line->contains($line)) {
            $this->line->add($line);
            $line->setPurchase($this);
        }

        return $this;
    }

    public function removeLine(PurchaseLine $line): static
    {
        if ($this->line->removeElement($line)) {
            // set the owning side to null (unless already changed)
            if ($line->getPurchase() === $this) {
                $line->setPurchase(null);
            }
        }

        return $this;
    }
}
