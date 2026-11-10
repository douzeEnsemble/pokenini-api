<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ColorGameAnswerController;
use App\Controller\ColorGameController;
use App\Controller\ColorGameDexController;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(ColorGameController::class)]
#[CoversClass(ColorGameDexController::class)]
#[CoversClass(ColorGameAnswerController::class)]
final class ColorGameControllersLangTest extends AbstractTestControllerApi
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client->disableReboot();
    }

    #[Test]
    public function dex(): void
    {
        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/dex', ['lang' => 'fr']);

        $this->assertJsonResponseIsOK();

        /** @var array<string, mixed> $demo */
        $demo = $this->getJsonDecodedResponseContent()[0];
        $this->assertSame('Démo', $demo['name']);
        $this->assertNull($demo['french_name']);
        $this->assertNull($demo['french_description']);

        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/dex');

        /** @var array<string, mixed> $legacy */
        $legacy = $this->getJsonDecodedResponseContent()[0];
        $this->assertSame('Demo', $legacy['name']);
        $this->assertSame('Démo', $legacy['french_name']);
        $this->assertSame($legacy['french_description'], $demo['description']);

        /** @var array<string, mixed> $game */
        $game = $demo['current_game'];
        $this->assertSame(['slug' => 'demo', 'name' => 'Démo', 'french_name' => null], $game['dex']);
    }

    #[Test]
    public function list(): void
    {
        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games', ['lang' => 'fr']);

        $this->assertJsonResponseIsOK();

        foreach ($this->getJsonDecodedResponseContent() as $game) {
            $this->assertIsArray($game);
            $this->assertIsArray($game['dex']);
            $this->assertNull($game['dex']['french_name']);
            $this->assertNotSame('', $game['dex']['name']);
        }
    }

    #[Test]
    public function create(): void
    {
        $this->apiRequest('POST', '/color_game/'.self::TRAINER.'/games', ['lang' => 'fr'], null, '{"dex_slug": "demo"}');

        $this->assertSame(201, $this->getClientResponse()->getStatusCode());
        $this->assertSame(
            ['slug' => 'demo', 'name' => 'Démo', 'french_name' => null],
            $this->getJsonDecodedResponseContent()['dex'],
        );
    }

    #[Test]
    public function show(): void
    {
        $url = '/color_game/'.self::TRAINER.'/games/'.$this->getGameId('in_progress');

        $this->apiRequest('GET', $url, ['lang' => 'fr']);

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
        $this->assertIsArray($content['round']['choices']);

        /** @var list<array<string, mixed>> $choices */
        $choices = $content['round']['choices'];

        foreach ($choices as $choice) {
            $this->assertNull($choice['french_names']);
            $this->assertNotEmpty($choice['names']);
        }

        $this->apiRequest('GET', $url);

        $legacy = $this->getJsonDecodedResponseContent();
        $this->assertIsArray($legacy['round']);

        /** @var list<array<string, mixed>> $legacyChoices */
        $legacyChoices = $legacy['round']['choices'];

        foreach ($legacyChoices as $index => $choice) {
            $this->assertSame($choice['french_names'], $choices[$index]['names']);
        }
    }

    #[Test]
    public function rounds(): void
    {
        $this->apiRequest('GET', '/color_game/'.self::TRAINER.'/games/'.$this->getGameId('completed').'/rounds', ['lang' => 'fr']);

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();
        $this->assertIsArray($content['rounds']);
        $this->assertNotEmpty($content['rounds']);

        foreach ($content['rounds'] as $round) {
            $this->assertIsArray($round);
            $this->assertIsArray($round['pokemon']);
            $this->assertNull($round['pokemon']['french_name']);

            /** @var list<array<string, mixed>> $choices */
            $choices = $round['choices'];

            foreach ($choices as $choice) {
                $this->assertNull($choice['french_names']);
            }
        }
    }

    #[Test]
    public function answer(): void
    {
        $this->apiRequest(
            'POST',
            '/color_game/'.self::TRAINER.'/games/'.$this->getGameId('in_progress').'/answers',
            ['lang' => 'fr'],
            null,
            '{"position": 2, "choice": 0}',
        );

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();
        $this->assertIsArray($content['game']);
        $this->assertSame(
            ['slug' => 'demo', 'name' => 'Démo', 'french_name' => null],
            $content['game']['dex'],
        );
    }

    #[Test]
    public function notEnabledLanguage(): void
    {
        $gameId = $this->getGameId('in_progress');

        foreach (
            [
                ['GET', '/color_game/'.self::TRAINER.'/dex', null],
                ['GET', '/color_game/'.self::TRAINER.'/games', null],
                ['POST', '/color_game/'.self::TRAINER.'/games', '{"dex_slug": "demo"}'],
                ['GET', '/color_game/'.self::TRAINER."/games/{$gameId}", null],
                ['GET', '/color_game/'.self::TRAINER."/games/{$gameId}/rounds", null],
                ['POST', '/color_game/'.self::TRAINER."/games/{$gameId}/answers", '{"position": 2, "choice": 0}'],
            ] as [$method, $url, $content]
        ) {
            $this->apiRequest($method, $url, ['lang' => 'de'], null, $content);

            $this->assertSame(400, $this->getClientResponse()->getStatusCode(), $url);
        }
    }

    private function getGameId(string $status): string
    {
        $connection = self::getContainer()->get(Connection::class);

        /** @var string */
        return $connection->fetchOne(
            'SELECT id FROM color_game WHERE trainer_external_id = ? AND status = ?',
            [self::TRAINER, $status],
        );
    }
}
