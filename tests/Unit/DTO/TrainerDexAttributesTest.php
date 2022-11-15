<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\TrainerDexAttributes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class TrainerDexAttributesTest extends TestCase
{
    public function testOk(): void
    {
        $values = new TrainerDexAttributes(['isPrivate' => false, 'isOnHome' => false]);

        $this->assertFalse($values->isPrivate);
    }

    public function testWrongValue(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerDexAttributes(['isPrivate' => 'yes', 'isOnHome' => false]);
    }

    public function testMissingValue(): void
    {
        $this->expectException(MissingOptionsException::class);
        new TrainerDexAttributes([]);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new TrainerDexAttributes(['is_private' => true, 'isOnHome' => false]);
    }
}
