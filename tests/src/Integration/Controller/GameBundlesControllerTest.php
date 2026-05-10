<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\GameBundlesController;
use App\Service\GameBundlesService;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GameBundlesController::class)]
#[CoversClass(GameBundlesService::class)]
final class GameBundlesControllerTest extends AbstractTestControllerApi
{
    public function testGetCollection(): void
    {
        $this->apiRequest('GET', '/game_bundles');

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(19, $content);

        $this->assertEquals([
            'name' => 'Red, Green, Blue, Yellow',
            'french_name' => 'Rouge, Vert, Bleu, Jaune',
            'slug' => 'redgreenblueyellow',
            'generation_slug' => '1',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Ruby, Sapphire, Emerald',
            'french_name' => 'Rubis, Saphir, Émeraude',
            'slug' => 'rubysapphireemerald',
            'generation_slug' => '3',
        ], $content[2]);

        $this->assertEquals([
            'name' => 'Black, White',
            'french_name' => 'Noir, Blanc',
            'slug' => 'blackwhite',
            'generation_slug' => '5',
        ], $content[6]);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest('GET', '/game_bundles', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]);

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(19, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest('GET', '/game_bundles', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
