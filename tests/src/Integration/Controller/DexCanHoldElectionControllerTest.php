<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexCanHoldElectionController;
use App\Factory\DexResponseFactory;
use App\Service\DexCanHoldElectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(DexCanHoldElectionController::class)]
#[CoversClass(DexResponseFactory::class)]
#[CoversClass(DexCanHoldElectionService::class)]
final class DexCanHoldElectionControllerTest extends AbstractTestControllerApi
{
    #[Test]
    public function listReturnsDexByDefault(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election');

        $this->assertResponseIsOK();

        /** @var array<int, array<string, mixed>> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(1, $content);

        $this->assertEquals([
            'slug' => 'home',
            'original_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'flags' => [
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            'description' => '',
            'french_description' => '',
            'dex_total_count' => 22,
        ], $content[0]);
    }

    #[Test]
    public function listReturnsDexWithAllOptions(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election', [
            'include_unreleased_dex' => 1,
            'include_premium_dex' => 1,
        ]);

        $this->assertResponseIsOK();

        /** @var array<int, array<string, mixed>> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(4, $content);

        $this->assertEquals([
            'slug' => 'homepogo',
            'original_slug' => 'homepogo',
            'name' => 'Home PoGo',
            'french_name' => 'Home PoGo',
            'flags' => [
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => false,
                'is_released' => false,
                'is_premium' => false,
                'is_custom' => false,
            ],
            'description' => '',
            'french_description' => '',
            'dex_total_count' => 1,
        ], $content[0]);

        $this->assertEquals([
            'slug' => 'home',
            'original_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'flags' => [
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            'description' => '',
            'french_description' => '',
            'dex_total_count' => 22,
        ], $content[1]);

        $this->assertEquals([
            'slug' => 'redgreenblueyellow',
            'original_slug' => 'redgreenblueyellow',
            'name' => 'Red / Green / Blue / Yellow',
            'french_name' => 'Rouge / Vert / Bleu / Jaune',
            'flags' => [
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => false,
            ],
            'description' => 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            'french_description' => 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            'dex_total_count' => 7,
        ], $content[2]);

        $this->assertEquals([
            'slug' => 'spoon',
            'original_slug' => 'spoon',
            'name' => 'Spoon',
            'french_name' => 'Cuillière',
            'flags' => [
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => false,
                'is_premium' => true,
                'is_custom' => false,
            ],
            'description' => '',
            'french_description' => '',
            'dex_total_count' => 1,
        ], $content[3]);
    }

    #[Test]
    public function listResponseMatchesFixture(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/dex_can_hold_election_response.json',
            $content,
        );
    }

    #[Test]
    public function listReturnsOkWithAuth(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]);

        $this->assertResponseIsOK();
    }

    #[Test]
    public function listReturnsBadAuthWith401(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
