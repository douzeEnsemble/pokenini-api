<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\WhichShinyGameRound;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(WhichShinyGameRound::class)]
final class WhichShinyGameRoundTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new WhichShinyGameRound();

        $this->assertNull($entity->getIdentifier());
    }
}
