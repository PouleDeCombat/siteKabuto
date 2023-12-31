<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique:true)]
    private ?string $reference = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $Users = null;

    #[ORM\OneToMany(mappedBy: 'orders', targetEntity: OrdersDetails::class, orphanRemoval: true)]
    private Collection $ordersDetails;

    #[ORM\Column(type: "float")]
    private ?float $total = null;


    #[ORM\Column]
    private ?bool $isProcessed = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column]
    private bool $isPayer = false;

    #[ORM\ManyToMany(targetEntity: Abonnements::class, inversedBy: 'orders')]
    private Collection $abonnement;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Kids $kid = null;


    public function __construct()
    {
        $this->ordersDetails = new ArrayCollection();
        $this->abonnement = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->Users;
    }

    public function setUsers(?Users $Users): static
    {
        $this->Users = $Users;

        return $this;
    }

    /**
     * @return Collection<int, OrdersDetails>
     */
    public function getOrdersDetails(): Collection
    {
        return $this->ordersDetails;
    }

    public function addOrdersDetail(OrdersDetails $ordersDetail): static
    {
        if (!$this->ordersDetails->contains($ordersDetail)) {
            $this->ordersDetails->add($ordersDetail);
            $ordersDetail->setOrders($this);
        }

        return $this;
    }

    public function removeOrdersDetail(OrdersDetails $ordersDetail): static
    {
        if ($this->ordersDetails->removeElement($ordersDetail)) {
            // set the owning side to null (unless already changed)
            if ($ordersDetail->getOrders() === $this) {
                $ordersDetail->setOrders(null);
            }
        }

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getIsPayer(): ?bool
{
    return $this->isPayer;
}

public function setIsPayer(?bool $isPayer): self
{
    $this->isPayer = $isPayer ?? false;
    return $this;
}

    

    public function isIsProcessed(): ?bool
    {
        return $this->isProcessed;
    }

    public function setIsProcessed(bool $isProcessed): static
    {
        $this->isProcessed = $isProcessed;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @return Collection<int, Abonnements>
     */
    public function getAbonnement(): Collection
    {
        return $this->abonnement;
    }

    public function addAbonnement(Abonnements $abonnement): static
    {
        if (!$this->abonnement->contains($abonnement)) {
            $this->abonnement->add($abonnement);
        }

        return $this;
    }

    public function removeAbonnement(Abonnements $abonnement): static
    {
        $this->abonnement->removeElement($abonnement);

        return $this;
    }

    public function getKid(): ?Kids
    {
        return $this->kid;
    }

    public function setKid(?Kids $kid): static
    {
        $this->kid = $kid;

        return $this;
    }

   

   

}
