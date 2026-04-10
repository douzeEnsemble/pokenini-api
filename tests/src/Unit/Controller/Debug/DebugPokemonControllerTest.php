<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Debug;

use App\Controller\Debug\DebugPokemonController;
use App\Entity\Pokemon;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use App\Service\PokedexService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

/**
 * @internal
 */
#[CoversClass(DebugPokemonController::class)]
#[CoversClass(PokedexService::class)]
final class DebugPokemonControllerTest extends TestCase
{
    public function testPokemonCleanCaches(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'zaertyuiop';

        $gamesAvailabilitiesService = $this->createMock(GamesAvailabilitiesService::class);
        $gamesAvailabilitiesService
            ->expects($this->once())
            ->method('cleanCacheFromPokemon')
            ->with($pokemon)
        ;

        $gamesShiniesAvailabilitiesService = $this->createMock(GamesShiniesAvailabilitiesService::class);
        $gamesShiniesAvailabilitiesService
            ->expects($this->once())
            ->method('cleanCacheFromPokemon')
            ->with($pokemon)
        ;

        $gameBundlesAvailabilitiesService = $this->createMock(GameBundlesAvailabilitiesService::class);
        $gameBundlesAvailabilitiesService
            ->expects($this->once())
            ->method('cleanCacheFromPokemon')
            ->with($pokemon)
        ;

        $gameBundlesShiniesAvailabilitiesService = $this->createMock(GameBundlesShiniesAvailabilitiesService::class);
        $gameBundlesShiniesAvailabilitiesService
            ->expects($this->once())
            ->method('cleanCacheFromPokemon')
            ->with($pokemon)
        ;

        $collectionsAvailabilitiesService = $this->createMock(CollectionsAvailabilitiesService::class);
        $collectionsAvailabilitiesService
            ->expects($this->once())
            ->method('cleanCacheFromPokemon')
            ->with($pokemon)
        ;

        $controller = new DebugPokemonController(new Serializer());

        $controller->pokemonCaches(
            $gamesAvailabilitiesService,
            $gamesShiniesAvailabilitiesService,
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
            $collectionsAvailabilitiesService,
            $pokemon
        );
    }
}
