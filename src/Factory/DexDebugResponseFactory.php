<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexDebugFlagsResponse;
use App\DTO\Response\DexDebugOrderingResponse;
use App\DTO\Response\DexDebugResponse;
use App\DTO\Response\RegionResponse;
use App\Entity\Dex;
use App\Entity\Region;

final class DexDebugResponseFactory
{
    public static function fromDex(Dex $dex): DexDebugResponse
    {
        return new DexDebugResponse(
            identifier: $dex->getIdentifier()?->toRfc4122(),
            slug: $dex->slug,
            name: $dex->name,
            frenchName: $dex->frenchName,
            selectionRule: $dex->selectionRule,
            ordering: self::buildOrdering($dex),
            flags: self::buildFlags($dex),
            displayTemplate: $dex->displayTemplate,
            region: null !== $dex->region ? self::buildRegion($dex->region) : null,
            description: $dex->description,
            frenchDescription: $dex->frenchDescription,
            lastChangedAt: $dex->lastChangedAt->format(\DateTime::ATOM),
            deletedAt: $dex->deletedAt?->format(\DateTime::ATOM),
        );
    }

    private static function buildOrdering(Dex $dex): DexDebugOrderingResponse
    {
        return new DexDebugOrderingResponse(
            main: $dex->orderNumber,
            election: $dex->electionOrderNumber,
        );
    }

    private static function buildFlags(Dex $dex): DexDebugFlagsResponse
    {
        return new DexDebugFlagsResponse(
            isShiny: $dex->isShiny,
            isPremium: $dex->isPremium,
            isDisplayForm: $dex->isDisplayForm,
            isReleased: $dex->isReleased,
            canHoldElection: $dex->canHoldElection,
        );
    }

    private static function buildRegion(Region $region): RegionResponse
    {
        return new RegionResponse(
            identifier: $region->getIdentifier()?->toRfc4122(),
            slug: $region->slug,
            name: $region->name,
            frenchName: $region->frenchName,
            orderNumber: $region->orderNumber,
            deletedAt: $region->deletedAt?->format(\DateTime::ATOM),
        );
    }
}
