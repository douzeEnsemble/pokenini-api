<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ColorGameAnswer
{
    public int $position;
    public int $choice;

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
         *  choice: int,
         * } $options
         */
        $options = $resolver->resolve($values);

        $this->position = $options['position'];
        $this->choice = $options['choice'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('position');
        $resolver->setAllowedTypes('position', 'int');
        $resolver->setAllowedValues('position', static fn (int $value): bool => $value >= 1);

        $resolver->setRequired('choice');
        $resolver->setAllowedTypes('choice', 'int');
        $resolver->setAllowedValues('choice', static fn (int $value): bool => $value >= 0);
    }
}
