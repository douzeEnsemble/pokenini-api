<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\VariantForm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(VariantForm::class)]
final class VariantFormTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new VariantForm();

        $this->assertNull($entity->getIdentifier());
    }

    #[Test]
    public function convertToString(): void
    {
        $entity = new VariantForm();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
