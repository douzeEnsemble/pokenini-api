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
        $resolver->setRequired('isPrivate');
        $resolver->setAllowedTypes('isPrivate', 'bool');

        $resolver->setRequired('isOnHome');
        $resolver->setAllowedTypes('isOnHome', 'bool');
    }
}
