<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ShinyGameAnswer
{
    public int $position;
    public bool $isShiny;

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
         *  is_shiny: bool,
         * } $options
         */
        $options = $resolver->resolve($values);

        $this->position = $options['position'];
        $this->isShiny = $options['is_shiny'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('position');
        $resolver->setAllowedTypes('position', 'int');
        $resolver->setAllowedValues('position', static fn (int $value): bool => $value >= 1);

        $resolver->setRequired('is_shiny');
        $resolver->setAllowedTypes('is_shiny', 'bool');
    }
}
