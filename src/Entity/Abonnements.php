<?php

namespace App\Entity;

use App\Repository\AbonnementsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbonnementsRepository::class)]
class Abonnements
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $categorie = null;

    #[ORM\Column(length: 100)]
    private ?string $discipline = null;

    #[ORM\Column(length: 100)]
    private ?float $prix = null;

    #[ORM\Column(length: 100)]
    private ?string $durée = null;

    #[ORM\OneToMany(mappedBy: 'abonnement', targetEntity: Adhesions::class)]
    private Collection $adhesions;

    public function __construct()
    {
        $this->adhesions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDiscipline(): ?string
    {
        return $this->discipline;
    }

    public function setDiscipline(string $discipline): static
    {
        $this->discipline = $discipline;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDurée(): ?string
    {
        return $this->durée;
    }

    public function setDurée(string $durée): static
    {
        $this->durée = $durée;

        return $this;
    }

    /**
     * @return Collection<int, Adhesions>
     */
    public function getAdhesions(): Collection
    {
        return $this->adhesions;
    }

    public function addAdhesion(Adhesions $adhesion): static
    {
        if (!$this->adhesions->contains($adhesion)) {
            $this->adhesions->add($adhesion);
            $adhesion->setAbonnement($this);
        }

        return $this;
    }

    public function removeAdhesion(Adhesions $adhesion): static
    {
        if ($this->adhesions->removeElement($adhesion)) {
            // set the owning side to null (unless already changed)
            if ($adhesion->getAbonnement() === $this) {
                $adhesion->setAbonnement(null);
            }
        }

        return $this;
    }
}
