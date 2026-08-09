<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\DexQueryOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(DexQueryOptions::class)]
final class DexQueryOptionsTest extends TestCase
{
    #[Test]
    public function ok(): void
    {
        $attributes = new DexQueryOptions([
            'include_unreleased_dex' => false,
            'include_premium_dex' => true,
        ]);

        $this->assertFalse($attributes->includeUnreleasedDex);
        $this->assertTrue($attributes->includePremiumDex);
    }

    #[Test]
    public function missingAllValue(): void
    {
        $attributes = new DexQueryOptions([]);

        $this->assertFalse($attributes->includeUnreleasedDex);
        $this->assertFalse($attributes->includePremiumDex);
    }

    #[Test]
    public function wrongValueUnreleased(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new DexQueryOptions([
            'include_unreleased_dex' => 'yes',
        ]);
    }

    #[Test]
    public function wrongValuePremium(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new DexQueryOptions([
            'include_premium_dex' => 'yes',
        ]);
    }

    #[Test]
    public function anotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new DexQueryOptions(['includeUnreleasedDex' => true, 'is_on_home' => false]);
    }
}
