<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class TrainerDexAttributes
{
    public bool $isPrivate;
    public bool $isOnHome;

    /**
     * @param string[]|bool[] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($values);

        $this->isPrivate = $options['isPrivate'];
        $this->isOnHome = $options['isOnHome'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('isPrivate', false);
        $resolver->setAllowedTypes('isPrivate', 'bool');

        $resolver->setDefault('isOnHome', false);
        $resolver->setAllowedTypes('isOnHome', 'bool');
    }
}
