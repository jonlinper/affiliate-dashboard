<?php

namespace App\Entity;

use App\Repository\SaleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SaleRepository::class)]
#[UniqueEntity(fields: ['reference'])]
class Sale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $venueName = null;

    #[ORM\Column(length: 255)]
    private ?string $eventName = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    private ?int $numTickets = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $buyDate = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $reference = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Visit $visit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVenueName(): ?string
    {
        return $this->venueName;
    }

    public function setVenueName(string $venueName): self
    {
        $this->venueName = $venueName;

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getNumTickets(): ?int
    {
        return $this->numTickets;
    }

    public function setNumTickets(int $numTickets): self
    {
        $this->numTickets = $numTickets;

        return $this;
    }

    public function getBuyDate(): ?\DateTimeImmutable
    {
        return $this->buyDate;
    }

    public function setBuyDate(\DateTimeImmutable $buyDate): self
    {
        $this->buyDate = $buyDate;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getVisit(): ?Visit
    {
        return $this->visit;
    }

    public function setVisit(?Visit $visit): self
    {
        $this->visit = $visit;

        return $this;
    }
}
