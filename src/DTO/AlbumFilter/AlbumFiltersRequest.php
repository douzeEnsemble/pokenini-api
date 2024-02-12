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
            'any_types' => [],
            'category_forms' => [],
            'regional_forms' => [],
            'special_forms' => [],
            'variant_forms' => [],
            'catch_states' => [],
            'original_game_bundles' => [],
            'game_bundle_availabilities' => [],
            'game_bundle_shiny_availabilities' => [],
        ]);

        $options = $resolver->resolve($request->query->all());

        return AlbumFilters::createFromArray([
            'primaryTypes' => $options['primary_types'],
            'secondaryTypes' => $options['secondary_types'],
            'anyTypes' => $options['any_types'],
            'categoryForms' => $options['category_forms'],
            'regionalForms' => $options['regional_forms'],
            'specialForms' => $options['special_forms'],
            'variantForms' => $options['variant_forms'],
            'catchStates' => $options['catch_states'],
            'originalGameBundles' => $options['original_game_bundles'],
            'gameBundleAvailabilities' => $options['game_bundle_availabilities'],
            'gameBundleShinyAvailabilities' => $options['game_bundle_shiny_availabilities'],
        ]);
    }
}
