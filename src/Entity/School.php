<?php

namespace App\Entity;

use App\Repository\SchoolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SchoolRepository::class)]
#[ORM\Table(name: 'schools')]
class School implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['school:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Groups(['school:read'])]
    private string $name;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank]
    #[Groups(['school:read'])]
    private string $mobile;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Groups(['school:read'])]
    private string $schoolCode;

    #[ORM\Column]
    private string $password;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['school:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'school', targetEntity: SchoolClass::class, cascade: ['persist', 'remove'])]
    private Collection $classes;

    #[ORM\OneToMany(mappedBy: 'school', targetEntity: Student::class, cascade: ['persist', 'remove'])]
    private Collection $students;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->classes = new ArrayCollection();
        $this->students = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getSchoolCode(): string
    {
        return $this->schoolCode;
    }

    public function setSchoolCode(string $schoolCode): self
    {
        $this->schoolCode = $schoolCode;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->schoolCode;
    }

    public function getUsername(): string
    {
        return $this->schoolCode;
    }

    public function getRoles(): array
    {
        return ['ROLE_SCHOOL'];
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function eraseCredentials(): void
    {
    }
}
