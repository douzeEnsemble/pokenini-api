<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ShinyGameAnswerController;
use App\Controller\ShinyGameController;
use App\Controller\ShinyGameDexController;
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
#[CoversClass(ShinyGameController::class)]
#[CoversClass(ShinyGameDexController::class)]
#[CoversClass(ShinyGameAnswerController::class)]
#[CoversClass(WhichShinyGameController::class)]
#[CoversClass(WhichShinyGameDexController::class)]
#[CoversClass(WhichShinyGameAnswerController::class)]
final class GameControllersLangTest extends AbstractTestControllerApi
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client->disableReboot();
    }

    #[DataProvider('providerRoutes')]
    #[Test]
    public function dex(string $route): void
    {
        $this->apiRequest('GET', "/{$route}/".self::TRAINER.'/dex', ['lang' => 'fr']);

        $this->assertJsonResponseIsOK();

        /** @var array<string, mixed> $demo */
        $demo = $this->getJsonDecodedResponseContent()[0];
        $this->assertSame('Démo', $demo['name']);
        $this->assertNull($demo['french_name']);
        $this->assertNull($demo['french_description']);

        $this->apiRequest('GET', "/{$route}/".self::TRAINER.'/dex');

        /** @var array<string, mixed> $legacy */
        $legacy = $this->getJsonDecodedResponseContent()[0];
        $this->assertSame($legacy['french_description'], $demo['description']);

        /** @var array<string, mixed> $game */
        $game = $demo['current_game'];
        $this->assertSame(['slug' => 'demo', 'name' => 'Démo', 'french_name' => null], $game['dex']);
    }

    #[DataProvider('providerRoutes')]
    #[Test]
    public function dexWithoutLanguage(string $route): void
    {
        $this->apiRequest('GET', "/{$route}/".self::TRAINER.'/dex');

        $this->assertJsonResponseIsOK();

        /** @var array<string, mixed> $demo */
        $demo = $this->getJsonDecodedResponseContent()[0];
        $this->assertSame('Demo', $demo['name']);
        $this->assertSame('Démo', $demo['french_name']);
    }

    #[DataProvider('providerRoutes')]
    #[Test]
    public function list(string $route): void
    {
        $this->apiRequest('GET', "/{$route}/".self::TRAINER.'/games', ['lang' => 'fr']);

        $this->assertJsonResponseIsOK();

        foreach ($this->getJsonDecodedResponseContent() as $game) {
            $this->assertIsArray($game);
            $this->assertIsArray($game['dex']);
            $this->assertNull($game['dex']['french_name']);
            $this->assertNotSame('', $game['dex']['name']);
        }
    }

    #[DataProvider('providerRoutes')]
    #[Test]
    public function create(string $route): void
    {
        $this->apiRequest('POST', "/{$route}/".self::TRAINER.'/games', ['lang' => 'fr'], null, '{"dex_slug": "demo"}');

        $this->assertSame(201, $this->getClientResponse()->getStatusCode());
        $this->assertSame(
            ['slug' => 'demo', 'name' => 'Démo', 'french_name' => null],
            $this->getJsonDecodedResponseContent()['dex'],
        );
    }

    #[DataProvider('providerRoutes')]
    #[Test]
    public function show(string $route): void
    {
        $this->apiRequest('GET', "/{$route}/".self::TRAINER.'/games/'.$this->getGameId($route, 'in_progress'), ['lang' => 'fr']);

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();
        $this->assertIsArray($content['game']);
        $this->assertIsArray($content['game']['dex']);
        $this->assertSame('Démo', $content['game']['dex']['name']);
        $this->assertIsArray($content['round']);
        $this->assertIsArray($content['round']['pokemon']);
        $this->assertNull($content['round']['pokemon']['french_name']);
        $this->assertNull($content['round']['pokemon']['simplified_french_name']);
        $this->assertNull($content['round']['pokemon']['forms_french_label']);
        $this->assertSame('Herbizarre', $content['round']['pokemon']['name']);
    }

    #[DataProvider('providerRoutes')]
    #[Test]
    public function rounds(string $route): void
    {
        $this->apiRequest('GET', "/{$route}/".self::TRAINER.'/games/'.$this->getGameId($route, 'completed').'/rounds', ['lang' => 'fr']);

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();
        $this->assertIsArray($content['rounds']);
        $this->assertNotEmpty($content['rounds']);

        foreach ($content['rounds'] as $round) {
            $this->assertIsArray($round);
            $this->assertIsArray($round['pokemon']);
            $this->assertNull($round['pokemon']['french_name']);
            $this->assertNotSame('', $round['pokemon']['name']);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function providerRoutes(): array
    {
        return ['shiny game' => ['shiny_game'], 'which shiny game' => ['which_shiny_game']];
    }

    #[DataProvider('providerAnswers')]
    #[Test]
    public function answer(string $route, string $answer): void
    {
        $this->apiRequest(
            'POST',
            "/{$route}/".self::TRAINER.'/games/'.$this->getGameId($route, 'in_progress').'/answers',
            ['lang' => 'fr'],
            null,
            $answer,
        );

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();
        $this->assertIsArray($content['game']);
        $this->assertSame(
            ['slug' => 'demo', 'name' => 'Démo', 'french_name' => null],
            $content['game']['dex'],
        );
    }

    #[DataProvider('providerAnswers')]
    #[Test]
    public function notEnabledLanguage(string $route, string $answer): void
    {
        $gameId = $this->getGameId($route, 'in_progress');

        foreach (
            [
                ['GET', "/{$route}/".self::TRAINER.'/dex', null],
                ['GET', "/{$route}/".self::TRAINER.'/games', null],
                ['POST', "/{$route}/".self::TRAINER.'/games', '{"dex_slug": "demo"}'],
                ['GET', "/{$route}/".self::TRAINER."/games/{$gameId}", null],
                ['GET', "/{$route}/".self::TRAINER."/games/{$gameId}/rounds", null],
                ['POST', "/{$route}/".self::TRAINER."/games/{$gameId}/answers", $answer],
            ] as [$method, $url, $content]
        ) {
            $this->apiRequest($method, $url, ['lang' => 'de'], null, $content);

            $this->assertSame(400, $this->getClientResponse()->getStatusCode(), $url);
        }
    }

    /**
     * The route of a game is the name of its table.
     *
     * @return array<string, array{string, string}>
     */
    public static function providerAnswers(): array
    {
        return [
            'shiny game' => ['shiny_game', '{"position": 2, "is_shiny": false}'],
            'which shiny game' => ['which_shiny_game', '{"position": 2, "slot": 1}'],
        ];
    }

    private function getGameId(string $table, string $status): string
    {
        $connection = self::getContainer()->get(Connection::class);

        /** @var string */
        return $connection->fetchOne(
            "SELECT id FROM {$table} WHERE trainer_external_id = ? AND status = ?",
            [self::TRAINER, $status],
        );
    }
}
