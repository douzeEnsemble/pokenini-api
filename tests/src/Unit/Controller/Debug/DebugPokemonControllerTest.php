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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DebugPokemonController::class)]
final class DebugPokemonControllerTest extends TestCase
{
    #[Test]
    public function pokemonCleanCaches(): void
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

        $controller = new DebugPokemonController();

        $response = $controller->pokemonCaches(
            $gamesAvailabilitiesService,
            $gamesShiniesAvailabilitiesService,
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
            $collectionsAvailabilitiesService,
            $pokemon
        );

        $this->assertEmpty($response->getContent());
    }
}
