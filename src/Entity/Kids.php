<?php

namespace App\Entity;

use App\Repository\KidsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KidsRepository::class)]
/**
 * @ORM\HasLifecycleCallbacks()
 */
class Kids
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    private ?string $zipcode = null;

    #[ORM\Column(length: 255)]
    private ?string $ville = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_de_naissance = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu_de_naissance = null;

    #[ORM\ManyToOne(inversedBy: 'kids')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\ManyToMany(targetEntity: KidsCompetitions::class, mappedBy: 'kids')]
    private Collection $kidsCompetitions;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ceinture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $categoriePoid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trancheAge = null;

    #[ORM\OneToMany(mappedBy: 'kids', targetEntity: Adhesions::class)]
    private Collection $adhesions;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $certificatMedical = null;




   

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->kidsCompetitions = new ArrayCollection();
        $this->adhesions = new ArrayCollection();
    }

  


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): static
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getDateDeNaissance(): ?\DateTimeInterface
    {
        return $this->date_de_naissance;
    }

    public function setDateDeNaissance(\DateTimeInterface $date_de_naissance): static
    {
        $this->date_de_naissance = $date_de_naissance;

        return $this;
    }

    public function getLieuDeNaissance(): ?string
    {
        return $this->lieu_de_naissance;
    }

    public function setLieuDeNaissance(string $lieu_de_naissance): static
    {
        $this->lieu_de_naissance = $lieu_de_naissance;

        return $this;
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

    /**
     * @return Collection<int, KidsCompetitions>
     */
    public function getKidsCompetitions(): Collection
    {
        return $this->kidsCompetitions;
    }

    public function addKidsCompetition(KidsCompetitions $kidsCompetition): static
    {
        if (!$this->kidsCompetitions->contains($kidsCompetition)) {
            $this->kidsCompetitions->add($kidsCompetition);
            $kidsCompetition->addKid($this);
        }

        return $this;
    }

    public function removeKidsCompetition(KidsCompetitions $kidsCompetition): static
    {
        if ($this->kidsCompetitions->removeElement($kidsCompetition)) {
            $kidsCompetition->removeKid($this);
        }

        return $this;
    }

    public function getCeinture(): ?string
    {
        return $this->ceinture;
    }

    public function setCeinture(string $ceinture): static
    {
        $this->ceinture = $ceinture;

        return $this;
    }

    public function getCategoriePoid(): ?string
    {
        return $this->categoriePoid;
    }

    public function setCategoriePoid(string $categoriePoid): static
    {
        $this->categoriePoid = $categoriePoid;

        return $this;
    }

    public function getTrancheAge(): ?string
    {
        return $this->trancheAge;
    }

    public function setTrancheAge(string $trancheAge): static
    {
        $this->trancheAge = $trancheAge;

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
            $adhesion->setKids($this);
        }

        return $this;
    }

    public function removeAdhesion(Adhesions $adhesion): static
    {
        if ($this->adhesions->removeElement($adhesion)) {
            // set the owning side to null (unless already changed)
            if ($adhesion->getKids() === $this) {
                $adhesion->setKids(null);
            }
        }

        return $this;
    }

    public function getCertificatMedical(): ?string
    {
        return $this->certificatMedical;
    }

    public function setCertificatMedical(?string $certificatMedical): static
    {
        $this->certificatMedical = $certificatMedical;

        return $this;
    }

   
}
