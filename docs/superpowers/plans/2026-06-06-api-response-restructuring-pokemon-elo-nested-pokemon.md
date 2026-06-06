# API Response Restructuring (POST /election/vote — Nested Pokemon Object) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restructure `PokemonEloResponse` so the flat `pokemon_slug` string becomes a nested `PokemonSlugResponse` object under the `pokemon` key, aligning with the object-oriented response pattern used across the API (issue #256).

**Architecture:** Create an immutable `PokemonSlugResponse` DTO (one field: `slug`). Update `PokemonEloResponse` to hold `PokemonSlugResponse $pokemon` instead of `string $pokemonSlug`. Update `ElectionVoteResultResponseFactory` to build `PokemonSlugResponse` from `$pokemonElo->getPokemonSlug()`. Update all affected unit tests and the integration test to reflect the new JSON shape. No changes to `ElectionVoteController`, `ElectionService`, or internal DTOs.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Current response structure

```json
{
  "election_vote": { "..." : "..." },
  "pokemons_elo": {
    "winners": [
      { "pokemon_slug": "butterfree", "elo": 1016 }
    ],
    "losers": [
      { "pokemon_slug": "caterpie", "elo": 984 },
      { "pokemon_slug": "metapod", "elo": 984 }
    ]
  }
}
```

## Target response structure

```json
{
  "election_vote": { "..." : "..." },
  "pokemons_elo": {
    "winners": [
      { "pokemon": { "slug": "butterfree" }, "elo": 1016 }
    ],
    "losers": [
      { "pokemon": { "slug": "caterpie" }, "elo": 984 },
      { "pokemon": { "slug": "metapod" }, "elo": 984 }
    ]
  }
}
```

---

## File Structure

**Create:**
- `src/DTO/Response/PokemonSlugResponse.php` — immutable DTO wrapping the pokemon slug reference
- `tests/src/Unit/DTO/Response/PokemonSlugResponseTest.php` — unit tests for PokemonSlugResponse

**Modify:**
- `src/DTO/Response/PokemonEloResponse.php` — replace `$pokemonSlug: string` with `$pokemon: PokemonSlugResponse`
- `src/Factory/ElectionVoteResultResponseFactory.php` — build nested `PokemonSlugResponse` from `getPokemonSlug()`
- `tests/src/Unit/DTO/Response/PokemonEloResponseTest.php` — update constructor calls and assertions
- `tests/src/Unit/DTO/Response/PokemonsEloResponseTest.php` — update `PokemonEloResponse` constructions
- `tests/src/Unit/DTO/Response/ElectionVoteResultResponseTest.php` — update `PokemonEloResponse` constructions
- `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php` — update assertions from `->pokemonSlug` to `->pokemon->slug`
- `tests/src/Integration/Controller/ElectionVoteControllerTest.php` — update expected JSON from `pokemon_slug` to `pokemon => [slug]`

---

## Tasks

### Task 1: Create PokemonSlugResponse DTO and its unit test

**Files:**
- Create: `src/DTO/Response/PokemonSlugResponse.php`
- Create: `tests/src/Unit/DTO/Response/PokemonSlugResponseTest.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonSlugResponse
{
    public function __construct(
        public readonly string $slug,
    ) {}
}
```

Save as `src/DTO/Response/PokemonSlugResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonSlugResponse::class)]
final class PokemonSlugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesSlug(): void
    {
        $response = new PokemonSlugResponse(slug: 'pikachu');

        self::assertSame('pikachu', $response->slug);
    }

    #[Test]
    public function slugIsReadonly(): void
    {
        $response = new PokemonSlugResponse(slug: 'charizard');

        self::assertSame('charizard', $response->slug);
    }
}
```

Save as `tests/src/Unit/DTO/Response/PokemonSlugResponseTest.php`.

---

### Task 2: Update PokemonEloResponse DTO and its unit test

**Files:**
- Modify: `src/DTO/Response/PokemonEloResponse.php`
- Modify: `tests/src/Unit/DTO/Response/PokemonEloResponseTest.php`

- [ ] **Step 1: Replace `$pokemonSlug: string` with `$pokemon: PokemonSlugResponse`**

Current content of `src/DTO/Response/PokemonEloResponse.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonEloResponse
{
    public function __construct(
        #[SerializedName('pokemon_slug')]
        public readonly string $pokemonSlug,
        public readonly int $elo,
    ) {}
}
```

Replace the entire file with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonEloResponse
{
    public function __construct(
        public readonly PokemonSlugResponse $pokemon,
        public readonly int $elo,
    ) {}
}
```

Save as `src/DTO/Response/PokemonEloResponse.php`.

- [ ] **Step 2: Update the unit test**

Current content of `tests/src/Unit/DTO/Response/PokemonEloResponseTest.php` uses `pokemonSlug: string`. Replace the entire file with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonEloResponse::class)]
final class PokemonEloResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $pokemon = new PokemonSlugResponse(slug: 'pikachu');
        $response = new PokemonEloResponse(
            pokemon: $pokemon,
            elo: 1200,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame('pikachu', $response->pokemon->slug);
        self::assertSame(1200, $response->elo);
    }

    #[Test]
    public function constructorAcceptsNegativeElo(): void
    {
        $response = new PokemonEloResponse(
            pokemon: new PokemonSlugResponse(slug: 'snorlax'),
            elo: -500,
        );

        self::assertSame('snorlax', $response->pokemon->slug);
        self::assertSame(-500, $response->elo);
    }
}
```

Save as `tests/src/Unit/DTO/Response/PokemonEloResponseTest.php`.

---

### Task 3: Update PokemonsEloResponseTest

**Files:**
- Modify: `tests/src/Unit/DTO/Response/PokemonsEloResponseTest.php`

`PokemonsEloResponse` itself does not change (it still holds `PokemonEloResponse[]`), but its test instantiates `PokemonEloResponse` with the old `pokemonSlug:` parameter and must be updated.

- [ ] **Step 1: Update all PokemonEloResponse constructions to use the new `pokemon:` parameter**

Current content uses `new PokemonEloResponse(pokemonSlug: 'pikachu', elo: 1016)`. Replace the entire file with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\DTO\Response\PokemonsEloResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonsEloResponse::class)]
final class PokemonsEloResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $winner = new PokemonEloResponse(pokemon: new PokemonSlugResponse(slug: 'pikachu'), elo: 1016);
        $loser = new PokemonEloResponse(pokemon: new PokemonSlugResponse(slug: 'magikarp'), elo: 984);

        $response = new PokemonsEloResponse(
            winners: [$winner],
            losers: [$loser],
        );

        self::assertSame([$winner], $response->winners);
        self::assertSame([$loser], $response->losers);
    }

    #[Test]
    public function constructorAcceptsEmptyArrays(): void
    {
        $response = new PokemonsEloResponse(
            winners: [],
            losers: [],
        );

        self::assertSame([], $response->winners);
        self::assertSame([], $response->losers);
    }
}
```

Save as `tests/src/Unit/DTO/Response/PokemonsEloResponseTest.php`.

---

### Task 4: Update ElectionVoteResultResponseTest

**Files:**
- Modify: `tests/src/Unit/DTO/Response/ElectionVoteResultResponseTest.php`

`ElectionVoteResultResponse` itself does not change, but its test instantiates `PokemonEloResponse` with the old parameter.

- [ ] **Step 1: Update PokemonEloResponse constructions to use the new `pokemon:` parameter**

Current content uses `new PokemonEloResponse(pokemonSlug: 'pikachu', elo: 1016)`. Replace the entire file with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\ElectionVoteResultResponse;
use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\DTO\Response\PokemonsEloResponse;
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
            dexSlug: 'national',
            electionSlug: '',
            winnersSlugs: ['pikachu'],
            losersSlugs: ['magikarp'],
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

### Task 5: Update ElectionVoteResultResponseFactory and its unit test

**Files:**
- Modify: `src/Factory/ElectionVoteResultResponseFactory.php`
- Modify: `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php`

- [ ] **Step 1: Update the factory to build nested PokemonSlugResponse**

The only change is in `buildPokemonEloList`: replace `pokemonSlug: $pokemonElo->getPokemonSlug()` with `pokemon: new PokemonSlugResponse(slug: $pokemonElo->getPokemonSlug())` and add the import.

Replace the entire file `src/Factory/ElectionVoteResultResponseFactory.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionVote;
use App\DTO\ElectionVoteResult;
use App\DTO\PokemonElo;
use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\ElectionVoteResultResponse;
use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\DTO\Response\PokemonsEloResponse;

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
            dexSlug: $vote->dexSlug,
            electionSlug: $vote->electionSlug,
            winnersSlugs: $vote->winnersSlugs,
            losersSlugs: $vote->losersSlugs,
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

- [ ] **Step 2: Update the factory unit test to assert on `->pokemon->slug` instead of `->pokemonSlug`**

Replace the entire file `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php` with:

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
        self::assertSame('national', $response->electionVote->dexSlug);
        self::assertSame('gen1', $response->electionVote->electionSlug);
        self::assertSame(['pikachu'], $response->electionVote->winnersSlugs);
        self::assertSame(['caterpie', 'metapod'], $response->electionVote->losersSlugs);
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

        self::assertSame([], $response->pokemonsElo->winners);
        self::assertSame([], $response->pokemonsElo->losers);
        self::assertSame([], $response->electionVote->winnersSlugs);
        self::assertSame([], $response->electionVote->losersSlugs);
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

        self::assertCount(0, $response->pokemonsElo->winners);
        self::assertCount(3, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->losers[0]->pokemon->slug);
        self::assertSame('metapod', $response->pokemonsElo->losers[1]->pokemon->slug);
        self::assertSame('butterfree', $response->pokemonsElo->losers[2]->pokemon->slug);
    }
}
```

---

### Task 6: Update ElectionVoteControllerTest integration test

**Files:**
- Modify: `tests/src/Integration/Controller/ElectionVoteControllerTest.php`

Every test method that asserts on `pokemons_elo` currently expects `'pokemon_slug' => 'X'`. This must become `'pokemon' => ['slug' => 'X']`.

- [ ] **Step 1: Replace all `'pokemon_slug' => '...'` entries with `'pokemon' => ['slug' => '...']`**

Replace the entire file `tests/src/Integration/Controller/ElectionVoteControllerTest.php` with:

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
                    'dex_slug' => 'demo',
                    'election_slug' => '',
                    'winners_slugs' => [
                        'butterfree',
                    ],
                    'losers_slugs' => [
                        'caterpie',
                        'metapod',
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
                    'dex_slug' => 'demo',
                    'election_slug' => '',
                    'winners_slugs' => [
                        'butterfree',
                    ],
                    'losers_slugs' => [
                        'caterpie',
                        'metapod',
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
                    'dex_slug' => 'demo',
                    'election_slug' => '',
                    'winners_slugs' => [],
                    'losers_slugs' => [
                        'caterpie',
                        'metapod',
                        'butterfree',
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
                    'dex_slug' => 'demo',
                    'election_slug' => '',
                    'winners_slugs' => [
                        'caterpie',
                        'metapod',
                        'butterfree',
                    ],
                    'losers_slugs' => [],
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

---

### Task 7: Run quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all tests**

Run: `make tests`

Expected: All unit and integration tests pass, 0 failures.

- [ ] **Step 2: Run code quality**

Run: `make quality`

Expected: PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint all green.

- [ ] **Step 3: Run coverage and mutation**

Run: `make measures`

Expected: 100% line coverage and 100% MSI for all new and modified code.

---

## Self-Review

**Spec coverage:**
- ✅ New `PokemonSlugResponse` DTO (`slug: string`) — `src/DTO/Response/PokemonSlugResponse.php`
- ✅ `PokemonSlugResponseTest` — 2 test cases covering the constructor
- ✅ `PokemonEloResponse` updated — `$pokemon: PokemonSlugResponse` replaces `$pokemonSlug: string`; `#[SerializedName('pokemon_slug')]` removed (serializer uses property name `pokemon` automatically, producing a nested object)
- ✅ `PokemonEloResponseTest` updated — 2 test cases asserting on `$response->pokemon->slug`
- ✅ `PokemonsEloResponseTest` updated — all `PokemonEloResponse` constructions use new `pokemon:` parameter
- ✅ `ElectionVoteResultResponseTest` updated — all `PokemonEloResponse` constructions use new `pokemon:` parameter
- ✅ `ElectionVoteResultResponseFactory` updated — `buildPokemonEloList` wraps slug in `new PokemonSlugResponse()`
- ✅ `ElectionVoteResultResponseFactoryTest` updated — 4 test cases, all assertions use `->pokemon->slug`
- ✅ `ElectionVoteControllerTest` updated — 4 positive test methods; every `'pokemon_slug' => 'X'` becomes `'pokemon' => ['slug' => 'X']`; 3 error-path tests untouched
- ✅ No changes to `ElectionVoteController`, `ElectionService`, `PokemonElo`, `ElectionVote`, `ElectionVoteResult`

**Placeholder scan:** No TBD, no "similar to task N", all code blocks complete.

**Type consistency:**
- `ElectionVoteResultResponseFactory::buildPokemonEloList()` → returns `PokemonEloResponse[]` ✅
- `PokemonEloResponse::$pokemon` → `PokemonSlugResponse` ✅
- `PokemonSlugResponse::$slug` → `string` ✅
- Factory calls `$pokemonElo->getPokemonSlug()` matching `PokemonElo::getPokemonSlug(): string` ✅
- Integration test data `'pokemon' => ['slug' => 'X']` matches what Symfony Serializer produces for a `PokemonSlugResponse` with `slug = 'X'` ✅
- Factory test assertions use `$response->pokemonsElo->winners[0]->pokemon->slug` matching the new DTO property path ✅
