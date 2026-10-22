<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ShinyGameCreation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(ShinyGameCreation::class)]
final class ShinyGameCreationTest extends TestCase
{
    #[Test]
    public function everythingOK(): void
    {
        $creation = new ShinyGameCreation(['dex_slug' => 'demo']);

        $this->assertSame('demo', $creation->dexSlug);
        $this->assertNull($creation->roundCount);
    }

    #[Test]
    public function withRoundCount(): void
    {
        $creation = new ShinyGameCreation(['dex_slug' => 'demo', 'round_count' => 10]);

        $this->assertSame('demo', $creation->dexSlug);
        $this->assertSame(10, $creation->roundCount);
    }

    #[Test]
    public function withOneRound(): void
    {
        $creation = new ShinyGameCreation(['dex_slug' => 'demo', 'round_count' => 1]);

        $this->assertSame(1, $creation->roundCount);
    }

    #[Test]
    public function withNullRoundCount(): void
    {
        $creation = new ShinyGameCreation(['dex_slug' => 'demo', 'round_count' => null]);

        $this->assertNull($creation->roundCount);
    }

    #[Test]
    #[DataProvider('providerWrongRoundCount')]
    public function wrongRoundCount(mixed $roundCount): void
    {
        $this->expectException(InvalidOptionsException::class);

        new ShinyGameCreation(['dex_slug' => 'demo', 'round_count' => $roundCount]);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function providerWrongRoundCount(): array
    {
        return [
            'zero' => [0],
            'negative' => [-5],
            'string' => ['10'],
            'float' => [10.5],
        ];
    }

    #[Test]
    public function missingDexSlug(): void
    {
        $this->expectException(MissingOptionsException::class);

        new ShinyGameCreation();
    }

    #[Test]
    public function wrongTypeForDexSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new ShinyGameCreation(['dex_slug' => 12]);
    }

    #[Test]
    public function unknownOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        new ShinyGameCreation(['dex_slug' => 'demo', 'douze' => 12]);
    }
}
