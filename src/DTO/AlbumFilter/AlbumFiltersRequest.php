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
            'families' => [],
            'collection_availabilities' => [],
        ]);

        /**
         * @var array{
         *  primary_types: string[],
         *  secondary_types: string[],
         *  any_types: string[],
         *  category_forms: string[],
         *  regional_forms: string[],
         *  special_forms: string[],
         *  variant_forms: string[],
         *  catch_states: string[],
         *  original_game_bundles: string[],
         *  game_bundle_availabilities: string[],
         *  game_bundle_shiny_availabilities: string[],
         *  families: string[],
         *  collection_availabilities: string[],
         * }
         */
        $options = $resolver->resolve($request->query->all());

        return AlbumFilters::createFromArray([
            'primary_types' => $options['primary_types'],
            'secondary_types' => $options['secondary_types'],
            'any_types' => $options['any_types'],
            'category_forms' => $options['category_forms'],
            'regional_forms' => $options['regional_forms'],
            'special_forms' => $options['special_forms'],
            'variant_forms' => $options['variant_forms'],
            'catch_states' => $options['catch_states'],
            'original_game_bundles' => $options['original_game_bundles'],
            'game_bundle_availabilities' => $options['game_bundle_availabilities'],
            'game_bundle_shiny_availabilities' => $options['game_bundle_shiny_availabilities'],
            'families' => $options['families'],
            'collection_availabilities' => $options['collection_availabilities'],
        ]);
    }
}
