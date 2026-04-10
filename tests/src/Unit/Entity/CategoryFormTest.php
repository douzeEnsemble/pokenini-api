<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\CategoryForm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CategoryForm::class)]
final class CategoryFormTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new CategoryForm();

        $this->assertNull($entity->getIdentifier());
    }

    public function testConvertToString(): void
    {
        $entity = new CategoryForm();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
