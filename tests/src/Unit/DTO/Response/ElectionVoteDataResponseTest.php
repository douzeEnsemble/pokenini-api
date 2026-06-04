<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionVoteDataResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteDataResponse::class)]
final class ElectionVoteDataResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ElectionVoteDataResponse(
            trainerExternalId: 'abc123',
            dexSlug: 'national',
            electionSlug: 'gen1',
            winnersSlugs: ['pikachu', 'raichu'],
            losersSlugs: ['magikarp'],
        );

        self::assertSame('abc123', $response->trainerExternalId);
        self::assertSame('national', $response->dexSlug);
        self::assertSame('gen1', $response->electionSlug);
        self::assertSame(['pikachu', 'raichu'], $response->winnersSlugs);
        self::assertSame(['magikarp'], $response->losersSlugs);
    }

    #[Test]
    public function constructorAcceptsEmptySlugArrays(): void
    {
        $response = new ElectionVoteDataResponse(
            trainerExternalId: 'xyz',
            dexSlug: '',
            electionSlug: '',
            winnersSlugs: [],
            losersSlugs: [],
        );

        self::assertSame([], $response->winnersSlugs);
        self::assertSame([], $response->losersSlugs);
    }
}
