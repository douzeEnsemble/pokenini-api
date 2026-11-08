<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class TypesQueryOptions
{
    public ?string $lang;

    /**
     * @param array<string, mixed> $values
     * @param string[]             $locales the enabled locales, the only values accepted for the language
     */
    public function __construct(array $values = [], array $locales = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver, $locales);

        /**
         * @var array{
         *  lang: null|string,
         * }
         */
        $options = $resolver->resolve($values);

        $this->lang = $options['lang'];
    }

    /**
     * @param string[] $locales
     */
    private function configureOptions(OptionsResolver $resolver, array $locales): void
    {
        $resolver->setDefault('lang', null);
        // Only null or an enabled locale is accepted, which also rejects any other type
        $resolver->setAllowedValues('lang', [null, ...$locales]);
    }
}
