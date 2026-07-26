<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\AlbumIndexFilteredController;

use App\Controller\AlbumIndexController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(AlbumIndexController::class)]
final class ImageCreditTest extends AbstractTestAlbumIndexFilteredController
{
    public function testIndexIncludesBulbasaurImageCredits(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
        );

        $this->assertResponseIsOK();

        /** @var array<string, mixed> $data */
        $data = $this->getJsonDecodedResponseContent();

        $this->assertArrayHasKey('pokemons', $data);
        $this->assertIsArray($data['pokemons']);

        /** @var array<int, array<string, mixed>> $pokemons */
        $pokemons = $data['pokemons'];

        $bulbasaur = null;

        foreach ($pokemons as $item) {
            $this->assertArrayHasKey('pokemon', $item);
            $this->assertIsArray($item['pokemon']);

            /** @var array<string, mixed> $pokemon */
            $pokemon = $item['pokemon'];

            if ('bulbasaur' === $pokemon['slug']) {
                $bulbasaur = $pokemon;
            }
        }

        $this->assertNotNull($bulbasaur);

        $this->assertArrayHasKey('small_regular_credit', $bulbasaur);
        $this->assertIsArray($bulbasaur['small_regular_credit']);

        /** @var array<string, mixed> $smallRegularCredit */
        $smallRegularCredit = $bulbasaur['small_regular_credit'];
        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $smallRegularCredit['credit']);

        $this->assertArrayHasKey('big_regular_credit', $bulbasaur);
        $this->assertIsArray($bulbasaur['big_regular_credit']);

        /** @var array<string, mixed> $bigRegularCredit */
        $bigRegularCredit = $bulbasaur['big_regular_credit'];
        self::assertSame('PokemonDB - https://pokemondb.net/sprites/bulbasaur', $bigRegularCredit['credit']);

        self::assertNull($bulbasaur['small_shiny_credit']);
        self::assertNull($bulbasaur['big_shiny_credit']);
    }
}
