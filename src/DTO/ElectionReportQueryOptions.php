<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ElectionReportQueryOptions
{
    public string $electionSlug;
    public int $count;

    /**
     * @param int[]|string[] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        /**
         * @var array{
         *  election_slug: string,
         *  count: int,
         * }
         */
        $options = $resolver->resolve($values);

        $this->electionSlug = $options['election_slug'];
        $this->count = $options['count'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('election_slug', '');
        $resolver->setAllowedTypes('election_slug', 'string');

        $resolver->setDefault('count', 5);
        $resolver->setAllowedTypes('count', ['int', 'string']);
        $resolver->setNormalizer('count', function (Options $options, string $value): int {
            unset($options); // To remove PHPMD.UnusedFormalParameter warning

            return intval($value);
        });
    }
}
