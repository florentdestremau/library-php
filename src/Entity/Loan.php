<?php

namespace App\Entity;

use App\Repository\LoanRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Book::class, inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    private Book $book;

    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    private Member $member;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $borrowedAt;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $dueDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $returnedAt = null;

    #[ORM\Column(length: 20)]
    private string $status = 'borrowed';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->borrowedAt = new \DateTime();
        $this->dueDate = (new \DateTime())->modify('+21 days');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): Book
    {
        return $this->book;
    }

    public function setBook(Book $book): static
    {
        $this->book = $book;
        return $this;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): static
    {
        $this->member = $member;
        return $this;
    }

    public function getBorrowedAt(): \DateTimeInterface
    {
        return $this->borrowedAt;
    }

    public function setBorrowedAt(\DateTimeInterface $borrowedAt): static
    {
        $this->borrowedAt = $borrowedAt;
        return $this;
    }

    public function getDueDate(): \DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getReturnedAt(): ?\DateTimeInterface
    {
        return $this->returnedAt;
    }

    public function setReturnedAt(?\DateTimeInterface $returnedAt): static
    {
        $this->returnedAt = $returnedAt;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function isOverdue(): bool
    {
        return $this->status === 'borrowed' && $this->dueDate < new \DateTime();
    }
}
