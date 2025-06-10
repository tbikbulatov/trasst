<?php

declare(strict_types=1);

namespace App\Tests\Api\Common;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as BaseApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;

abstract class ApiTestCase extends BaseApiTestCase
{
    protected Client $client;

    protected static function getKernelClass(): string
    {
        return \App\Shared\Infrastructure\Symfony\Kernel::class;
    }

    protected function setUp(): void
    {
        // Create client with headers for JSON-LD
        $this->client = static::createClient([], [
            'headers' => [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/ld+json',
            ],
        ]);
    }
}
