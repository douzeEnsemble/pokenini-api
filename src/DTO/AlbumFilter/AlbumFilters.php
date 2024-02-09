<?php

declare(strict_types=1);

namespace App\DTO\AlbumFilter;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AlbumFilters
{
    private function __construct(
        public AlbumFilterValues $primaryTypes,
        public AlbumFilterValues $secondaryTypes,
        public AlbumFilterValues $anyTypes,
        public AlbumFilterValues $categoryForms,
        public AlbumFilterValues $regionalForms,
        public AlbumFilterValues $specialForms,
        public AlbumFilterValues $variantForms,
        public AlbumFilterValues $catchStates,
    ) {
    }

    /**
     * @param string[][] $data
     */
    public static function createFromArray(array $data): self
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults([
            'primaryTypes' => [],
            'secondaryTypes' => [],
            'anyTypes' => [],
            'categoryForms' => [],
            'regionalForms' => [],
            'specialForms' => [],
            'variantForms' => [],
            'catchStates' => [],
        ]);

        $resolver->setNormalizer('primaryTypes', function (Options $options, array $data): AlbumFilterValues {
            return new AlbumFilterValues($data);
        });
        $resolver->setNormalizer('secondaryTypes', function (Options $options, array $data): AlbumFilterValues {
            return new AlbumFilterValues($data);
        });
        $resolver->setNormalizer('anyTypes', function (Options $options, array $data): AlbumFilterValues {
            return new AlbumFilterValues($data);
        });
        $resolver->setNormalizer('categoryForms', function (Options $options, array $data): AlbumFilterValues {
            return new AlbumFilterValues($data);
        });
        $resolver->setNormalizer('regionalForms', function (Options $options, array $data): AlbumFilterValues {
            return new AlbumFilterValues($data);
        });
        $resolver->setNormalizer('specialForms', function (Options $options, array $data): AlbumFilterValues {
            return new AlbumFilterValues($data);
        });
        $resolver->setNormalizer('variantForms', function (Options $options, array $data): AlbumFilterValues {
            return new AlbumFilterValues($data);
        });
        $resolver->setNormalizer('catchStates', function (Options $options, array $data): AlbumFilterValues {
            return new AlbumFilterValues($data);
        });

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
        );
    }
}
