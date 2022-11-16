<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\TrainerDexAttributes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class TrainerDexAttributesTest extends TestCase
{
    public function testOk(): void
    {
        $attributes = new TrainerDexAttributes(['isPrivate' => false, 'isOnHome' => false]);

        $this->assertFalse($attributes->isPrivate);
    }

    public function testMissingOnelValue(): void
    {
        $attributes = new TrainerDexAttributes(['isOnHome' => true]);

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
        new TrainerDexAttributes(['isPrivate' => 'yes', 'isOnHome' => false]);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new TrainerDexAttributes(['is_private' => true, 'isOnHome' => false]);
    }
}
