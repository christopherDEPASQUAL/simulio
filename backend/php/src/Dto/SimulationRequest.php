<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class SimulationRequest
{
    public function __construct(
        #[Assert\Positive] public int $years,
        #[Assert\PositiveOrZero] public float $purchase_price,
        #[Assert\PositiveOrZero] public float $down_payment,
        #[Assert\PositiveOrZero] public float $works,
        #[Assert\PositiveOrZero] public float $agency_fee_rate_percent,
        #[Assert\PositiveOrZero] public float $notary_fee_rate_percent,
        #[Assert\PositiveOrZero] public float $interest_rate_percent,
        #[Assert\PositiveOrZero] public float $insurance_rate_percent,
        #[Assert\PositiveOrZero] public float $appreciation_rate_percent,
        #[Assert\Range(min:1, max:12)] public int $acquisition_month,
        #[Assert\Range(min:1990)] public int $acquisition_year,
        public ?ClientDto $client = null,
    ) {}

    /** @param array<string,mixed> $a */
    public static function fromArray(array $a): self
    {
        $client = isset($a['client']) && \is_array($a['client'])
            ? ClientDto::fromArray($a['client'])
            : null;

        return new self(
            (int)$a['years'],
            (float)$a['purchase_price'],
            (float)$a['down_payment'],
            (float)$a['works'],
            (float)$a['agency_fee_rate_percent'],
            (float)$a['notary_fee_rate_percent'],
            (float)$a['interest_rate_percent'],
            (float)$a['insurance_rate_percent'],
            (float)$a['appreciation_rate_percent'],
            (int)$a['acquisition_month'],
            (int)$a['acquisition_year'],
            $client
        );
    }

    /** @return array<string,mixed> */
    public function toPythonPayload(): array
    {
        // même schéma attendu par FastAPI
        return [
            'years' => $this->years,
            'purchase_price' => $this->purchase_price,
            'down_payment' => $this->down_payment,
            'works' => $this->works,
            'agency_fee_rate_percent' => $this->agency_fee_rate_percent,
            'notary_fee_rate_percent' => $this->notary_fee_rate_percent,
            'interest_rate_percent' => $this->interest_rate_percent,
            'insurance_rate_percent' => $this->insurance_rate_percent,
            'appreciation_rate_percent' => $this->appreciation_rate_percent,
            'acquisition_month' => $this->acquisition_month,
            'acquisition_year' => $this->acquisition_year,
        ];
    }
}
