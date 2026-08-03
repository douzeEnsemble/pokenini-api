<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\TrainerDexLinkResponse;
use App\Factory\TrainerDexLinkResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkResponseFactory::class)]
final class TrainerDexLinkResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'id' => 'link-1',
            'pair_id' => null,
            'direction' => 'to',
            'target_trainer_dex_id' => 'dex-1',
            'target_dex_slug' => 'shiny',
            'target_name' => 'Shiny Living',
            'target_french_name' => 'Vivarium Chromatique',
        ];

        $response = TrainerDexLinkResponseFactory::fromSqlRow($row);

        self::assertSame('link-1', $response->id);
        self::assertSame('to', $response->direction);
        self::assertSame('shiny', $response->targetDexSlug);
        self::assertSame('Shiny Living', $response->targetName);
        self::assertSame('Vivarium Chromatique', $response->targetFrenchName);
    }

    #[Test]
    public function fromSqlRowCastsValuesToCorrectTypes(): void
    {
        $row = [
            'id' => 123,
            'pair_id' => null,
            'direction' => 456,
            'target_trainer_dex_id' => 'dex-1',
            'target_dex_slug' => 789,
            'target_name' => 101,
            'target_french_name' => 202,
        ];

        $response = TrainerDexLinkResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->id);
        self::assertSame('456', $response->direction);
        self::assertSame('789', $response->targetDexSlug);
        self::assertSame('101', $response->targetName);
        self::assertSame('202', $response->targetFrenchName);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'id' => 'link-1',
                'pair_id' => null,
                'direction' => 'to',
                'target_trainer_dex_id' => 'dex-1',
                'target_dex_slug' => 'shiny',
                'target_name' => 'Shiny Living',
                'target_french_name' => 'Vivarium Chromatique',
            ],
            [
                'id' => 'link-2',
                'pair_id' => 'pair-1',
                'direction' => 'both',
                'target_trainer_dex_id' => 'dex-2',
                'target_dex_slug' => 'home',
                'target_name' => 'Home',
                'target_french_name' => 'Home',
            ],
        ];

        $responses = TrainerDexLinkResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(TrainerDexLinkResponse::class, $responses);
        self::assertSame('to', $responses[0]->direction);
        self::assertSame('both', $responses[1]->direction);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        self::assertSame([], TrainerDexLinkResponseFactory::fromSqlRows([]));
    }
}
