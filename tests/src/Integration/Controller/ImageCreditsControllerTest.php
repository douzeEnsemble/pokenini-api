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
    public function getReturnsDeduplicatedCreditsFromFixtures(): void
    {
        $client = self::createClient();
        $client->request('GET', '/credits', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var array<int, array<string, string>> $data */
        $data = json_decode($content, associative: true);

        self::assertContains(['name' => 'Bulbapedia', 'url' => 'https://bulbapedia.bulbagarden.net'], $data);
        self::assertContains(['name' => 'PokéSprite', 'url' => 'https://github.com/msikma/pokesprite'], $data);
        self::assertContains(['name' => 'PokemonDB', 'url' => 'https://pokemondb.net/sprites/bulbasaur'], $data);
        self::assertContains(['name' => 'Serebii', 'url' => 'https://serebii.net'], $data);
        self::assertCount(4, $data);
    }
}
