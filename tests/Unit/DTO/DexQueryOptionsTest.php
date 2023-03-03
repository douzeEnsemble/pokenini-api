<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\DexQueryOptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class DexQueryOptionsTest extends TestCase
{
    public function testOk(): void
    {
        $attributes = new DexQueryOptions(['include_unreleased_dex' => false]);

        $this->assertFalse($attributes->includeUnreleasedDex);
    }

    public function testMissingAllValue(): void
    {
        $attributes = new DexQueryOptions([]);

        $this->assertFalse($attributes->includeUnreleasedDex);
    }

    public function testWrongValue(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new DexQueryOptions(['include_unreleased_dex' => 'yes']);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new DexQueryOptions(['includeUnreleasedDex' => true, 'is_on_home' => false]);
    }
}
