<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\CatchState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CatchState::class)]
final class CatchStateTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new CatchState();

        $this->assertNull($entity->getIdentifier());
    }

    public function testConvertToString(): void
    {
        $entity = new CatchState();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
