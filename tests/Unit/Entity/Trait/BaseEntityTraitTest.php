<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Trait;

use App\Entity\Traits\BaseEntityTrait;
use PHPUnit\Framework\TestCase;

class BaseEntityTraitTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = $this->getObjectForTrait(BaseEntityTrait::class);

        $this->assertNull($entity->getIdentifier());
    }
}
