<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\WhichShinyGameController;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(WhichShinyGameController::class)]
final class WhichShinyGameRoundsControllerTest extends AbstractTestControllerApi
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
    public function rounds(): void
    {
        $gameId = $this->getGameId('which_shiny_game_redgreenblueyellow_completed');

        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games/'.$gameId.'/rounds');

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        /** @var array<string, mixed> $game */
        $game = $content['game'];
        $this->assertSame($gameId, $game['id']);
        $this->assertSame('completed', $game['status']);

        /** @var list<array<string, mixed>> $rounds */
        $rounds = $content['rounds'];
        $this->assertSame(
            [
                ['position' => 1, 'slug' => 'caterpie', 'is_correct' => true, 'shiny_slot' => 1, 'chosen_slot' => 1],
                ['position' => 2, 'slug' => 'metapod', 'is_correct' => false, 'shiny_slot' => 2, 'chosen_slot' => 1],
            ],
            array_map(
                static function (array $round): array {
                    /** @var array<string, mixed> $pokemon */
                    $pokemon = $round['pokemon'];

                    return [
                        'position' => $round['position'],
                        'slug' => $pokemon['slug'],
                        'is_correct' => $round['is_correct'],
                        'shiny_slot' => $round['shiny_slot'],
                        'chosen_slot' => $round['chosen_slot'],
                    ];
                },
                $rounds,
            ),
        );
    }

    #[Test]
    public function roundsOfAGameInProgress(): void
    {
        $gameId = $this->getGameId('which_shiny_game_demo_in_progress');

        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games/'.$gameId.'/rounds');

        $this->assertJsonResponseIsOK();

        // The rounds not answered yet are not sent: they would give the answers away
        $this->assertSame(
            [
                [
                    'position' => 1,
                    'pokemon' => [
                        'slug' => 'bulbasaur',
                        'name' => 'Bulbasaur',
                        'french_name' => 'Bulbizarre',
                        'simplified_name' => 'Bulbasaur',
                        'simplified_french_name' => 'Bulbizarre',
                        'forms_label' => '',
                        'forms_french_label' => '',
                        'national_dex_number' => 1,
                        'icon' => 'bulbasaur',
                    ],
                    'is_correct' => true,
                    'shiny_slot' => 2,
                    'chosen_slot' => 2,
                ],
            ],
            $this->getJsonDecodedResponseContent()['rounds'],
        );
    }

    #[Test]
    public function roundsNotFound(): void
    {
        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games/'.$this->getGameId('which_shiny_game_demo_other_trainer').'/rounds');
        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', '/which_shiny_game/'.self::TRAINER.'/games/01a15aac-cfd2-7085-bbc7-88594aa005dd/rounds');
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

    private function getConnection(): Connection
    {
        /** @var Connection */
        return self::getContainer()->get(Connection::class);
    }
}
