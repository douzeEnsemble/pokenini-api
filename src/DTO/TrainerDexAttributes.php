<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class TrainerDexAttributes
{
    public bool $isPrivate;
    public bool $isOnHome;
    public bool $isOnHub;

    /**
     * @param bool[]|string[] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        /**
         * @var array{
         *  is_private: bool,
         *  is_on_home: bool,
         *  is_on_hub: bool,
         * } $options
         */
        $options = $resolver->resolve($values);

        $this->isPrivate = $options['is_private'];
        $this->isOnHome = $options['is_on_home'];
        $this->isOnHub = $options['is_on_hub'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('is_private', false);
        $resolver->setAllowedTypes('is_private', 'bool');

        $resolver->setDefault('is_on_home', false);
        $resolver->setAllowedTypes('is_on_home', 'bool');

        $resolver->setDefault('is_on_hub', false);
        $resolver->setAllowedTypes('is_on_hub', 'bool');
    }
}
