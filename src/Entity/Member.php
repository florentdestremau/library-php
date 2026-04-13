<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $firstName;

    #[ORM\Column(length: 100)]
    private string $lastName;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $birthDate;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $membershipDate;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $membershipExpiry = null;

    #[ORM\Column(length: 20)]
    private string $status = 'active';

    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'member')]
    private Collection $loans;

    public function __construct()
    {
        $this->loans = new ArrayCollection();
        $this->membershipDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getBirthDate(): \DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getMembershipDate(): \DateTimeInterface
    {
        return $this->membershipDate;
    }

    public function setMembershipDate(\DateTimeInterface $membershipDate): static
    {
        $this->membershipDate = $membershipDate;
        return $this;
    }

    public function getMembershipExpiry(): ?\DateTimeInterface
    {
        return $this->membershipExpiry;
    }

    public function setMembershipExpiry(?\DateTimeInterface $membershipExpiry): static
    {
        $this->membershipExpiry = $membershipExpiry;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getLoans(): Collection
    {
        return $this->loans;
    }

    public function getActiveLoans(): Collection
    {
        return $this->loans->filter(fn(Loan $loan) => $loan->getStatus() === 'borrowed');
    }
}
