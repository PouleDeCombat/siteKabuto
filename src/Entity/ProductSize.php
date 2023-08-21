<?php

namespace App\Entity;

use App\Repository\ProductSizeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductSizeRepository::class)]
class ProductSize
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $taille = null;

    #[ORM\ManyToMany(targetEntity: Products::class, mappedBy: 'size')]
    private Collection $size;

    public function __construct()
    {
        $this->size = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(?string $taille): static
    {
        $this->taille = $taille;

        return $this;
    }

    /**
     * @return Collection<int, Products>
     */
    public function getSize(): Collection
    {
        return $this->size;
    }

    public function addSize(Products $size): static
    {
        if (!$this->size->contains($size)) {
            $this->size->add($size);
            $size->addSize($this);
        }

        return $this;
    }

    public function removeSize(Products $size): static
    {
        if ($this->size->removeElement($size)) {
            $size->removeSize($this);
        }

        return $this;
    }
}