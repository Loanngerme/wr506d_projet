<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ActorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActorRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['actor:read']],
    denormalizationContext: ['groups' => ['actor:write']]
)]
class Actor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['actor:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['actor:read', 'actor:write', 'movie:read'])]
    #[Assert\NotBlank(message: "Le nom de famille de l'acteur est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom de famille doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom de famille ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['actor:read', 'actor:write', 'movie:read'])]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le prénom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le prénom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['actor:read', 'actor:write'])]
    #[Assert\LessThan(
        value: "today",
        message: "La date de naissance doit être antérieure à aujourd'hui"
    )]
    private ?\DateTimeInterface $dob = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['actor:read', 'actor:write'])]
    #[Assert\GreaterThan(
        propertyPath: "dob",
        message: "La date de décès doit être postérieure à la date de naissance"
    )]
    #[Assert\LessThanOrEqual(
        value: "today",
        message: "La date de décès ne peut pas être dans le futur"
    )]
    private ?\DateTimeInterface $dod = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['actor:read', 'actor:write'])]
    #[Assert\Length(
        max: 2000,
        maxMessage: "La biographie ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['actor:read', 'actor:write'])]
    #[Assert\Url(message: "L'URL de la photo doit être valide")]
    private ?string $photo = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['actor:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToMany(targetEntity: Movie::class, mappedBy: 'actors')]
    #[Groups(['actor:read'])]
    private Collection $movies;

    public function __construct()
    {
        $this->movies = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getDob(): ?\DateTimeInterface
    {
        return $this->dob;
    }

    public function setDob(?\DateTimeInterface $dob): static
    {
        $this->dob = $dob;

        return $this;
    }

    public function getDod(): ?\DateTimeInterface
    {
        return $this->dod;
    }

    public function setDod(?\DateTimeInterface $dod): static
    {
        $this->dod = $dod;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Movie>
     */
    public function getMovies(): Collection
    {
        return $this->movies;
    }

    public function addMovie(Movie $movie): static
    {
        if (!$this->movies->contains($movie)) {
            $this->movies->add($movie);
            $movie->addActor($this);
        }

        return $this;
    }

    public function removeMovie(Movie $movie): static
    {
        if ($this->movies->removeElement($movie)) {
            $movie->removeActor($this);
        }

        return $this;
    }
}
