<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\ElectionReport\Report;
use App\Factory\ElectionReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionReportResponseFactory::class)]
final class ElectionReportResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromReportTransformsTopAndMetrics(): void
    {
        $topRow = [
            'elo' => 1200.5,
            'significance' => true,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'pichu',
            'original_game_bundle_slug' => 'red-blue',
            'pokemon_order_number' => '9999-0025-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'category_form_french_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'regional_form_french_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'special_form_french_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'variant_form_french_name' => null,
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ];

        $report = new Report(
            [$topRow],
            [
                'view_count_sum' => 9,
                'win_count_sum' => 6,
                'view_count_max' => 3,
                'win_count_max' => 3,
                'under_max_view_count' => 1,
                'max_view_count' => 1,
                'dex_total_count' => 7,
            ],
        );

        $response = ElectionReportResponseFactory::fromReport($report);

        $this->assertCount(1, $response->top);
        $this->assertSame('pikachu', $response->top[0]->pokemon->slug);
        $this->assertSame(1200.5, $response->top[0]->score->elo);
        $this->assertSame(9, $response->metrics->viewCount->sum);
        $this->assertSame(3, $response->metrics->viewCount->max);
        $this->assertSame(6, $response->metrics->winCount->sum);
        $this->assertSame(3, $response->metrics->winCount->max);
        $this->assertSame(1, $response->metrics->completion->atMaxCount);
        $this->assertSame(1, $response->metrics->completion->underMaxCount);
        $this->assertSame(7, $response->metrics->dexTotalCount);
    }

    #[Test]
    public function fromReportHandlesEmptyTop(): void
    {
        $report = new Report(
            [],
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
                'under_max_view_count' => 0,
                'max_view_count' => 0,
                'dex_total_count' => 0,
            ],
        );

        $response = ElectionReportResponseFactory::fromReport($report);

        $this->assertSame([], $response->top);
    }
}
