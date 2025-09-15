<?php
// src/Service/SimulatorClient.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SimulatorClient
{
    public function __construct(
        private HttpClientInterface $http,
        string $baseUrl,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    private string $baseUrl;

    /** @return array<string,mixed> */
    public function simulate(array $payload): array
    {
        $res    = $this->http->request('POST', $this->baseUrl . '/simulate', [
            'json'    => $payload,
            'timeout' => 10,
            'headers' => ['Accept' => 'application/json'],
        ]);

        $status = $res->getStatusCode();
        $body   = $res->toArray(false); // ne jette pas sur 4xx/5xx

        if ($status >= 400) {
            throw new UpstreamException($status, \is_array($body) ? $body : [], 'Simulator responded with error');
        }

        return \is_array($body) ? $body : [];
    }
}
