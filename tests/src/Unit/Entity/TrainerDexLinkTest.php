<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\TrainerDexLink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexLink::class)]
final class TrainerDexLinkTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new TrainerDexLink();

        $this->assertNull($entity->getIdentifier());
    }
}
