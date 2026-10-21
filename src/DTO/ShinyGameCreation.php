<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ShinyGameCreation
{
    public string $dexSlug;

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
         * } $options
         */
        $options = $resolver->resolve($values);

        $this->dexSlug = $options['dex_slug'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('dex_slug');
        $resolver->setAllowedTypes('dex_slug', 'string');
    }
}
