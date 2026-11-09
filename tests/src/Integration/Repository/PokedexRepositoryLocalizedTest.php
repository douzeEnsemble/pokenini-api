<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\PokedexRepository;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The names of the album in a single language.
 *
 * @internal
 */
#[CoversClass(PokedexRepository::class)]
final class PokedexRepositoryLocalizedTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function theNamesAreInTheRequestedLocale(): void
    {
        $bulbasaur = $this->bulbasaur('fr', 'en');

        $this->assertSame('de Départ', $bulbasaur['category_form_localized_name']);
        $this->assertSame('Plante', $bulbasaur['primary_type_localized_name']);
        $this->assertSame('Poison', $bulbasaur['secondary_type_localized_name']);
        $this->assertContains($bulbasaur['catch_state_localized_name'], ['Non', 'Peut être', 'Peut être pas', 'Oui']);
    }

    #[Test]
    public function theNamesOfThePokemonAreInEveryLanguage(): void
    {
        $bulbasaur = $this->bulbasaur('fr', 'en');

        $this->assertIsString($bulbasaur['pokemon_names']);
        $this->assertSame(['en' => 'Bulbasaur', 'fr' => 'Bulbizarre'], json_decode($bulbasaur['pokemon_names'], true));
        $this->assertIsString($bulbasaur['pokemon_simplified_names']);
        $this->assertSame(['en' => 'Bulbasaur', 'fr' => 'Bulbizarre'], json_decode($bulbasaur['pokemon_simplified_names'], true));
    }

    #[Test]
    public function theNamesAreInTheEnglishLocale(): void
    {
        $bulbasaur = $this->bulbasaur('en', 'en');

        $this->assertSame('Starter', $bulbasaur['category_form_localized_name']);
        $this->assertSame('Grass', $bulbasaur['primary_type_localized_name']);
    }

    #[Test]
    public function aMissingLocaleFallsBackToTheFallbackLocale(): void
    {
        $bulbasaur = $this->bulbasaur('de', 'fr');

        $this->assertSame('Plante', $bulbasaur['primary_type_localized_name']);
        $this->assertSame('de Départ', $bulbasaur['category_form_localized_name']);
    }

    #[Test]
    public function theNameColumnIsTheLastResort(): void
    {
        $this->connection()->executeStatement("UPDATE \"type\" SET names = '{}' WHERE slug = 'grass'");

        $this->assertSame('Grass', $this->bulbasaur('fr', 'en')['primary_type_localized_name']);
    }

    #[Test]
    public function theFormsLabelIsInTheRequestedLocale(): void
    {
        $this->connection()->executeStatement(
            'UPDATE pokemon SET forms_labels = CAST(:labels AS jsonb) WHERE slug = :slug',
            ['labels' => '{"en": "Alolan", "fr": "d\'Alola"}', 'slug' => 'bulbasaur'],
        );

        $this->assertSame("d'Alola", $this->bulbasaur('fr', 'en')['pokemon_localized_forms_label']);
        $this->assertSame('Alolan', $this->bulbasaur('en', 'en')['pokemon_localized_forms_label']);
    }

    #[Test]
    public function theCatchStatesCountsAreInTheRequestedLocale(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getCatchStatesCounts(self::TRAINER, 'home', AlbumFilters::createFromArray([]), 'fr', 'en');

        $this->assertSame(['Non', 'Peut être', 'Peut être pas', 'Oui'], array_column($counts, 'localized_name'));
        // The English name stays, the legacy shape still needs it
        $this->assertSame(['No', 'Maybe', 'Maybe not', 'Yes'], array_column($counts, 'name'));
    }

    #[Test]
    public function theCatchStatesCountsFallBackToTheFallbackLocale(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getCatchStatesCounts(self::TRAINER, 'home', AlbumFilters::createFromArray([]), 'de', 'fr');

        $this->assertSame(['Non', 'Peut être', 'Peut être pas', 'Oui'], array_column($counts, 'localized_name'));
    }

    /**
     * @return array<string, null|int|string>
     */
    private function bulbasaur(string $locale, string $fallbackLocale): array
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $list = $repo->getList(self::TRAINER, 'redgreenblueyellow', AlbumFilters::createFromArray([]), $locale, $fallbackLocale);

        foreach ($list as $row) {
            if ('bulbasaur' === $row['pokemon_slug']) {
                return $row;
            }
        }

        $this->fail('No bulbasaur in the album');
    }

    private function connection(): Connection
    {
        return self::getContainer()->get(Connection::class);
    }
}
