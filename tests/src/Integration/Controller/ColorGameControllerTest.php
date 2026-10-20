<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ColorGameAnswerController;
use App\Controller\ColorGameController;
use App\Controller\ColorGameDexController;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(ColorGameController::class)]
#[CoversClass(ColorGameDexController::class)]
#[CoversClass(ColorGameAnswerController::class)]
final class ColorGameControllerTest extends AbstractTestControllerApi
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Several requests are done in some tests, they must share the same database transaction
        $this->client->disableReboot();
    }

    #[Test]
    public function dex(): void
    {
        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/dex');

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(['homeshiny', 'demo'], array_column($content, 'slug'));

        /** @var array<string, mixed> $homeShiny */
        $homeShiny = $content[0];
        $this->assertSame(
            [
                'slug' => 'homeshiny',
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'is_shiny' => true,
                'is_released' => true,
                'is_premium' => false,
                'description' => '',
                'french_description' => '',
                'playable_count' => 2,
                'current_game' => null,
            ],
            $homeShiny,
        );

        /** @var array<string, mixed> $demo */
        $demo = $content[1];
        $this->assertSame(4, $demo['playable_count']);
        $this->assertSame(
            [
                'id' => $this->getGameId('color_game_demo_in_progress'),
                'dex' => [
                    'slug' => 'demo',
                    'name' => 'Demo',
                    'french_name' => 'Démo',
                ],
                'is_shiny' => false,
                'status' => 'in_progress',
                'round_count' => 3,
                'answered_count' => 1,
                'correct_count' => 1,
                'started_at' => '2026-10-10T10:00:00+00:00',
                'last_played_at' => '2026-10-10T10:05:00+00:00',
                'ended_at' => null,
            ],
            $demo['current_game'],
        );
    }

    #[Test]
    public function dexForAnotherTrainer(): void
    {
        $this->apiRequest('GET', '/color_game/unknown/dex');

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame([null, null], array_column($content, 'current_game'));
    }

    /**
     * @param list<string> $expected
     */
    #[Test]
    #[DataProvider('providerDexOptions')]
    public function dexWithOptions(bool $includeUnreleasedDex, bool $includePremiumDex, array $expected): void
    {
        $this->apiRequest(
            'GET',
            '/color_game/'.self::TRAINER.'/dex',
            [
                'include_unreleased_dex' => $includeUnreleasedDex ? 'true' : 'false',
                'include_premium_dex' => $includePremiumDex ? 'true' : 'false',
            ],
        );

        $this->assertJsonResponseIsOK();

        $this->assertSame($expected, array_column($this->getJsonDecodedResponseContent(), 'slug'));
    }

    /**
     * @return array<string, array{bool, bool, list<string>}>
     */
    public static function providerDexOptions(): array
    {
        return [
            'default' => [false, false, ['homeshiny', 'demo']],
            'unreleased' => [true, false, ['homeshiny', 'homepogo', 'demo']],
            'premium' => [false, true, ['redgreenblueyellow', 'homeshiny', 'demo']],
            'all' => [true, true, ['redgreenblueyellow', 'homeshiny', 'homepogo', 'demo']],
        ];
    }

    #[Test]
    public function list(): void
    {
        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games');

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(2, $content);
        $this->assertSame(
            [
                $this->getGameId('color_game_demo_in_progress'),
                $this->getGameId('color_game_homeshiny_completed'),
            ],
            array_column($content, 'id'),
        );
        $this->assertSame(
            [
                'id' => $this->getGameId('color_game_homeshiny_completed'),
                'dex' => [
                    'slug' => 'homeshiny',
                    'name' => "Home\nShiny",
                    'french_name' => "Home\nChromatique",
                ],
                'is_shiny' => true,
                'status' => 'completed',
                'round_count' => 2,
                'answered_count' => 2,
                'correct_count' => 1,
                'started_at' => '2026-10-01T09:00:00+00:00',
                'last_played_at' => '2026-10-01T09:02:00+00:00',
                'ended_at' => '2026-10-01T09:02:00+00:00',
            ],
            $content[1],
        );
    }

    #[Test]
    public function listForAnUnknownTrainer(): void
    {
        $this->apiRequest('GET', '/color_game/unknown/games');

        $this->assertJsonResponseIsOK();
        $this->assertSame([], $this->getJsonDecodedResponseContent());
    }

    #[Test]
    public function create(): void
    {
        $previousGameId = $this->getGameId('color_game_demo_in_progress');

        $this->apiRequest('POST', '/color_game/'.self::TRAINER.'/games', [], null, '{"dex_slug": "demo"}');

        $this->assertSame(201, $this->getClientResponse()->getStatusCode());

        $content = $this->getJsonDecodedResponseContent();

        $this->assertIsString($content['id']);
        $this->assertNotSame($previousGameId, $content['id']);
        $this->assertSame(['slug' => 'demo', 'name' => 'Demo', 'french_name' => 'Démo'], $content['dex']);
        $this->assertFalse($content['is_shiny']);
        $this->assertSame('in_progress', $content['status']);
        $this->assertSame(4, $content['round_count']);
        $this->assertSame(0, $content['answered_count']);
        $this->assertSame(0, $content['correct_count']);
        $this->assertNull($content['last_played_at']);
        $this->assertNull($content['ended_at']);

        // Every playable Pokémon of the dex is played once, with all its colour sets
        $rounds = $this->getRounds($content['id']);
        $this->assertSame([1, 2, 3, 4], array_column($rounds, 'position'));

        $choicesBySlug = array_column($rounds, 'choices_order', 'slug');
        ksort($choicesBySlug);
        $this->assertSame(['bulbasaur', 'caterpie', 'ivysaur', 'venusaur'], array_keys($choicesBySlug));

        $sortedChoices = array_map(
            static function (string $choicesOrder): string {
                $positions = explode(',', $choicesOrder);
                sort($positions);

                return implode(',', $positions);
            },
            $choicesBySlug,
        );
        $this->assertSame(
            [
                'bulbasaur' => '1,2,3',
                'caterpie' => '1,2',
                'ivysaur' => '1,2',
                'venusaur' => '1,2',
            ],
            $sortedChoices,
        );

        // The game in progress on the same dex is abandoned
        $previousGame = $this->getGame($previousGameId);
        $this->assertSame('abandoned', $previousGame['status']);
        $this->assertNotNull($previousGame['ended_at']);

        // Other games are untouched
        $this->assertSame('in_progress', $this->getGame($this->getGameId('color_game_demo_other_trainer'))['status']);
        $this->assertSame('completed', $this->getGame($this->getGameId('color_game_homeshiny_completed'))['status']);
    }

    #[Test]
    public function createShiny(): void
    {
        $this->apiRequest('POST', '/color_game/'.self::TRAINER.'/games', [], null, '{"dex_slug": "homeshiny"}');

        $this->assertSame(201, $this->getClientResponse()->getStatusCode());

        $content = $this->getJsonDecodedResponseContent();

        $this->assertTrue($content['is_shiny']);
        $this->assertSame(2, $content['round_count']);
        $this->assertIsString($content['id']);

        $slugs = array_column($this->getRounds($content['id']), 'slug');
        sort($slugs);
        $this->assertSame(['bulbasaur', 'caterpie'], $slugs);

        // A completed game is not abandoned
        $this->assertSame('completed', $this->getGame($this->getGameId('color_game_homeshiny_completed'))['status']);
    }

    #[Test]
    #[DataProvider('providerCreateErrors')]
    public function createErrors(string $content, int $expectedStatusCode): void
    {
        $this->apiRequest('POST', '/color_game/'.self::TRAINER.'/games', [], null, $content);

        $this->assertSame($expectedStatusCode, $this->getClientResponse()->getStatusCode());
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function providerCreateErrors(): array
    {
        return [
            'empty content' => ['', 400],
            'invalid json' => ['{"dex_slug": ', 400],
            'not an object' => ['"demo"', 400],
            'missing dex slug' => ['{}', 400],
            'invalid dex slug' => ['{"dex_slug": 12}', 400],
            'unknown option' => ['{"dex_slug": "demo", "douze": 12}', 400],
            'no playable Pokémon' => ['{"dex_slug": "homepogo"}', 400],
            'unknown dex' => ['{"dex_slug": "douze"}', 404],
            'not playable dex' => ['{"dex_slug": "home"}', 404],
            'deleted dex' => ['{"dex_slug": "spoon"}', 404],
        ];
    }

    #[Test]
    public function show(): void
    {
        $gameId = $this->getGameId('color_game_demo_in_progress');

        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/'.$gameId);

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        /** @var array<string, mixed> $game */
        $game = $content['game'];
        $this->assertSame($gameId, $game['id']);
        $this->assertSame(1, $game['answered_count']);

        $this->assertSame(
            [
                'position' => 2,
                'pokemon' => [
                    'slug' => 'ivysaur',
                    'name' => 'Ivysaur',
                    'french_name' => 'Herbizarre',
                    'simplified_name' => 'Ivysaur',
                    'simplified_french_name' => 'Herbizarre',
                    'forms_label' => '',
                    'forms_french_label' => '',
                    'national_dex_number' => 2,
                ],
                'choices' => [
                    [
                        'index' => 0,
                        'codes' => ['#B45AA0', '#8CE66E'],
                        'names' => ['purple', 'green'],
                        'french_names' => ['violet', 'vert'],
                    ],
                    [
                        'index' => 1,
                        'codes' => ['#5AA0B4', '#E66E8C'],
                        'names' => ['teal', 'pink'],
                        'french_names' => ['bleu vert', 'rose'],
                    ],
                ],
            ],
            $content['round'],
        );
    }

    #[Test]
    public function showShiny(): void
    {
        $this->apiRequest('POST', '/color_game/'.self::TRAINER.'/games', [], null, '{"dex_slug": "homeshiny"}');

        $gameId = $this->getJsonDecodedResponseContent()['id'];
        $this->assertIsString($gameId);

        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/'.$gameId);

        $this->assertJsonResponseIsOK();

        /** @var array{pokemon: array{slug: string}, choices: list<array{codes: list<string>}>} $round */
        $round = $this->getJsonDecodedResponseContent()['round'];

        $codes = array_merge(...array_column($round['choices'], 'codes'));
        sort($codes);

        $expected = [
            'bulbasaur' => ['#64DCC8', '#8CD2B4', '#D28CAA', '#DCC864'],
            'caterpie' => ['#3050F8', '#78C850', '#C878F8', '#F8D030'],
        ];

        $this->assertSame($expected[$round['pokemon']['slug']], $codes);
    }

    #[Test]
    public function showFinishedGame(): void
    {
        $gameId = $this->getGameId('color_game_homeshiny_completed');

        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/'.$gameId);

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertNull($content['round']);
        $this->assertIsArray($content['game']);
        $this->assertSame('completed', $content['game']['status']);
    }

    #[Test]
    public function showMissingColorSets(): void
    {
        $gameId = $this->getGameId('color_game_demo_in_progress');

        $this->executeSql(
            <<<'SQL'
                DELETE FROM pokemon_color_set
                WHERE pokemon_id = (SELECT id FROM pokemon WHERE slug = 'ivysaur') AND position = 2
                SQL
        );

        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/'.$gameId);

        $this->assertJsonResponseIsOK();

        /** @var array{choices: list<array<string, mixed>>} $round */
        $round = $this->getJsonDecodedResponseContent()['round'];

        $this->assertSame(
            [
                [
                    'index' => 0,
                    'codes' => [],
                    'names' => [],
                    'french_names' => [],
                ],
                [
                    'index' => 1,
                    'codes' => ['#5AA0B4', '#E66E8C'],
                    'names' => ['teal', 'pink'],
                    'french_names' => ['bleu vert', 'rose'],
                ],
            ],
            $round['choices'],
        );
    }

    #[Test]
    public function showNotFound(): void
    {
        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/'.$this->getGameId('color_game_demo_other_trainer'));
        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/01a15aac-cfd2-7085-bbc7-88594aa005dd');
        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/douze');
        $this->assertResponseIsNotFound();
    }

    #[Test]
    public function answer(): void
    {
        $gameId = $this->getGameId('color_game_demo_in_progress');

        $this->apiRequest(
            'POST',
            '/color_game/'.self::TRAINER.'/games/'.$gameId.'/answers',
            [],
            null,
            '{"position": 2, "choice": 1}',
        );

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(2, $content['position']);
        $this->assertTrue($content['is_correct']);
        $this->assertSame(1, $content['chosen_choice']);
        $this->assertSame(1, $content['correct_choice']);

        /** @var array<string, mixed> $game */
        $game = $content['game'];
        $this->assertSame('in_progress', $game['status']);
        $this->assertSame(2, $game['answered_count']);
        $this->assertSame(2, $game['correct_count']);
        $this->assertNotSame('2026-10-10T10:05:00+00:00', $game['last_played_at']);
        $this->assertNull($game['ended_at']);

        $round = $this->getRounds($gameId)[1];
        $this->assertSame(1, $round['chosen_choice']);
        $this->assertTrue($round['is_correct']);
        $this->assertNotNull($round['answered_at']);

        // The next round is now the third one
        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/'.$gameId);

        /** @var array{position: int} $nextRound */
        $nextRound = $this->getJsonDecodedResponseContent()['round'];
        $this->assertSame(3, $nextRound['position']);
    }

    #[Test]
    public function answerWrongAndComplete(): void
    {
        $gameId = $this->getGameId('color_game_demo_in_progress');

        $this->apiRequest(
            'POST',
            '/color_game/'.self::TRAINER.'/games/'.$gameId.'/answers',
            [],
            null,
            '{"position": 3, "choice": 1}',
        );

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertFalse($content['is_correct']);
        $this->assertSame(1, $content['chosen_choice']);
        $this->assertSame(0, $content['correct_choice']);

        /** @var array<string, mixed> $game */
        $game = $content['game'];
        $this->assertSame('in_progress', $game['status']);
        $this->assertSame(2, $game['answered_count']);
        $this->assertSame(1, $game['correct_count']);

        $this->apiRequest(
            'POST',
            '/color_game/'.self::TRAINER.'/games/'.$gameId.'/answers',
            [],
            null,
            '{"position": 2, "choice": 0}',
        );

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertFalse($content['is_correct']);

        /** @var array<string, mixed> $game */
        $game = $content['game'];
        $this->assertSame('completed', $game['status']);
        $this->assertSame(3, $game['answered_count']);
        $this->assertSame(1, $game['correct_count']);
        $this->assertNotNull($game['ended_at']);

        $round = $this->getRounds($gameId)[2];
        $this->assertSame(1, $round['chosen_choice']);
        $this->assertFalse($round['is_correct']);
    }

    #[Test]
    #[DataProvider('providerAnswerErrors')]
    public function answerErrors(string $gameKey, string $content, int $expectedStatusCode): void
    {
        $this->apiRequest(
            'POST',
            '/color_game/'.self::TRAINER.'/games/'.$this->getGameId($gameKey).'/answers',
            [],
            null,
            $content,
        );

        $this->assertSame($expectedStatusCode, $this->getClientResponse()->getStatusCode());
    }

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function providerAnswerErrors(): array
    {
        return [
            'empty content' => ['color_game_demo_in_progress', '', 400],
            'invalid json' => ['color_game_demo_in_progress', '{"position": ', 400],
            'missing position' => ['color_game_demo_in_progress', '{"choice": 0}', 400],
            'missing choice' => ['color_game_demo_in_progress', '{"position": 2}', 400],
            'string position' => ['color_game_demo_in_progress', '{"position": "2", "choice": 0}', 400],
            'zero position' => ['color_game_demo_in_progress', '{"position": 0, "choice": 0}', 400],
            'negative choice' => ['color_game_demo_in_progress', '{"position": 2, "choice": -1}', 400],
            'unknown choice' => ['color_game_demo_in_progress', '{"position": 2, "choice": 2}', 400],
            'unknown round' => ['color_game_demo_in_progress', '{"position": 4, "choice": 0}', 404],
            'already answered' => ['color_game_demo_in_progress', '{"position": 1, "choice": 0}', 409],
            'finished game' => ['color_game_homeshiny_completed', '{"position": 1, "choice": 0}', 409],
            'other trainer game' => ['color_game_demo_other_trainer', '{"position": 1, "choice": 0}', 404],
        ];
    }

    #[Test]
    public function answerUnknownGame(): void
    {
        $this->apiRequest(
            'POST',
            '/color_game/'.self::TRAINER.'/games/01a15aac-cfd2-7085-bbc7-88594aa005dd/answers',
            [],
            null,
            '{"position": 1, "choice": 0}',
        );

        $this->assertResponseIsNotFound();
    }

    private function getGameId(string $fixtureKey): string
    {
        $parameters = match ($fixtureKey) {
            'color_game_demo_in_progress' => [self::TRAINER, 'demo', 'in_progress'],
            'color_game_homeshiny_completed' => [self::TRAINER, 'homeshiny', 'completed'],
            'color_game_demo_other_trainer' => ['other_trainer', 'demo', 'in_progress'],
            default => throw new \InvalidArgumentException($fixtureKey),
        };

        /** @var string */
        return $this->getConnection()->fetchOne(
            <<<'SQL'
                SELECT  cg.id
                FROM    color_game AS cg
                    JOIN dex AS d ON d.id = cg.dex_id
                WHERE   cg.trainer_external_id = ?
                    AND d.slug = ?
                    AND cg.status = ?
                SQL,
            $parameters,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getGame(string $gameId): array
    {
        /** @var array<string, mixed> */
        return $this->getConnection()->fetchAssociative('SELECT * FROM color_game WHERE id = ?', [$gameId]);
    }

    /**
     * @return list<array{position: int, slug: string, choices_order: string, chosen_choice: ?int, is_correct: ?bool, answered_at: ?string}>
     */
    private function getRounds(string $gameId): array
    {
        /** @var list<array{position: int, slug: string, choices_order: string, chosen_choice: ?int, is_correct: ?bool, answered_at: ?string}> */
        return $this->getConnection()->fetchAllAssociative(
            <<<'SQL'
                SELECT      cgr.position, p.slug, cgr.choices_order, cgr.chosen_choice, cgr.is_correct, cgr.answered_at
                FROM        color_game_round AS cgr
                    JOIN    pokemon AS p ON p.id = cgr.pokemon_id
                WHERE       cgr.color_game_id = ?
                ORDER BY    cgr.position
                SQL,
            [$gameId],
        );
    }

    private function executeSql(string $sql): void
    {
        $this->getConnection()->executeStatement($sql);
    }

    private function getConnection(): Connection
    {
        /** @var Connection */
        return self::getContainer()->get(Connection::class);
    }
}
