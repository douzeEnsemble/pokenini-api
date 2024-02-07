<?php

declare(strict_types=1);

namespace App\DTO\AlbumFilter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AlbumFiltersRequest
{
    public static function albumFiltersFromRequest(Request $request): AlbumFilters
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults([
            'primary_types' => [],
            'secondary_types' => [],
            'category_forms' => [],
            'regional_forms' => [],
            'special_forms' => [],
            'variant_forms' => [],
        ]);

        $options = $resolver->resolve($request->query->all());

        return AlbumFilters::createFromArray([
            'primaryTypes' => $options['primary_types'],
            'secondaryTypes' => $options['secondary_types'],
            'categoryForms' => $options['category_forms'],
            'regionalForms' => $options['regional_forms'],
            'specialForms' => $options['special_forms'],
            'variantForms' => $options['variant_forms'],
        ]);
    }
}
