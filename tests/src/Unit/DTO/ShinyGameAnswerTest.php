<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ShinyGameAnswer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * @internal
 */
#[CoversClass(ShinyGameAnswer::class)]
final class ShinyGameAnswerTest extends TestCase
{
    #[Test]
    public function everythingOK(): void
    {
        $answer = new ShinyGameAnswer(['position' => 3, 'is_shiny' => true]);

        $this->assertSame(3, $answer->position);
        $this->assertTrue($answer->isShiny);
    }

    #[Test]
    public function lowestValues(): void
    {
        $answer = new ShinyGameAnswer(['position' => 1, 'is_shiny' => false]);

        $this->assertSame(1, $answer->position);
        $this->assertFalse($answer->isShiny);
    }

    /**
     * @param array<string, bool|int> $values
     */
    #[Test]
    #[DataProvider('providerMissing')]
    public function missing(array $values): void
    {
        $this->expectException(MissingOptionsException::class);

        new ShinyGameAnswer($values);
    }

    /**
     * @return array<string, array{array<string, bool|int>}>
     */
    public static function providerMissing(): array
    {
        return [
            'position' => [['is_shiny' => true]],
            'is_shiny' => [['position' => 1]],
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

        new ShinyGameAnswer($values);
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function providerInvalid(): array
    {
        return [
            'string position' => [['position' => '1', 'is_shiny' => true]],
            'integer is_shiny' => [['position' => 1, 'is_shiny' => 1]],
            'string is_shiny' => [['position' => 1, 'is_shiny' => 'true']],
            'zero position' => [['position' => 0, 'is_shiny' => true]],
        ];
    }
}
