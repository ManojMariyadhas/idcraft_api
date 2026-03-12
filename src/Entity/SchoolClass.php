<?php

namespace App\Entity;

use App\Repository\SchoolClassRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SchoolClassRepository::class)]
#[ORM\Table(name: 'classes', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_school_class_division', columns: ['school_id', 'class', 'division'])])]
class SchoolClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['class:read', 'student:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: School::class, inversedBy: 'classes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private School $school;

    #[ORM\Column(name: 'class', length: 20)]
    #[Assert\NotBlank]
    #[Groups(['class:read', 'student:read'])]
    private string $className;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    #[Groups(['class:read', 'student:read'])]
    private string $division;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['class:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'class', targetEntity: Student::class, cascade: ['persist', 'remove'])]
    private Collection $students;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->students = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchool(): School
    {
        return $this->school;
    }

    public function setSchool(School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    public function getDivision(): string
    {
        return $this->division;
    }

    public function setDivision(string $division): self
    {
        $this->division = $division;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStudents(): Collection
    {
        return $this->students;
    }
}
