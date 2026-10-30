<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ColorGameController;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(ColorGameController::class)]
final class ColorGameRoundsControllerTest extends AbstractTestControllerApi
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
        $gameId = $this->getGameId('color_game_homeshiny_completed');

        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/'.$gameId.'/rounds');

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
                [
                    'position' => 1,
                    'slug' => 'bulbasaur',
                    'is_correct' => true,
                    'codes' => [['#8CD2B4', '#DCC864'], ['#D28CAA', '#64DCC8']],
                    'chosen_choice' => 0,
                    'correct_choice' => 0,
                ],
                [
                    'position' => 2,
                    'slug' => 'caterpie',
                    'is_correct' => false,
                    'codes' => [['#F8D030', '#78C850'], ['#3050F8', '#C878F8']],
                    'chosen_choice' => 1,
                    'correct_choice' => 0,
                ],
            ],
            array_map(
                static function (array $round): array {
                    /** @var array<string, mixed> $pokemon */
                    $pokemon = $round['pokemon'];

                    /** @var list<array<string, mixed>> $choices */
                    $choices = $round['choices'];

                    return [
                        'position' => $round['position'],
                        'slug' => $pokemon['slug'],
                        'is_correct' => $round['is_correct'],
                        'codes' => array_column($choices, 'codes'),
                        'chosen_choice' => $round['chosen_choice'],
                        'correct_choice' => $round['correct_choice'],
                    ];
                },
                $rounds,
            ),
        );
    }

    #[Test]
    public function roundsOfAGameInProgress(): void
    {
        $gameId = $this->getGameId('color_game_demo_in_progress');

        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/'.$gameId.'/rounds');

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
                    'choices' => [
                        [
                            'index' => 0,
                            'codes' => ['#B45A6E', '#DC64C8', '#642C40'],
                            'names' => ['pink', 'magenta', 'dark red'],
                            'french_names' => ['rose', 'magenta', 'rouge foncé'],
                        ],
                        [
                            'index' => 1,
                            'codes' => ['#5AB4A0', '#94DC64', '#2C6450'],
                            'names' => ['teal', 'light green', 'dark teal'],
                            'french_names' => ['bleu vert', 'vert clair', 'bleu vert foncé'],
                        ],
                        [
                            'index' => 2,
                            'codes' => ['#A0B45A', '#64DC94', '#50642C'],
                            'names' => ['olive', 'green', 'dark olive'],
                            'french_names' => ['olive', 'vert', 'olive foncé'],
                        ],
                    ],
                    'chosen_choice' => 1,
                    'correct_choice' => 1,
                ],
            ],
            $this->getJsonDecodedResponseContent()['rounds'],
        );
    }

    #[Test]
    public function roundsNotFound(): void
    {
        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/'.$this->getGameId('color_game_demo_other_trainer').'/rounds');
        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/01a15aac-cfd2-7085-bbc7-88594aa005dd/rounds');
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

    private function getConnection(): Connection
    {
        /** @var Connection */
        return self::getContainer()->get(Connection::class);
    }
}
