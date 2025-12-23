<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['movie:read']],
    denormalizationContext: ['groups' => ['movie:write']]
)]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['movie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['movie:read', 'movie:write', 'actor:read', 'category:read'])]
    #[Assert\NotBlank(message: "Le nom du film est obligatoire")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "Le nom du film doit contenir au moins {{ limit }} caractère",
        maxMessage: "Le nom du film ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\Length(
        max: 5000,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\Positive(message: "La durée doit être un nombre positif")]
    #[Assert\LessThan(
        value: 1000,
        message: "La durée ne peut pas dépasser {{ compared_value }} minutes"
    )]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['movie:read', 'movie:write'])]
    private ?\DateTimeInterface $releaseDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['movie:read', 'movie:write'])]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['movie:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups(['movie:read', 'movie:write'])]
    private bool $online = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\PositiveOrZero(message: "Le nombre d'entrées doit être un nombre positif ou zéro")]
    private ?int $nbEntries = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\Url(message: "L'URL doit être valide")]
    #[Assert\Length(
        max: 500,
        maxMessage: "L'URL ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $url = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\PositiveOrZero(message: "Le budget doit être un nombre positif ou zéro")]
    private ?float $budget = null;

    #[ORM\ManyToOne(targetEntity: Director::class, inversedBy: 'movies')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\Valid]
    private ?Director $director = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'movies')]
    #[ORM\JoinTable(name: 'movie_category')]
    #[Groups(['movie:read', 'movie:write'])]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: Actor::class, inversedBy: 'movies')]
    #[ORM\JoinTable(name: 'movie_actor')]
    #[Groups(['movie:read', 'movie:write'])]
    private Collection $actors;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'movies')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['movie:read'])]
    private ?User $author = null;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'movie', cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->actors = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTimeInterface $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

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
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return Collection<int, Actor>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Actor $actor): static
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
        }

        return $this;
    }

    public function removeActor(Actor $actor): static
    {
        $this->actors->removeElement($actor);

        return $this;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): static
    {
        $this->online = $online;

        return $this;
    }

    public function getNbEntries(): ?int
    {
        return $this->nbEntries;
    }

    public function setNbEntries(?int $nbEntries): static
    {
        $this->nbEntries = $nbEntries;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getBudget(): ?float
    {
        return $this->budget;
    }

    public function setBudget(?float $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    public function getDirector(): ?Director
    {
        return $this->director;
    }

    public function setDirector(?Director $director): static
    {
        $this->director = $director;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setMovie($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getMovie() === $this) {
                $comment->setMovie(null);
            }
        }

        return $this;
    }
}
