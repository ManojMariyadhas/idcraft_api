<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ORM\Table(name: 'students', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_school_admission', columns: ['school_id', 'admission_no'])])]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['student:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: School::class, inversedBy: 'students')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private School $school;

    #[ORM\ManyToOne(targetEntity: SchoolClass::class, inversedBy: 'students')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['student:read'])]
    #[MaxDepth(1)]
    private SchoolClass $class;

    #[ORM\Column(name: 'admission_no', length: 30)]
    #[Assert\NotBlank]
    #[Groups(['student:read'])]
    private string $admissionNo;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Groups(['student:read'])]
    private string $name;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Groups(['student:read'])]
    private string $phone;

    #[ORM\Column(length: 180, nullable: true)]
    #[Groups(['student:read'])]
    private ?string $parentName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['student:read'])]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['student:read'])]
    private ?string $photo = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['student:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getClass(): SchoolClass
    {
        return $this->class;
    }

    public function setClass(SchoolClass $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getAdmissionNo(): string
    {
        return $this->admissionNo;
    }

    public function setAdmissionNo(string $admissionNo): self
    {
        $this->admissionNo = $admissionNo;

        return $this;
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

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getParentName(): ?string
    {
        return $this->parentName;
    }

    public function setParentName(?string $parentName): self
    {
        $this->parentName = $parentName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
