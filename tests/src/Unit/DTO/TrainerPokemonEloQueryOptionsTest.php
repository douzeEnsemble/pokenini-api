<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\TrainerPokemonEloQueryOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloQueryOptions::class)]
final class TrainerPokemonEloQueryOptionsTest extends TestCase
{
    public function testOk(): void
    {
        $attributes = new TrainerPokemonEloQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => 10,
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('douze', $attributes->electionSlug);
        $this->assertSame(10, $attributes->count);
    }

    public function testMissingElectionSlug(): void
    {
        $attributes = new TrainerPokemonEloQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'count' => 10,
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('', $attributes->electionSlug);
        $this->assertSame(10, $attributes->count);
    }

    public function testMissingCount(): void
    {
        $attributes = new TrainerPokemonEloQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('douze', $attributes->electionSlug);
        $this->assertSame(5, $attributes->count);
    }

    public function testMissingDexSlug(): void
    {
        $this->expectException(MissingOptionsException::class);
        new TrainerPokemonEloQueryOptions([
            'trainer_external_id' => '67865468',
            'count' => 10,
        ]);
    }

    public function testWrongValueForTrainerExternalId(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerPokemonEloQueryOptions([
            'trainer_external_id' => 67865468,
            'dex_slug' => 'demo',
            'count' => 10,
        ]);
    }

    public function testWrongValueForElectionSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /**
         * @psalm-suppress InvalidArgument
         *
         * @phpstan-ignore argument.type
         */
        new TrainerPokemonEloQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => false,
            'count' => 10,
        ]);
    }

    public function testWrongValueForDexSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerPokemonEloQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 54,
            'election_slug' => '',
            'count' => 10,
        ]);
    }

    public function testWrongValueForCount(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /**
         * @psalm-suppress InvalidArgument
         *
         * @phpstan-ignore argument.type
         */
        new TrainerPokemonEloQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => 5.4,
        ]);
    }

    public function testNormalizedValueForCount(): void
    {
        $attributes = new TrainerPokemonEloQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => '10',
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('douze', $attributes->electionSlug);
        $this->assertSame(10, $attributes->count);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new TrainerPokemonEloQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => 10,
            'other' => 'idk',
        ]);
    }
}
