<?php

// src/Service/UpstreamException.php
namespace App\Service;

final class UpstreamException extends \RuntimeException
{
    public function __construct(
        public readonly int $status,
        public readonly array $body = [],
        string $message = 'Upstream error'
    ) {
        parent::__construct($message, $status);
    }
}