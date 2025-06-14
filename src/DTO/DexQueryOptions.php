<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class DexQueryOptions
{
    public bool $includeUnreleasedDex;
    public bool $includePremiumDex;

    /**
     * @param bool[]|string[] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($values);

        $this->includeUnreleasedDex = $options['include_unreleased_dex'];
        $this->includePremiumDex = $options['include_premium_dex'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('include_unreleased_dex', false);
        $resolver->setAllowedTypes('include_unreleased_dex', 'bool');

        $resolver->setDefault('include_premium_dex', false);
        $resolver->setAllowedTypes('include_premium_dex', 'bool');
    }
}
