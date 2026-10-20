<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ColorGameRound;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ColorGameRound::class)]
final class ColorGameRoundTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new ColorGameRound();

        $this->assertNull($entity->getIdentifier());
    }
}
