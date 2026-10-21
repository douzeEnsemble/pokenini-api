<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\WhichShinyGameAnswer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * @internal
 */
#[CoversClass(WhichShinyGameAnswer::class)]
final class WhichShinyGameAnswerTest extends TestCase
{
    #[Test]
    public function everythingOK(): void
    {
        $answer = new WhichShinyGameAnswer(['position' => 3, 'slot' => 2]);

        $this->assertSame(3, $answer->position);
        $this->assertSame(2, $answer->slot);
    }

    #[Test]
    public function lowestValues(): void
    {
        $answer = new WhichShinyGameAnswer(['position' => 1, 'slot' => 1]);

        $this->assertSame(1, $answer->position);
        $this->assertSame(1, $answer->slot);
    }

    /**
     * @param array<string, int> $values
     */
    #[Test]
    #[DataProvider('providerMissing')]
    public function missing(array $values): void
    {
        $this->expectException(MissingOptionsException::class);

        new WhichShinyGameAnswer($values);
    }

    /**
     * @return array<string, array{array<string, int>}>
     */
    public static function providerMissing(): array
    {
        return [
            'position' => [['slot' => 1]],
            'slot' => [['position' => 1]],
        ];
    }

    /**
     * @param array<string, mixed> $values
     */
    #[Test]
    #[DataProvider('providerInvalid')]
    public function invalid(array $values): void
    {
        $this->expectException(InvalidOptionsException::class);

        new WhichShinyGameAnswer($values);
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function providerInvalid(): array
    {
        return [
            'string position' => [['position' => '1', 'slot' => 1]],
            'zero position' => [['position' => 0, 'slot' => 1]],
            'boolean slot' => [['position' => 1, 'slot' => true]],
            'string slot' => [['position' => 1, 'slot' => '1']],
            'zero slot' => [['position' => 1, 'slot' => 0]],
            'third slot' => [['position' => 1, 'slot' => 3]],
        ];
    }
}
