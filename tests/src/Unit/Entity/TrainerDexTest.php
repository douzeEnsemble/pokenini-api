<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\TrainerDex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDex::class)]
final class TrainerDexTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new TrainerDex();

        $this->assertNull($entity->getIdentifier());
    }
}
