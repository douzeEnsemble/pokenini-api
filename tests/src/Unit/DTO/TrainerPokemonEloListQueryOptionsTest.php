<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\TrainerPokemonEloListQueryOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloListQueryOptions::class)]
final class TrainerPokemonEloListQueryOptionsTest extends TestCase
{
    public function testOk(): void
    {
        $attributes = new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => 12,
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('douze', $attributes->electionSlug);
        $this->assertSame(12, $attributes->count);
    }

    public function testWithAlbumFilters(): void
    {
        $attributes = new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => 12,
            'primary_types' => ['fire', 'water'],
            'secondary_types' => ['water', 'fire'],
            'any_types' => ['normal'],
            'category_forms' => ['starter', 'finisher'],
            'regional_forms' => ['provence', 'sud', 'mer'],
            'special_forms' => ['banana', 'orange'],
            'variant_forms' => ['gender'],
            'catch_states' => ['maybe'],
            'original_game_bundles' => ['redgreenblueyellow'],
            'game_bundle_availabilities' => ['sunmoon'],
            'game_bundle_shiny_availabilities' => ['ultrasunutramoon'],
            'families' => ['pichu', 'eevee'],
            'collection_availabilities' => ['swshdens'],
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('douze', $attributes->electionSlug);
        $this->assertSame(12, $attributes->count);
    }

    public function testMissingTrainerExternalId(): void
    {
        $this->expectException(MissingOptionsException::class);
        new TrainerPokemonEloListQueryOptions([
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => 12,
        ]);
    }

    public function testMissingDexSlug(): void
    {
        $this->expectException(MissingOptionsException::class);
        new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => '67865468',
            'election_slug' => 'douze',
            'count' => 12,
        ]);
    }

    public function testMissingElectionSlug(): void
    {
        $attributes = new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'count' => 12,
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('', $attributes->electionSlug);
        $this->assertSame(12, $attributes->count);
    }

    public function testMissingCount(): void
    {
        $this->expectException(MissingOptionsException::class);
        new TrainerPokemonEloListQueryOptions([
            'dex_slug' => 'demo',
            'trainer_external_id' => '67865468',
            'election_slug' => 'douze',
        ]);
    }

    public function testWrongValueForTrainerExternalId(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => 67865468,
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => 12,
        ]);
    }

    public function testWrongValueForDexSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 78,
            'election_slug' => 'douze',
            'count' => 12,
        ]);
    }

    public function testWrongValueForElectionSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 4568,
            'count' => 12,
        ]);
    }

    public function testWrongValueForCount(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /** @psalm-suppress InvalidArgument */
        new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => '',
            'count' => false,
        ]);
    }

    public function testCountNormalizer(): void
    {
        $attributes = new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => '12',
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('douze', $attributes->electionSlug);
        $this->assertSame(12, $attributes->count);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => 12,
            'other' => 'idk',
        ]);
    }
}
