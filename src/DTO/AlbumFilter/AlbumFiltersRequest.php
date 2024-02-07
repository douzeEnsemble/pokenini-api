<?php

declare(strict_types=1);


namespace App\DTO\AlbumFilter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AlbumFiltersRequest
{
    /**
     * @param string[][] $data
     */
    public static function albumFiltersFromRequest(Request $request): AlbumFilters
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults([
            't1' => [],
            't2' => [],
            'fc' => [],
            'fr' => [],
            'fs' => [],
            'fv' => [],
        ]);

        $options = $resolver->resolve($request->query->all());

        return AlbumFilters::createFromArray([
            'primaryTypes' => $options['t1'],
            'secondaryTypes' => $options['t2'],
            'categoryForms' => $options['fc'],
            'regionalForms' => $options['fr'],
            'specialForms' => $options['fs'],
            'variantForms' => $options['fv'],
        ]);
    }
}
