<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\RegionalDexNumber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RegionalDexNumber::class)]
final class RegionalDexNumberTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new RegionalDexNumber();

        $this->assertNull($entity->getIdentifier());
    }
}
