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
     * @param array<string, mixed> $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        /**
         * @var array{
         *  trainer: array<string, mixed>,
         *  dex_slug: string,
         *  election_slug: string,
         *  winners_slugs: string[],
         *  losers_slugs: string[],
         * } $options
         */
        $options = $resolver->resolve($values);

        if (!isset($options['trainer']['external_id']) || !is_string($options['trainer']['external_id'])) {
            throw new \InvalidArgumentException('trainer.external_id must be a string');
        }

        $this->trainerExternalId = $options['trainer']['external_id'];
        $this->dexSlug = $options['dex_slug'];
        $this->electionSlug = $options['election_slug'];
        $this->winnersSlugs = $options['winners_slugs'];
        $this->losersSlugs = $options['losers_slugs'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('trainer');
        $resolver->setAllowedTypes('trainer', 'array');

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
