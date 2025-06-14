<?php

declare(strict_types=1);

namespace App\DTO\AlbumFilter;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AlbumFilters
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public AlbumFilterValues $primaryTypes,
        public AlbumFilterValues $secondaryTypes,
        public AlbumFilterValues $anyTypes,
        public AlbumFilterValues $categoryForms,
        public AlbumFilterValues $regionalForms,
        public AlbumFilterValues $specialForms,
        public AlbumFilterValues $variantForms,
        public AlbumFilterValues $catchStates,
        public AlbumFilterValues $originalGameBundles,
        public AlbumFilterValues $gameBundleAvailabilities,
        public AlbumFilterValues $gameBundleShinyAvailabilities,
        public AlbumFilterValues $families,
        public AlbumFilterValues $collectionAvailabilities,
    ) {}

    /**
     * @param string[][] $data
     */
    public static function createFromArray(array $data): self
    {
        $resolver = new OptionsResolver();
        self::configureOptions($resolver);
        $options = $resolver->resolve($data);

        return new self(
            $options['primary_types'],
            $options['secondary_types'],
            $options['any_types'],
            $options['category_forms'],
            $options['regional_forms'],
            $options['special_forms'],
            $options['variant_forms'],
            $options['catch_states'],
            $options['original_game_bundles'],
            $options['game_bundle_availabilities'],
            $options['game_bundle_shiny_availabilities'],
            $options['families'],
            $options['collection_availabilities'],
        );
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $defaultsValues = [
            'primary_types' => [],
            'secondary_types' => [],
            'any_types' => [],
            'category_forms' => [],
            'regional_forms' => [],
            'special_forms' => [],
            'variant_forms' => [],
            'catch_states' => [],
            'original_game_bundles' => [],
            'game_bundle_availabilities' => [],
            'game_bundle_shiny_availabilities' => [],
            'families' => [],
            'collection_availabilities' => [],
        ];

        $resolver->setDefaults($defaultsValues);

        foreach (array_keys($defaultsValues) as $key) {
            $resolver->setNormalizer(
                $key,
                function (Options $options, array $data): AlbumFilterValues {
                    unset($options); // To remove PHPMD.UnusedFormalParameter warning

                    return self::normalizer($data);
                }
            );
        }
    }

    /**
     * @param string[] $data
     */
    public static function normalizer(array $data): AlbumFilterValues
    {
        // Remove empty value
        $cleanData = array_filter($data);

        // Replace string null to null
        $newData = array_map(
            fn ($value) => ('null' == $value) ? null : $value,
            $cleanData
        );

        return new AlbumFilterValues($newData);
    }
}
