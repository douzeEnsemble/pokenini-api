# API Response Restructuring (POST /election/vote — Nested Dex & Pokemon Objects in ElectionVoteDataResponse) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restructure `ElectionVoteDataResponse` so the flat `dex_slug` string becomes a nested `DexSlugResponse` object under the `dex` key, and the flat `winners_slugs`/`losers_slugs` string arrays become `PokemonSlugResponse[]` arrays under `winners`/`losers` keys, aligning with the object-oriented response pattern used across the API (issue #256).

**Architecture:** `DexSlugResponse` and `PokemonSlugResponse` already exist. Update `ElectionVoteDataResponse` to replace its three flat string fields with nested DTO fields. Update `ElectionVoteResultResponseFactory::buildElectionVoteData()` to build those nested objects from the `ElectionVote` input DTO. Update all affected unit tests and integration tests to reflect the new JSON shape. No changes to `ElectionVoteController`, `ElectionService`, or the `ElectionVote` / `ElectionVoteResult` internal DTOs.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Current response structure

```json
{
  "election_vote": {
    "trainer_external_id": "12",
    "dex_slug": "demo",
    "election_slug": "",
    "winners_slugs": ["butterfree"],
    "losers_slugs": ["caterpie", "metapod"]
  },
  "pokemons_elo": {
    "winners": [{ "pokemon": { "slug": "butterfree" }, "elo": 1016 }],
    "losers": [
      { "pokemon": { "slug": "caterpie" }, "elo": 984 },
      { "pokemon": { "slug": "metapod" }, "elo": 984 }
    ]
  }
}
```

## Target response structure

```json
{
  "election_vote": {
    "trainer_external_id": "12",
    "dex": { "slug": "demo" },
    "election_slug": "",
    "winners": [{ "slug": "butterfree" }],
    "losers": [{ "slug": "caterpie" }, { "slug": "metapod" }]
  },
  "pokemons_elo": {
    "winners": [{ "pokemon": { "slug": "butterfree" }, "elo": 1016 }],
    "losers": [
      { "pokemon": { "slug": "caterpie" }, "elo": 984 },
      { "pokemon": { "slug": "metapod" }, "elo": 984 }
    ]
  }
}
```

---

## File Structure

**No new files** — `DexSlugResponse` and `PokemonSlugResponse` already exist.

**Modify:**
- `src/DTO/Response/ElectionVoteDataResponse.php` — replace `dexSlug: string`, `winnersSlugs: string[]`, `losersSlugs: string[]` with `dex: DexSlugResponse`, `winners: PokemonSlugResponse[]`, `losers: PokemonSlugResponse[]`
- `src/Factory/ElectionVoteResultResponseFactory.php` — build nested DTOs in `buildElectionVoteData()`
- `tests/src/Unit/DTO/Response/ElectionVoteDataResponseTest.php` — update constructor calls and assertions
- `tests/src/Unit/DTO/Response/ElectionVoteResultResponseTest.php` — update `ElectionVoteDataResponse` constructions
- `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php` — update assertions from flat strings to nested objects
- `tests/src/Integration/Controller/ElectionVoteControllerTest.php` — update expected JSON shape

---

## Tasks

### Task 1: Update `ElectionVoteDataResponse` DTO

**Files:**
- Modify: `src/DTO/Response/ElectionVoteDataResponse.php`

- [ ] **Step 1: Replace the flat fields with nested DTOs**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionVoteDataResponse
{
    /**
     * @param PokemonSlugResponse[] $winners
     * @param PokemonSlugResponse[] $losers
     */
    public function __construct(
        #[SerializedName('trainer_external_id')]
        public readonly string $trainerExternalId,
        public readonly DexSlugResponse $dex,
        #[SerializedName('election_slug')]
        public readonly string $electionSlug,
        public readonly array $winners,
        public readonly array $losers,
    ) {}
}
```

Save as `src/DTO/Response/ElectionVoteDataResponse.php`.

---

### Task 2: Update `ElectionVoteResultResponseFactory`

**Files:**
- Modify: `src/Factory/ElectionVoteResultResponseFactory.php`

- [ ] **Step 1: Update `buildElectionVoteData()` and add missing imports**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionVote;
use App\DTO\ElectionVoteResult;
use App\DTO\PokemonElo;
use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\ElectionVoteResultResponse;
use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonsEloResponse;
use App\DTO\Response\PokemonSlugResponse;

final class ElectionVoteResultResponseFactory
{
    public static function fromElectionVoteResult(ElectionVoteResult $result): ElectionVoteResultResponse
    {
        return new ElectionVoteResultResponse(
            electionVote: self::buildElectionVoteData($result->getElectionVote()),
            pokemonsElo: self::buildPokemonsElo($result->getPokemonsElo()),
        );
    }

    private static function buildElectionVoteData(ElectionVote $vote): ElectionVoteDataResponse
    {
        return new ElectionVoteDataResponse(
            trainerExternalId: $vote->trainerExternalId,
            dex: new DexSlugResponse(slug: $vote->dexSlug),
            electionSlug: $vote->electionSlug,
            winners: array_map(
                static fn (string $slug): PokemonSlugResponse => new PokemonSlugResponse(slug: $slug),
                $vote->winnersSlugs,
            ),
            losers: array_map(
                static fn (string $slug): PokemonSlugResponse => new PokemonSlugResponse(slug: $slug),
                $vote->losersSlugs,
            ),
        );
    }

    /**
     * @param PokemonElo[][] $pokemonsElo
     */
    private static function buildPokemonsElo(array $pokemonsElo): PokemonsEloResponse
    {
        return new PokemonsEloResponse(
            winners: self::buildPokemonEloList($pokemonsElo['winners'] ?? []),
            losers: self::buildPokemonEloList($pokemonsElo['losers'] ?? []),
        );
    }

    /**
     * @param PokemonElo[] $pokemonElos
     *
     * @return PokemonEloResponse[]
     */
    private static function buildPokemonEloList(array $pokemonElos): array
    {
        return array_map(
            static fn (PokemonElo $pokemonElo): PokemonEloResponse => new PokemonEloResponse(
                pokemon: new PokemonSlugResponse(slug: $pokemonElo->getPokemonSlug()),
                elo: $pokemonElo->getElo(),
            ),
            $pokemonElos,
        );
    }
}
```

Save as `src/Factory/ElectionVoteResultResponseFactory.php`.

---

### Task 3: Update unit test for `ElectionVoteDataResponse`

**Files:**
- Modify: `tests/src/Unit/DTO/Response/ElectionVoteDataResponseTest.php`

- [ ] **Step 1: Rewrite tests for the new structure**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\PokemonSlugResponse;
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
        $dex = new DexSlugResponse(slug: 'national');
        $winner1 = new PokemonSlugResponse(slug: 'pikachu');
        $winner2 = new PokemonSlugResponse(slug: 'raichu');
        $loser = new PokemonSlugResponse(slug: 'magikarp');

        $response = new ElectionVoteDataResponse(
            trainerExternalId: 'abc123',
            dex: $dex,
            electionSlug: 'gen1',
            winners: [$winner1, $winner2],
            losers: [$loser],
        );

        self::assertSame('abc123', $response->trainerExternalId);
        self::assertSame($dex, $response->dex);
        self::assertSame('national', $response->dex->slug);
        self::assertSame('gen1', $response->electionSlug);
        self::assertSame([$winner1, $winner2], $response->winners);
        self::assertSame('pikachu', $response->winners[0]->slug);
        self::assertSame('raichu', $response->winners[1]->slug);
        self::assertSame([$loser], $response->losers);
        self::assertSame('magikarp', $response->losers[0]->slug);
    }

    #[Test]
    public function constructorAcceptsEmptyPokemonArrays(): void
    {
        $dex = new DexSlugResponse(slug: '');
        $response = new ElectionVoteDataResponse(
            trainerExternalId: 'xyz',
            dex: $dex,
            electionSlug: '',
            winners: [],
            losers: [],
        );

        self::assertSame($dex, $response->dex);
        self::assertSame('', $response->dex->slug);
        self::assertSame([], $response->winners);
        self::assertSame([], $response->losers);
    }
}
```

Save as `tests/src/Unit/DTO/Response/ElectionVoteDataResponseTest.php`.

---

### Task 4: Update unit test for `ElectionVoteResultResponse`

**Files:**
- Modify: `tests/src/Unit/DTO/Response/ElectionVoteResultResponseTest.php`

- [ ] **Step 1: Update the `ElectionVoteDataResponse` construction**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\ElectionVoteResultResponse;
use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonsEloResponse;
use App\DTO\Response\PokemonSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteResultResponse::class)]
final class ElectionVoteResultResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $electionVoteData = new ElectionVoteDataResponse(
            trainerExternalId: 'trainer1',
            dex: new DexSlugResponse(slug: 'national'),
            electionSlug: '',
            winners: [new PokemonSlugResponse(slug: 'pikachu')],
            losers: [new PokemonSlugResponse(slug: 'magikarp')],
        );
        $pokemonsElo = new PokemonsEloResponse(
            winners: [new PokemonEloResponse(pokemon: new PokemonSlugResponse(slug: 'pikachu'), elo: 1016)],
            losers: [new PokemonEloResponse(pokemon: new PokemonSlugResponse(slug: 'magikarp'), elo: 984)],
        );

        $response = new ElectionVoteResultResponse(
            electionVote: $electionVoteData,
            pokemonsElo: $pokemonsElo,
        );

        self::assertSame($electionVoteData, $response->electionVote);
        self::assertSame($pokemonsElo, $response->pokemonsElo);
    }
}
```

Save as `tests/src/Unit/DTO/Response/ElectionVoteResultResponseTest.php`.

---

### Task 5: Update unit test for `ElectionVoteResultResponseFactory`

**Files:**
- Modify: `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php`

- [ ] **Step 1: Update all assertions from flat strings to nested objects**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\ElectionVote;
use App\DTO\ElectionVoteResult;
use App\DTO\PokemonElo;
use App\Factory\ElectionVoteResultResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteResultResponseFactory::class)]
final class ElectionVoteResultResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromElectionVoteResultTransformsAllFields(): void
    {
        $electionVote = new ElectionVote([
            'trainer_external_id' => 'trainer42',
            'dex_slug' => 'national',
            'election_slug' => 'gen1',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['caterpie', 'metapod'],
        ]);
        $pokemonsElo = [
            'winners' => [new PokemonElo('pikachu', 1016)],
            'losers' => [
                new PokemonElo('caterpie', 984),
                new PokemonElo('metapod', 984),
            ],
        ];
        $result = new ElectionVoteResult($electionVote, $pokemonsElo);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame('trainer42', $response->electionVote->trainerExternalId);
        self::assertSame('national', $response->electionVote->dex->slug);
        self::assertSame('gen1', $response->electionVote->electionSlug);
        self::assertCount(1, $response->electionVote->winners);
        self::assertSame('pikachu', $response->electionVote->winners[0]->slug);
        self::assertCount(2, $response->electionVote->losers);
        self::assertSame('caterpie', $response->electionVote->losers[0]->slug);
        self::assertSame('metapod', $response->electionVote->losers[1]->slug);
        self::assertCount(1, $response->pokemonsElo->winners);
        self::assertSame('pikachu', $response->pokemonsElo->winners[0]->pokemon->slug);
        self::assertSame(1016, $response->pokemonsElo->winners[0]->elo);
        self::assertCount(2, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->losers[0]->pokemon->slug);
        self::assertSame(984, $response->pokemonsElo->losers[0]->elo);
        self::assertSame('metapod', $response->pokemonsElo->losers[1]->pokemon->slug);
        self::assertSame(984, $response->pokemonsElo->losers[1]->elo);
    }

    #[Test]
    public function fromElectionVoteResultHandlesEmptyPokemonLists(): void
    {
        $electionVote = new ElectionVote([
            'trainer_external_id' => 'trainer1',
            'dex_slug' => '',
            'election_slug' => '',
            'winners_slugs' => [],
            'losers_slugs' => [],
        ]);
        $result = new ElectionVoteResult($electionVote, ['winners' => [], 'losers' => []]);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame('', $response->electionVote->dex->slug);
        self::assertSame([], $response->electionVote->winners);
        self::assertSame([], $response->electionVote->losers);
        self::assertSame([], $response->pokemonsElo->winners);
        self::assertSame([], $response->pokemonsElo->losers);
    }

    #[Test]
    public function fromElectionVoteResultHandlesAllWinners(): void
    {
        $electionVote = new ElectionVote([
            'trainer_external_id' => 'trainer1',
            'dex_slug' => 'demo',
            'election_slug' => '',
            'winners_slugs' => ['caterpie', 'metapod', 'butterfree'],
            'losers_slugs' => [],
        ]);
        $pokemonsElo = [
            'winners' => [
                new PokemonElo('caterpie', 1016),
                new PokemonElo('metapod', 1016),
                new PokemonElo('butterfree', 1016),
            ],
            'losers' => [],
        ];
        $result = new ElectionVoteResult($electionVote, $pokemonsElo);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame('demo', $response->electionVote->dex->slug);
        self::assertCount(3, $response->electionVote->winners);
        self::assertSame('caterpie', $response->electionVote->winners[0]->slug);
        self::assertSame('metapod', $response->electionVote->winners[1]->slug);
        self::assertSame('butterfree', $response->electionVote->winners[2]->slug);
        self::assertSame([], $response->electionVote->losers);
        self::assertCount(3, $response->pokemonsElo->winners);
        self::assertCount(0, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->winners[0]->pokemon->slug);
        self::assertSame(1016, $response->pokemonsElo->winners[0]->elo);
        self::assertSame('metapod', $response->pokemonsElo->winners[1]->pokemon->slug);
        self::assertSame('butterfree', $response->pokemonsElo->winners[2]->pokemon->slug);
    }

    #[Test]
    public function fromElectionVoteResultHandlesAllLosers(): void
    {
        $electionVote = new ElectionVote([
            'trainer_external_id' => 'trainer1',
            'dex_slug' => 'demo',
            'election_slug' => '',
            'winners_slugs' => [],
            'losers_slugs' => ['caterpie', 'metapod', 'butterfree'],
        ]);
        $pokemonsElo = [
            'winners' => [],
            'losers' => [
                new PokemonElo('caterpie', 984),
                new PokemonElo('metapod', 984),
                new PokemonElo('butterfree', 984),
            ],
        ];
        $result = new ElectionVoteResult($electionVote, $pokemonsElo);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame([], $response->electionVote->winners);
        self::assertCount(3, $response->electionVote->losers);
        self::assertSame('caterpie', $response->electionVote->losers[0]->slug);
        self::assertSame('metapod', $response->electionVote->losers[1]->slug);
        self::assertSame('butterfree', $response->electionVote->losers[2]->slug);
        self::assertCount(0, $response->pokemonsElo->winners);
        self::assertCount(3, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->losers[0]->pokemon->slug);
        self::assertSame('metapod', $response->pokemonsElo->losers[1]->pokemon->slug);
        self::assertSame('butterfree', $response->pokemonsElo->losers[2]->pokemon->slug);
    }

    #[Test]
    public function fromElectionVoteResultHandlesMissingPokemonEloKeys(): void
    {
        $electionVote = new ElectionVote([
            'trainer_external_id' => 'trainer1',
            'dex_slug' => 'demo',
            'election_slug' => '',
            'winners_slugs' => [],
            'losers_slugs' => [],
        ]);
        $result = new ElectionVoteResult($electionVote, []);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame([], $response->pokemonsElo->winners);
        self::assertSame([], $response->pokemonsElo->losers);
        self::assertSame([], $response->electionVote->winners);
        self::assertSame([], $response->electionVote->losers);
    }
}
```

Save as `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php`.

---

### Task 6: Update integration test for `ElectionVoteController`

**Files:**
- Modify: `tests/src/Integration/Controller/ElectionVoteControllerTest.php`

- [ ] **Step 1: Update all expected JSON structures from flat to nested**

Replace every occurrence of:
```php
'election_vote' => [
    'trainer_external_id' => '...',
    'dex_slug' => 'demo',
    'election_slug' => '',
    'winners_slugs' => [...],
    'losers_slugs' => [...],
],
```

with:
```php
'election_vote' => [
    'trainer_external_id' => '...',
    'dex' => ['slug' => 'demo'],
    'election_slug' => '',
    'winners' => array_map(static fn (string $s): array => ['slug' => $s], [...]),
    'losers' => array_map(static fn (string $s): array => ['slug' => $s], [...]),
],
```

The complete updated file:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ElectionVoteController;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteController::class)]
final class ElectionVoteControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    public function testVote(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer_external_id": "12", "dex_slug": "demo", "election_slug": "", "winners_slugs": ["butterfree"], "losers_slugs": ["caterpie", "metapod"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer_external_id' => '12',
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [
                        ['slug' => 'butterfree'],
                    ],
                    'losers' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    public function testVoteBis(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554", "dex_slug": "demo", "election_slug": "", "winners_slugs": ["butterfree"], "losers_slugs": ["caterpie", "metapod"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [
                        ['slug' => 'butterfree'],
                    ],
                    'losers' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    public function testVoteAllLosers(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554", "dex_slug": "demo", "election_slug": "", "winners_slugs": [], "losers_slugs": ["caterpie", "metapod", "butterfree"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [],
                    'losers' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                        ['slug' => 'butterfree'],
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [],
                    'losers' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    public function testVoteAllWinners(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554", "dex_slug": "demo", "election_slug": "", "winners_slugs": ["caterpie", "metapod", "butterfree"], "losers_slugs": []}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                        ['slug' => 'butterfree'],
                    ],
                    'losers' => [],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 1016,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 1016,
                        ],
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [],
                ],
            ],
            $data,
        );
    }

    public function testEmptyData(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '',
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testEmptyDataBis(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{}',
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testBadVote(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainerExternalId": "12", "dex_slug": "demo", "electionSlug": "", "winnersSlugs": "pichu", "losersSlugs": ["pikachu", "raichu"]}',
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
```

Save as `tests/src/Integration/Controller/ElectionVoteControllerTest.php`.

---

### Task 7: Run quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run unit tests**

Run: `make tests-unit`

Expected: All unit tests pass, 0 failures.

- [ ] **Step 2: Run integration tests**

Run: `make tests-integration`

Expected: All integration tests pass, 0 failures.

- [ ] **Step 3: Run full quality suite**

Run: `make quality && make measures`

Expected: All checks green, 100% code coverage, 100% MSI.
