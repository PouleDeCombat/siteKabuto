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

    // #[ORM\OneToMany(mappedBy: 'abonnement', targetEntity: Adhesions::class)]
    // private Collection $adhesions;

    #[ORM\ManyToMany(targetEntity: Adhesions::class, mappedBy: "abonnements")]
    private Collection $adhesions;


    #[ORM\ManyToMany(targetEntity: Orders::class, mappedBy: 'abonnement')]
    private Collection $orders;

    #[ORM\ManyToMany(targetEntity: Kids::class, mappedBy: 'abonnement')]
    private Collection $kids;

    public function __construct()
    {
        $this->adhesions = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->kids = new ArrayCollection();
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
        $adhesion->addAbonnement($this);  // ensure bidirectionality
    }
    return $this;
    }

public function removeAdhesion(Adhesions $adhesion): static
    {
    if ($this->adhesions->removeElement($adhesion)) {
        $adhesion->removeAbonnement($this);  // ensure bidirectionality
    }
    return $this;
    }


    /**
     * @return Collection<int, Orders>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Orders $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->addAbonnement($this);
        }

        return $this;
    }

    public function removeOrder(Orders $order): static
    {
        if ($this->orders->removeElement($order)) {
            $order->removeAbonnement($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Kids>
     */
    public function getKids(): Collection
    {
        return $this->kids;
    }

    public function addKid(Kids $kid): static
    {
        if (!$this->kids->contains($kid)) {
            $this->kids->add($kid);
            $kid->addAbonnement($this);
        }

        return $this;
    }

    public function removeKid(Kids $kid): static
    {
        if ($this->kids->removeElement($kid)) {
            $kid->removeAbonnement($this);
        }

        return $this;
    }
}
