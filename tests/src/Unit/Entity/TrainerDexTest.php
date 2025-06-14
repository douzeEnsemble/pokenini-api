<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\TrainerDex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDex::class)]
class TrainerDexTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new TrainerDex();

        $this->assertNull($entity->getIdentifier());
    }
}
