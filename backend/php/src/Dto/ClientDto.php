<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ClientDto
{
    public function __construct(
        #[Assert\NotBlank] public string $first_name,
        #[Assert\NotBlank] public string $last_name,
        #[Assert\Email]    public string $email,
        public ?string $phone = null,
        public ?string $address = null,
    ) {}

    /** @param array<string,mixed> $a */
    public static function fromArray(array $a): self {
        return new self(
            (string)($a['first_name'] ?? ''),
            (string)($a['last_name']  ?? ''),
            (string)($a['email']      ?? ''),
            isset($a['phone'])   ? (string)$a['phone']   : null,
            isset($a['address']) ? (string)$a['address'] : null,
        );
    }
}
