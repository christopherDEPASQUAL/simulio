<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]

// #[\Deprecated]
// public function eraseCredentials(): void {}

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 100)]
    private string $firstName;

    #[ORM\Column(length: 100)]
    private string $lastName;

    #[ORM\Column]
    private string $password;

    /** @var Collection<int, Simulation> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Simulation::class, orphanRemoval: true)]
    private Collection $simulations;

    /** @var Collection<int, Client> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Client::class)]
    private Collection $clients;

    public function __construct()
    {
        $this->simulations = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getUserIdentifier(): string { return $this->email; }
    public function getRoles(): array { return ['ROLE_USER']; }
    public function eraseCredentials(): void {}

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $v): self { $this->email = $v; return $this; }

    public function getFirstName(): string { return $this->firstName; }
    public function setFirstName(string $v): self { $this->firstName = $v; return $this; }

    public function getLastName(): string { return $this->lastName; }
    public function setLastName(string $v): self { $this->lastName = $v; return $this; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $v): self { $this->password = $v; return $this; }

    /** @return Collection<int, Simulation> */
    public function getSimulations(): Collection { return $this->simulations; }

    /** @return Collection<int, Client> */
    public function getClients(): Collection { return $this->clients; }
}
