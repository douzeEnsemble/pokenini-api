<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\TrainerDexAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(TrainerDexAttributes::class)]
final class TrainerDexAttributesTest extends TestCase
{
    #[Test]
    public function ok(): void
    {
        $attributes = new TrainerDexAttributes(['is_private' => false, 'is_on_home' => false]);

        $this->assertFalse($attributes->isPrivate);
    }

    #[Test]
    public function missingOneValue(): void
    {
        $attributes = new TrainerDexAttributes(['is_on_home' => true]);

        $this->assertFalse($attributes->isPrivate);
    }

    #[Test]
    public function missingAllValue(): void
    {
        $attributes = new TrainerDexAttributes([]);

        $this->assertFalse($attributes->isPrivate);
        $this->assertFalse($attributes->isOnHome);
    }

    #[Test]
    public function wrongValue(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerDexAttributes(['is_private' => 'yes', 'is_on_home' => false]);
    }

    #[Test]
    public function wrongValueBis(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerDexAttributes(['is_private' => true, 'is_on_home' => 'no']);
    }

    #[Test]
    public function anotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new TrainerDexAttributes(['isPrivate' => true, 'is_on_home' => false]);
    }
}
