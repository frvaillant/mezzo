<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
#[ORM\HasLifecycleCallbacks]
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentMode = null;

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

    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->createdAt = new \DateTimeImmutable();

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

    public function getPaymentMode(): ?string
    {
        return $this->paymentMode;
    }

    public function setPaymentMode(?string $paymentMode): static
    {
        $this->paymentMode = $paymentMode;

        return $this;
    }


    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getLine() as $line) {
            $total += $line->getTotal();
        }
        return $total;
    }

    public function getConsigne(): float
    {
        $total = 0;
        foreach ($this->getLine() as $line) {
            $total += $line->getConsigne();
        }
        return $total;
    }
}
