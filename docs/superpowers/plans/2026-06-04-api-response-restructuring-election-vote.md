# API Response Restructuring (POST /election/vote) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `POST /election/vote` to serialize through Response DTOs + Factory instead of the internal `ElectionVoteResult` DTO directly, aligning with the project's established pattern and producing snake_case JSON keys.

**Architecture:** Create four immutable Response DTOs (`PokemonEloResponse`, `PokemonsEloResponse`, `ElectionVoteDataResponse`, `ElectionVoteResultResponse`), a Factory that transforms `ElectionVoteResult` → `ElectionVoteResultResponse`, and update the Controller to call the Factory before serialization. The `ElectionService` and internal DTOs (`ElectionVoteResult`, `ElectionVote`, `PokemonElo`) remain untouched.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Current response structure (camelCase, internal DTO serialized directly)

```json
{
  "electionVote": {
    "trainerExternalId": "12",
    "dexSlug": "demo",
    "electionSlug": "",
    "winnersSlugs": ["butterfree"],
    "losersSlugs": ["caterpie", "metapod"]
  },
  "pokemonsElo": {
    "winners": [{"pokemonSlug": "butterfree", "elo": 1016}],
    "losers": [
      {"pokemonSlug": "caterpie", "elo": 984},
      {"pokemonSlug": "metapod", "elo": 984}
    ]
  }
}
```

## Target response structure (snake_case, Response DTOs)

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
    "winners": [{"pokemon_slug": "butterfree", "elo": 1016}],
    "losers": [
      {"pokemon_slug": "caterpie", "elo": 984},
      {"pokemon_slug": "metapod", "elo": 984}
    ]
  }
}
```

---

## File Structure

**Create:**
- `src/DTO/Response/PokemonEloResponse.php` — (pokemonSlug: string + #[SerializedName('pokemon_slug')], elo: int)
- `src/DTO/Response/PokemonsEloResponse.php` — (winners: PokemonEloResponse[], losers: PokemonEloResponse[])
- `src/DTO/Response/ElectionVoteDataResponse.php` — (trainer_external_id, dex_slug, election_slug, winners_slugs, losers_slugs)
- `src/DTO/Response/ElectionVoteResultResponse.php` — (electionVote: ElectionVoteDataResponse + #[SerializedName('election_vote')], pokemonsElo: PokemonsEloResponse + #[SerializedName('pokemons_elo')])
- `src/Factory/ElectionVoteResultResponseFactory.php` — transforms ElectionVoteResult → ElectionVoteResultResponse
- `tests/src/Unit/DTO/Response/PokemonEloResponseTest.php`
- `tests/src/Unit/DTO/Response/PokemonsEloResponseTest.php`
- `tests/src/Unit/DTO/Response/ElectionVoteDataResponseTest.php`
- `tests/src/Unit/DTO/Response/ElectionVoteResultResponseTest.php`
- `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php`

**Modify:**
- `src/Controller/ElectionVoteController.php` — call Factory before Serializer
- `tests/src/Integration/Controller/ElectionVoteControllerTest.php` — update all assertions to snake_case

**Unchanged (internal layer stays untouched):**
- `src/DTO/ElectionVoteResult.php`
- `src/DTO/ElectionVote.php`
- `src/DTO/PokemonElo.php`
- `src/Service/ElectionService.php`

---

## Tasks

### Task 1: Create PokemonEloResponse DTO

**Files:**
- Create: `src/DTO/Response/PokemonEloResponse.php`
- Create: `tests/src/Unit/DTO/Response/PokemonEloResponseTest.php`

- [ ] **Step 1: Create the DTO file**

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

Save as `src/DTO/Response/PokemonEloResponse.php`.

- [ ] **Step 2: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonEloResponse;
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
        $response = new PokemonEloResponse(
            pokemonSlug: 'pikachu',
            elo: 1200,
        );

        self::assertSame('pikachu', $response->pokemonSlug);
        self::assertSame(1200, $response->elo);
    }

    #[Test]
    public function constructorAcceptsNegativeElo(): void
    {
        $response = new PokemonEloResponse(
            pokemonSlug: 'snorlax',
            elo: -500,
        );

        self::assertSame('snorlax', $response->pokemonSlug);
        self::assertSame(-500, $response->elo);
    }
}
```

Save as `tests/src/Unit/DTO/Response/PokemonEloResponseTest.php`.

---

### Task 2: Create PokemonsEloResponse DTO

**Files:**
- Create: `src/DTO/Response/PokemonsEloResponse.php`
- Create: `tests/src/Unit/DTO/Response/PokemonsEloResponseTest.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonsEloResponse
{
    /**
     * @param PokemonEloResponse[] $winners
     * @param PokemonEloResponse[] $losers
     */
    public function __construct(
        public readonly array $winners,
        public readonly array $losers,
    ) {}
}
```

Save as `src/DTO/Response/PokemonsEloResponse.php`.

- [ ] **Step 2: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonEloResponse;
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
        $winner = new PokemonEloResponse(pokemonSlug: 'pikachu', elo: 1016);
        $loser = new PokemonEloResponse(pokemonSlug: 'magikarp', elo: 984);

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

### Task 3: Create ElectionVoteDataResponse DTO

**Files:**
- Create: `src/DTO/Response/ElectionVoteDataResponse.php`
- Create: `tests/src/Unit/DTO/Response/ElectionVoteDataResponseTest.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionVoteDataResponse
{
    /**
     * @param string[] $winnersSlugs
     * @param string[] $losersSlugs
     */
    public function __construct(
        #[SerializedName('trainer_external_id')]
        public readonly string $trainerExternalId,
        #[SerializedName('dex_slug')]
        public readonly string $dexSlug,
        #[SerializedName('election_slug')]
        public readonly string $electionSlug,
        #[SerializedName('winners_slugs')]
        public readonly array $winnersSlugs,
        #[SerializedName('losers_slugs')]
        public readonly array $losersSlugs,
    ) {}
}
```

Save as `src/DTO/Response/ElectionVoteDataResponse.php`.

- [ ] **Step 2: Create the unit test file**

```php
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
```

Save as `tests/src/Unit/DTO/Response/ElectionVoteDataResponseTest.php`.

---

### Task 4: Create ElectionVoteResultResponse DTO

**Files:**
- Create: `src/DTO/Response/ElectionVoteResultResponse.php`
- Create: `tests/src/Unit/DTO/Response/ElectionVoteResultResponseTest.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionVoteResultResponse
{
    public function __construct(
        #[SerializedName('election_vote')]
        public readonly ElectionVoteDataResponse $electionVote,
        #[SerializedName('pokemons_elo')]
        public readonly PokemonsEloResponse $pokemonsElo,
    ) {}
}
```

Save as `src/DTO/Response/ElectionVoteResultResponse.php`.

- [ ] **Step 2: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\ElectionVoteResultResponse;
use App\DTO\Response\PokemonEloResponse;
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
            winners: [new PokemonEloResponse(pokemonSlug: 'pikachu', elo: 1016)],
            losers: [new PokemonEloResponse(pokemonSlug: 'magikarp', elo: 984)],
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

### Task 5: Create ElectionVoteResultResponseFactory

**Files:**
- Create: `src/Factory/ElectionVoteResultResponseFactory.php`
- Create: `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php`

- [ ] **Step 1: Create the factory file**

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
     * @return PokemonEloResponse[]
     */
    private static function buildPokemonEloList(array $pokemonElos): array
    {
        return array_map(
            static fn(PokemonElo $pokemonElo): PokemonEloResponse => new PokemonEloResponse(
                pokemonSlug: $pokemonElo->getPokemonSlug(),
                elo: $pokemonElo->getElo(),
            ),
            $pokemonElos,
        );
    }
}
```

Save as `src/Factory/ElectionVoteResultResponseFactory.php`.

- [ ] **Step 2: Create the unit test file**

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
        self::assertSame('pikachu', $response->pokemonsElo->winners[0]->pokemonSlug);
        self::assertSame(1016, $response->pokemonsElo->winners[0]->elo);
        self::assertCount(2, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->losers[0]->pokemonSlug);
        self::assertSame(984, $response->pokemonsElo->losers[0]->elo);
        self::assertSame('metapod', $response->pokemonsElo->losers[1]->pokemonSlug);
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
        self::assertSame('caterpie', $response->pokemonsElo->winners[0]->pokemonSlug);
        self::assertSame(1016, $response->pokemonsElo->winners[0]->elo);
        self::assertSame('metapod', $response->pokemonsElo->winners[1]->pokemonSlug);
        self::assertSame('butterfree', $response->pokemonsElo->winners[2]->pokemonSlug);
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
        self::assertSame('caterpie', $response->pokemonsElo->losers[0]->pokemonSlug);
        self::assertSame('metapod', $response->pokemonsElo->losers[1]->pokemonSlug);
        self::assertSame('butterfree', $response->pokemonsElo->losers[2]->pokemonSlug);
    }
}
```

Save as `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php`.

---

### Task 6: Update ElectionVoteController

**Files:**
- Modify: `src/Controller/ElectionVoteController.php`

- [ ] **Step 1: Update controller to use Factory before Serializer**

Replace the file content with:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ElectionVote;
use App\Factory\ElectionVoteResultResponseFactory;
use App\Service\ElectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/election')]
final class ElectionVoteController extends AbstractController
{
    #[Route(path: '/vote', methods: ['POST'])]
    public function vote(
        Request $request,
        ElectionService $electionService,
        SerializerInterface $serializer,
    ): JsonResponse {
        $json = $request->getContent();

        if (!$json) {
            throw new BadRequestHttpException();
        }

        /** @var string[]|string[][] */
        $content = json_decode($json, true);

        if (!$content) {
            throw new BadRequestHttpException();
        }

        try {
            $electionVote = new ElectionVote($content);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $results = $electionService->vote($electionVote);

        return JsonResponse::fromJsonString(
            $serializer->serialize(
                ElectionVoteResultResponseFactory::fromElectionVoteResult($results),
                'json',
            ),
        );
    }
}
```

---

### Task 7: Update ElectionVoteControllerTest integration test

**Files:**
- Modify: `tests/src/Integration/Controller/ElectionVoteControllerTest.php`

- [ ] **Step 1: Update all JSON key assertions from camelCase to snake_case**

Replace the file content with:

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
                            'pokemon_slug' => 'butterfree',
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemon_slug' => 'caterpie',
                            'elo' => 984,
                        ],
                        [
                            'pokemon_slug' => 'metapod',
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
                            'pokemon_slug' => 'butterfree',
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemon_slug' => 'caterpie',
                            'elo' => 984,
                        ],
                        [
                            'pokemon_slug' => 'metapod',
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
                            'pokemon_slug' => 'caterpie',
                            'elo' => 984,
                        ],
                        [
                            'pokemon_slug' => 'metapod',
                            'elo' => 984,
                        ],
                        [
                            'pokemon_slug' => 'butterfree',
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
                            'pokemon_slug' => 'caterpie',
                            'elo' => 1016,
                        ],
                        [
                            'pokemon_slug' => 'metapod',
                            'elo' => 1016,
                        ],
                        [
                            'pokemon_slug' => 'butterfree',
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

### Task 8: Run quality checks

- [ ] **Step 1: Run all tests**

```bash
make tests
```

Expected: All unit and integration tests pass, 0 failures.

- [ ] **Step 2: Run code quality**

```bash
make quality
```

Expected: All quality checks pass (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint).

- [ ] **Step 3: Run coverage and mutation**

```bash
make measures
```

Expected: 100% code coverage and 100% MSI for all new code.

---

## Self-Review

**Spec coverage:**
- ✅ `PokemonEloResponse` DTO — (pokemonSlug: string + #[SerializedName('pokemon_slug')], elo: int)
- ✅ `PokemonsEloResponse` DTO — (winners: PokemonEloResponse[], losers: PokemonEloResponse[])
- ✅ `ElectionVoteDataResponse` DTO — all vote input fields in snake_case
- ✅ `ElectionVoteResultResponse` DTO — election_vote + pokemons_elo
- ✅ Unit tests for all 4 Response DTOs
- ✅ `ElectionVoteResultResponseFactory` — converts ElectionVoteResult → ElectionVoteResultResponse
- ✅ Unit tests for factory — 4 cases: nominal, empty lists, all-winners, all-losers
- ✅ `ElectionVoteController` updated — calls Factory then Serializer, internal DTOs untouched
- ✅ Integration tests updated — all 7 test methods updated to snake_case keys

**Placeholder scan:** No TBD, no "similar to task N", all code blocks complete.

**Type consistency:**
- `ElectionVoteResultResponseFactory::fromElectionVoteResult(ElectionVoteResult)` → `ElectionVoteResultResponse` ✅
- `ElectionVoteResultResponse::electionVote` → `ElectionVoteDataResponse` ✅
- `ElectionVoteResultResponse::pokemonsElo` → `PokemonsEloResponse` ✅
- `PokemonsEloResponse::winners/losers` → `PokemonEloResponse[]` ✅
- `PokemonEloResponse::pokemonSlug` → string, `PokemonEloResponse::elo` → int ✅
- Factory `buildPokemonEloList` uses `$pokemonElo->getPokemonSlug()` and `$pokemonElo->getElo()` matching `PokemonElo` getters ✅
- Factory `buildElectionVoteData` uses `$vote->trainerExternalId`, `$vote->dexSlug`, etc. matching `ElectionVote` public properties ✅
