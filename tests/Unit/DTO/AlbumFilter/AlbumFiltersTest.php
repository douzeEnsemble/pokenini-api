<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumFilter\AlbumFilterValues;
use PHPUnit\Framework\TestCase;

class AlbumFiltersTest extends TestCase
{
    public function testCreateFromArrayEmpty(): void
    {
        $filters = AlbumFilters::createFromArray([]);

        $this->assertInstanceOf(AlbumFilters::class, $filters);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->primaryTypes);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->secondaryTypes);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->categoryForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->regionalForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->specialForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->variantForms);
        
        $this->assertEmpty($filters->primaryTypes->values);
        $this->assertEmpty($filters->secondaryTypes->values);
        $this->assertEmpty($filters->categoryForms->values);
        $this->assertEmpty($filters->regionalForms->values);
        $this->assertEmpty($filters->specialForms->values);
        $this->assertEmpty($filters->variantForms->values);
    }

    public function testCreateFromArray(): void
    {
        $filters = AlbumFilters::createFromArray([
            'primaryTypes' => ['fire', 'water'],
            'secondaryTypes' => ['water', 'fire'],
            'categoryForms' => ['starter', 'finisher'],
            'regionalForms' => ['provence', 'sud', 'mer'],
            'specialForms' => ['banana', 'orange'],
            'variantForms' => ['gender'],
        ]);

        $this->assertInstanceOf(AlbumFilters::class, $filters);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->primaryTypes);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->secondaryTypes);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->categoryForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->regionalForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->specialForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->variantForms);
        
        $this->assertCount(2, $filters->primaryTypes->values);
        $this->assertCount(2, $filters->secondaryTypes->values);
        $this->assertCount(2, $filters->categoryForms->values);
        $this->assertCount(3, $filters->regionalForms->values);
        $this->assertCount(2, $filters->specialForms->values);
        $this->assertCount(1, $filters->variantForms->values);
    }
}
