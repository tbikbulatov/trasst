<?php

declare(strict_types=1);

namespace App\Tests\Common;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BaseKernelTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel(['environment' => 'test']);

        parent::setUp();
    }

    /**
     * @template T
     *
     * @param class-string<T>|string $id
     *
     * @return T|mixed
     */
    protected static function get(string $id): object
    {
        return static::getContainer()->get($id);
    }
}
