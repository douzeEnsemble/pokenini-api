# API Response Restructuring (Pokemon Availabilities — Nested) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /debogage/pokemon/{slug}/availabilities` — the last non-migrated endpoint (issue #256) — from camelCase flat `slug => bool` maps to a snake_case, nested object-oriented structure.

**Architecture:** Create two nested DTOs (`GameAvailabilityResponse`, `GameBundleAvailabilityResponse`) plus a `GameSlugResponse` leaf DTO; rework `PokemonAvailabilitiesResponse` to hold lists of those DTOs with `#[SerializedName]` snake_case keys; rework `PokemonAvailabilitiesResponseFactory` to build the nested lists from the four `*Availabilities` value-objects. The controller is untouched (same factory signature).

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer, PHPUnit

---

## Response shape change

**Before** (camelCase, flat maps — the only endpoint still violating the snake_case convention, see `doc/endpoints.md`):

```json
{
  "gamesAvailabilities": { "x": true, "y": true },
  "gamesShiniesAvailabilities": { "x": true },
  "gameBundlesAvailabilities": { "goldsilvercrystal": false, "xy": true },
  "gameBundlesShiniesAvailabilities": { "goldsilvercrystal": false, "xy": true }
}
```

**After** (snake_case, nested objects):

```json
{
  "games_availabilities": [
    { "game": { "slug": "x" }, "is_available": true },
    { "game": { "slug": "y" }, "is_available": true }
  ],
  "games_shinies_availabilities": [
    { "game": { "slug": "x" }, "is_available": true }
  ],
  "game_bundles_availabilities": [
    { "game_bundle": { "slug": "goldsilvercrystal" }, "is_available": false },
    { "game_bundle": { "slug": "xy" }, "is_available": true }
  ],
  "game_bundles_shinies_availabilities": [
    { "game_bundle": { "slug": "goldsilvercrystal" }, "is_available": false },
    { "game_bundle": { "slug": "xy" }, "is_available": true }
  ]
}
```

Breaking change on a debug endpoint — no Postman/Newman coverage, no Moco fixture, no downstream BFF/web usage to migrate.

---

## File Structure

**Create:**
- `src/DTO/Response/GameSlugResponse.php` — leaf DTO `{slug}` (mirror of existing `GameBundleSlugResponse`)
- `src/DTO/Response/GameAvailabilityResponse.php` — `{game: GameSlugResponse, is_available: bool}`
- `src/DTO/Response/GameBundleAvailabilityResponse.php` — `{game_bundle: GameBundleSlugResponse, is_available: bool}`
- `tests/src/Unit/DTO/Response/GameSlugResponseTest.php`
- `tests/src/Unit/DTO/Response/GameAvailabilityResponseTest.php`
- `tests/src/Unit/DTO/Response/GameBundleAvailabilityResponseTest.php`

**Modify:**
- `src/DTO/Response/PokemonAvailabilitiesResponse.php` — nested DTO lists + `#[SerializedName]` snake_case
- `src/Factory/PokemonAvailabilitiesResponseFactory.php` — builds the nested lists
- `tests/src/Unit/DTO/Response/PokemonAvailabilitiesResponseTest.php` — new constructor shape
- `tests/src/Unit/Factory/PokemonAvailabilitiesResponseFactoryTest.php` — assertions on nested objects
- `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php` — `testPokemonAvailabilities()` asserts new JSON shape
- `doc/endpoints.md` — section 35 example + remove the camelCase exception note

**Untouched:**
- `src/Controller/Debug/DebugPokemonController.php` — factory signature unchanged

---

## Tasks

### Task 1: Create GameSlugResponse DTO

**Files:**
- Create: `src/DTO/Response/GameSlugResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class GameSlugResponse
{
    public function __construct(
        public readonly string $slug,
    ) {}
}
```

Save as `src/DTO/Response/GameSlugResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/GameSlugResponse.php`

Expected: `No syntax errors detected`.

---

### Task 2: Create unit test for GameSlugResponse

**Files:**
- Create: `tests/src/Unit/DTO/Response/GameSlugResponseTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameSlugResponse::class)]
final class GameSlugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesSlug(): void
    {
        $response = new GameSlugResponse(slug: 'x');

        self::assertSame('x', $response->slug);
    }

    #[Test]
    public function constructorAcceptsAnotherSlug(): void
    {
        $response = new GameSlugResponse(slug: 'omegaruby');

        self::assertSame('omegaruby', $response->slug);
    }
}
```

Save as `tests/src/Unit/DTO/Response/GameSlugResponseTest.php`.

- [ ] **Step 2: Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/GameSlugResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 3: Create GameAvailabilityResponse DTO

**Files:**
- Create: `src/DTO/Response/GameAvailabilityResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameAvailabilityResponse
{
    public function __construct(
        public readonly GameSlugResponse $game,
        #[SerializedName('is_available')]
        public readonly bool $isAvailable,
    ) {}
}
```

Save as `src/DTO/Response/GameAvailabilityResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/GameAvailabilityResponse.php`

Expected: `No syntax errors detected`.

---

### Task 4: Create unit test for GameAvailabilityResponse

**Files:**
- Create: `tests/src/Unit/DTO/Response/GameAvailabilityResponseTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameAvailabilityResponse;
use App\DTO\Response\GameSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameAvailabilityResponse::class)]
final class GameAvailabilityResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $game = new GameSlugResponse(slug: 'x');
        $response = new GameAvailabilityResponse(
            game: $game,
            isAvailable: true,
        );

        self::assertSame($game, $response->game);
        self::assertTrue($response->isAvailable);
    }

    #[Test]
    public function constructorAcceptsUnavailableGame(): void
    {
        $game = new GameSlugResponse(slug: 'blue');
        $response = new GameAvailabilityResponse(
            game: $game,
            isAvailable: false,
        );

        self::assertSame($game, $response->game);
        self::assertFalse($response->isAvailable);
    }
}
```

Save as `tests/src/Unit/DTO/Response/GameAvailabilityResponseTest.php`.

- [ ] **Step 2: Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/GameAvailabilityResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 5: Create GameBundleAvailabilityResponse DTO

**Files:**
- Create: `src/DTO/Response/GameBundleAvailabilityResponse.php`

`GameBundleSlugResponse` already exists in the same namespace — it is reused, not recreated.

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameBundleAvailabilityResponse
{
    public function __construct(
        #[SerializedName('game_bundle')]
        public readonly GameBundleSlugResponse $gameBundle,
        #[SerializedName('is_available')]
        public readonly bool $isAvailable,
    ) {}
}
```

Save as `src/DTO/Response/GameBundleAvailabilityResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/GameBundleAvailabilityResponse.php`

Expected: `No syntax errors detected`.

---

### Task 6: Create unit test for GameBundleAvailabilityResponse

**Files:**
- Create: `tests/src/Unit/DTO/Response/GameBundleAvailabilityResponseTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundleAvailabilityResponse;
use App\DTO\Response\GameBundleSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleAvailabilityResponse::class)]
final class GameBundleAvailabilityResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $gameBundle = new GameBundleSlugResponse(slug: 'xy');
        $response = new GameBundleAvailabilityResponse(
            gameBundle: $gameBundle,
            isAvailable: true,
        );

        self::assertSame($gameBundle, $response->gameBundle);
        self::assertTrue($response->isAvailable);
    }

    #[Test]
    public function constructorAcceptsUnavailableGameBundle(): void
    {
        $gameBundle = new GameBundleSlugResponse(slug: 'goldsilvercrystal');
        $response = new GameBundleAvailabilityResponse(
            gameBundle: $gameBundle,
            isAvailable: false,
        );

        self::assertSame($gameBundle, $response->gameBundle);
        self::assertFalse($response->isAvailable);
    }
}
```

Save as `tests/src/Unit/DTO/Response/GameBundleAvailabilityResponseTest.php`.

- [ ] **Step 2: Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/GameBundleAvailabilityResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 7: Rework PokemonAvailabilitiesResponse DTO

**Files:**
- Modify: `src/DTO/Response/PokemonAvailabilitiesResponse.php`

- [ ] **Step 1: Replace the full file content**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonAvailabilitiesResponse
{
    /**
     * @param GameAvailabilityResponse[]       $gamesAvailabilities
     * @param GameAvailabilityResponse[]       $gamesShiniesAvailabilities
     * @param GameBundleAvailabilityResponse[] $gameBundlesAvailabilities
     * @param GameBundleAvailabilityResponse[] $gameBundlesShiniesAvailabilities
     */
    public function __construct(
        #[SerializedName('games_availabilities')]
        public readonly array $gamesAvailabilities,
        #[SerializedName('games_shinies_availabilities')]
        public readonly array $gamesShiniesAvailabilities,
        #[SerializedName('game_bundles_availabilities')]
        public readonly array $gameBundlesAvailabilities,
        #[SerializedName('game_bundles_shinies_availabilities')]
        public readonly array $gameBundlesShiniesAvailabilities,
    ) {}
}
```

Save as `src/DTO/Response/PokemonAvailabilitiesResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/PokemonAvailabilitiesResponse.php`

Expected: `No syntax errors detected`.

---

### Task 8: Update unit test for PokemonAvailabilitiesResponse

**Files:**
- Modify: `tests/src/Unit/DTO/Response/PokemonAvailabilitiesResponseTest.php`

The constructor keeps the same four named arguments but now receives lists of nested DTOs. Distinct content per field catches any field-swap mutation.

- [ ] **Step 1: Replace the full test file content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameAvailabilityResponse;
use App\DTO\Response\GameBundleAvailabilityResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\GameSlugResponse;
use App\DTO\Response\PokemonAvailabilitiesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonAvailabilitiesResponse::class)]
final class PokemonAvailabilitiesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $gameAvailability = new GameAvailabilityResponse(
            game: new GameSlugResponse(slug: 'x'),
            isAvailable: true,
        );
        $gameShinyAvailability = new GameAvailabilityResponse(
            game: new GameSlugResponse(slug: 'y'),
            isAvailable: false,
        );
        $gameBundleAvailability = new GameBundleAvailabilityResponse(
            gameBundle: new GameBundleSlugResponse(slug: 'xy'),
            isAvailable: true,
        );
        $gameBundleShinyAvailability = new GameBundleAvailabilityResponse(
            gameBundle: new GameBundleSlugResponse(slug: 'goldsilvercrystal'),
            isAvailable: false,
        );

        $response = new PokemonAvailabilitiesResponse(
            gamesAvailabilities: [$gameAvailability],
            gamesShiniesAvailabilities: [$gameShinyAvailability],
            gameBundlesAvailabilities: [$gameBundleAvailability],
            gameBundlesShiniesAvailabilities: [$gameBundleShinyAvailability],
        );

        self::assertSame([$gameAvailability], $response->gamesAvailabilities);
        self::assertSame([$gameShinyAvailability], $response->gamesShiniesAvailabilities);
        self::assertSame([$gameBundleAvailability], $response->gameBundlesAvailabilities);
        self::assertSame([$gameBundleShinyAvailability], $response->gameBundlesShiniesAvailabilities);
    }

    #[Test]
    public function constructorAcceptsEmptyArrays(): void
    {
        $response = new PokemonAvailabilitiesResponse(
            gamesAvailabilities: [],
            gamesShiniesAvailabilities: [],
            gameBundlesAvailabilities: [],
            gameBundlesShiniesAvailabilities: [],
        );

        self::assertSame([], $response->gamesAvailabilities);
        self::assertSame([], $response->gamesShiniesAvailabilities);
        self::assertSame([], $response->gameBundlesAvailabilities);
        self::assertSame([], $response->gameBundlesShiniesAvailabilities);
    }
}
```

Save as `tests/src/Unit/DTO/Response/PokemonAvailabilitiesResponseTest.php`.

- [ ] **Step 2: Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/PokemonAvailabilitiesResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 9: Rework PokemonAvailabilitiesResponseFactory

**Files:**
- Modify: `src/Factory/PokemonAvailabilitiesResponseFactory.php`

The public signature `fromAvailabilities()` is unchanged — the controller needs no modification. Two private helpers map a `slug => bool` map to a list of nested DTOs. Array keys are cast to `string` because PHP silently converts numeric string keys (e.g. a game slug like `"123"`) to `int`.

- [ ] **Step 1: Replace the full file content**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\DTO\GamesAvailabilities;
use App\DTO\GamesShiniesAvailabilities;
use App\DTO\Response\GameAvailabilityResponse;
use App\DTO\Response\GameBundleAvailabilityResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\GameSlugResponse;
use App\DTO\Response\PokemonAvailabilitiesResponse;

final class PokemonAvailabilitiesResponseFactory
{
    public static function fromAvailabilities(
        GamesAvailabilities $gamesAvailabilities,
        GamesShiniesAvailabilities $gamesShiniesAvailabilities,
        GameBundlesAvailabilities $gameBundlesAvailabilities,
        GameBundlesShiniesAvailabilities $gameBundlesShiniesAvailabilities,
    ): PokemonAvailabilitiesResponse {
        return new PokemonAvailabilitiesResponse(
            gamesAvailabilities: self::gameAvailabilitiesFromMap($gamesAvailabilities->all()),
            gamesShiniesAvailabilities: self::gameAvailabilitiesFromMap($gamesShiniesAvailabilities->all()),
            gameBundlesAvailabilities: self::gameBundleAvailabilitiesFromMap($gameBundlesAvailabilities->all()),
            gameBundlesShiniesAvailabilities: self::gameBundleAvailabilitiesFromMap(
                $gameBundlesShiniesAvailabilities->all(),
            ),
        );
    }

    /**
     * @param bool[] $map
     *
     * @return GameAvailabilityResponse[]
     */
    private static function gameAvailabilitiesFromMap(array $map): array
    {
        $availabilities = [];
        foreach ($map as $slug => $isAvailable) {
            $availabilities[] = new GameAvailabilityResponse(
                game: new GameSlugResponse(slug: (string) $slug),
                isAvailable: $isAvailable,
            );
        }

        return $availabilities;
    }

    /**
     * @param bool[] $map
     *
     * @return GameBundleAvailabilityResponse[]
     */
    private static function gameBundleAvailabilitiesFromMap(array $map): array
    {
        $availabilities = [];
        foreach ($map as $slug => $isAvailable) {
            $availabilities[] = new GameBundleAvailabilityResponse(
                gameBundle: new GameBundleSlugResponse(slug: (string) $slug),
                isAvailable: $isAvailable,
            );
        }

        return $availabilities;
    }
}
```

Save as `src/Factory/PokemonAvailabilitiesResponseFactory.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/Factory/PokemonAvailabilitiesResponseFactory.php`

Expected: `No syntax errors detected`.

---

### Task 10: Update unit test for PokemonAvailabilitiesResponseFactory

**Files:**
- Modify: `tests/src/Unit/Factory/PokemonAvailabilitiesResponseFactoryTest.php`

Tests use **distinct slugs and distinct boolean values per availability type** so any field swap or boolean inversion (likely mutations) is caught. A multi-entry test locks the ordering, and a numeric-key test covers the `(string)` cast.

- [ ] **Step 1: Replace the full test file content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\DTO\GamesAvailabilities;
use App\DTO\GamesShiniesAvailabilities;
use App\DTO\Response\GameAvailabilityResponse;
use App\DTO\Response\GameBundleAvailabilityResponse;
use App\Factory\PokemonAvailabilitiesResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonAvailabilitiesResponseFactory::class)]
final class PokemonAvailabilitiesResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromAvailabilitiesBuildsResponseWithCorrectFieldMapping(): void
    {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            new GamesAvailabilities(['games-key' => true]),
            new GamesShiniesAvailabilities(['shinies-key' => false]),
            new GameBundlesAvailabilities(['bundles-key' => true]),
            new GameBundlesShiniesAvailabilities(['bundlesshinies-key' => false]),
        );

        self::assertCount(1, $response->gamesAvailabilities);
        self::assertInstanceOf(GameAvailabilityResponse::class, $response->gamesAvailabilities[0]);
        self::assertSame('games-key', $response->gamesAvailabilities[0]->game->slug);
        self::assertTrue($response->gamesAvailabilities[0]->isAvailable);

        self::assertCount(1, $response->gamesShiniesAvailabilities);
        self::assertInstanceOf(GameAvailabilityResponse::class, $response->gamesShiniesAvailabilities[0]);
        self::assertSame('shinies-key', $response->gamesShiniesAvailabilities[0]->game->slug);
        self::assertFalse($response->gamesShiniesAvailabilities[0]->isAvailable);

        self::assertCount(1, $response->gameBundlesAvailabilities);
        self::assertInstanceOf(GameBundleAvailabilityResponse::class, $response->gameBundlesAvailabilities[0]);
        self::assertSame('bundles-key', $response->gameBundlesAvailabilities[0]->gameBundle->slug);
        self::assertTrue($response->gameBundlesAvailabilities[0]->isAvailable);

        self::assertCount(1, $response->gameBundlesShiniesAvailabilities);
        self::assertInstanceOf(GameBundleAvailabilityResponse::class, $response->gameBundlesShiniesAvailabilities[0]);
        self::assertSame('bundlesshinies-key', $response->gameBundlesShiniesAvailabilities[0]->gameBundle->slug);
        self::assertFalse($response->gameBundlesShiniesAvailabilities[0]->isAvailable);
    }

    #[Test]
    public function fromAvailabilitiesPreservesMapOrder(): void
    {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            new GamesAvailabilities(['x' => true, 'y' => false, 'omegaruby' => true]),
            new GamesShiniesAvailabilities([]),
            new GameBundlesAvailabilities(['goldsilvercrystal' => false, 'xy' => true]),
            new GameBundlesShiniesAvailabilities([]),
        );

        self::assertCount(3, $response->gamesAvailabilities);
        self::assertSame('x', $response->gamesAvailabilities[0]->game->slug);
        self::assertTrue($response->gamesAvailabilities[0]->isAvailable);
        self::assertSame('y', $response->gamesAvailabilities[1]->game->slug);
        self::assertFalse($response->gamesAvailabilities[1]->isAvailable);
        self::assertSame('omegaruby', $response->gamesAvailabilities[2]->game->slug);
        self::assertTrue($response->gamesAvailabilities[2]->isAvailable);

        self::assertCount(2, $response->gameBundlesAvailabilities);
        self::assertSame('goldsilvercrystal', $response->gameBundlesAvailabilities[0]->gameBundle->slug);
        self::assertFalse($response->gameBundlesAvailabilities[0]->isAvailable);
        self::assertSame('xy', $response->gameBundlesAvailabilities[1]->gameBundle->slug);
        self::assertTrue($response->gameBundlesAvailabilities[1]->isAvailable);
    }

    #[Test]
    public function fromAvailabilitiesCastsNumericSlugsToString(): void
    {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            new GamesAvailabilities(['123' => true]),
            new GamesShiniesAvailabilities([]),
            new GameBundlesAvailabilities(['456' => false]),
            new GameBundlesShiniesAvailabilities([]),
        );

        self::assertSame('123', $response->gamesAvailabilities[0]->game->slug);
        self::assertSame('456', $response->gameBundlesAvailabilities[0]->gameBundle->slug);
    }

    #[Test]
    public function fromAvailabilitiesHandlesEmptyAvailabilities(): void
    {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            new GamesAvailabilities([]),
            new GamesShiniesAvailabilities([]),
            new GameBundlesAvailabilities([]),
            new GameBundlesShiniesAvailabilities([]),
        );

        self::assertSame([], $response->gamesAvailabilities);
        self::assertSame([], $response->gamesShiniesAvailabilities);
        self::assertSame([], $response->gameBundlesAvailabilities);
        self::assertSame([], $response->gameBundlesShiniesAvailabilities);
    }
}
```

Save as `tests/src/Unit/Factory/PokemonAvailabilitiesResponseFactoryTest.php`.

- [ ] **Step 2: Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/PokemonAvailabilitiesResponseFactoryTest.php`

Expected: 4 tests pass, 0 failures.

---

### Task 11: Update integration test for the endpoint

**Files:**
- Modify: `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php`

Only `testPokemonAvailabilities()` changes — the other test methods stay exactly as they are. The new assertions navigate the nested structure: collect the slugs of each list, then check presence/absence as before.

- [ ] **Step 1: Replace the `testPokemonAvailabilities()` method**

In `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php`, replace the whole `testPokemonAvailabilities()` method with:

```php
    public function testPokemonAvailabilities(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega/availabilities');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var ?array<string, array<int, array{game?: array{slug: string}, game_bundle?: array{slug: string}, is_available: bool}>> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('games_availabilities', $data);
        $gamesSlugs = $this->getGameSlugs($data['games_availabilities']);
        $this->assertNotContains('blue', $gamesSlugs);
        $this->assertNotContains('gold', $gamesSlugs);
        $this->assertContains('x', $gamesSlugs);

        $this->assertArrayHasKey('games_shinies_availabilities', $data);
        $gamesShiniesSlugs = $this->getGameSlugs($data['games_shinies_availabilities']);
        $this->assertNotContains('blue', $gamesShiniesSlugs);
        $this->assertNotContains('gold', $gamesShiniesSlugs);
        $this->assertContains('x', $gamesShiniesSlugs);

        $this->assertArrayHasKey('game_bundles_availabilities', $data);
        $gameBundlesSlugs = $this->getGameBundleSlugs($data['game_bundles_availabilities']);
        $this->assertContains('goldsilvercrystal', $gameBundlesSlugs);

        $this->assertArrayHasKey('game_bundles_shinies_availabilities', $data);
        $gameBundlesShiniesSlugs = $this->getGameBundleSlugs($data['game_bundles_shinies_availabilities']);
        $this->assertContains('goldsilvercrystal', $gameBundlesShiniesSlugs);

        foreach ($data as $availabilities) {
            foreach ($availabilities as $availability) {
                $this->assertArrayHasKey('is_available', $availability);
                $this->assertIsBool($availability['is_available']);
            }
        }
    }

    /**
     * @param array<int, array{game?: array{slug: string}, game_bundle?: array{slug: string}, is_available: bool}> $availabilities
     *
     * @return string[]
     */
    private function getGameSlugs(array $availabilities): array
    {
        $slugs = [];
        foreach ($availabilities as $availability) {
            $this->assertArrayHasKey('game', $availability);
            $slugs[] = $availability['game']['slug'];
        }

        return $slugs;
    }

    /**
     * @param array<int, array{game?: array{slug: string}, game_bundle?: array{slug: string}, is_available: bool}> $availabilities
     *
     * @return string[]
     */
    private function getGameBundleSlugs(array $availabilities): array
    {
        $slugs = [];
        foreach ($availabilities as $availability) {
            $this->assertArrayHasKey('game_bundle', $availability);
            $slugs[] = $availability['game_bundle']['slug'];
        }

        return $slugs;
    }
```

Keep `testPokemonAvailabilitiesNotFound()` and all other methods unchanged.

- [ ] **Step 2: Run the integration test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php`

Expected: 6 tests pass, 0 failures.

---

### Task 12: Update doc/endpoints.md

**Files:**
- Modify: `doc/endpoints.md`

- [ ] **Step 1: Remove the camelCase exception from the intro note**

Replace:

```markdown
- Toutes les réponses JSON sont en `Content-Type: application/json`. Les clés sont en `snake_case` (via `#[SerializedName]`), **sauf** la réponse de `/debogage/pokemon/{slug}/availabilities` qui est en `camelCase`.
```

With:

```markdown
- Toutes les réponses JSON sont en `Content-Type: application/json`. Les clés sont en `snake_case` (via `#[SerializedName]`).
```

- [ ] **Step 2: Update section 35 (`GET /debogage/pokemon/{slug}/availabilities`)**

Replace the description line and the example response of section 35 with:

```markdown
Disponibilités calculées d'un pokémon, par jeu et par bundle (normal et shiny). Chaque liste contient des objets `{ game | game_bundle, is_available }`.

Exemple de requête :

​```bash
curl -u web:douze http://web:8080/debogage/pokemon/venusaur-mega/availabilities
​```

Exemple de réponse (`200`, tronquée) :

​```json
{
  "games_availabilities": [
    { "game": { "slug": "x" }, "is_available": true },
    { "game": { "slug": "y" }, "is_available": true },
    { "game": { "slug": "omegaruby" }, "is_available": true },
    { "game": { "slug": "alphasapphire" }, "is_available": true }
  ],
  "games_shinies_availabilities": [
    { "game": { "slug": "x" }, "is_available": true },
    { "game": { "slug": "y" }, "is_available": true }
  ],
  "game_bundles_availabilities": [
    { "game_bundle": { "slug": "goldsilvercrystal" }, "is_available": false },
    { "game_bundle": { "slug": "xy" }, "is_available": true }
  ],
  "game_bundles_shinies_availabilities": [
    { "game_bundle": { "slug": "goldsilvercrystal" }, "is_available": false },
    { "game_bundle": { "slug": "xy" }, "is_available": true }
  ]
}
​```
```

(The `​```` fences above are shown escaped — write normal fenced blocks in the actual file.)

---

### Task 13: Run full quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all unit tests**

Run: `make tests-unit`

Expected: All unit tests pass, 0 failures.

- [ ] **Step 2: Run all integration tests**

Run: `make tests-integration`

Expected: All integration tests pass, 0 failures — in particular `DebugPokemonControllerTest`.

- [ ] **Step 3: Run code quality checks**

Run: `make quality`

Expected: All quality checks green (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint).

- [ ] **Step 4: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% line coverage and 100% MSI, including the three new DTOs and the reworked factory.

---

## Self-Review

**Spec coverage:**
- ✅ Last non-migrated endpoint (`GET /debogage/pokemon/{slug}/availabilities`) restructured to nested object format (issue #256)
- ✅ snake_case keys via `#[SerializedName]` — camelCase exception removed
- ✅ Every new class except Controller/Repository has a dedicated unit test (`GameSlugResponse`, `GameAvailabilityResponse`, `GameBundleAvailabilityResponse`, reworked `PokemonAvailabilitiesResponse` + Factory)
- ✅ Mutation-resistant tests: distinct slugs/booleans per field, ordering locked, `(string)` cast covered, empty-map case covered
- ✅ Controller untouched (factory signature unchanged) — Deptrac layers respected (Factory → DTO only)
- ✅ `doc/endpoints.md` updated (intro note + section 35)

**Placeholder scan:** No TBDs, no incomplete steps.

**Type consistency:**
- `GameAvailabilityResponse(game: GameSlugResponse, isAvailable: bool)` used identically in Tasks 3, 8, 9, 10 ✓
- `GameBundleAvailabilityResponse(gameBundle: GameBundleSlugResponse, isAvailable: bool)` used identically in Tasks 5, 8, 9, 10 ✓
- `PokemonAvailabilitiesResponse` keeps its four constructor argument names — controller and factory signatures unchanged ✓

---

## Next Steps (not in this plan)

Breaking change limited to a debug endpoint:

1. **pokenini-back / pokenini-web** — no known usage of this debug endpoint; verify with a quick grep before deploying.
2. **Issue #256** — with this migration, every response-bearing endpoint uses the DTO + Factory + Serializer pattern in snake_case; the issue can be closed after deployment.
