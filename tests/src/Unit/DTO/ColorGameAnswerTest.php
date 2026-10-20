<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ColorGameAnswer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * @internal
 */
#[CoversClass(ColorGameAnswer::class)]
final class ColorGameAnswerTest extends TestCase
{
    #[Test]
    public function everythingOK(): void
    {
        $answer = new ColorGameAnswer(['position' => 3, 'choice' => 2]);

        $this->assertSame(3, $answer->position);
        $this->assertSame(2, $answer->choice);
    }

    #[Test]
    public function lowestValues(): void
    {
        $answer = new ColorGameAnswer(['position' => 1, 'choice' => 0]);

        $this->assertSame(1, $answer->position);
        $this->assertSame(0, $answer->choice);
    }

    /**
     * @param array<string, int> $values
     */
    #[Test]
    #[DataProvider('providerMissing')]
    public function missing(array $values): void
    {
        $this->expectException(MissingOptionsException::class);

        new ColorGameAnswer($values);
    }

    /**
     * @return array<string, array{array<string, int>}>
     */
    public static function providerMissing(): array
    {
        return [
            'position' => [['choice' => 0]],
            'choice' => [['position' => 1]],
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

        new ColorGameAnswer($values);
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function providerInvalid(): array
    {
        return [
            'string position' => [['position' => '1', 'choice' => 0]],
            'string choice' => [['position' => 1, 'choice' => '0']],
            'zero position' => [['position' => 0, 'choice' => 0]],
            'negative choice' => [['position' => 1, 'choice' => -1]],
        ];
    }
}
