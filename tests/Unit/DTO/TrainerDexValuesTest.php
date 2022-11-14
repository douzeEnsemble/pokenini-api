<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\GameBundlesAvailabilities;
use App\DTO\TrainerDexValues;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class TrainerDexValuesTest extends TestCase
{
    public function testOk(): void
    {
        $values = new TrainerDexValues(['isPrivate' => false]);

        $this->assertFalse($values->isPrivate);
    }

    public function testWrongValue(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerDexValues(['isPrivate' => 'yes']);
    }

    public function testMissingValue(): void
    {
        $this->expectException(MissingOptionsException::class);
        new TrainerDexValues([]);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new TrainerDexValues(['is_private' => true]);
    }
}
