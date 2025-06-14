<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ElectionVote
{
    public string $trainerExternalId;
    public string $dexSlug;
    public string $electionSlug;

    /**
     * @var string[]
     */
    public array $winnersSlugs;

    /**
     * @var string[]
     */
    public array $losersSlugs;

    /**
     * @param string[]|string[][] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($values);

        $this->trainerExternalId = $options['trainer_external_id'];
        $this->dexSlug = $options['dex_slug'];
        $this->electionSlug = $options['election_slug'];
        $this->winnersSlugs = $options['winners_slugs'];
        $this->losersSlugs = $options['losers_slugs'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('trainer_external_id');
        $resolver->setAllowedTypes('trainer_external_id', 'string');

        $resolver->setDefault('election_slug', '');
        $resolver->setAllowedTypes('election_slug', 'string');

        $resolver->setDefault('dex_slug', '');
        $resolver->setAllowedTypes('dex_slug', 'string');

        $resolver->setRequired('winners_slugs');
        $resolver->setAllowedTypes('winners_slugs', 'string[]');

        $resolver->setRequired('losers_slugs');
        $resolver->setAllowedTypes('losers_slugs', 'string[]');
    }
}
