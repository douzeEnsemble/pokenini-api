<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\AlbumFilter\AlbumFilters;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TrainerPokemonEloListQueryOptions
{
    public string $trainerExternalId;
    public string $dexSlug;
    public string $electionSlug;
    public int $count;
    public AlbumFilters $albumFilters;

    /**
     * @param int[]|string[]|string[][] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();

        $this->configureOptions($resolver);

        $options = $resolver->resolve($values);

        $this->trainerExternalId = $options['trainer_external_id'];
        $this->dexSlug = $options['dex_slug'];
        $this->electionSlug = $options['election_slug'];
        $this->count = $options['count'];

        $this->albumFilters = new AlbumFilters(
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

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('trainer_external_id');
        $resolver->setAllowedTypes('trainer_external_id', 'string');

        $resolver->setRequired('dex_slug');
        $resolver->setAllowedTypes('dex_slug', 'string');

        $resolver->setDefault('election_slug', '');
        $resolver->setAllowedTypes('election_slug', 'string');

        $resolver->setRequired('count');
        $resolver->setAllowedTypes('count', ['int', 'string']);
        $resolver->setNormalizer('count', function (Options $options, string $value): int {
            unset($options); // To remove PHPMD.UnusedFormalParameter warning

            return intval($value);
        });

        AlbumFilters::configureOptions($resolver);
    }
}
