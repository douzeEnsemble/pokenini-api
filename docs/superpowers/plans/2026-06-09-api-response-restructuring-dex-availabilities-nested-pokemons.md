# API Response Restructuring (GET /debogage/dex/{slug}/availabilities — Nested Pokemon Objects) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restructure `DexAvailabilitiesResponse` so the flat `pokemons: string[]` array becomes `PokemonSlugResponse[]`, aligning with the object-oriented response pattern used across the API (issue #256).

**Architecture:** `PokemonSlugResponse` already exists. Modify `DexAvailabilitiesResponse` to update the phpDoc type from `string[]` to `PokemonSlugResponse[]`. Update `DexAvailabilitiesResponseFactory::fromDexAvailabilities()` to wrap each entity slug in a `PokemonSlugResponse`. Update unit and integration tests to reflect the new JSON shape. No changes to `DebugDexController`, `DexAvailabilitiesService`, or entities.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine ORM entities, Symfony Serializer

---

## Current response structure

```json
{
  "pokemons": ["bulbasaur", "ivysaur", "venusaur"]
}
```

## Target response structure

```json
{
  "pokemons": [{"slug": "bulbasaur"}, {"slug": "ivysaur"}, {"slug": "venusaur"}]
}
```

---

## File Structure

**Modify:**
- `src/DTO/Response/DexAvailabilitiesResponse.php` — change phpDoc from `string[]` to `PokemonSlugResponse[]`
- `src/Factory/DexAvailabilitiesResponseFactory.php` — build `PokemonSlugResponse` instead of extracting a plain string
- `tests/src/Unit/DTO/Response/DexAvailabilitiesResponseTest.php` — update to pass and assert `PokemonSlugResponse` objects
- `tests/src/Unit/Factory/DexAvailabilitiesResponseFactoryTest.php` — assert `PokemonSlugResponse` instances instead of plain strings
- `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php` — update `testDexAvailabilities()` for the new JSON shape

---

## Tasks

### Task 1: Update DexAvailabilitiesResponseTest and DexAvailabilitiesResponse DTO

**Files:**
- Modify: `tests/src/Unit/DTO/Response/DexAvailabilitiesResponseTest.php`
- Modify: `src/DTO/Response/DexAvailabilitiesResponse.php`

- [ ] **Step 1: Update the unit test to use PokemonSlugResponse objects**

Replace the full content of `tests/src/Unit/DTO/Response/DexAvailabilitiesResponseTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexAvailabilitiesResponse;
use App\DTO\Response\PokemonSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailabilitiesResponse::class)]
final class DexAvailabilitiesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $pokemon1 = new PokemonSlugResponse(slug: 'bulbasaur');
        $pokemon2 = new PokemonSlugResponse(slug: 'ivysaur');

        $response = new DexAvailabilitiesResponse(
            pokemons: [$pokemon1, $pokemon2],
        );

        self::assertCount(2, $response->pokemons);
        self::assertContainsOnly(PokemonSlugResponse::class, $response->pokemons);
        self::assertSame('bulbasaur', $response->pokemons[0]->slug);
        self::assertSame('ivysaur', $response->pokemons[1]->slug);
    }

    #[Test]
    public function constructorAcceptsEmptyArray(): void
    {
        $response = new DexAvailabilitiesResponse(
            pokemons: [],
        );

        self::assertSame([], $response->pokemons);
    }
}
```

- [ ] **Step 2: Update DexAvailabilitiesResponse DTO phpDoc**

Replace the full content of `src/DTO/Response/DexAvailabilitiesResponse.php` with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class DexAvailabilitiesResponse
{
    /**
     * @param PokemonSlugResponse[] $pokemons
     */
    public function __construct(
        public readonly array $pokemons,
    ) {}
}
```

- [ ] **Step 3: Run unit tests for the DTO**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/DexAvailabilitiesResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 2: Update DexAvailabilitiesResponseFactory and its unit test (TDD)

**Files:**
- Modify: `tests/src/Unit/Factory/DexAvailabilitiesResponseFactoryTest.php`
- Modify: `src/Factory/DexAvailabilitiesResponseFactory.php`

- [ ] **Step 1: Update the factory test to expect PokemonSlugResponse instances**

Replace the full content of `tests/src/Unit/Factory/DexAvailabilitiesResponseFactoryTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\PokemonSlugResponse;
use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Factory\DexAvailabilitiesResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailabilitiesResponseFactory::class)]
final class DexAvailabilitiesResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromDexAvailabilitiesExtractsPokemonSlugsInOrder(): void
    {
        $pokemon1 = new Pokemon();
        $pokemon1->slug = 'bulbasaur';

        $pokemon2 = new Pokemon();
        $pokemon2->slug = 'ivysaur';

        $result = DexAvailabilitiesResponseFactory::fromDexAvailabilities([
            DexAvailability::create($pokemon1, new Dex()),
            DexAvailability::create($pokemon2, new Dex()),
        ]);

        self::assertCount(2, $result->pokemons);
        self::assertContainsOnly(PokemonSlugResponse::class, $result->pokemons);
        self::assertSame('bulbasaur', $result->pokemons[0]->slug);
        self::assertSame('ivysaur', $result->pokemons[1]->slug);
    }

    #[Test]
    public function fromDexAvailabilitiesHandlesEmptyArray(): void
    {
        $result = DexAvailabilitiesResponseFactory::fromDexAvailabilities([]);

        self::assertSame([], $result->pokemons);
    }
}
```

- [ ] **Step 2: Run the factory test to verify it FAILS**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/DexAvailabilitiesResponseFactoryTest.php`

Expected: `fromDexAvailabilitiesExtractsPokemonSlugsInOrder` FAILS because the factory still returns `string[]`, not `PokemonSlugResponse[]`.

- [ ] **Step 3: Update DexAvailabilitiesResponseFactory to build PokemonSlugResponse objects**

Replace the full content of `src/Factory/DexAvailabilitiesResponseFactory.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexAvailabilitiesResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\Entity\DexAvailability;

final class DexAvailabilitiesResponseFactory
{
    /**
     * @param DexAvailability[] $dexAvailabilities
     */
    public static function fromDexAvailabilities(array $dexAvailabilities): DexAvailabilitiesResponse
    {
        $pokemons = array_map(
            static fn (DexAvailability $dexAvailability): PokemonSlugResponse => new PokemonSlugResponse(
                slug: $dexAvailability->pokemon->slug,
            ),
            $dexAvailabilities
        );

        return new DexAvailabilitiesResponse(pokemons: $pokemons);
    }
}
```

- [ ] **Step 4: Run the factory test to verify it PASSES**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/DexAvailabilitiesResponseFactoryTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 3: Update integration test DebugDexControllerTest

**Files:**
- Modify: `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php`

- [ ] **Step 1: Update testDexAvailabilities() for the new JSON shape**

In `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php`, replace only the `testDexAvailabilities` method with:

```php
#[Test]
public function testDexAvailabilities(): void
{
    $this->apiRequest('GET', '/debogage/dex/redgreenblueyellow/availabilities');

    $this->assertJsonResponseIsOK();

    $content = $this->getClientResponseContent();

    $this->assertStringNotContainsString('__', $content);

    $this->assertJson($content);

    /** @var null|array{pokemons: array{slug: string}[]} $data */
    $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

    $this->assertNotNull($data);

    $this->assertArrayHasKey('pokemons', $data);

    $slugs = array_column($data['pokemons'], 'slug');
    $this->assertContains('bulbasaur', $slugs);
    $this->assertContains('douze', $slugs);
}
```

- [ ] **Step 2: Run the integration tests for this controller**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/Debug/DebugDexControllerTest.php`

Expected: 4 tests pass, 0 failures (testDex, testDexNotFound, testDexAvailabilities, testDexAvailabilitiesNotFound).

---

### Task 4: Run full quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all unit tests**

Run: `make tests-unit`

Expected: All unit tests pass, 0 failures.

- [ ] **Step 2: Run all integration tests**

Run: `make tests-integration`

Expected: All integration tests pass, 0 failures.

- [ ] **Step 3: Run code quality checks**

Run: `make quality`

Expected: All quality checks pass (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint).

- [ ] **Step 4: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% line coverage for all modified code, 100% MSI for all modified code, all checks green.

- [ ] **Step 5: Document completion**

Summary of changes:
- ✅ Updated `DexAvailabilitiesResponse` phpDoc: `string[]` → `PokemonSlugResponse[]`
- ✅ Updated `DexAvailabilitiesResponseFactory`: wraps each entity slug in `new PokemonSlugResponse(slug: ...)`
- ✅ Updated `DexAvailabilitiesResponseTest`: constructs and asserts `PokemonSlugResponse` instances
- ✅ Updated `DexAvailabilitiesResponseFactoryTest`: asserts `PokemonSlugResponse` instances and individual slug values
- ✅ Updated `DebugDexControllerTest::testDexAvailabilities()`: extracts slugs via `array_column` before asserting

**Status:** `GET /debogage/dex/{slug}/availabilities` fully migrated to nested `PokemonSlugResponse[]` pattern.

---

## Next Steps (not in this plan)

Once this plan is complete:

- Nest `PokemonDataResponse.family_lead_slug: ?string` → `family_lead: ?PokemonSlugResponse` (affects `AlbumPokemonResponseFactory`, `ElectionEloResponseFactory`, `ElectionPokemonResponseFactory`)
- Nest `PokemonDataResponse.original_game_bundle_slug: ?string` and `game_bundles/game_bundles_shiny: string[]` → would require a new `GameBundleSlugResponse` DTO
