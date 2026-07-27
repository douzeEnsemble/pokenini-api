<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ImageCreditsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ImageCreditsController::class)]
final class ImageCreditsControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/credits', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsCreditsGroupedBySourceWithTheirImagesFromFixtures(): void
    {
        $client = self::createClient();
        $client->request('GET', '/credits', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var array<int, array{credit: string, images: array<int, array<string, mixed>>}> $data */
        $data = json_decode($content, associative: true);

        self::assertCount(4, $data);

        // Sorted by image count descending: PokéSprite has 2 images (bulbasaur small
        // regular + ivysaur big regular, see fixtures/pokemon_image_credits.yaml), the
        // other 3 sources have 1 image each and tie-break alphabetically.
        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $data[0]['credit']);
        self::assertCount(2, $data[0]['images']);
        self::assertSame('Bulbapedia - https://bulbapedia.bulbagarden.net', $data[1]['credit']);
        self::assertSame('PokemonDB - https://pokemondb.net/sprites/bulbasaur', $data[2]['credit']);
        self::assertSame('Serebii - https://serebii.net', $data[3]['credit']);

        self::assertContains(
            [
                'pokemon_slug' => 'bulbasaur',
                'pokemon_name' => 'Bulbasaur',
                'pokemon_french_name' => 'Bulbizarre',
                'pokemon_icon' => 'bulbasaur',
                'size' => 'small',
                'is_shiny' => false,
            ],
            $data[0]['images'],
        );
    }
}
