<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ElectionVote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(ElectionVote::class)]
final class ElectionVoteTest extends TestCase
{
    public function testOk(): void
    {
        $attributes = new ElectionVote([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('douze', $attributes->electionSlug);
        $this->assertSame(['pikachu'], $attributes->winnersSlugs);
        $this->assertSame(['pichu', 'raichu'], $attributes->losersSlugs);
    }

    public function testMissingElectionSlug(): void
    {
        $attributes = new ElectionVote([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('', $attributes->electionSlug);
        $this->assertSame(['pikachu'], $attributes->winnersSlugs);
        $this->assertSame(['pichu', 'raichu'], $attributes->losersSlugs);
    }

    public function testWrongValueForTrainerExternalId(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /**
         * @psalm-suppress InvalidArgument
         *
         * @phpstan-ignore argument.type
         */
        new ElectionVote([
            'trainer_external_id' => 67865468,
            'dex_slug' => 'demo',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
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
        new ElectionVote([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => false,
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    public function testWrongValueForDexSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /**
         * @psalm-suppress InvalidArgument
         *
         * @phpstan-ignore argument.type
         */
        new ElectionVote([
            'trainer_external_id' => '67865468',
            'dex_slug' => false,
            'election_slug' => 'fav',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    public function testWrongValueForWinnersSlugs(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new ElectionVote([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'winners_slugs' => 'pikachu',
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    public function testWrongValueForLosersSlugs(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new ElectionVote([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => 'pichu',
        ]);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new ElectionVote([
            'trainer_external_id' => '67865468',
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
            'other' => 'idk',
        ]);
    }
}
