<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class TrainerDexAttributes
{
    public bool $isPrivate;
    public bool $isOnHome;

    /**
     * @param bool[]|string[] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($values);

        $this->isPrivate = $options['is_private'];
        $this->isOnHome = $options['is_on_home'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('is_private', false);
        $resolver->setAllowedTypes('is_private', 'bool');

        $resolver->setDefault('is_on_home', false);
        $resolver->setAllowedTypes('is_on_home', 'bool');
    }
}
