<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SimulationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SimulationRepository::class)]
#[ORM\Table(name: 'simulations')]
#[ORM\Index(columns: ['created_at'], name: 'idx_sim_created')]
class Simulation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Client $client = null;

    #[ORM\Column(type: 'json')]
    private array $inputJson = [];

    #[ORM\Column(type: 'json')]
    private array $resultJson = [];

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $monthlyPaymentEur = '0.00';

    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $c): self { $this->client = $c; return $this; }

    /** @return array<string,mixed> */ public function getInputJson(): array { return $this->inputJson; }
    /** @param array<string,mixed> $v */ public function setInputJson(array $v): self { $this->inputJson = $v; return $this; }

    /** @return array<string,mixed> */ public function getResultJson(): array { return $this->resultJson; }
    /** @param array<string,mixed> $v */ public function setResultJson(array $v): self { $this->resultJson = $v; return $this; }

    public function getMonthlyPaymentEur(): string { return $this->monthlyPaymentEur; }
    public function setMonthlyPaymentEur(string $v): self { $this->monthlyPaymentEur = $v; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
