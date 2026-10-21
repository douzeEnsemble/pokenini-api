<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class WhichShinyGameAnswer
{
    public int $position;
    public int $slot;

    /**
     * @param mixed[] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        /**
         * @var array{
         *  position: int,
         *  slot: int,
         * } $options
         */
        $options = $resolver->resolve($values);

        $this->position = $options['position'];
        $this->slot = $options['slot'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('position');
        $resolver->setAllowedTypes('position', 'int');
        $resolver->setAllowedValues('position', static fn (int $value): bool => $value >= 1);

        $resolver->setRequired('slot');
        $resolver->setAllowedTypes('slot', 'int');
        $resolver->setAllowedValues('slot', [1, 2]);
    }
}
