<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ActionLog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ActionLog::class)]
final class ActionLogTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $entity = new ActionLog('alpha');

        $this->assertSame('alpha', $entity->getType());
    }

    public function testGetIdentifierDefault(): void
    {
        $entity = new ActionLog('alpha');

        $this->assertNull($entity->getIdentifier());
    }
}
