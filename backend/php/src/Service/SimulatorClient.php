<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SimulatorClient
{
    public function __construct(
        private HttpClientInterface $http,
        private string $baseUrl = 'http://backend-python:8001',
    ) {}

    /**
     * @param array<string,mixed> $input Payload attendu par FastAPI /simulate
     * @return array<string,mixed>      Résultat JSON du microservice
     */
    public function simulate(array $input): array
    {
        $url = rtrim($this->baseUrl, '/') . '/simulate';

        $response = $this->http->request('POST', $url, [
            'json' => $input,
            'timeout' => 10,
        ]);

        $status = $response->getStatusCode();
        $data   = $response->toArray(false); // ne jette pas sur 4xx/5xx

        if ($status >= 400) {
            $body = is_array($data) ? json_encode($data, JSON_UNESCAPED_SLASHES) : (string)$data;
            throw new \RuntimeException(sprintf('Simulator error %d: %s', $status, $body));
        }

        /** @var array<string,mixed> $data */
        return $data;
    }
}
