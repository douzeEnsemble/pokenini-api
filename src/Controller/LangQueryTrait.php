<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LangQueryOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

trait LangQueryTrait
{
    /**
     * The language asked by the "lang" query parameter, null when there is none.
     *
     * @param string[] $locales the enabled locales
     *
     * @throws BadRequestHttpException when the language is not an enabled locale
     */
    private function getLang(Request $request, array $locales): ?string
    {
        try {
            return new LangQueryOptions(['lang' => $request->query->get('lang')], $locales)->lang;
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
