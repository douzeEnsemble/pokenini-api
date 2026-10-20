<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ColorGameAnswerResponse;
use App\DTO\Response\ColorGameChoiceResponse;
use App\DTO\Response\ColorGameDexResponse;
use App\DTO\Response\ColorGameDexSummaryResponse;
use App\DTO\Response\ColorGamePokemonResponse;
use App\DTO\Response\ColorGameResponse;
use App\DTO\Response\ColorGameRoundResponse;
use App\DTO\Response\ColorGameStateResponse;
use App\Factory\ColorGameResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ColorGameResponseFactory::class)]
#[CoversClass(ColorGameAnswerResponse::class)]
#[CoversClass(ColorGameChoiceResponse::class)]
#[CoversClass(ColorGameDexResponse::class)]
#[CoversClass(ColorGameDexSummaryResponse::class)]
#[CoversClass(ColorGamePokemonResponse::class)]
#[CoversClass(ColorGameResponse::class)]
#[CoversClass(ColorGameRoundResponse::class)]
#[CoversClass(ColorGameStateResponse::class)]
final class ColorGameResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromGameRow(): void
    {
        $this->assertEquals(
            new ColorGameResponse(
                id: '01a15aac-cfd2-7085-bbc7-88594aa005dd',
                dex: new ColorGameDexSummaryResponse(slug: 'demo', name: 'Demo', frenchName: 'Démo'),
                isShiny: true,
                status: 'completed',
                roundCount: 12,
                answeredCount: 12,
                correctCount: 7,
                startedAt: '2026-10-10T10:00:00+00:00',
                lastPlayedAt: '2026-10-10T10:05:01+00:00',
                endedAt: '2026-10-10T10:05:02+00:00',
            ),
            ColorGameResponseFactory::fromGameRow($this->getGameRow()),
        );
    }

    #[Test]
    public function fromGameRowWithNullDates(): void
    {
        $response = ColorGameResponseFactory::fromGameRow(
            array_merge(
                $this->getGameRow(),
                [
                    'is_shiny' => 0,
                    'status' => 'in_progress',
                    'last_played_at' => null,
                    'ended_at' => null,
                ],
            ),
        );

        $this->assertFalse($response->isShiny);
        $this->assertSame('in_progress', $response->status);
        $this->assertSame('2026-10-10T10:00:00+00:00', $response->startedAt);
        $this->assertNull($response->lastPlayedAt);
        $this->assertNull($response->endedAt);
    }

    #[Test]
    public function fromGameRows(): void
    {
        $responses = ColorGameResponseFactory::fromGameRows([
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
        $responses = ColorGameResponseFactory::fromDexRows(
            [
                2 => $this->getDexRow('demo'),
                5 => array_merge($this->getDexRow('home'), ['is_shiny' => 1, 'is_released' => 0, 'is_premium' => 1]),
            ],
            [
                'home' => $this->getGameRow(),
                'other' => array_merge($this->getGameRow(), ['id' => 'other']),
            ],
        );

        $this->assertEquals(
            [
                new ColorGameDexResponse(
                    slug: 'demo',
                    name: 'Name demo',
                    frenchName: 'Nom demo',
                    isShiny: false,
                    isReleased: true,
                    isPremium: false,
                    description: 'Description demo',
                    frenchDescription: 'Description française demo',
                    playableCount: 151,
                    currentGame: null,
                ),
                new ColorGameDexResponse(
                    slug: 'home',
                    name: 'Name home',
                    frenchName: 'Nom home',
                    isShiny: true,
                    isReleased: false,
                    isPremium: true,
                    description: 'Description home',
                    frenchDescription: 'Description française home',
                    playableCount: 151,
                    currentGame: ColorGameResponseFactory::fromGameRow($this->getGameRow()),
                ),
            ],
            $responses,
        );
        $this->assertSame([0, 1], array_keys($responses));
    }

    #[Test]
    public function fromState(): void
    {
        $response = ColorGameResponseFactory::fromState([
            'game' => $this->getGameRow(),
            'round' => [
                'position' => '4',
                'choices_order' => '3,1,4,2',
                'chosen_choice' => null,
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
            ],
            'color_sets' => [
                ['position' => '1', 'codes' => '#F0B030;#FFFFFF', 'names' => 'orange;white', 'french_names' => 'orange;blanc'],
                ['position' => '2', 'codes' => '#3080F0', 'names' => 'blue', 'french_names' => 'bleu'],
                ['position' => '3', 'codes' => '#30F080;#000000;#FF0000', 'names' => 'green;black;red', 'french_names' => 'vert;noir;rouge'],
            ],
        ]);

        $this->assertEquals(
            new ColorGameStateResponse(
                game: ColorGameResponseFactory::fromGameRow($this->getGameRow()),
                round: new ColorGameRoundResponse(
                    position: 4,
                    pokemon: new ColorGamePokemonResponse(
                        slug: 'raichu-alola',
                        name: 'Raichu Alola',
                        frenchName: 'Raichu d\'Alola',
                        simplifiedName: 'Raichu',
                        simplifiedFrenchName: 'Raichu',
                        formsLabel: 'Alola',
                        formsFrenchLabel: 'Alola',
                        nationalDexNumber: 26,
                    ),
                    choices: [
                        new ColorGameChoiceResponse(
                            index: 0,
                            codes: ['#30F080', '#000000', '#FF0000'],
                            names: ['green', 'black', 'red'],
                            frenchNames: ['vert', 'noir', 'rouge'],
                        ),
                        new ColorGameChoiceResponse(
                            index: 1,
                            codes: ['#F0B030', '#FFFFFF'],
                            names: ['orange', 'white'],
                            frenchNames: ['orange', 'blanc'],
                        ),
                        new ColorGameChoiceResponse(
                            index: 2,
                            codes: [],
                            names: [],
                            frenchNames: [],
                        ),
                        new ColorGameChoiceResponse(
                            index: 3,
                            codes: ['#3080F0'],
                            names: ['blue'],
                            frenchNames: ['bleu'],
                        ),
                    ],
                ),
            ),
            $response,
        );

        $this->assertNotNull($response->round);
        $this->assertSame(4, $response->round->position);
        $this->assertSame(26, $response->round->pokemon->nationalDexNumber);
        $this->assertSame([0, 1, 2, 3], array_keys($response->round->choices));
        $this->assertSame(3, $response->round->choices[3]->index);
    }

    #[Test]
    public function fromStateWithoutRound(): void
    {
        $response = ColorGameResponseFactory::fromState([
            'game' => $this->getGameRow(),
            'round' => null,
            'color_sets' => [],
        ]);

        $this->assertEquals(ColorGameResponseFactory::fromGameRow($this->getGameRow()), $response->game);
        $this->assertNull($response->round);
    }

    #[Test]
    public function fromAnswer(): void
    {
        $this->assertEquals(
            new ColorGameAnswerResponse(
                position: 3,
                isCorrect: false,
                chosenChoice: 1,
                correctChoice: 4,
                game: ColorGameResponseFactory::fromGameRow($this->getGameRow()),
            ),
            ColorGameResponseFactory::fromAnswer([
                'position' => 3,
                'is_correct' => false,
                'chosen_choice' => 1,
                'correct_choice' => 4,
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
            'is_shiny' => 1,
            'status' => 'completed',
            'round_count' => '12',
            'answered_count' => '12',
            'correct_count' => '7',
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
            'is_shiny' => 0,
            'is_released' => 1,
            'is_premium' => 0,
            'description' => "Description {$slug}",
            'french_description' => "Description française {$slug}",
            'playable_count' => '151',
        ];
    }
}
