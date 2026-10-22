<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ColorGameCreation
{
    public string $dexSlug;

    /**
     * Null to play every Pokémon of the dex.
     */
    public ?int $roundCount;

    /**
     * @param mixed[] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        /**
         * @var array{
         *  dex_slug: string,
         *  round_count: null|int,
         * } $options
         */
        $options = $resolver->resolve($values);

        $this->dexSlug = $options['dex_slug'];
        $this->roundCount = $options['round_count'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('dex_slug');
        $resolver->setAllowedTypes('dex_slug', 'string');

        $resolver->setDefault('round_count', null);
        $resolver->setAllowedTypes('round_count', ['null', 'int']);
        $resolver->setAllowedValues('round_count', static fn (?int $value): bool => null === $value || $value > 0);
    }
}
