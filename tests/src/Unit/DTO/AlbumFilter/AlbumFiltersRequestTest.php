<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\AlbumFilter;

use App\DTO\AlbumFilter\AlbumFiltersRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[CoversClass(AlbumFiltersRequest::class)]
final class AlbumFiltersRequestTest extends TestCase
{
    public function testAlbumFiltersFromRequestEmpty(): void
    {
        $request = new Request([]);

        $filters = AlbumFiltersRequest::albumFiltersFromRequest($request);

        $this->assertEmpty($filters->primaryTypes->values);
        $this->assertEmpty($filters->secondaryTypes->values);
        $this->assertEmpty($filters->anyTypes->values);
        $this->assertEmpty($filters->categoryForms->values);
        $this->assertEmpty($filters->regionalForms->values);
        $this->assertEmpty($filters->specialForms->values);
        $this->assertEmpty($filters->variantForms->values);
        $this->assertEmpty($filters->catchStates->values);
        $this->assertEmpty($filters->originalGameBundles->values);
        $this->assertEmpty($filters->gameBundleAvailabilities->values);
        $this->assertEmpty($filters->gameBundleShinyAvailabilities->values);
        $this->assertEmpty($filters->families->values);
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
            'catch_states' => ['maybe', 'maybenot', 'yes'],
            'original_game_bundles' => ['redgreenblueyellow', 'goldsilvercrystal'],
            'game_bundle_availabilities' => ['goldsilvercrystal'],
            'game_bundle_shiny_availabilities' => ['goldsilvercrystal'],
            'families' => ['pichu', 'eevee'],
        ]);

        $filters = AlbumFiltersRequest::albumFiltersFromRequest($request);

        $this->assertCount(2, $filters->primaryTypes->values);
        $this->assertCount(2, $filters->secondaryTypes->values);
        $this->assertCount(1, $filters->anyTypes->values);
        $this->assertCount(2, $filters->categoryForms->values);
        $this->assertCount(3, $filters->regionalForms->values);
        $this->assertCount(2, $filters->specialForms->values);
        $this->assertCount(1, $filters->variantForms->values);
        $this->assertCount(3, $filters->catchStates->values);
        $this->assertCount(2, $filters->originalGameBundles->values);
        $this->assertCount(1, $filters->gameBundleAvailabilities->values);
        $this->assertCount(1, $filters->gameBundleShinyAvailabilities->values);
        $this->assertCount(2, $filters->families->values);
    }
}
