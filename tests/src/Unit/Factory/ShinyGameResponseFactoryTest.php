<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\DexCategoryResponse;
use App\DTO\Response\ShinyGameAnswerResponse;
use App\DTO\Response\ShinyGameDexResponse;
use App\DTO\Response\ShinyGameDexSummaryResponse;
use App\DTO\Response\ShinyGamePokemonResponse;
use App\DTO\Response\ShinyGameResponse;
use App\DTO\Response\ShinyGameRoundResponse;
use App\DTO\Response\ShinyGameStateResponse;
use App\Factory\ShinyGameResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ShinyGameResponseFactory::class)]
#[CoversClass(ShinyGameAnswerResponse::class)]
#[CoversClass(ShinyGameDexResponse::class)]
#[CoversClass(ShinyGameDexSummaryResponse::class)]
#[CoversClass(ShinyGamePokemonResponse::class)]
#[CoversClass(ShinyGameResponse::class)]
#[CoversClass(ShinyGameRoundResponse::class)]
#[CoversClass(ShinyGameStateResponse::class)]
final class ShinyGameResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromGameRow(): void
    {
        $this->assertEquals(
            new ShinyGameResponse(
                id: '01a15aac-cfd2-7085-bbc7-88594aa005dd',
                dex: new ShinyGameDexSummaryResponse(slug: 'demo', name: 'Demo', frenchName: 'Démo'),
                status: 'completed',
                roundCount: 12,
                answeredCount: 12,
                correctCount: 7,
                results: [true, false, true],
                startedAt: '2026-10-10T10:00:00+00:00',
                lastPlayedAt: '2026-10-10T10:05:01+00:00',
                endedAt: '2026-10-10T10:05:02+00:00',
            ),
            ShinyGameResponseFactory::fromGameRow($this->getGameRow()),
        );
    }

    #[Test]
    public function fromGameRowWithNullDates(): void
    {
        $response = ShinyGameResponseFactory::fromGameRow(
            array_merge(
                $this->getGameRow(),
                [
                    'status' => 'in_progress',
                    'last_played_at' => null,
                    'ended_at' => null,
                    'results' => null,
                ],
            ),
        );

        $this->assertSame('in_progress', $response->status);
        $this->assertSame('2026-10-10T10:00:00+00:00', $response->startedAt);
        $this->assertNull($response->lastPlayedAt);
        $this->assertNull($response->endedAt);
        $this->assertSame([], $response->results);
    }

    #[Test]
    public function fromGameRows(): void
    {
        $responses = ShinyGameResponseFactory::fromGameRows([
            3 => $this->getGameRow(),
            7 => array_merge($this->getGameRow(), ['id' => 'other']),
        ]);

        $this->assertSame([0, 1], array_keys($responses));
        $this->assertSame('01a15aac-cfd2-7085-bbc7-88594aa005dd', $responses[0]->id);
        $this->assertSame('other', $responses[1]->id);
    }

    #[Test]
    public function fromDexRows(): void
    {
        $responses = ShinyGameResponseFactory::fromDexRows(
            [
                2 => $this->getDexRow('demo'),
                5 => array_merge($this->getDexRow('home'), ['is_released' => 0, 'is_premium' => 1, 'category_slug' => 'home', 'category_name' => 'Home', 'category_french_name' => 'Home', 'category_order_number' => '2']),
            ],
            [
                'home' => $this->getGameRow(),
                'other' => array_merge($this->getGameRow(), ['id' => 'other']),
            ],
        );

        $this->assertEquals(
            [
                new ShinyGameDexResponse(
                    slug: 'demo',
                    name: 'Name demo',
                    frenchName: 'Nom demo',
                    isReleased: true,
                    isPremium: false,
                    description: 'Description demo',
                    frenchDescription: 'Description française demo',
                    playableCount: 151,
                    currentGame: null,
                    category: null,
                ),
                new ShinyGameDexResponse(
                    slug: 'home',
                    name: 'Name home',
                    frenchName: 'Nom home',
                    isReleased: false,
                    isPremium: true,
                    description: 'Description home',
                    frenchDescription: 'Description française home',
                    playableCount: 151,
                    currentGame: ShinyGameResponseFactory::fromGameRow($this->getGameRow()),
                    category: new DexCategoryResponse(
                        slug: 'home',
                        name: 'Home',
                        frenchName: 'Home',
                        orderNumber: 2,
                    ),
                ),
            ],
            $responses,
        );
        $this->assertSame([0, 1], array_keys($responses));
    }

    #[Test]
    public function fromState(): void
    {
        $response = ShinyGameResponseFactory::fromState([
            'game' => $this->getGameRow(),
            'round' => [
                'position' => '4',
                'is_shiny' => 1,
                'chosen_shiny' => null,
                'is_correct' => null,
                'pokemon_id' => 'p1',
                'pokemon_slug' => 'raichu-alola',
                'pokemon_name' => 'Raichu Alola',
                'pokemon_french_name' => 'Raichu d\'Alola',
                'pokemon_simplified_name' => 'Raichu',
                'pokemon_simplified_french_name' => 'Raichu',
                'pokemon_forms_label' => 'Alola',
                'pokemon_forms_french_label' => 'Alola',
                'pokemon_national_dex_number' => '26',
                'pokemon_icon' => 'raichu-alola',
            ],
        ]);

        $this->assertEquals(
            new ShinyGameStateResponse(
                game: ShinyGameResponseFactory::fromGameRow($this->getGameRow()),
                round: new ShinyGameRoundResponse(
                    position: 4,
                    pokemon: new ShinyGamePokemonResponse(
                        slug: 'raichu-alola',
                        name: 'Raichu Alola',
                        frenchName: 'Raichu d\'Alola',
                        simplifiedName: 'Raichu',
                        simplifiedFrenchName: 'Raichu',
                        formsLabel: 'Alola',
                        formsFrenchLabel: 'Alola',
                        nationalDexNumber: 26,
                        icon: 'raichu-alola',
                    ),
                    isShiny: true,
                ),
            ),
            $response,
        );

        $this->assertNotNull($response->round);
        $this->assertSame(4, $response->round->position);
        $this->assertSame(26, $response->round->pokemon->nationalDexNumber);
        $this->assertTrue($response->round->isShiny);
    }

    #[Test]
    public function fromStateWithARegularRound(): void
    {
        $response = ShinyGameResponseFactory::fromState([
            'game' => $this->getGameRow(),
            'round' => [
                'position' => 1,
                'is_shiny' => 0,
                'pokemon_slug' => 'pikachu',
                'pokemon_name' => 'Pikachu',
                'pokemon_french_name' => 'Pikachu',
                'pokemon_simplified_name' => 'Pikachu',
                'pokemon_simplified_french_name' => 'Pikachu',
                'pokemon_forms_label' => '',
                'pokemon_forms_french_label' => '',
                'pokemon_national_dex_number' => 25,
                'pokemon_icon' => 'pikachu',
            ],
        ]);

        $this->assertNotNull($response->round);
        $this->assertFalse($response->round->isShiny);
    }

    #[Test]
    public function fromStateWithoutRound(): void
    {
        $response = ShinyGameResponseFactory::fromState([
            'game' => $this->getGameRow(),
            'round' => null,
        ]);

        $this->assertEquals(ShinyGameResponseFactory::fromGameRow($this->getGameRow()), $response->game);
        $this->assertNull($response->round);
    }

    #[Test]
    public function fromAnswer(): void
    {
        $this->assertEquals(
            new ShinyGameAnswerResponse(
                position: 3,
                isCorrect: false,
                chosenShiny: false,
                isShiny: true,
                game: ShinyGameResponseFactory::fromGameRow($this->getGameRow()),
            ),
            ShinyGameResponseFactory::fromAnswer([
                'position' => 3,
                'is_correct' => false,
                'chosen_shiny' => false,
                'is_shiny' => true,
                'game' => $this->getGameRow(),
            ]),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getGameRow(): array
    {
        return [
            'id' => '01a15aac-cfd2-7085-bbc7-88594aa005dd',
            'dex_slug' => 'demo',
            'dex_name' => 'Demo',
            'dex_french_name' => 'Démo',
            'status' => 'completed',
            'round_count' => '12',
            'answered_count' => '12',
            'correct_count' => '7',
            'results' => '101',
            'started_at' => '2026-10-10 10:00:00',
            'last_played_at' => '2026-10-10 10:05:01',
            'ended_at' => '2026-10-10 10:05:02',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getDexRow(string $slug): array
    {
        return [
            'slug' => $slug,
            'name' => "Name {$slug}",
            'french_name' => "Nom {$slug}",
            'is_released' => 1,
            'is_premium' => 0,
            'description' => "Description {$slug}",
            'french_description' => "Description française {$slug}",
            'playable_count' => '151',
            'category_slug' => null,
            'category_name' => null,
            'category_french_name' => null,
            'category_order_number' => null,
        ];
    }
}
