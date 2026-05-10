<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexCanHoldElectionController;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(DexCanHoldElectionController::class)]
final class DexCanHoldElectionControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    public function testGet(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/dex/can_hold_election',
            [
                'include_unreleased_dex' => true,
                'include_premium_dex' => true,
            ],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                [
                    'slug' => 'homepogo',
                    'original_slug' => 'homepogo',
                    'name' => 'Home PoGo',
                    'french_name' => 'Home PoGo',
                    'is_shiny' => false,
                    'is_display_form' => false,
                    'description' => '',
                    'french_description' => '',
                    'is_released' => false,
                    'is_premium' => false,
                    'dex_total_count' => 1,
                ],
                [
                    'slug' => 'home',
                    'original_slug' => 'home',
                    'name' => 'Home',
                    'french_name' => 'Home',
                    'is_shiny' => false,
                    'is_display_form' => true,
                    'description' => '',
                    'french_description' => '',
                    'is_released' => true,
                    'is_premium' => false,
                    'dex_total_count' => 22,
                ],
                [
                    'slug' => 'redgreenblueyellow',
                    'original_slug' => 'redgreenblueyellow',
                    'name' => 'Red / Green / Blue / Yellow',
                    'french_name' => 'Rouge / Vert / Bleu / Jaune',
                    'is_shiny' => false,
                    'is_display_form' => true,
                    'description' => 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
                    'french_description' => 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
                    'is_released' => true,
                    'is_premium' => true,
                    'dex_total_count' => 7,
                ],
                [
                    'slug' => 'spoon',
                    'original_slug' => 'spoon',
                    'name' => 'Spoon',
                    'french_name' => 'Cuillière',
                    'is_shiny' => false,
                    'is_display_form' => true,
                    'description' => '',
                    'french_description' => '',
                    'is_released' => false,
                    'is_premium' => true,
                    'dex_total_count' => 1,
                ],
            ],
            $data
        );
    }

    public function testGetEmpty(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/dex/can_hold_election',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                [
                    'slug' => 'home',
                    'original_slug' => 'home',
                    'name' => 'Home',
                    'french_name' => 'Home',
                    'is_shiny' => false,
                    'is_display_form' => true,
                    'description' => '',
                    'french_description' => '',
                    'is_released' => true,
                    'is_premium' => false,
                    'dex_total_count' => 22,
                ],
            ],
            $data
        );
    }
}
