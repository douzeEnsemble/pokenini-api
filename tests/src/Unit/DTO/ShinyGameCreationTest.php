<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ShinyGameCreation;
use PHPUnit\Framework\Attributes\CoversClass;
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
