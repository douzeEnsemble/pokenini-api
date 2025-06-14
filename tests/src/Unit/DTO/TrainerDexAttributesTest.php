<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\TrainerDexAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(TrainerDexAttributes::class)]
class TrainerDexAttributesTest extends TestCase
{
    public function testOk(): void
    {
        $attributes = new TrainerDexAttributes(['is_private' => false, 'is_on_home' => false]);

        $this->assertFalse($attributes->isPrivate);
    }

    public function testMissingOneValue(): void
    {
        $attributes = new TrainerDexAttributes(['is_on_home' => true]);

        $this->assertFalse($attributes->isPrivate);
    }

    public function testMissingAllValue(): void
    {
        $attributes = new TrainerDexAttributes([]);

        $this->assertFalse($attributes->isPrivate);
        $this->assertFalse($attributes->isOnHome);
    }

    public function testWrongValue(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerDexAttributes(['is_private' => 'yes', 'is_on_home' => false]);
    }

    public function testWrongValueBis(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerDexAttributes(['is_private' => true, 'is_on_home' => 'no']);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new TrainerDexAttributes(['isPrivate' => true, 'is_on_home' => false]);
    }
}
