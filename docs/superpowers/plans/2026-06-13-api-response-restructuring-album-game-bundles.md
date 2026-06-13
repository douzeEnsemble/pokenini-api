# API Response Restructuring (Album — game_bundles) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move `game_bundle_slugs` comma-string parsing from `AlbumPokemonService.explodesFlatList()` into `AlbumPokemonResponseFactory.buildPokemon()`, making the album endpoint consistent with `ElectionEloResponseFactory` and `ElectionPokemonResponseFactory`.

**Architecture:** Currently the service pre-parses `game_bundle_slugs` (comma-separated SQL string) into PHP arrays before passing data to the factory, via a private `explodesFlatList()` method. This is inconsistent with all election factories, which parse the comma-separated string directly in `buildPokemon()` using `array_values(array_filter(explode(',', ...)))`. After this change, `AlbumPokemonService.get()` returns raw SQL rows (`PokedexRepositoryItems`) without any conversion, and the factory handles all parsing. Test fixtures in `PokemonData` and `AlbumData` must be updated to use the string-keyed format (`game_bundle_slugs`/`game_bundle_shiny_slugs`) instead of pre-parsed PHP arrays.

**Tech Stack:** Symfony 8 / PHP 8.5, PHPUnit, Psalm strict, PHPStan level 9

---

## File Structure

### Modified
- `src/Factory/AlbumPokemonResponseFactory.php` — parse `game_bundle_slugs`/`game_bundle_shiny_slugs` strings directly (replaces `(array) $row['game_bundles']` pattern)
- `src/Service/Album/AlbumPokemonService.php` — remove `explodesFlatList()`, `get()` returns `PokedexRepositoryItems` directly
- `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php` — update `getBulbasaurRow()`/`getDouzeRow()` fixtures; update null test; add 4 new dedicated parsing tests
- `tests/src/Common/Data/PokemonData.php` — all methods: `game_bundles => [...]` → `game_bundle_slugs => '...'`; `game_bundles_shiny => [...]` → `game_bundle_shiny_slugs => '...'`
- `tests/src/Common/Data/AlbumData.php` — inline fixtures: same key rename; `toNestedFormat()`: parse string instead of casting array
- `tests/src/Common/Types/PokedexTypes.php` — remove `PokedexResponseItem` / `PokedexResponseItems`; update `PokedexResponse.pokemons` to `PokedexRepositoryItems`
- `tests/src/Integration/Controller/AlbumIndexControllerTest.php` — add explicit per-item `game_bundles` assertions (foreach checking `slug` key)

---

## Task 1: Update `AlbumPokemonResponseFactoryTest` — fixtures + 4 new parsing tests

**Files:**
- Modify: `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

- [ ] **Step 1: Update `getBulbasaurRow()` — switch to string-keyed format**

In `getBulbasaurRow()`, replace the two `game_bundles`/`game_bundles_shiny` lines (currently PHP arrays) with the comma-string keys the factory will now read directly from SQL:

```php
// Replace this:
'game_bundles' => ['redgreenblueyellow', 'goldsilvercrystal'],
'game_bundles_shiny' => ['redgreenblueyellow'],

// With this:
'game_bundle_slugs' => 'redgreenblueyellow,goldsilvercrystal',
'game_bundle_shiny_slugs' => 'redgreenblueyellow',
```

- [ ] **Step 2: Update `getDouzeRow()` — switch to string-keyed format**

In `getDouzeRow()`, replace the two `game_bundles`/`game_bundles_shiny` lines:

```php
// Replace this:
'game_bundles' => ['un', 'dos', 'tres'],
'game_bundles_shiny' => [],

// With this:
'game_bundle_slugs' => 'un,dos,tres',
'game_bundle_shiny_slugs' => '',
```

- [ ] **Step 3: Update the existing null test — rename and update keys**

Rename `fromSqlRowCastsNullGameBundlesToEmptyArray` to `fromSqlRowParsesNullGameBundleSlugsAsEmptyArrayForBothFields` and update the two keys it overrides:

```php
#[Test]
public function fromSqlRowParsesNullGameBundleSlugsAsEmptyArrayForBothFields(): void
{
    $row = $this->getBulbasaurRow();
    $row['game_bundle_slugs'] = null;
    $row['game_bundle_shiny_slugs'] = null;

    $result = AlbumPokemonResponseFactory::fromSqlRow($row);

    self::assertSame([], $result->pokemon->gameBundles);
    self::assertSame([], $result->pokemon->gameBundlesShiny);
}
```

- [ ] **Step 4: Add `fromSqlRowParsesEmptyGameBundleSlugsAsEmptyArray`**

```php
#[Test]
public function fromSqlRowParsesEmptyGameBundleSlugsAsEmptyArray(): void
{
    $row = $this->getBulbasaurRow();
    $row['game_bundle_slugs'] = '';
    $row['game_bundle_shiny_slugs'] = '';

    $result = AlbumPokemonResponseFactory::fromSqlRow($row);

    self::assertSame([], $result->pokemon->gameBundles);
    self::assertSame([], $result->pokemon->gameBundlesShiny);
}
```

- [ ] **Step 5: Add `fromSqlRowParsesPopulatedGameBundleSlugs`**

```php
#[Test]
public function fromSqlRowParsesPopulatedGameBundleSlugs(): void
{
    $row = $this->getBulbasaurRow();
    $row['game_bundle_slugs'] = 'redgreenblueyellow,goldsilvercrystal';

    $result = AlbumPokemonResponseFactory::fromSqlRow($row);

    self::assertCount(2, $result->pokemon->gameBundles);
    self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles[0]);
    self::assertSame('redgreenblueyellow', $result->pokemon->gameBundles[0]->slug);
    self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles[1]);
    self::assertSame('goldsilvercrystal', $result->pokemon->gameBundles[1]->slug);
}
```

- [ ] **Step 6: Add `fromSqlRowParsesPopulatedGameBundleShinySlugs`**

```php
#[Test]
public function fromSqlRowParsesPopulatedGameBundleShinySlugs(): void
{
    $row = $this->getBulbasaurRow();
    $row['game_bundle_shiny_slugs'] = 'redgreenblueyellow,goldsilvercrystal';

    $result = AlbumPokemonResponseFactory::fromSqlRow($row);

    self::assertCount(2, $result->pokemon->gameBundlesShiny);
    self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundlesShiny[0]);
    self::assertSame('redgreenblueyellow', $result->pokemon->gameBundlesShiny[0]->slug);
    self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundlesShiny[1]);
    self::assertSame('goldsilvercrystal', $result->pokemon->gameBundlesShiny[1]->slug);
}
```

- [ ] **Step 7: Run unit tests — expect failures (factory not yet updated)**

```bash
docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php
```

Expected: FAIL — factory still reads `$row['game_bundles']` which no longer exists in fixtures.

---

## Task 2: Update `AlbumPokemonResponseFactory.buildPokemon()` to parse `game_bundle_slugs`

**Files:**
- Modify: `src/Factory/AlbumPokemonResponseFactory.php:89-103`

- [ ] **Step 1: Replace `(array) $row['game_bundles']` block with string-parsing pattern**

In `buildPokemon()`, replace lines 89–103 (the two `game_bundles` / `game_bundles_shiny` blocks):

```php
// Replace this:
/** @var array<string> $gameBundleSlugs */
$gameBundleSlugs = (array) $row['game_bundles'];

$gameBundles = array_map(
    static fn (string $slug): GameBundleSlugResponse => new GameBundleSlugResponse(slug: $slug),
    $gameBundleSlugs,
);

/** @var array<string> $gameBundlesShinySlugs */
$gameBundlesShinySlugs = (array) $row['game_bundles_shiny'];

$gameBundlesShiny = array_map(
    static fn (string $slug): GameBundleSlugResponse => new GameBundleSlugResponse(slug: $slug),
    $gameBundlesShinySlugs,
);

// With this:
/** @var null|scalar $gameBundleSlugsRaw */
$gameBundleSlugsRaw = $row['game_bundle_slugs'] ?? null;

/** @var array<string> $gameBundleSlugs */
$gameBundleSlugs = array_values(array_filter(explode(',', (string) ($gameBundleSlugsRaw ?? ''))));

$gameBundles = array_map(
    static fn (string $slug): GameBundleSlugResponse => new GameBundleSlugResponse(slug: $slug),
    $gameBundleSlugs,
);

/** @var null|scalar $gameBundleShinySlugRaw */
$gameBundleShinySlugRaw = $row['game_bundle_shiny_slugs'] ?? null;

/** @var array<string> $gameBundleShinySlugs */
$gameBundleShinySlugs = array_values(array_filter(explode(',', (string) ($gameBundleShinySlugRaw ?? ''))));

$gameBundlesShiny = array_map(
    static fn (string $slug): GameBundleSlugResponse => new GameBundleSlugResponse(slug: $slug),
    $gameBundleShinySlugs,
);
```

- [ ] **Step 2: Run unit tests — expect green**

```bash
docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php
```

Expected: all PASS.

---

## Task 3: Remove `explodesFlatList()` from `AlbumPokemonService` — update `PokedexTypes`

**Files:**
- Modify: `src/Service/Album/AlbumPokemonService.php`
- Modify: `tests/src/Common/Types/PokedexTypes.php`

- [ ] **Step 1: Simplify `AlbumPokemonService` — delete `explodesFlatList()` and return repository items directly**

Replace the entire class content with:

```php
<?php

declare(strict_types=1);

namespace App\Service\Album;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\PokedexRepository;

/**
 * @psalm-import-type PokedexRepositoryItems from \App\Tests\Common\Types\PokedexTypes
 */
class AlbumPokemonService
{
    public function __construct(
        private readonly PokedexRepository $pokedexRepository,
    ) {}

    /**
     * @return PokedexRepositoryItems
     */
    public function get(string $trainerExternalId, string $dexSlug, AlbumFilters $albumFilters): array
    {
        return $this->pokedexRepository->getList(
            $trainerExternalId,
            $dexSlug,
            $albumFilters,
        );
    }
}
```

- [ ] **Step 2: Remove `PokedexResponseItem` and `PokedexResponseItems` from `PokedexTypes.php` — update `PokedexResponse`**

In `tests/src/Common/Types/PokedexTypes.php`, remove the two blocks starting with `@psalm-type PokedexResponseItem` and `@psalm-type PokedexResponseItems`, then update `PokedexResponse.pokemons` to reference `PokedexRepositoryItems`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Common\Types;

/**
 * @psalm-type PokedexRepositoryItem = array{
 *  pokemon_slug: string,
 *  pokemon_name: string,
 *  pokemon_national_dex_number: string,
 *  pokemon_simplified_name: string,
 *  pokemon_forms_label: string,
 *  pokemon_french_name: string,
 *  pokemon_simplified_french_name: string,
 *  pokemon_forms_french_label: string,
 *  pokemon_icon: string,
 *  pokemon_family_order: int,
 *  family_lead_slug: string,
 *  category_form_slug: string|null,
 *  category_form_name: string|null,
 *  regional_form_slug: string|null,
 *  regional_form_name: string|null,
 *  special_form_slug: string|null,
 *  special_form_name: string|null,
 *  variant_form_slug: string|null,
 *  variant_form_name: string|null,
 *  catch_state_slug: string|null,
 *  catch_state_name: string|null,
 *  catch_state_french_name: string|null,
 *  catch_state_color: string|null,
 *  pokemon_regional_dex_number: string|null,
 *  primary_type_slug: string,
 *  primary_type_name: string,
 *  primary_type_french_name: string,
 *  primary_type_color: string,
 *  secondary_type_slug: string|null,
 *  secondary_type_name: string|null,
 *  secondary_type_french_name: string|null,
 *  secondary_type_color: string|null,
 *  original_game_bundle_slug: string,
 *  pokemon_order_number: string,
 *  game_bundle_slugs: string|null,
 *  game_bundle_shiny_slugs: string|null,
 * }
 * @psalm-type PokedexRepositoryItems = array<int, PokedexRepositoryItem>
 * @psalm-type PokedexResponseReport = array{
 *  detail: array<int, array{
 *      count: int,
 *      catch_state: array{
 *          slug: string,
 *          name: string,
 *          french_name: string,
 *          color: string
 *      }
 *  }>,
 *  total: int,
 *  total_caught: int,
 *  total_uncaught: int
 * }
 * @psalm-type PokedexResponse = array{
 *  dex: array<string, mixed>|null,
 *  pokemons: PokedexRepositoryItems,
 *  filtered_report: PokedexResponseReport,
 *  report: PokedexResponseReport,
 * }
 *
 * @psalm-suppress UnusedClass
 */
final class PokedexTypes {}
```

- [ ] **Step 3: Check if `AlbumPokemonServiceTest` imports `PokedexResponseItems` — update if needed**

```bash
grep -r 'PokedexResponseItem' tests/src/
```

For each file that still imports `PokedexResponseItems`, replace the import line with `PokedexRepositoryItems`.

Example — if `AlbumPokemonServiceTest.php` has:
```php
 * @psalm-import-type PokedexResponseItems from \App\Tests\Common\Types\PokedexTypes
```
Replace with:
```php
 * @psalm-import-type PokedexRepositoryItems from \App\Tests\Common\Types\PokedexTypes
```
And update the `@var` annotation on `$result` from `PokedexResponseItems` to `PokedexRepositoryItems`.

---

## Task 4: Update `PokemonData` and `AlbumData` test fixtures

**Files:**
- Modify: `tests/src/Common/Data/PokemonData.php`
- Modify: `tests/src/Common/Data/AlbumData.php`

- [ ] **Step 1: Update all methods in `PokemonData.php` — replace PHP arrays with comma strings**

Apply this pattern to **every** method in the class that currently has `game_bundles` / `game_bundles_shiny` keys (`getBulbasaurData`, `getIvysaurData`, `getVenusaurData`, `getCaterpieData`, `getMetapodData`, `getButterfreeData`, `getDouzeData`, `getCharmanderData`, `getCharmeleonData`, `getCharizardData`, `getRattataData`, `getRattataFemaleData`, `getRattataAlolanData`, `getRaticateData`, `getRaticateFemaleData`, `getRaticateAlolanData`, `getRaticateAlolanTotemData`):

```php
// Replace this pattern (example for Bulbasaur — non-empty arrays):
'game_bundles' => [
    'redgreenblueyellow',
    'goldsilvercrystal',
],
'game_bundles_shiny' => [
    'redgreenblueyellow',
    'goldsilvercrystal',
],

// With this (join the array values with comma, or empty string for []):
'game_bundle_slugs' => 'redgreenblueyellow,goldsilvercrystal',
'game_bundle_shiny_slugs' => 'redgreenblueyellow,goldsilvercrystal',

// Example for a Pokémon with empty game_bundles (e.g. Douze):
'game_bundle_slugs' => '',
'game_bundle_shiny_slugs' => '',
```

- [ ] **Step 2: Update inline fixtures in `AlbumData.php` — rename keys**

In `getExpectedHomeContent()` and `getExpectedHomeShinyContent()`, all inline Pokémon arrays (venusaur-f, venusaur-mega, venusaur-gmax, butterfree-f, butterfree-gmax) currently use `game_bundles => [...]`. Replace with the string format. Example for the venusaur variants (non-empty):

```php
// Replace this:
'game_bundles' => [
    'redgreenblueyellow',
    'goldsilvercrystal',
],
'game_bundles_shiny' => [
    'redgreenblueyellow',
    'goldsilvercrystal',
],

// With this:
'game_bundle_slugs' => 'redgreenblueyellow,goldsilvercrystal',
'game_bundle_shiny_slugs' => 'redgreenblueyellow,goldsilvercrystal',
```

For butterfree-f and butterfree-gmax (empty arrays):

```php
// Replace this:
'game_bundles' => [],
'game_bundles_shiny' => [],

// With this:
'game_bundle_slugs' => '',
'game_bundle_shiny_slugs' => '',
```

- [ ] **Step 3: Update `toNestedFormat()` in `AlbumData.php` — parse string instead of casting array**

Replace the two lines that read `(array) $flat['game_bundles']` and `(array) $flat['game_bundles_shiny']` with string-parsing:

```php
// Replace this:
/** @var array<string> $gameBundles */
$gameBundles = (array) $flat['game_bundles'];

/** @var array<string> $gameBundlesShiny */
$gameBundlesShiny = (array) $flat['game_bundles_shiny'];

// With this:
/** @var null|scalar $gameBundleSlugsRaw */
$gameBundleSlugsRaw = $flat['game_bundle_slugs'] ?? null;

/** @var array<string> $gameBundles */
$gameBundles = array_values(array_filter(explode(',', (string) ($gameBundleSlugsRaw ?? ''))));

/** @var null|scalar $gameBundlesShinySlugsRaw */
$gameBundlesShinySlugsRaw = $flat['game_bundle_shiny_slugs'] ?? null;

/** @var array<string> $gameBundlesShiny */
$gameBundlesShiny = array_values(array_filter(explode(',', (string) ($gameBundlesShinySlugsRaw ?? ''))));
```

- [ ] **Step 4: Run all unit + integration tests — expect green**

```bash
docker compose exec php php vendor/bin/phpunit tests/src/Unit/
docker compose exec php php vendor/bin/phpunit tests/src/Integration/
```

Expected: all PASS. If `AlbumPokemonServiceTest` or `AlbumIndexControllerTest` fails, check whether any other inline fixture in `AlbumData.php` still uses the old `game_bundles` key.

---

## Task 5: Add explicit `game_bundles` assertions in `AlbumIndexControllerTest`

**Files:**
- Modify: `tests/src/Integration/Controller/AlbumIndexControllerTest.php`

- [ ] **Step 1: Add a helper method that asserts `game_bundles` structure on every Pokémon item**

Add a private method `assertPokemonsHaveGameBundlesStructure(array $pokemons): void` and call it from each test method that retrieves pokemons:

```php
/**
 * @param array<array<string, mixed>> $pokemons
 */
private function assertPokemonsHaveGameBundlesStructure(array $pokemons): void
{
    foreach ($pokemons as $pokemon) {
        $this->assertArrayHasKey('pokemon', $pokemon);

        /** @var array<string, mixed> $pokemonData */
        $pokemonData = $pokemon['pokemon'];

        $this->assertArrayHasKey('game_bundles', $pokemonData);
        $this->assertIsArray($pokemonData['game_bundles']);

        $this->assertArrayHasKey('game_bundles_shiny', $pokemonData);
        $this->assertIsArray($pokemonData['game_bundles_shiny']);

        /** @var array<string, mixed> $gameBundle */
        foreach ($pokemonData['game_bundles'] as $gameBundle) {
            $this->assertArrayHasKey('slug', $gameBundle);
            $this->assertIsString($gameBundle['slug']);
        }

        /** @var array<string, mixed> $gameBundleShiny */
        foreach ($pokemonData['game_bundles_shiny'] as $gameBundleShiny) {
            $this->assertArrayHasKey('slug', $gameBundleShiny);
            $this->assertIsString($gameBundleShiny['slug']);
        }
    }
}
```

- [ ] **Step 2: Call the helper from `testListUser12RedGreenBlueYellow`**

After the `assertEquals(AlbumData::getExpectedRegGreenBlueYellowNestedContent(...), $pokemons)` assertion in `testListUser12RedGreenBlueYellow`, add:

```php
$this->assertPokemonsHaveGameBundlesStructure($pokemons);
```

(The `$pokemons` variable is already declared as `$data['pokemons']` in that test.)

- [ ] **Step 3: Repeat for all other test methods that check pokemons**

Apply `$this->assertPokemonsHaveGameBundlesStructure($pokemons)` to every test method in `AlbumIndexControllerTest` that retrieves the `pokemons` key from the response (check `testListUser34Home`, `testListHomeShiny`, etc. — look for `$data['pokemons']` assignments).

- [ ] **Step 4: Run the full integration test suite**

```bash
docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/AlbumIndexControllerTest.php
```

Expected: all PASS.
