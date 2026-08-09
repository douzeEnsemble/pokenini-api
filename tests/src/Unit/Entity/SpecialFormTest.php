<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\SpecialForm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SpecialForm::class)]
final class SpecialFormTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new SpecialForm();

        $this->assertNull($entity->getIdentifier());
    }

    #[Test]
    public function convertToString(): void
    {
        $entity = new SpecialForm();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
