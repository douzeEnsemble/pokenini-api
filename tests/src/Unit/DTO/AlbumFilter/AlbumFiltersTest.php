<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\AlbumFilter;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumFilter\AlbumFilterValues;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @internal
 */
#[CoversClass(AlbumFilters::class)]
final class AlbumFiltersTest extends TestCase
{
    public function testCreateFromArrayEmpty(): void
    {
        $filters = AlbumFilters::createFromArray([]);

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
        $this->assertEmpty($filters->collectionAvailabilities->values);

        $this->assertEmpty($filters->primaryTypes->negativeValues);
        $this->assertEmpty($filters->secondaryTypes->negativeValues);
        $this->assertEmpty($filters->anyTypes->negativeValues);
        $this->assertEmpty($filters->categoryForms->negativeValues);
        $this->assertEmpty($filters->regionalForms->negativeValues);
        $this->assertEmpty($filters->specialForms->negativeValues);
        $this->assertEmpty($filters->variantForms->negativeValues);
        $this->assertEmpty($filters->catchStates->negativeValues);
        $this->assertEmpty($filters->originalGameBundles->negativeValues);
        $this->assertEmpty($filters->gameBundleAvailabilities->negativeValues);
        $this->assertEmpty($filters->gameBundleShinyAvailabilities->negativeValues);
        $this->assertEmpty($filters->families->negativeValues);
        $this->assertEmpty($filters->collectionAvailabilities->negativeValues);
    }

    public function testCreateFromArray(): void
    {
        $filters = AlbumFilters::createFromArray([
            'primary_types' => ['fire', 'water'],
            'secondary_types' => ['water', 'fire'],
            'any_types' => ['normal'],
            'category_forms' => ['starter', 'finisher'],
            'regional_forms' => ['provence', 'sud', 'mer'],
            'special_forms' => ['banana', 'orange'],
            'variant_forms' => ['gender'],
            'catch_states' => ['maybe'],
            'original_game_bundles' => ['redgreenblueyellow'],
            'game_bundle_availabilities' => ['sunmoon'],
            'game_bundle_shiny_availabilities' => ['ultrasunutramoon'],
            'families' => ['pichu', 'eevee'],
            'collection_availabilities' => ['swshdens'],
        ]);

        $this->assertCount(2, $filters->primaryTypes->values);
        $this->assertCount(2, $filters->secondaryTypes->values);
        $this->assertCount(1, $filters->anyTypes->values);
        $this->assertCount(2, $filters->categoryForms->values);
        $this->assertCount(3, $filters->regionalForms->values);
        $this->assertCount(2, $filters->specialForms->values);
        $this->assertCount(1, $filters->variantForms->values);
        $this->assertCount(1, $filters->catchStates->values);
        $this->assertCount(1, $filters->originalGameBundles->values);
        $this->assertCount(1, $filters->gameBundleAvailabilities->values);
        $this->assertCount(1, $filters->gameBundleShinyAvailabilities->values);
        $this->assertCount(2, $filters->families->values);
        $this->assertCount(1, $filters->collectionAvailabilities->values);

        $this->assertEmpty($filters->primaryTypes->negativeValues);
        $this->assertEmpty($filters->secondaryTypes->negativeValues);
        $this->assertEmpty($filters->anyTypes->negativeValues);
        $this->assertEmpty($filters->categoryForms->negativeValues);
        $this->assertEmpty($filters->regionalForms->negativeValues);
        $this->assertEmpty($filters->specialForms->negativeValues);
        $this->assertEmpty($filters->variantForms->negativeValues);
        $this->assertEmpty($filters->catchStates->negativeValues);
        $this->assertEmpty($filters->originalGameBundles->negativeValues);
        $this->assertEmpty($filters->gameBundleAvailabilities->negativeValues);
        $this->assertEmpty($filters->gameBundleShinyAvailabilities->negativeValues);
        $this->assertEmpty($filters->families->negativeValues);
        $this->assertEmpty($filters->collectionAvailabilities->negativeValues);
    }

    public function testCreateFromArrayWithNegative(): void
    {
        $filters = AlbumFilters::createFromArray([
            'primary_types' => ['fire', 'water'],
            'secondary_types' => ['water', 'fire'],
            'any_types' => ['normal'],
            'category_forms' => ['starter', 'finisher'],
            'regional_forms' => ['provence', 'sud', 'mer'],
            'special_forms' => ['banana', 'orange'],
            'variant_forms' => ['gender'],
            'catch_states' => ['!maybe'],
            'original_game_bundles' => ['redgreenblueyellow'],
            'game_bundle_availabilities' => ['!sunmoon'],
            'game_bundle_shiny_availabilities' => ['!ultrasunutramoon'],
            'families' => ['pichu', 'eevee'],
            'collection_availabilities' => ['!swshdens'],
        ]);

        $this->assertCount(2, $filters->primaryTypes->values);
        $this->assertCount(2, $filters->secondaryTypes->values);
        $this->assertCount(1, $filters->anyTypes->values);
        $this->assertCount(2, $filters->categoryForms->values);
        $this->assertCount(3, $filters->regionalForms->values);
        $this->assertCount(2, $filters->specialForms->values);
        $this->assertCount(1, $filters->variantForms->values);
        $this->assertCount(0, $filters->catchStates->values);
        $this->assertCount(1, $filters->originalGameBundles->values);
        $this->assertCount(0, $filters->gameBundleAvailabilities->values);
        $this->assertCount(0, $filters->gameBundleShinyAvailabilities->values);
        $this->assertCount(2, $filters->families->values);
        $this->assertCount(0, $filters->collectionAvailabilities->values);

        $this->assertCount(0, $filters->primaryTypes->negativeValues);
        $this->assertCount(0, $filters->secondaryTypes->negativeValues);
        $this->assertCount(0, $filters->anyTypes->negativeValues);
        $this->assertCount(0, $filters->categoryForms->negativeValues);
        $this->assertCount(0, $filters->regionalForms->negativeValues);
        $this->assertCount(0, $filters->specialForms->negativeValues);
        $this->assertCount(0, $filters->variantForms->negativeValues);
        $this->assertCount(1, $filters->catchStates->negativeValues);
        $this->assertCount(0, $filters->originalGameBundles->negativeValues);
        $this->assertCount(1, $filters->gameBundleAvailabilities->negativeValues);
        $this->assertCount(1, $filters->gameBundleShinyAvailabilities->negativeValues);
        $this->assertCount(0, $filters->families->negativeValues);
        $this->assertCount(1, $filters->collectionAvailabilities->negativeValues);
    }

    public function testNormalizer(): void
    {
        $filterValues = AlbumFilters::normalizer(['a', 'b', 'c']);

        $this->assertCount(3, $filterValues->values);
        $this->assertCount(0, $filterValues->negativeValues);
        $this->assertFalse($filterValues->hasNull());
    }

    public function testNormalizerWithNullValue(): void
    {
        $filterValues = AlbumFilters::normalizer(['a', 'null', 'c']);

        $this->assertCount(3, $filterValues->values);
        $this->assertCount(0, $filterValues->negativeValues);
        $this->assertTrue($filterValues->hasNull());
    }

    public function testNormalizerWithEmptyValue(): void
    {
        $filterValues = AlbumFilters::normalizer(['a', '', 'c']);

        $this->assertCount(2, $filterValues->values);
        $this->assertCount(0, $filterValues->negativeValues);
        $this->assertFalse($filterValues->hasNull());
    }

    public function testNormalizerWithEmptyNegativeValue(): void
    {
        $filterValues = AlbumFilters::normalizer(['a', '!b', 'c']);

        $this->assertCount(2, $filterValues->values);
        $this->assertCount(1, $filterValues->negativeValues);
        $this->assertFalse($filterValues->hasNull());
    }

    public function testCreateWithOptionsResolver(): void
    {
        $resolver = new OptionsResolver();

        $resolver->setRequired('value');
        $resolver->setAllowedTypes('value', 'string');
        AlbumFilters::configureOptions($resolver);

        $options = $resolver->resolve([
            'value' => 'douze',
            'primary_types' => ['fire', 'water'],
            'secondary_types' => ['water', 'fire'],
            'any_types' => ['normal'],
            'category_forms' => ['starter', 'finisher'],
            'regional_forms' => ['provence', 'sud', 'mer'],
            'special_forms' => ['banana', 'orange'],
            'variant_forms' => ['gender'],
            'catch_states' => ['maybe'],
            'original_game_bundles' => ['redgreenblueyellow'],
            'game_bundle_availabilities' => ['sunmoon'],
            'game_bundle_shiny_availabilities' => ['ultrasunutramoon'],
            'families' => ['pichu', 'eevee'],
            'collection_availabilities' => ['swshdens'],
        ]);

        $this->assertSame('douze', $options['value']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['primary_types']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['secondary_types']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['any_types']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['category_forms']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['regional_forms']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['special_forms']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['variant_forms']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['catch_states']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['original_game_bundles']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['game_bundle_availabilities']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['game_bundle_shiny_availabilities']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['families']);
        $this->assertInstanceOf(AlbumFilterValues::class, $options['collection_availabilities']);
    }

    public function testCreateWithOptionsResolverException(): void
    {
        $resolver = new OptionsResolver();

        $resolver->setRequired('value');
        $resolver->setAllowedTypes('value', 'string');
        AlbumFilters::configureOptions($resolver);

        $this->expectException(MissingOptionsException::class);
        $resolver->resolve([
            'primary_types' => ['fire', 'water'],
            'secondary_types' => ['water', 'fire'],
            'any_types' => ['normal'],
            'category_forms' => ['starter', 'finisher'],
            'regional_forms' => ['provence', 'sud', 'mer'],
            'special_forms' => ['banana', 'orange'],
            'variant_forms' => ['gender'],
            'catch_states' => ['maybe'],
            'original_game_bundles' => ['redgreenblueyellow'],
            'game_bundle_availabilities' => ['sunmoon'],
            'game_bundle_shiny_availabilities' => ['ultrasunutramoon'],
            'families' => ['pichu', 'eevee'],
            'collection_availabilities' => ['swshdens'],
        ]);
    }
}
