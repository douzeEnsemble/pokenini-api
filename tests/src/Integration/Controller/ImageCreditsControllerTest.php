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
    public function getReturnsOneEntryPerSpeciesOrderedByNationalDexNumberFromFixtures(): void
    {
        $client = self::createClient();
        $client->request('GET', '/credits', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var array<int, array{pokemon_slug: string, pokemon_name: string, pokemon_french_name: string, pokemon_icon: string, small_regular_credit: ?array{credit: string}, small_shiny_credit: ?array{credit: string}, big_regular_credit: ?array{credit: string}, big_shiny_credit: ?array{credit: string}}> $data */
        $data = json_decode($content, associative: true);

        self::assertCount(26, $data);
        self::assertSame('bulbasaur', $data[0]['pokemon_slug']);
        self::assertSame(
            'PokéSprite - https://github.com/msikma/pokesprite',
            $data[0]['small_regular_credit']['credit'] ?? null,
        );
        self::assertNull($data[0]['small_shiny_credit']);

        $charmander = self::findEntryBySlug($data, 'charmander');
        self::assertNull($charmander['small_regular_credit']);
        self::assertNull($charmander['small_shiny_credit']);
        self::assertNull($charmander['big_regular_credit']);
        self::assertNull($charmander['big_shiny_credit']);
    }

    /**
     * @param array<int, array{pokemon_slug: string, small_regular_credit: ?array{credit: string}, small_shiny_credit: ?array{credit: string}, big_regular_credit: ?array{credit: string}, big_shiny_credit: ?array{credit: string}}> $data
     *
     * @return array{pokemon_slug: string, small_regular_credit: ?array{credit: string}, small_shiny_credit: ?array{credit: string}, big_regular_credit: ?array{credit: string}, big_shiny_credit: ?array{credit: string}}
     */
    private static function findEntryBySlug(array $data, string $slug): array
    {
        foreach ($data as $entry) {
            if ($entry['pokemon_slug'] === $slug) {
                return $entry;
            }
        }

        self::fail(\sprintf('No entry found for slug "%s"', $slug));
    }
}
