<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ReportsController;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ReportsController::class)]
class ReportsControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    public function testReports(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/reports',
            [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
        );

        $this->assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        /** @var int[][]|string[][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(self::getReportsData(), $data);
    }

    /**
     * @return array<string, array<int, array<string, int|string>>>
     */
    private static function getReportsData(): array
    {
        return [
            'catch_state_counts_defined_by_trainer' => [
                [
                    'nb' => 28,
                    'trainer' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                ],
                [
                    'nb' => 3,
                    'trainer' => 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
                ],
            ],
            'dex_usage' => [
                [
                    'nb' => 2,
                    'name' => 'Red / Green / Blue / Yellow',
                    'french_name' => 'Rouge / Vert / Bleu / Jaune',
                ],
                [
                    'nb' => 2,
                    'name' => 'Gold / Silver / Crystal',
                    'french_name' => 'Or / Argent / Cristal',
                ],
                [
                    'nb' => 2,
                    'name' => 'Home',
                    'french_name' => 'Home',
                ],
                [
                    'nb' => 1,
                    'name' => 'Ruby / Sapphire / Emerald',
                    'french_name' => 'Rubis / Saphir / Émeraude',
                ],
                [
                    'nb' => 1,
                    'name' => "Home\nShiny",
                    'french_name' => "Home\nChromatique",
                ],
                [
                    'nb' => 1,
                    'name' => 'Home PoGo',
                    'french_name' => 'Home PoGo',
                ],
            ],
            'catch_state_usage' => [
                [
                    'nb' => 11,
                    'name' => 'No',
                    'french_name' => 'Non',
                    'color' => '#e57373',
                ],
                [
                    'nb' => 4,
                    'name' => 'Maybe',
                    'french_name' => 'Peut être',
                    'color' => 'blue',
                ],
                [
                    'nb' => 5,
                    'name' => 'Maybe not',
                    'french_name' => 'Peut être pas',
                    'color' => 'yellow',
                ],
                [
                    'nb' => 11,
                    'name' => 'Yes',
                    'french_name' => 'Oui',
                    'color' => '#66bb6a',
                ],
            ],
        ];
    }
}
