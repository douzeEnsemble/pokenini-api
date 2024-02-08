<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumFilter\AlbumFiltersRequest;
use App\DTO\AlbumFilter\AlbumFilterValues;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class AlbumFiltersRequestTest extends TestCase
{
    public function testAlbumFiltersFromRequestEmpty(): void
    {
        $request = new Request([]);

        $filters = AlbumFiltersRequest::albumFiltersFromRequest($request);

        $this->assertInstanceOf(AlbumFilters::class, $filters);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->primaryTypes);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->secondaryTypes);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->anyTypes);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->categoryForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->regionalForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->specialForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->variantForms);

        $this->assertEmpty($filters->primaryTypes->values);
        $this->assertEmpty($filters->secondaryTypes->values);
        $this->assertEmpty($filters->anyTypes->values);
        $this->assertEmpty($filters->categoryForms->values);
        $this->assertEmpty($filters->regionalForms->values);
        $this->assertEmpty($filters->specialForms->values);
        $this->assertEmpty($filters->variantForms->values);
    }

    public function testAlbumFiltersFromRequest(): void
    {
        $request = new Request([
            'primary_types' => ['fire', 'water'],
            'secondary_types' => ['water', 'fire'],
            'any_types' => ['normal'],
            'category_forms' => ['starter', 'finisher'],
            'regional_forms' => ['provence', 'sud', 'mer'],
            'special_forms' => ['banana', 'orange'],
            'variant_forms' => ['gender'],
        ]);

        $filters = AlbumFiltersRequest::albumFiltersFromRequest($request);

        $this->assertInstanceOf(AlbumFilters::class, $filters);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->primaryTypes);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->secondaryTypes);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->anyTypes);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->categoryForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->regionalForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->specialForms);
        $this->assertInstanceOf(AlbumFilterValues::class, $filters->variantForms);

        $this->assertCount(2, $filters->primaryTypes->values);
        $this->assertCount(2, $filters->secondaryTypes->values);
        $this->assertCount(1, $filters->anyTypes->values);
        $this->assertCount(2, $filters->categoryForms->values);
        $this->assertCount(3, $filters->regionalForms->values);
        $this->assertCount(2, $filters->specialForms->values);
        $this->assertCount(1, $filters->variantForms->values);
    }
}
