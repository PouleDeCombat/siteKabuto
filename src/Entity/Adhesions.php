<?php

namespace App\Entity;

use App\Repository\AdhesionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdhesionsRepository::class)]
class Adhesions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'abonnement')]
    private ?Users $user = null;

    #[ORM\ManyToOne(inversedBy: 'adhesions')]
    private ?Kids $kids = null;

    #[ORM\ManyToOne(inversedBy: 'adhesions')]
    private ?Abonnements $abonnement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $statut = null;

   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getKids(): ?Kids
    {
        return $this->kids;
    }

    public function setKids(?Kids $kids): static
    {
        $this->kids = $kids;

        return $this;
    }

    public function getAbonnement(): ?Abonnements
    {
        return $this->abonnement;
    }

    public function setAbonnement(?Abonnements $abonnement): static
    {
        $this->abonnement = $abonnement;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(?\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    
}
