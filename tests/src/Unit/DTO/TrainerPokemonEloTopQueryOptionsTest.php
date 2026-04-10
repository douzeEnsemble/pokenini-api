<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\TrainerPokemonEloTopQueryOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloTopQueryOptions::class)]
class TrainerPokemonEloTopQueryOptionsTest extends TestCase
{
    public function testOk(): void
    {
        $attributes = new TrainerPokemonEloTopQueryOptions([
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
        $attributes = new TrainerPokemonEloTopQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'count' => 10,
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('', $attributes->electionSlug);
        $this->assertSame(10, $attributes->count);
    }

    public function testWrongValueForTrainerExternalId(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerPokemonEloTopQueryOptions([
            'trainer_external_id' => 67865468,
            'dex_slug' => 'demo',
            'count' => 10,
        ]);
    }

    public function testWrongValueForElectionSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /** @psalm-suppress InvalidArgument */
        new TrainerPokemonEloTopQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => false,
            'count' => 10,
        ]);
    }

    public function testWrongValueForDexSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new TrainerPokemonEloTopQueryOptions([
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
        new TrainerPokemonEloTopQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => '',
            'count' => [10],
        ]);
    }

    public function testUnnormalizeValueForCount(): void
    {
        $attributes = new TrainerPokemonEloTopQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => '',
            'count' => '10',
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('', $attributes->electionSlug);
        $this->assertSame(10, $attributes->count);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new TrainerPokemonEloTopQueryOptions([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'count' => 10,
            'other' => 'idk',
        ]);
    }
}
