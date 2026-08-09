<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ElectionVote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(ElectionVote::class)]
final class ElectionVoteTest extends TestCase
{
    #[Test]
    public function everythingOK(): void
    {
        $attributes = new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
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

    #[Test]
    public function missingElectionSlug(): void
    {
        $attributes = new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
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

    #[Test]
    public function wrongTypeForTrainer(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new ElectionVote([
            'trainer' => 'not-an-array',
            'dex_slug' => 'demo',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    #[Test]
    public function wrongTypeForTrainerExternalId(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ElectionVote([
            'trainer' => ['external_id' => 12345],
            'dex_slug' => 'demo',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    #[Test]
    public function wrongValueForElectionSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => 'demo',
            'election_slug' => false,
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    #[Test]
    public function wrongValueForDexSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => false,
            'election_slug' => 'fav',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    #[Test]
    public function wrongValueForWinnersSlugs(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => 'demo',
            'winners_slugs' => 'pikachu',
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    #[Test]
    public function wrongValueForLosersSlugs(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => 'demo',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => 'pichu',
        ]);
    }

    #[Test]
    public function anotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
            'other' => 'idk',
        ]);
    }
}
