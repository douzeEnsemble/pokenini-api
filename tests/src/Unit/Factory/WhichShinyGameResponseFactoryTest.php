<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\WhichShinyGameAnswerResponse;
use App\DTO\Response\WhichShinyGameDexResponse;
use App\DTO\Response\WhichShinyGameDexSummaryResponse;
use App\DTO\Response\WhichShinyGamePokemonResponse;
use App\DTO\Response\WhichShinyGameResponse;
use App\DTO\Response\WhichShinyGameRoundResponse;
use App\DTO\Response\WhichShinyGameStateResponse;
use App\Factory\WhichShinyGameResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(WhichShinyGameResponseFactory::class)]
#[CoversClass(WhichShinyGameAnswerResponse::class)]
#[CoversClass(WhichShinyGameDexResponse::class)]
#[CoversClass(WhichShinyGameDexSummaryResponse::class)]
#[CoversClass(WhichShinyGamePokemonResponse::class)]
#[CoversClass(WhichShinyGameResponse::class)]
#[CoversClass(WhichShinyGameRoundResponse::class)]
#[CoversClass(WhichShinyGameStateResponse::class)]
final class WhichShinyGameResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromGameRow(): void
    {
        $this->assertEquals(
            new WhichShinyGameResponse(
                id: '01a15aac-cfd2-7085-bbc7-88594aa005dd',
                dex: new WhichShinyGameDexSummaryResponse(slug: 'demo', name: 'Demo', frenchName: 'Démo'),
                status: 'completed',
                roundCount: 12,
                answeredCount: 12,
                correctCount: 7,
                startedAt: '2026-10-10T10:00:00+00:00',
                lastPlayedAt: '2026-10-10T10:05:01+00:00',
                endedAt: '2026-10-10T10:05:02+00:00',
            ),
            WhichShinyGameResponseFactory::fromGameRow($this->getGameRow()),
        );
    }

    #[Test]
    public function fromGameRowWithNullDates(): void
    {
        $response = WhichShinyGameResponseFactory::fromGameRow(
            array_merge(
                $this->getGameRow(),
                [
                    'status' => 'in_progress',
                    'last_played_at' => null,
                    'ended_at' => null,
                ],
            ),
        );

        $this->assertSame('in_progress', $response->status);
        $this->assertSame('2026-10-10T10:00:00+00:00', $response->startedAt);
        $this->assertNull($response->lastPlayedAt);
        $this->assertNull($response->endedAt);
    }

    #[Test]
    public function fromGameRows(): void
    {
        $responses = WhichShinyGameResponseFactory::fromGameRows([
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
        $responses = WhichShinyGameResponseFactory::fromDexRows(
            [
                2 => $this->getDexRow('demo'),
                5 => array_merge($this->getDexRow('home'), ['is_released' => 0, 'is_premium' => 1]),
            ],
            [
                'home' => $this->getGameRow(),
                'other' => array_merge($this->getGameRow(), ['id' => 'other']),
            ],
        );

        $this->assertEquals(
            [
                new WhichShinyGameDexResponse(
                    slug: 'demo',
                    name: 'Name demo',
                    frenchName: 'Nom demo',
                    isReleased: true,
                    isPremium: false,
                    description: 'Description demo',
                    frenchDescription: 'Description française demo',
                    playableCount: 151,
                    currentGame: null,
                ),
                new WhichShinyGameDexResponse(
                    slug: 'home',
                    name: 'Name home',
                    frenchName: 'Nom home',
                    isReleased: false,
                    isPremium: true,
                    description: 'Description home',
                    frenchDescription: 'Description française home',
                    playableCount: 151,
                    currentGame: WhichShinyGameResponseFactory::fromGameRow($this->getGameRow()),
                ),
            ],
            $responses,
        );
        $this->assertSame([0, 1], array_keys($responses));
    }

    #[Test]
    public function fromState(): void
    {
        $response = WhichShinyGameResponseFactory::fromState([
            'game' => $this->getGameRow(),
            'round' => [
                'position' => '4',
                'shiny_slot' => '2',
                'chosen_slot' => null,
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
            new WhichShinyGameStateResponse(
                game: WhichShinyGameResponseFactory::fromGameRow($this->getGameRow()),
                round: new WhichShinyGameRoundResponse(
                    position: 4,
                    pokemon: new WhichShinyGamePokemonResponse(
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
                    shinySlot: 2,
                ),
            ),
            $response,
        );

        $this->assertNotNull($response->round);
        $this->assertSame(4, $response->round->position);
        $this->assertSame(26, $response->round->pokemon->nationalDexNumber);
        $this->assertSame(2, $response->round->shinySlot);
    }

    #[Test]
    public function fromStateWithTheShinyFirst(): void
    {
        $response = WhichShinyGameResponseFactory::fromState([
            'game' => $this->getGameRow(),
            'round' => [
                'position' => 1,
                'shiny_slot' => 1,
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
        $this->assertSame(1, $response->round->shinySlot);
    }

    #[Test]
    public function fromStateWithoutRound(): void
    {
        $response = WhichShinyGameResponseFactory::fromState([
            'game' => $this->getGameRow(),
            'round' => null,
        ]);

        $this->assertEquals(WhichShinyGameResponseFactory::fromGameRow($this->getGameRow()), $response->game);
        $this->assertNull($response->round);
    }

    #[Test]
    public function fromAnswer(): void
    {
        $this->assertEquals(
            new WhichShinyGameAnswerResponse(
                position: 3,
                isCorrect: false,
                chosenSlot: 1,
                shinySlot: 2,
                game: WhichShinyGameResponseFactory::fromGameRow($this->getGameRow()),
            ),
            WhichShinyGameResponseFactory::fromAnswer([
                'position' => 3,
                'is_correct' => false,
                'chosen_slot' => 1,
                'shiny_slot' => 2,
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
        ];
    }
}
