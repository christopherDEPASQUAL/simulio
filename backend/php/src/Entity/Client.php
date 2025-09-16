<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'clients')]
#[ORM\Index(columns: ['created_at'], name: 'idx_client_created')]
#[UniqueEntity(fields: ['user','email'], message: 'Un client avec cet email existe déjà.')]
class Client
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'clients')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    #[ORM\Column(length: 80)]
    private ?string $firstName = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    #[ORM\Column(length: 80)]
    private ?string $lastName = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[Assert\Length(max: 30)]
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getFirstName(): string { return $this->firstName; }
    public function setFirstName(string $v): self { $this->firstName = $v; return $this; }

    public function getLastName(): string { return $this->lastName; }
    public function setLastName(string $v): self { $this->lastName = $v; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $v): self { $this->email = $v; return $this; }

    public function getPhone(): string { return $this->phone; }
    public function setPhone(string $v): self { $this->phone = $v; return $this; }

    public function getAddress(): string { return $this->address; }
    public function setAddress(string $v): self { $this->address = $v; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $u): self { $this->user = $u; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
