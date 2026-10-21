<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ShinyGame;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ShinyGame::class)]
final class ShinyGameTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new ShinyGame();

        $this->assertNull($entity->getIdentifier());
    }
}
