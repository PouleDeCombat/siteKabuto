<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use app\Entity\Competiteurs;
use App\Repository\UsersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];


    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 5)]
    private ?string $zipcode = null;

    #[ORM\Column(length: 150)]
    private ?string $ville = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 100)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profession = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_de_naissance = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu_de_naissance = null;


    



   

  

    #[ORM\OneToMany(mappedBy: 'Users', targetEntity: Orders::class)]
    private Collection $orders;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $categoriePoid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ceinture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $kimono = null;

    #[ORM\ManyToMany(targetEntity: Competitions::class, inversedBy: 'users')]
    private ?Collection $competitions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Kids::class)]
    private Collection $kids;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Adhesions::class)]
    private Collection $abonnement;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $statutAbonnement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $debutAbonnement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $finAbonnement = null;

  

    public function __construct(){
        $this->created_at = new \DateTimeImmutable();
        $this->orders = new ArrayCollection();
        $this->competitions = new ArrayCollection();
        $this->kids = new ArrayCollection();
        $this->abonnement = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): static
    {
        $this->profession = $profession;

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
            $order->setUsers($this);
        }

        return $this;
    }

    public function removeOrder(Orders $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUsers() === $this) {
                $order->setUsers(null);
            }
        }

        return $this;
    }

    public function getCategoriePoid(): ?string
    {
        return $this->categoriePoid;
    }

    public function setCategoriePoid(?string $categoriePoid): static
    {
        $this->categoriePoid = $categoriePoid;

        return $this;
    }

    public function getCeinture(): ?string
    {
        return $this->ceinture;
    }

    public function setCeinture(?string $ceinture): static
    {
        $this->ceinture = $ceinture;

        return $this;
    }

    public function getKimono(): ?string
    {
        return $this->kimono;
    }

    public function setKimono(?string $kimono): static
    {
        $this->kimono = $kimono;

        return $this;
    }

    /**
     * @return Collection<int, Competitions>
     */
    public function getCompetitions(): Collection
    {
        return $this->competitions;
    }

    public function addCompetition(Competitions $competition): static
    {
        if (!$this->competitions->contains($competition)) {
            $this->competitions->add($competition);
        }

        return $this;
    }

    public function removeCompetition(Competitions $competition): static
    {
        if ($this->competitions->contains($competition)) {
            $this->competitions->removeElement($competition);
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
            $kid->setUser($this);
        }

        return $this;
    }

    public function removeKid(Kids $kid): static
    {
        if ($this->kids->removeElement($kid)) {
            // set the owning side to null (unless already changed)
            if ($kid->getUser() === $this) {
                $kid->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Adhesions>
     */
    public function getAbonnement(): Collection
    {
        return $this->abonnement;
    }

    public function addAbonnement(Adhesions $abonnement): static
    {
        if (!$this->abonnement->contains($abonnement)) {
            $this->abonnement->add($abonnement);
            $abonnement->setUser($this);
        }

        return $this;
    }

    public function removeAbonnement(Adhesions $abonnement): static
    {
        if ($this->abonnement->removeElement($abonnement)) {
            // set the owning side to null (unless already changed)
            if ($abonnement->getUser() === $this) {
                $abonnement->setUser(null);
            }
        }

        return $this;
    }

    public function getStatutAbonnement(): ?string
    {
        return $this->statutAbonnement;
    }

    public function setStatutAbonnement(?string $statutAbonnement): static
    {
        $this->statutAbonnement = $statutAbonnement;

        return $this;
    }

    public function getDebutAbonnement(): ?\DateTimeInterface
    {
        return $this->debutAbonnement;
    }

    public function setDebutAbonnement(?\DateTimeInterface $debutAbonnement): static
    {
        $this->debutAbonnement = $debutAbonnement;

        return $this;
    }

    public function getFinAbonnement(): ?\DateTimeInterface
    {
        return $this->finAbonnement;
    }

    public function setFinAbonnement(?\DateTimeInterface $finAbonnement): static
    {
        $this->finAbonnement = $finAbonnement;

        return $this;
    }
}
