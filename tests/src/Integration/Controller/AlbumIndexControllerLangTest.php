<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\AlbumIndexController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * The album with the names in a single language.
 *
 * @internal
 */
#[CoversClass(AlbumIndexController::class)]
final class AlbumIndexControllerLangTest extends AbstractTestControllerApi
{
    private const string URI = '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow';

    #[Test]
    public function inFrench(): void
    {
        $data = $this->getAlbum(['lang' => 'fr']);

        $dex = $this->arrayOf($data['dex']);
        $this->assertSame('Rouge / Vert / Bleu / Jaune', $dex['name']);
        $this->assertSame('La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.', $dex['description']);
        $this->assertSame(['name' => 'Kanto'], $dex['region']);
        $this->assertArrayNotHasKey('french_name', $dex);
        $this->assertArrayNotHasKey('french_description', $dex);

        $bulbasaur = $this->bulbasaur($data);
        $this->assertSame(
            ['en' => 'Bulbasaur', 'fr' => 'Bulbizarre'],
            $this->arrayOf($this->arrayOf($bulbasaur['pokemon'])['labels'])['names'],
        );
        $this->assertSame(
            ['en' => 'Bulbasaur', 'fr' => 'Bulbizarre'],
            $this->arrayOf($this->arrayOf($bulbasaur['pokemon'])['labels'])['simplified_names'],
        );
        $this->assertSame(
            ['slug' => 'starter', 'name' => 'de Départ'],
            $this->arrayOf($bulbasaur['forms'])['category'],
        );
        $this->assertSame('Plante', $this->arrayOf($this->arrayOf($bulbasaur['types'])['primary'])['name']);
        $this->assertArrayNotHasKey('french_name', $this->arrayOf($this->arrayOf($bulbasaur['types'])['primary']));
        $this->assertArrayNotHasKey('french_name', $this->arrayOf($this->arrayOf($bulbasaur['pokemon'])['labels']));
    }

    #[Test]
    public function theReportsAreInTheRequestedLanguage(): void
    {
        $data = $this->getAlbum(['lang' => 'fr']);

        foreach (['report', 'filtered_report'] as $key) {
            $detail = $this->arrayOf($this->arrayOf($data[$key])['detail']);

            $names = array_map(
                fn (mixed $line): mixed => $this->arrayOf($this->arrayOf($line)['catch_state'])['name'],
                $detail,
            );

            $this->assertSame(['Non', 'Peut être', 'Peut être pas', 'Oui'], $names);
        }
    }

    #[Test]
    public function inEnglish(): void
    {
        $data = $this->getAlbum(['lang' => 'en']);

        $this->assertSame('Red / Green / Blue / Yellow', $this->arrayOf($data['dex'])['name']);
        $bulbasaur = $this->bulbasaur($data);
        $this->assertSame('Grass', $this->arrayOf($this->arrayOf($bulbasaur['types'])['primary'])['name']);
    }

    #[Test]
    public function aLanguageDoesNotDisturbTheFilters(): void
    {
        $filtered = $this->getAlbum(['lang' => 'fr', 'primary_types' => ['grass']]);
        $all = $this->getAlbum(['lang' => 'fr']);

        $this->assertLessThan(\count($this->arrayOf($all['pokemons'])), \count($this->arrayOf($filtered['pokemons'])));
        $this->assertNotEmpty($filtered['pokemons']);
    }

    #[Test]
    public function withoutLanguageTheNamesAreInEnglishAndFrench(): void
    {
        $data = $this->getAlbum([]);

        $this->assertSame('Rouge / Vert / Bleu / Jaune', $this->arrayOf($data['dex'])['french_name']);
        $labels = $this->arrayOf($this->arrayOf($this->bulbasaur($data)['pokemon'])['labels']);
        $this->assertSame('Bulbizarre', $labels['french_name']);
        $this->assertArrayNotHasKey('names', $labels);
    }

    #[Test]
    public function aNotEnabledLanguageIsABadRequest(): void
    {
        $this->apiRequest('GET', self::URI, ['lang' => 'de']);

        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * @param array<string, list<string>|string> $params
     *
     * @return array<string, mixed>
     */
    private function getAlbum(array $params): array
    {
        $this->apiRequest('GET', self::URI, $params);

        $this->assertJsonResponseIsOK();

        /** @var array<string, mixed> */
        return json_decode($this->getClientResponseContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function bulbasaur(array $data): array
    {
        /** @var mixed $candidate */
        foreach ($this->arrayOf($data['pokemons']) as $candidate) {
            $pokemon = $this->arrayOf($candidate);

            if ('bulbasaur' === $this->arrayOf($pokemon['pokemon'])['slug']) {
                /** @var array<string, mixed> */
                return $pokemon;
            }
        }

        $this->fail('No bulbasaur in the album');
    }

    /**
     * @return array<array-key, mixed>
     */
    private function arrayOf(mixed $value): array
    {
        $this->assertIsArray($value);

        return $value;
    }
}
