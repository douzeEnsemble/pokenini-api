<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\DexQueryOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(DexQueryOptions::class)]
final class DexQueryOptionsTest extends TestCase
{
    public function testOk(): void
    {
        $attributes = new DexQueryOptions([
            'include_unreleased_dex' => false,
            'include_premium_dex' => true,
        ]);

        $this->assertFalse($attributes->includeUnreleasedDex);
        $this->assertTrue($attributes->includePremiumDex);
    }

    public function testMissingAllValue(): void
    {
        $attributes = new DexQueryOptions([]);

        $this->assertFalse($attributes->includeUnreleasedDex);
        $this->assertFalse($attributes->includePremiumDex);
    }

    public function testWrongValueUnreleased(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new DexQueryOptions([
            'include_unreleased_dex' => 'yes',
        ]);
    }

    public function testWrongValuePremium(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new DexQueryOptions([
            'include_premium_dex' => 'yes',
        ]);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new DexQueryOptions(['includeUnreleasedDex' => true, 'is_on_home' => false]);
    }
}
