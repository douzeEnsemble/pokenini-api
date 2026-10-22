<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\WhichShinyGameAnswerController;
use App\Controller\WhichShinyGameController;
use App\Controller\WhichShinyGameDexController;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(WhichShinyGameController::class)]
#[CoversClass(WhichShinyGameDexController::class)]
#[CoversClass(WhichShinyGameAnswerController::class)]
final class WhichShinyGameControllerTest extends AbstractTestControllerApi
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
        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/dex');

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(['demo'], array_column($content, 'slug'));

        /** @var array<string, mixed> $demo */
        $demo = $content[0];
        $this->assertSame('Demo', $demo['name']);
        $this->assertSame('Démo', $demo['french_name']);
        $this->assertTrue($demo['is_released']);
        $this->assertFalse($demo['is_premium']);
        $this->assertSame(21, $demo['playable_count']);
        $this->assertArrayNotHasKey('is_shiny', $demo);
        $this->assertSame(
            [
                'id' => $this->getGameId('which_shiny_game_demo_in_progress'),
                'dex' => [
                    'slug' => 'demo',
                    'name' => 'Demo',
                    'french_name' => 'Démo',
                ],
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
        $this->apiRequest('GET', '/which_shiny_game/unknown/dex');

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame([null], array_column($content, 'current_game'));
    }

    /**
     * @param array<string, int> $expected
     */
    #[Test]
    #[DataProvider('providerDexOptions')]
    public function dexWithOptions(bool $includeUnreleasedDex, bool $includePremiumDex, array $expected): void
    {
        $this->apiRequest(
            'GET',
            '/which_shiny_game/'.self::TRAINER.'/dex',
            [
                'include_unreleased_dex' => $includeUnreleasedDex ? 'true' : 'false',
                'include_premium_dex' => $includePremiumDex ? 'true' : 'false',
            ],
        );

        $this->assertJsonResponseIsOK();

        $this->assertSame($expected, array_column($this->getJsonDecodedResponseContent(), 'playable_count', 'slug'));
    }

    /**
     * @return array<string, array{bool, bool, array<string, int>}>
     */
    public static function providerDexOptions(): array
    {
        return [
            'default' => [false, false, ['demo' => 21]],
            'unreleased' => [true, false, ['demo' => 21, 'homepogo' => 0]],
            'premium' => [false, true, ['demo' => 21, 'redgreenblueyellow' => 7]],
            'all' => [true, true, ['demo' => 21, 'redgreenblueyellow' => 7, 'homepogo' => 0]],
        ];
    }

    #[Test]
    public function list(): void
    {
        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games');

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(2, $content);
        $this->assertSame(
            [
                $this->getGameId('which_shiny_game_demo_in_progress'),
                $this->getGameId('which_shiny_game_redgreenblueyellow_completed'),
            ],
            array_column($content, 'id'),
        );
        $this->assertSame(
            [
                'id' => $this->getGameId('which_shiny_game_redgreenblueyellow_completed'),
                'dex' => [
                    'slug' => 'redgreenblueyellow',
                    'name' => 'Red / Green / Blue / Yellow',
                    'french_name' => 'Rouge / Vert / Bleu / Jaune',
                ],
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
        $this->apiRequest('GET', '/which_shiny_game/unknown/games');

        $this->assertJsonResponseIsOK();
        $this->assertSame([], $this->getJsonDecodedResponseContent());
    }

    #[Test]
    public function create(): void
    {
        $previousGameId = $this->getGameId('which_shiny_game_demo_in_progress');

        $this->apiRequest('POST', '/which_shiny_game/'.self::TRAINER.'/games', [], null, '{"dex_slug": "demo"}');

        $this->assertSame(201, $this->getClientResponse()->getStatusCode());

        $content = $this->getJsonDecodedResponseContent();

        $this->assertIsString($content['id']);
        $this->assertNotSame($previousGameId, $content['id']);
        $this->assertSame(['slug' => 'demo', 'name' => 'Demo', 'french_name' => 'Démo'], $content['dex']);
        $this->assertSame('in_progress', $content['status']);
        $this->assertSame(21, $content['round_count']);
        $this->assertSame(0, $content['answered_count']);
        $this->assertSame(0, $content['correct_count']);
        $this->assertNull($content['last_played_at']);
        $this->assertNull($content['ended_at']);

        // Every Pokémon of the dex is played once
        $rounds = $this->getRounds($content['id']);
        $this->assertSame(range(1, 21), array_column($rounds, 'position'));

        $slugs = array_column($rounds, 'slug');
        $this->assertCount(21, array_unique($slugs));
        $this->assertContains('bulbasaur', $slugs);
        $this->assertContains('raticate-alola-totem', $slugs);

        foreach ($rounds as $round) {
            $this->assertNull($round['chosen_slot']);
        }

        // The game in progress on the same dex is abandoned
        $previousGame = $this->getGame($previousGameId);
        $this->assertSame('abandoned', $previousGame['status']);
        $this->assertNotNull($previousGame['ended_at']);

        // Other games are untouched
        $this->assertSame('in_progress', $this->getGame($this->getGameId('which_shiny_game_demo_other_trainer'))['status']);
        $this->assertSame('completed', $this->getGame($this->getGameId('which_shiny_game_redgreenblueyellow_completed'))['status']);
    }

    #[Test]
    public function createOnACompletedDex(): void
    {
        $this->apiRequest('POST', '/which_shiny_game/'.self::TRAINER.'/games', [], null, '{"dex_slug": "redgreenblueyellow"}');

        $this->assertSame(201, $this->getClientResponse()->getStatusCode());

        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(7, $content['round_count']);
        $this->assertIsString($content['id']);

        $slugs = array_column($this->getRounds($content['id']), 'slug');
        sort($slugs);
        $this->assertSame(['bulbasaur', 'butterfree', 'caterpie', 'douze', 'ivysaur', 'metapod', 'venusaur'], $slugs);

        // A completed game is not abandoned
        $this->assertSame('completed', $this->getGame($this->getGameId('which_shiny_game_redgreenblueyellow_completed'))['status']);
    }

    #[Test]
    #[DataProvider('providerCreateErrors')]
    public function createErrors(string $content, int $expectedStatusCode): void
    {
        $this->apiRequest('POST', '/which_shiny_game/'.self::TRAINER.'/games', [], null, $content);

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
            'shiny dex' => ['{"dex_slug": "homeshiny"}', 404],
            'deleted dex' => ['{"dex_slug": "spoon"}', 404],
        ];
    }

    #[Test]
    public function show(): void
    {
        $gameId = $this->getGameId('which_shiny_game_demo_in_progress');

        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games/'.$gameId);

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
                    'icon' => 'ivysaur',
                ],
                'shiny_slot' => 1,
            ],
            $content['round'],
        );
    }

    #[Test]
    public function showNextRound(): void
    {
        $gameId = $this->getGameId('which_shiny_game_demo_in_progress');

        $this->apiRequest(
            'POST',
            '/which_shiny_game/'.self::TRAINER.'/games/'.$gameId.'/answers',
            [],
            null,
            '{"position": 2, "slot": 1}',
        );

        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games/'.$gameId);

        $this->assertJsonResponseIsOK();

        /** @var array{position: int, pokemon: array{slug: string}, shiny_slot: int} $round */
        $round = $this->getJsonDecodedResponseContent()['round'];

        $this->assertSame(3, $round['position']);
        $this->assertSame('venusaur', $round['pokemon']['slug']);
        $this->assertSame(2, $round['shiny_slot']);
    }

    #[Test]
    public function showFinishedGame(): void
    {
        $gameId = $this->getGameId('which_shiny_game_redgreenblueyellow_completed');

        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games/'.$gameId);

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertNull($content['round']);
        $this->assertIsArray($content['game']);
        $this->assertSame('completed', $content['game']['status']);
    }

    #[Test]
    public function showNotFound(): void
    {
        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games/'.$this->getGameId('which_shiny_game_demo_other_trainer'));
        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games/01a15aac-cfd2-7085-bbc7-88594aa005dd');
        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games/douze');
        $this->assertResponseIsNotFound();
    }

    #[Test]
    public function answer(): void
    {
        $gameId = $this->getGameId('which_shiny_game_demo_in_progress');

        $this->apiRequest(
            'POST',
            '/which_shiny_game/'.self::TRAINER.'/games/'.$gameId.'/answers',
            [],
            null,
            '{"position": 2, "slot": 1}',
        );

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(2, $content['position']);
        $this->assertTrue($content['is_correct']);
        $this->assertSame(1, $content['chosen_slot']);
        $this->assertSame(1, $content['shiny_slot']);

        /** @var array<string, mixed> $game */
        $game = $content['game'];
        $this->assertSame('in_progress', $game['status']);
        $this->assertSame(2, $game['answered_count']);
        $this->assertSame(2, $game['correct_count']);
        $this->assertNotSame('2026-10-10T10:05:00+00:00', $game['last_played_at']);
        $this->assertNull($game['ended_at']);

        $round = $this->getRounds($gameId)[1];
        $this->assertSame(1, $round['chosen_slot']);
        $this->assertTrue($round['is_correct']);
        $this->assertNotNull($round['answered_at']);
    }

    #[Test]
    public function answerWrongAndComplete(): void
    {
        $gameId = $this->getGameId('which_shiny_game_demo_in_progress');

        $this->apiRequest(
            'POST',
            '/which_shiny_game/'.self::TRAINER.'/games/'.$gameId.'/answers',
            [],
            null,
            '{"position": 3, "slot": 1}',
        );

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertFalse($content['is_correct']);
        $this->assertSame(1, $content['chosen_slot']);
        $this->assertSame(2, $content['shiny_slot']);

        /** @var array<string, mixed> $game */
        $game = $content['game'];
        $this->assertSame('in_progress', $game['status']);
        $this->assertSame(2, $game['answered_count']);
        $this->assertSame(1, $game['correct_count']);

        $this->apiRequest(
            'POST',
            '/which_shiny_game/'.self::TRAINER.'/games/'.$gameId.'/answers',
            [],
            null,
            '{"position": 2, "slot": 2}',
        );

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertFalse($content['is_correct']);
        $this->assertSame(2, $content['chosen_slot']);
        $this->assertSame(1, $content['shiny_slot']);

        /** @var array<string, mixed> $game */
        $game = $content['game'];
        $this->assertSame('completed', $game['status']);
        $this->assertSame(3, $game['answered_count']);
        $this->assertSame(1, $game['correct_count']);
        $this->assertNotNull($game['ended_at']);

        $round = $this->getRounds($gameId)[2];
        $this->assertSame(1, $round['chosen_slot']);
        $this->assertFalse($round['is_correct']);
    }

    #[Test]
    #[DataProvider('providerAnswerErrors')]
    public function answerErrors(string $gameKey, string $content, int $expectedStatusCode): void
    {
        $this->apiRequest(
            'POST',
            '/which_shiny_game/'.self::TRAINER.'/games/'.$this->getGameId($gameKey).'/answers',
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
            'empty content' => ['which_shiny_game_demo_in_progress', '', 400],
            'invalid json' => ['which_shiny_game_demo_in_progress', '{"position": ', 400],
            'missing position' => ['which_shiny_game_demo_in_progress', '{"slot": 2}', 400],
            'missing slot' => ['which_shiny_game_demo_in_progress', '{"position": 2}', 400],
            'string position' => ['which_shiny_game_demo_in_progress', '{"position": "2", "slot": 2}', 400],
            'zero position' => ['which_shiny_game_demo_in_progress', '{"position": 0, "slot": 2}', 400],
            'boolean slot' => ['which_shiny_game_demo_in_progress', '{"position": 2, "slot": true}', 400],
            'third slot' => ['which_shiny_game_demo_in_progress', '{"position": 2, "slot": 3}', 400],
            'unknown round' => ['which_shiny_game_demo_in_progress', '{"position": 4, "slot": 2}', 404],
            'already answered' => ['which_shiny_game_demo_in_progress', '{"position": 1, "slot": 1}', 409],
            'finished game' => ['which_shiny_game_redgreenblueyellow_completed', '{"position": 1, "slot": 1}', 409],
            'other trainer game' => ['which_shiny_game_demo_other_trainer', '{"position": 1, "slot": 1}', 404],
        ];
    }

    #[Test]
    public function answerUnknownGame(): void
    {
        $this->apiRequest(
            'POST',
            '/which_shiny_game/'.self::TRAINER.'/games/01a15aac-cfd2-7085-bbc7-88594aa005dd/answers',
            [],
            null,
            '{"position": 1, "slot": 1}',
        );

        $this->assertResponseIsNotFound();
    }

    private function getGameId(string $fixtureKey): string
    {
        $parameters = match ($fixtureKey) {
            'which_shiny_game_demo_in_progress' => [self::TRAINER, 'demo', 'in_progress'],
            'which_shiny_game_redgreenblueyellow_completed' => [self::TRAINER, 'redgreenblueyellow', 'completed'],
            'which_shiny_game_demo_other_trainer' => ['other_trainer', 'demo', 'in_progress'],
            default => throw new \InvalidArgumentException($fixtureKey),
        };

        /** @var string */
        return $this->getConnection()->fetchOne(
            <<<'SQL'
                SELECT  sg.id
                FROM    which_shiny_game AS sg
                    JOIN dex AS d ON d.id = sg.dex_id
                WHERE   sg.trainer_external_id = ?
                    AND d.slug = ?
                    AND sg.status = ?
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
        return $this->getConnection()->fetchAssociative('SELECT * FROM which_shiny_game WHERE id = ?', [$gameId]);
    }

    /**
     * @return list<array{position: int, slug: string, shiny_slot: int, chosen_slot: ?int, is_correct: ?bool, answered_at: ?string}>
     */
    private function getRounds(string $gameId): array
    {
        /** @var list<array{position: int, slug: string, shiny_slot: int, chosen_slot: ?int, is_correct: ?bool, answered_at: ?string}> */
        return $this->getConnection()->fetchAllAssociative(
            <<<'SQL'
                SELECT      sgr.position, p.slug, sgr.shiny_slot, sgr.chosen_slot, sgr.is_correct, sgr.answered_at
                FROM        which_shiny_game_round AS sgr
                    JOIN    pokemon AS p ON p.id = sgr.pokemon_id
                WHERE       sgr.which_shiny_game_id = ?
                ORDER BY    sgr.position
                SQL,
            [$gameId],
        );
    }

    private function getConnection(): Connection
    {
        /** @var Connection */
        return self::getContainer()->get(Connection::class);
    }
}
