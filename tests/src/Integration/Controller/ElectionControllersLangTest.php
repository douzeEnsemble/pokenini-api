<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ElectionReportController;
use App\Controller\PokemonsController;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(ElectionReportController::class)]
#[CoversClass(PokemonsController::class)]
final class ElectionControllersLangTest extends AbstractTestControllerApi
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    /**
     * @param array<string, string> $parameters
     */
    #[DataProvider('providerPokemonsParameters')]
    #[Test]
    public function pokemonsToChoose(array $parameters): void
    {
        $parameters += ['count' => '12', 'trainer_external_id' => self::TRAINER];

        $this->apiRequest('GET', '/pokemons/to_choose', $parameters + ['lang' => 'fr']);
        $this->assertJsonResponseIsOK();

        /** @var array{items: list<array<string, mixed>>} $french */
        $french = $this->getJsonDecodedResponseContent();

        $this->assertNotEmpty($french['items']);

        $connection = self::getContainer()->get(Connection::class);

        foreach ($french['items'] as $item) {
            $this->assertIsArray($item['pokemon']);
            $this->assertIsArray($item['pokemon']['labels']);
            $this->assertArrayNotHasKey('french_name', $item['pokemon']['labels']);

            $expected = $connection->fetchAssociative(
                'SELECT name, french_name FROM pokemon WHERE slug = ?',
                [$item['pokemon']['slug']],
            );
            $this->assertIsArray($expected);

            /** @var array<string, string> $names */
            $names = $item['pokemon']['labels']['names'];
            $this->assertSame(
                ['en' => $expected['name'], 'fr' => $expected['french_name']],
                array_intersect_key($names, ['en' => 0, 'fr' => 0]),
            );
        }
    }

    /**
     * @return array<string, array{array<string, string>}>
     */
    public static function providerPokemonsParameters(): array
    {
        return [
            'to pick' => [['dex_slug' => 'home']],
            'to vote' => [['dex_slug' => 'redgreenblueyellow', 'election_slug' => 'affinee']],
        ];
    }

    #[Test]
    public function report(): void
    {
        $url = '/election/'.self::TRAINER.'/home';

        $this->apiRequest('GET', $url, ['election_slug' => 'favorite', 'count' => '5']);

        /** @var array{top: list<array<string, mixed>>} $legacy */
        $legacy = $this->getJsonDecodedResponseContent();

        $this->apiRequest('GET', $url, ['election_slug' => 'favorite', 'count' => '5', 'lang' => 'fr']);
        $this->assertResponseIsOK();

        /** @var array{top: list<array<string, mixed>>, metrics: array<string, mixed>} $french */
        $french = $this->getJsonDecodedResponseContent();

        $this->assertCount(5, $french['top']);
        $this->assertArrayHasKey('metrics', $french);

        foreach ($french['top'] as $index => $item) {
            $this->assertIsArray($item['pokemon']);
            $this->assertIsArray($item['pokemon']['labels']);
            $this->assertArrayHasKey('names', $item['pokemon']['labels']);
            $this->assertIsArray($legacy['top'][$index]['pokemon']);
            $this->assertSame($legacy['top'][$index]['pokemon']['slug'], $item['pokemon']['slug']);
        }
    }

    #[Test]
    public function list(): void
    {
        $url = '/election/'.self::TRAINER.'/list';

        $this->apiRequest('GET', $url);

        /** @var list<array<string, mixed>> $legacy */
        $legacy = $this->getJsonDecodedResponseContent();

        $this->apiRequest('GET', $url, ['lang' => 'fr']);
        $this->assertResponseIsOK();

        /** @var list<array<string, mixed>> $french */
        $french = $this->getJsonDecodedResponseContent();

        $this->assertNotEmpty($french);
        $this->assertSame(array_column($legacy, 'slug'), array_column($french, 'slug'));
        $this->assertSame(array_column($legacy, 'french_name'), array_column($french, 'name'));
        $this->assertSame(array_column($legacy, 'french_description'), array_column($french, 'description'));
        $this->assertSame(array_fill(0, count($french), null), array_column($french, 'french_name'));
        $this->assertSame(array_fill(0, count($french), null), array_column($french, 'french_description'));
    }

    /**
     * @param array<string, string> $parameters
     */
    #[DataProvider('providerNotEnabledLanguage')]
    #[Test]
    public function notEnabledLanguage(string $url, array $parameters): void
    {
        $this->apiRequest('GET', $url, $parameters + ['lang' => 'de']);

        $this->assertSame(400, $this->getClientResponse()->getStatusCode());
    }

    /**
     * @return array<string, array{string, array<string, string>}>
     */
    public static function providerNotEnabledLanguage(): array
    {
        return [
            'to choose' => ['/pokemons/to_choose', ['count' => '12', 'dex_slug' => 'home', 'trainer_external_id' => self::TRAINER]],
            'report' => ['/election/'.self::TRAINER.'/home', []],
            'list' => ['/election/'.self::TRAINER.'/list', []],
        ];
    }
}
