<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class DexQueryOptions
{
    public bool $includeUnreleasedDex;

    /**
     * @param string[]|bool[] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($values);

        $this->includeUnreleasedDex = $options['include_unreleased_dex'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('include_unreleased_dex', false);
        $resolver->setAllowedTypes('include_unreleased_dex', 'bool');
    }
}
