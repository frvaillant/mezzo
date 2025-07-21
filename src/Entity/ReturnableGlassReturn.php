<?php

namespace App\Entity;

use App\Repository\ReturnableGlassReturnRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReturnableGlassReturnRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ReturnableGlassReturn
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $returnedAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReturnedAt(): ?\DateTime
    {
        return $this->returnedAt;
    }

    #[ORM\PrePersist]
    public function setReturnedAt(): static
    {
        $this->returnedAt = new \DateTime();

        return $this;
    }

}
