<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ClientDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $first_name;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $last_name;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email;

    #[Assert\Length(max: 50)]
    public ?string $phone = null;

    #[Assert\Length(max: 1000)]
    public ?string $address = null;

    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): self
    {
        $c = new self();
        $c->first_name = (string)($data['first_name'] ?? '');
        $c->last_name  = (string)($data['last_name'] ?? '');
        $c->email      = (string)($data['email'] ?? '');
        $c->phone      = isset($data['phone']) ? (string)$data['phone'] : null;
        $c->address    = isset($data['address']) ? (string)$data['address'] : null;
        return $c;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'address'    => $this->address,
        ];
    }
}
