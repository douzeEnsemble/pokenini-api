<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\WhichShinyGame;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(WhichShinyGame::class)]
final class WhichShinyGameTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new WhichShinyGame();

        $this->assertNull($entity->getIdentifier());
    }
}
