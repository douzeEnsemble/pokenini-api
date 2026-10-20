<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ColorGame;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ColorGame::class)]
final class ColorGameTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new ColorGame();

        $this->assertNull($entity->getIdentifier());
    }
}
