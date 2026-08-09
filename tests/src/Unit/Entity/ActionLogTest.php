<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ActionLog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ActionLog::class)]
final class ActionLogTest extends TestCase
{
    #[Test]
    public function constructorAndGetters(): void
    {
        $entity = new ActionLog('alpha');

        $this->assertSame('alpha', $entity->getType());
    }

    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new ActionLog('alpha');

        $this->assertNull($entity->getIdentifier());
    }
}
