<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class TrainerDexValues
{
    public bool $isPrivate;

    /**
     * @param string[]|bool[] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($values);

        $this->isPrivate = $options['isPrivate'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('isPrivate');
        $resolver->setAllowedTypes('isPrivate', 'bool');
    }
}
