<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ShinyGameRound;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ShinyGameRound::class)]
final class ShinyGameRoundTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new ShinyGameRound();

        $this->assertNull($entity->getIdentifier());
    }
}
