<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Trait;

use App\Entity\Traits\NamedTrait;
use PHPUnit\Framework\TestCase;

class NamedTraitTest extends TestCase
{
    public function testGetIdentifier(): void
    {
        $entity = $this->getObjectForTrait(NamedTrait::class);
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
