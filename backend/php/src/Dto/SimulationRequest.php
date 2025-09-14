<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class SimulationRequest
{
    #[Assert\NotNull] #[Assert\Positive] #[Assert\LessThanOrEqual(40)]
    public int $years;

    #[Assert\NotNull] #[Assert\GreaterThanOrEqual(0)]
    public float $purchase_price;

    #[Assert\NotNull] #[Assert\GreaterThanOrEqual(0)]
    public float $down_payment;

    #[Assert\NotNull] #[Assert\GreaterThanOrEqual(0)]
    public float $works;

    #[Assert\NotNull] #[Assert\Range(min: 0, max: 100)]
    public float $agency_fee_rate_percent;

    #[Assert\NotNull] #[Assert\Range(min: 0, max: 100)]
    public float $notary_fee_rate_percent;

    #[Assert\NotNull] #[Assert\Range(min: 0, max: 100)]
    public float $interest_rate_percent;

    #[Assert\NotNull] #[Assert\Range(min: 0, max: 100)]
    public float $insurance_rate_percent;

    #[Assert\NotNull] #[Assert\Range(min: 0, max: 100)]
    public float $appreciation_rate_percent;

    #[Assert\NotNull] #[Assert\Range(min: 1, max: 12)]
    public int $acquisition_month;

    #[Assert\NotNull] #[Assert\Range(min: 1900, max: 2100)]
    public int $acquisition_year;

    public ?ClientDto $client = null;

    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): self
    {
        $s = new self();
        $s->years                       = (int)($data['years'] ?? 0);
        $s->purchase_price              = (float)($data['purchase_price'] ?? 0);
        $s->down_payment                = (float)($data['down_payment'] ?? 0);
        $s->works                       = (float)($data['works'] ?? 0);
        $s->agency_fee_rate_percent     = (float)($data['agency_fee_rate_percent'] ?? 0);
        $s->notary_fee_rate_percent     = (float)($data['notary_fee_rate_percent'] ?? 0);
        $s->interest_rate_percent       = (float)($data['interest_rate_percent'] ?? 0);
        $s->insurance_rate_percent      = (float)($data['insurance_rate_percent'] ?? 0);
        $s->appreciation_rate_percent   = (float)($data['appreciation_rate_percent'] ?? 0);
        $s->acquisition_month           = (int)($data['acquisition_month'] ?? 0);
        $s->acquisition_year            = (int)($data['acquisition_year'] ?? 0);
        $s->client = isset($data['client']) && is_array($data['client'])
            ? ClientDto::fromArray($data['client'])
            : null;
        return $s;
    }

    /** Payload exact attendu par FastAPI
     *  @return array<string,mixed>
     */
    public function toPythonPayload(): array
    {
        return [
            'years'                       => $this->years,
            'purchase_price'              => $this->purchase_price,
            'down_payment'                => $this->down_payment,
            'works'                       => $this->works,
            'agency_fee_rate_percent'     => $this->agency_fee_rate_percent,
            'notary_fee_rate_percent'     => $this->notary_fee_rate_percent,
            'interest_rate_percent'       => $this->interest_rate_percent,
            'insurance_rate_percent'      => $this->insurance_rate_percent,
            'appreciation_rate_percent'   => $this->appreciation_rate_percent,
            'acquisition_month'           => $this->acquisition_month,
            'acquisition_year'            => $this->acquisition_year,
        ];
    }
}
