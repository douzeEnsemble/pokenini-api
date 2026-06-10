# API Response Restructuring (PokemonData — Nested GameBundle Slugs) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wrap the flat `game_bundles: string[]` and `game_bundles_shiny: string[]` arrays in `PokemonDataResponse` into `GameBundleSlugResponse[]` objects, so every item has `{"slug": "…"}` shape — the same pattern already applied to `DexAvailabilitiesResponse.pokemons` (plan `2026-06-09`, issue #256).

**Architecture:** Create a new immutable `GameBundleSlugResponse` DTO. Update `PokemonDataResponse` to change the phpDoc for `gameBundles` and `gameBundlesShiny` from `array<string>` to `array<GameBundleSlugResponse>`. Update `AlbumPokemonResponseFactory.buildPokemon()` to wrap each string slug in a `GameBundleSlugResponse` via `array_map`. Update all unit tests and the `AlbumData` integration-test helper. No changes to any controller, repository, service, entity, or SQL file.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Response shape change

**Before** (current serialized output for `GET /album/{trainerExternalId}/{dexSlug}`):
```json
{
  "pokemons": [
    {
      "pokemon": {
        "slug": "bulbasaur",
        "game_bundles": ["redgreenblueyellow", "goldsilvercrystal"],
        "game_bundles_shiny": ["redgreenblueyellow"]
      },
      "catch_state": { "slug": "no", "name": "No", "french_name": "Non" },
      "forms": null,
      "types": { "primary": { "slug": "grass", "name": "Grass", "french_name": "Plante" }, "secondary": null }
    }
  ]
}
```

**After:**
```json
{
  "pokemons": [
    {
      "pokemon": {
        "slug": "bulbasaur",
        "game_bundles": [{"slug": "redgreenblueyellow"}, {"slug": "goldsilvercrystal"}],
        "game_bundles_shiny": [{"slug": "redgreenblueyellow"}]
      },
      "catch_state": { "slug": "no", "name": "No", "french_name": "Non" },
      "forms": null,
      "types": { "primary": { "slug": "grass", "name": "Grass", "french_name": "Plante" }, "secondary": null }
    }
  ]
}
```

When there are no game bundles, both arrays remain empty (`[]`), which is valid for both `string[]` and `GameBundleSlugResponse[]`:
```json
{
  "pokemon": {
    "game_bundles": [],
    "game_bundles_shiny": []
  }
}
```

---

## File Structure

**Create:**
- `src/DTO/Response/GameBundleSlugResponse.php` — immutable DTO with a single `slug: string` property
- `tests/src/Unit/DTO/Response/GameBundleSlugResponseTest.php` — unit tests for `GameBundleSlugResponse`

**Modify:**
- `src/DTO/Response/PokemonDataResponse.php` — change `@param array<string>` to `@param array<GameBundleSlugResponse>` for both `$gameBundles` and `$gameBundlesShiny`
- `src/Factory/AlbumPokemonResponseFactory.php` — wrap each slug string in a `GameBundleSlugResponse` using `array_map`
- `tests/src/Unit/DTO/Response/PokemonDataResponseTest.php` — pass and assert `GameBundleSlugResponse` instances instead of plain strings
- `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php` — assert `GameBundleSlugResponse` instances in the `gameBundles`/`gameBundlesShiny` assertions
- `tests/src/Common/Data/AlbumData.php` — change `'redgreenblueyellow'` string items to `['slug' => 'redgreenblueyellow']` sub-arrays in every `game_bundles` / `game_bundles_shiny` entry

---

## Tasks

### Task 1: Create GameBundleSlugResponse DTO and its unit test

**Files:**
- Create: `src/DTO/Response/GameBundleSlugResponse.php`
- Create: `tests/src/Unit/DTO/Response/GameBundleSlugResponseTest.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class GameBundleSlugResponse
{
    public function __construct(
        public readonly string $slug,
    ) {}
}
```

Save as `src/DTO/Response/GameBundleSlugResponse.php`.

- [ ] **Step 2: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundleSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleSlugResponse::class)]
final class GameBundleSlugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesSlug(): void
    {
        $response = new GameBundleSlugResponse(slug: 'redgreenblueyellow');

        self::assertSame('redgreenblueyellow', $response->slug);
    }

    #[Test]
    public function slugIsReadonly(): void
    {
        $response = new GameBundleSlugResponse(slug: 'goldsilvercrystal');

        self::assertSame('goldsilvercrystal', $response->slug);
    }
}
```

Save as `tests/src/Unit/DTO/Response/GameBundleSlugResponseTest.php`.

---

### Task 2: Update PokemonDataResponse phpDoc and its unit test

**Files:**
- Modify: `src/DTO/Response/PokemonDataResponse.php`
- Modify: `tests/src/Unit/DTO/Response/PokemonDataResponseTest.php`

- [ ] **Step 1: Update PokemonDataResponse phpDoc**

In `src/DTO/Response/PokemonDataResponse.php`, change:
```php
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     *
     * @param array<string> $gameBundles
     * @param array<string> $gameBundlesShiny
     */
```
to:
```php
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     *
     * @param array<GameBundleSlugResponse> $gameBundles
     * @param array<GameBundleSlugResponse> $gameBundlesShiny
     */
```

- [ ] **Step 2: Update PokemonDataResponseTest to use GameBundleSlugResponse objects**

Replace the full content of `tests/src/Unit/DTO/Response/PokemonDataResponseTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\PokemonDataResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDataResponse::class)]
final class PokemonDataResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $gameBundles = [
            new GameBundleSlugResponse(slug: 'rby'),
            new GameBundleSlugResponse(slug: 'gsc'),
        ];
        $gameBundlesShiny = [
            new GameBundleSlugResponse(slug: 'rby'),
        ];

        $response = new PokemonDataResponse(
            slug: 'pikachu',
            name: 'Pikachu',
            frenchName: 'Pikachu',
            nationalDexNumber: 25,
            regionalDexNumber: 35,
            simplifiedName: 'Pikachu Base',
            formsLabel: 'Original Cap',
            simplifiedFrenchName: 'Pikachu Base FR',
            formsFrenchLabel: 'Casquette Originale',
            icon: 'pikachu.png',
            familyOrder: 1,
            familyLeadSlug: 'pichu',
            originalGameBundleSlug: 'rby',
            orderNumber: '0025.001',
            gameBundles: $gameBundles,
            gameBundlesShiny: $gameBundlesShiny,
        );

        self::assertSame('pikachu', $response->slug);
        self::assertSame('Pikachu', $response->name);
        self::assertSame('Pikachu', $response->frenchName);
        self::assertSame(25, $response->nationalDexNumber);
        self::assertSame(35, $response->regionalDexNumber);
        self::assertSame('Pikachu Base', $response->simplifiedName);
        self::assertSame('Original Cap', $response->formsLabel);
        self::assertSame('Pikachu Base FR', $response->simplifiedFrenchName);
        self::assertSame('Casquette Originale', $response->formsFrenchLabel);
        self::assertSame('pikachu.png', $response->icon);
        self::assertSame(1, $response->familyOrder);
        self::assertSame('pichu', $response->familyLeadSlug);
        self::assertSame('rby', $response->originalGameBundleSlug);
        self::assertSame('0025.001', $response->orderNumber);
        self::assertCount(2, $response->gameBundles);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->gameBundles[0]);
        self::assertSame('rby', $response->gameBundles[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->gameBundles[1]);
        self::assertSame('gsc', $response->gameBundles[1]->slug);
        self::assertCount(1, $response->gameBundlesShiny);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->gameBundlesShiny[0]);
        self::assertSame('rby', $response->gameBundlesShiny[0]->slug);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new PokemonDataResponse(
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            nationalDexNumber: 1,
            regionalDexNumber: null,
            simplifiedName: null,
            formsLabel: null,
            simplifiedFrenchName: null,
            formsFrenchLabel: null,
            icon: null,
            familyOrder: 1,
            familyLeadSlug: null,
            originalGameBundleSlug: null,
            orderNumber: '0001.001',
            gameBundles: [],
            gameBundlesShiny: [],
        );

        self::assertNull($response->regionalDexNumber);
        self::assertNull($response->simplifiedName);
        self::assertNull($response->formsLabel);
        self::assertNull($response->simplifiedFrenchName);
        self::assertNull($response->formsFrenchLabel);
        self::assertNull($response->icon);
        self::assertNull($response->familyLeadSlug);
        self::assertNull($response->originalGameBundleSlug);
        self::assertSame([], $response->gameBundles);
        self::assertSame([], $response->gameBundlesShiny);
    }
}
```

---

### Task 3: Update AlbumPokemonResponseFactory to wrap slugs in GameBundleSlugResponse

**Files:**
- Modify: `src/Factory/AlbumPokemonResponseFactory.php`

- [ ] **Step 1: Add GameBundleSlugResponse import and update buildPokemon()**

In `src/Factory/AlbumPokemonResponseFactory.php`, add the import:
```php
use App\DTO\Response\GameBundleSlugResponse;
```

Then replace the `$gameBundles` and `$gameBundlesShiny` block inside `buildPokemon()`:

Old code:
```php
        /** @var array<string> $gameBundles */
        $gameBundles = (array) $row['game_bundles'];

        /** @var array<string> $gameBundlesShiny */
        $gameBundlesShiny = (array) $row['game_bundles_shiny'];
```

New code:
```php
        /** @var array<string> $gameBundleSlugs */
        $gameBundleSlugs = (array) $row['game_bundles'];

        /** @var array<GameBundleSlugResponse> $gameBundles */
        $gameBundles = array_map(
            static fn (string $slug): GameBundleSlugResponse => new GameBundleSlugResponse(slug: $slug),
            $gameBundleSlugs,
        );

        /** @var array<string> $gameBundlesShinySlugs */
        $gameBundlesShinySlugs = (array) $row['game_bundles_shiny'];

        /** @var array<GameBundleSlugResponse> $gameBundlesShiny */
        $gameBundlesShiny = array_map(
            static fn (string $slug): GameBundleSlugResponse => new GameBundleSlugResponse(slug: $slug),
            $gameBundlesShinySlugs,
        );
```

---

### Task 4: Update AlbumPokemonResponseFactoryTest gameBundles assertions

**Files:**
- Modify: `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

- [ ] **Step 1: Add GameBundleSlugResponse import**

Add to the imports section:
```php
use App\DTO\Response\GameBundleSlugResponse;
```

- [ ] **Step 2: Update fromSqlRowBuildsPokemonSubObject assertions**

Replace:
```php
        self::assertSame(['redgreenblueyellow', 'goldsilvercrystal'], $result->pokemon->gameBundles);
        self::assertSame(['redgreenblueyellow'], $result->pokemon->gameBundlesShiny);
```

With:
```php
        self::assertCount(2, $result->pokemon->gameBundles);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles[0]);
        self::assertSame('redgreenblueyellow', $result->pokemon->gameBundles[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles[1]);
        self::assertSame('goldsilvercrystal', $result->pokemon->gameBundles[1]->slug);
        self::assertCount(1, $result->pokemon->gameBundlesShiny);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundlesShiny[0]);
        self::assertSame('redgreenblueyellow', $result->pokemon->gameBundlesShiny[0]->slug);
```

- [ ] **Step 3: Update fromSqlRowCastsNullGameBundlesToEmptyArray assertions**

The null-cast test (`fromSqlRowCastsNullGameBundlesToEmptyArray`) passes `game_bundles: null` which becomes `(array) null = []`. After `array_map` over `[]`, the result is still `[]`. The assertions `self::assertSame([], ...)` remain valid with no change needed.

Verify by reading the test: the assertions are:
```php
        self::assertSame([], $result->pokemon->gameBundles);
        self::assertSame([], $result->pokemon->gameBundlesShiny);
```
These pass unchanged since an empty `GameBundleSlugResponse[]` equals `[]`.

---

### Task 5: Update AlbumData integration-test helper

**Files:**
- Modify: `tests/src/Common/Data/AlbumData.php`

- [ ] **Step 1: Replace all string game-bundle slugs with sub-arrays**

In `tests/src/Common/Data/AlbumData.php`, every non-empty `game_bundles` / `game_bundles_shiny` entry currently holds plain strings:

```php
'game_bundles' => [
    'redgreenblueyellow',
    'goldsilvercrystal',
],
'game_bundles_shiny' => [
    'redgreenblueyellow',
],
```

After the migration, the Serializer outputs `{"slug": "..."}` objects. When decoded to a PHP associative array they become `['slug' => 'redgreenblueyellow']`. Update every occurrence to:

```php
'game_bundles' => [
    ['slug' => 'redgreenblueyellow'],
    ['slug' => 'goldsilvercrystal'],
],
'game_bundles_shiny' => [
    ['slug' => 'redgreenblueyellow'],
],
```

Empty arrays (`[]`) require no change.

There are 11 non-empty `game_bundles` entries and 6 non-empty `game_bundles_shiny` entries throughout `AlbumData.php`. Apply the transformation to every one.

---

### Task 6: Verify unit tests pass

- [ ] **Step 1: Run the new GameBundleSlugResponseTest**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/GameBundleSlugResponseTest.php`

Expected: 2 tests pass, 0 failures.

- [ ] **Step 2: Run the updated PokemonDataResponseTest**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/PokemonDataResponseTest.php`

Expected: 2 tests pass, 0 failures.

- [ ] **Step 3: Run the updated AlbumPokemonResponseFactoryTest**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

Expected: All tests pass, 0 failures.

---

### Task 7: Verify integration tests pass

- [ ] **Step 1: Run AlbumIndexControllerTest**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/AlbumIndexControllerTest.php`

Expected: All tests pass, 0 failures. (AlbumData now provides the nested format, the Controller still calls AlbumPokemonResponseFactory which now wraps slugs.)

- [ ] **Step 2: Run AlbumIndexFilteredController tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/AlbumIndexFilteredController/`

Expected: All tests pass, 0 failures.

---

### Task 8: Full quality check

- [ ] **Step 1: Run all unit tests**

Run: `make tu`

Expected: All unit tests pass.

- [ ] **Step 2: Run all integration tests**

Run: `make ti`

Expected: All integration tests pass.

- [ ] **Step 3: Run coverage**

Run: `make coverage`

Expected: 100% code coverage, including the new `GameBundleSlugResponse` class and updated factory path.

- [ ] **Step 4: Run mutation testing**

Run: `make infection`

Expected: 100% MSI.

- [ ] **Step 5: Run full quality checks**

Run: `make quality`

Expected: All quality checks pass (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac).

---

## Summary of all files touched

| File | Action |
|------|--------|
| `src/DTO/Response/GameBundleSlugResponse.php` | Create |
| `tests/src/Unit/DTO/Response/GameBundleSlugResponseTest.php` | Create |
| `src/DTO/Response/PokemonDataResponse.php` | Modify phpDoc only |
| `src/Factory/AlbumPokemonResponseFactory.php` | Modify `buildPokemon()` |
| `tests/src/Unit/DTO/Response/PokemonDataResponseTest.php` | Modify assertions |
| `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php` | Modify assertions |
| `tests/src/Common/Data/AlbumData.php` | Modify expected nested format |

No controller, repository, service, entity, or SQL file is changed.
