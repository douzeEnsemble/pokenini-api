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
    private function __construct(
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
    ) {
    }

    /**
     * @param string[][] $data
     */
    public static function createFromArray(array $data): self
    {
        $resolver = new OptionsResolver();

        $defaultsValues = [
            'primaryTypes' => [],
            'secondaryTypes' => [],
            'anyTypes' => [],
            'categoryForms' => [],
            'regionalForms' => [],
            'specialForms' => [],
            'variantForms' => [],
            'catchStates' => [],
            'originalGameBundles' => [],
            'gameBundleAvailabilities' => [],
            'gameBundleShinyAvailabilities' => [],
        ];

        $resolver->setDefaults($defaultsValues);

        foreach (array_keys($defaultsValues) as $key) {
            $resolver->setNormalizer(
                $key,
                function (Options $options, array $data): AlbumFilterValues {
                    return self::normalizer($data);
                }
            );
        }

        $options = $resolver->resolve($data);

        return new self(
            $options['primaryTypes'],
            $options['secondaryTypes'],
            $options['anyTypes'],
            $options['categoryForms'],
            $options['regionalForms'],
            $options['specialForms'],
            $options['variantForms'],
            $options['catchStates'],
            $options['originalGameBundles'],
            $options['gameBundleAvailabilities'],
            $options['gameBundleShinyAvailabilities'],
        );
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
            fn($value) => (('null' == $value) ? null : $value),
            $cleanData
        );

        return new AlbumFilterValues($newData);
    }
}
