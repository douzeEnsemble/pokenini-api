<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use App\DTO\Response\PokemonDebugFormsResponse;
use App\DTO\Response\PokemonDebugResponse;
use App\DTO\Response\PokemonDebugTypesResponse;
use App\DTO\Response\TypeDebugResponse;
use App\Entity\CategoryForm;
use App\Entity\GameBundle;
use App\Entity\GameGeneration;
use App\Entity\Pokemon;
use App\Entity\RegionalForm;
use App\Entity\SpecialForm;
use App\Entity\Type;
use App\Entity\VariantForm;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
final class PokemonDebugResponseFactory
{
    public static function fromPokemon(Pokemon $pokemon): PokemonDebugResponse
    {
        return new PokemonDebugResponse(
            identifier: $pokemon->getIdentifier()?->toRfc4122(),
            slug: $pokemon->slug,
            name: $pokemon->name,
            frenchName: $pokemon->frenchName,
            simplifiedName: $pokemon->simplifiedName,
            simplifiedFrenchName: $pokemon->simplifiedFrenchName,
            formsLabel: $pokemon->formsLabel,
            formsFrenchLabel: $pokemon->formsFrenchLabel,
            nationalDexNumber: $pokemon->nationalDexNumber,
            family: $pokemon->family,
            bankable: $pokemon->bankable,
            bankableish: $pokemon->bankableish,
            iconName: $pokemon->iconName,
            familyOrder: $pokemon->familyOrder,
            originalGameBundle: self::buildGameBundle($pokemon->originalGameBundle),
            forms: self::buildForms($pokemon),
            types: self::buildTypes($pokemon),
            deletedAt: $pokemon->deletedAt?->format(DATE_ATOM),
        );
    }

    private static function buildForms(Pokemon $pokemon): ?PokemonDebugFormsResponse
    {
        if (
            null === $pokemon->variantForm
            && null === $pokemon->regionalForm
            && null === $pokemon->specialForm
            && null === $pokemon->categoryForm
        ) {
            return null;
        }

        return new PokemonDebugFormsResponse(
            category: null !== $pokemon->categoryForm ? self::buildForm($pokemon->categoryForm) : null,
            regional: null !== $pokemon->regionalForm ? self::buildForm($pokemon->regionalForm) : null,
            special: null !== $pokemon->specialForm ? self::buildForm($pokemon->specialForm) : null,
            variant: null !== $pokemon->variantForm ? self::buildForm($pokemon->variantForm) : null,
        );
    }

    private static function buildTypes(Pokemon $pokemon): PokemonDebugTypesResponse
    {
        return new PokemonDebugTypesResponse(
            primary: null !== $pokemon->primaryType ? self::buildType($pokemon->primaryType) : null,
            secondary: null !== $pokemon->secondaryType ? self::buildType($pokemon->secondaryType) : null,
        );
    }

    private static function buildGameBundle(GameBundle $gameBundle): GameBundleDebugResponse
    {
        return new GameBundleDebugResponse(
            identifier: $gameBundle->getIdentifier()?->toRfc4122(),
            slug: $gameBundle->slug,
            name: $gameBundle->name,
            frenchName: $gameBundle->frenchName,
            orderNumber: $gameBundle->orderNumber,
            generation: self::buildGeneration($gameBundle->generation),
            deletedAt: $gameBundle->deletedAt?->format(DATE_ATOM),
        );
    }

    private static function buildGeneration(GameGeneration $generation): GameGenerationDebugResponse
    {
        return new GameGenerationDebugResponse(
            identifier: $generation->getIdentifier()?->toRfc4122(),
            slug: $generation->slug,
            name: $generation->name,
            deletedAt: $generation->deletedAt?->format(DATE_ATOM),
        );
    }

    private static function buildForm(CategoryForm|RegionalForm|SpecialForm|VariantForm $form): FormDebugResponse
    {
        return new FormDebugResponse(
            identifier: $form->getIdentifier()?->toRfc4122(),
            slug: $form->slug,
            name: $form->name,
            frenchName: $form->frenchName,
            orderNumber: $form->orderNumber,
            deletedAt: $form->deletedAt?->format(DATE_ATOM),
        );
    }

    private static function buildType(Type $type): TypeDebugResponse
    {
        return new TypeDebugResponse(
            identifier: $type->getIdentifier()?->toRfc4122(),
            slug: $type->slug,
            name: $type->name,
            frenchName: $type->frenchName,
            orderNumber: $type->orderNumber,
            color: $type->color,
            deletedAt: $type->deletedAt?->format(DATE_ATOM),
        );
    }
}
