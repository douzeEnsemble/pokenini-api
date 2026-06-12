# API Response Restructuring (PokemonData — Nested family_lead & original_game_bundle) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the last two flat fields of `PokemonDataResponse` — `family_lead_slug: string|null` and `original_game_bundle_slug: string|null` — with nested objects `family_lead: {"slug": "…"}|null` (via `PokemonSlugResponse`) and `original_game_bundle: {"slug": "…"}|null` (via `GameBundleSlugResponse`), completing issue #256 for the three endpoints that serialize `PokemonDataResponse`: `GET /album/{trainerExternalId}/{dexSlug}`, `GET /pokemons/to_choose`, `GET /election/top`.

**Architecture:** No new class needed — `PokemonSlugResponse` and `GameBundleSlugResponse` already exist and already have dedicated unit tests. Change the two constructor properties of `PokemonDataResponse`, then update the three factories that build it (`AlbumPokemonResponseFactory`, `ElectionPokemonResponseFactory`, `ElectionEloResponseFactory`) to wrap the flat SQL columns (which do not change) into the nested DTOs. Update all unit tests, the `AlbumData` integration helper, the `TrainerPokemonEloControllerTest` integration assertions, the `ElectionElo/top.json` sample fixture, and the `doc/endpoints.md` examples. No controller, repository, service, entity, or SQL file changes.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

**Constraints (user request):**
- **No `git add` / `git commit`** — the user handles all git operations.
- **Do not run test or quality commands** (`make tests`, `make quality`, `make measures`, `phpunit`, …) — the user runs them manually at the end. Tasks therefore contain only file edits; the final validation section lists the commands the user will run.
- Every modified non-Controller / non-Repository class keeps a dedicated unit test, updated so that **100% coverage and 100% MSI hold**: each new ternary branch (`null` vs non-`null`) is exercised, and the `(string)` casts are asserted via the existing "casts" tests.

---

## Response shape change

**Before** (current serialized `pokemon` object, identical in the 3 endpoints):
```json
{
  "pokemon": {
    "slug": "bulbasaur",
    "family_order": 0,
    "family_lead_slug": "bulbasaur",
    "original_game_bundle_slug": "redgreenblueyellow",
    "order_number": "0001-0001-000"
  }
}
```

**After:**
```json
{
  "pokemon": {
    "slug": "bulbasaur",
    "family_order": 0,
    "family_lead": { "slug": "bulbasaur" },
    "original_game_bundle": { "slug": "redgreenblueyellow" },
    "order_number": "0001-0001-000"
  }
}
```

When the SQL column is `NULL`, the nested object is `null` (same as before with the flat field):
```json
{
  "pokemon": {
    "family_lead": null,
    "original_game_bundle": null
  }
}
```

---

## File Structure

**Create:** none — `PokemonSlugResponse` and `GameBundleSlugResponse` already exist with dedicated tests (`tests/src/Unit/DTO/Response/PokemonSlugResponseTest.php`, `tests/src/Unit/DTO/Response/GameBundleSlugResponseTest.php`).

**Modify (src):**
- `src/DTO/Response/PokemonDataResponse.php` — replace the two flat properties with nested DTOs
- `src/Factory/AlbumPokemonResponseFactory.php` — wrap slugs in `buildPokemon()`
- `src/Factory/ElectionPokemonResponseFactory.php` — wrap slugs in `buildPokemon()`
- `src/Factory/ElectionEloResponseFactory.php` — wrap slugs in `buildPokemonData()`

**Modify (tests):**
- `tests/src/Unit/DTO/Response/PokemonDataResponseTest.php`
- `tests/src/Unit/DTO/Response/AlbumPokemonResponseTest.php`
- `tests/src/Unit/DTO/Response/ElectionEloResponseTest.php`
- `tests/src/Unit/DTO/Response/ElectionPokemonResponseTest.php`
- `tests/src/Unit/DTO/Response/ElectionPokemonsListResponseTest.php`
- `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php` (+ one new test for the null branch)
- `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`
- `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php`
- `tests/src/Unit/Factory/AlbumIndexResponseFactoryTest.php`
- `tests/src/Common/Data/AlbumData.php` — `toNestedFormat()` expected JSON shape
- `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php` — key assertions
- `tests/resources/moco/ElectionElo/top.json` — sample fixture shape

**Modify (docs):**
- `doc/endpoints.md` — examples of endpoints 13, 16 and 18

**Explicitly unchanged** (verified — they describe flat SQL rows, which this change does not touch):
- `resources/sql/pokemons-get_n_to_pick.sql`, `resources/sql/pokemons-get_n_to_vote.sql`, `resources/sql/trainer_pokemon_elo-get_top_n.sql`
- `src/Repository/PokedexRepository.php`
- `tests/src/Common/Data/PokemonData.php` (flat SQL-row inputs)
- `tests/src/Common/Types/PokedexTypes.php` (psalm types of flat SQL rows)
- `tests/src/Integration/Repository/PokedexRepositoryList/DataTrait.php`
- `tests/src/Integration/Postman/collection.json` (no reference to the flat keys)

---

## Tasks

### Task 1: Update PokemonDataResponse DTO

**Files:**
- Modify: `src/DTO/Response/PokemonDataResponse.php`

- [ ] **Step 1: Replace the two flat properties with nested DTOs**

In `src/DTO/Response/PokemonDataResponse.php`, replace:

```php
        #[SerializedName('family_lead_slug')]
        public readonly ?string $familyLeadSlug,
        #[SerializedName('original_game_bundle_slug')]
        public readonly ?string $originalGameBundleSlug,
```

with:

```php
        #[SerializedName('family_lead')]
        public readonly ?PokemonSlugResponse $familyLead,
        #[SerializedName('original_game_bundle')]
        public readonly ?GameBundleSlugResponse $originalGameBundle,
```

No `use` statement is needed: `PokemonSlugResponse` and `GameBundleSlugResponse` are in the same namespace (`App\DTO\Response`).

---

### Task 2: Update PokemonDataResponseTest

**Files:**
- Modify: `tests/src/Unit/DTO/Response/PokemonDataResponseTest.php`

- [ ] **Step 1: Add the PokemonSlugResponse import**

Insert after the existing `use App\DTO\Response\PokemonDataResponse;` line (keeping alphabetical order):

```php
use App\DTO\Response\PokemonSlugResponse;
```

- [ ] **Step 2: Update `constructorInitializesAllProperties`**

Replace the constructor arguments:

```php
            familyLeadSlug: 'pichu',
            originalGameBundleSlug: 'rby',
```

with:

```php
            familyLead: new PokemonSlugResponse(slug: 'pichu'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'rby'),
```

Replace the assertions:

```php
        self::assertSame('pichu', $response->familyLeadSlug);
        self::assertSame('rby', $response->originalGameBundleSlug);
```

with:

```php
        self::assertInstanceOf(PokemonSlugResponse::class, $response->familyLead);
        self::assertSame('pichu', $response->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->originalGameBundle);
        self::assertSame('rby', $response->originalGameBundle->slug);
```

- [ ] **Step 3: Update `constructorAcceptsNullablePropertiesAsNull`**

Replace the constructor arguments:

```php
            familyLeadSlug: null,
            originalGameBundleSlug: null,
```

with:

```php
            familyLead: null,
            originalGameBundle: null,
```

Replace the assertions:

```php
        self::assertNull($response->familyLeadSlug);
        self::assertNull($response->originalGameBundleSlug);
```

with:

```php
        self::assertNull($response->familyLead);
        self::assertNull($response->originalGameBundle);
```

---

### Task 3: Update AlbumPokemonResponseFactory and its test

**Files:**
- Modify: `src/Factory/AlbumPokemonResponseFactory.php`
- Modify: `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

- [ ] **Step 1: Add the PokemonSlugResponse import to the factory**

In `src/Factory/AlbumPokemonResponseFactory.php`, after `use App\DTO\Response\PokemonDataResponse;` add:

```php
use App\DTO\Response\PokemonSlugResponse;
```

- [ ] **Step 2: Wrap the slugs in `buildPokemon()`**

In the `new PokemonDataResponse(...)` call at the end of `buildPokemon()`, replace:

```php
            familyLeadSlug: null !== $familyLeadSlug ? (string) $familyLeadSlug : null,
            originalGameBundleSlug: null !== $originalGameBundleSlug ? (string) $originalGameBundleSlug : null,
```

with:

```php
            familyLead: null !== $familyLeadSlug
                ? new PokemonSlugResponse(slug: (string) $familyLeadSlug)
                : null,
            originalGameBundle: null !== $originalGameBundleSlug
                ? new GameBundleSlugResponse(slug: (string) $originalGameBundleSlug)
                : null,
```

The local variables `$familyLeadSlug` / `$originalGameBundleSlug` (read from `$row['family_lead_slug']` / `$row['original_game_bundle_slug']`) do not change — SQL columns keep their names.

- [ ] **Step 3: Add the PokemonSlugResponse import to the test**

In `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`, after `use App\DTO\Response\GameBundleSlugResponse;` add (keeping alphabetical order, before `use App\Factory\AlbumPokemonResponseFactory;`):

```php
use App\DTO\Response\PokemonSlugResponse;
```

- [ ] **Step 4: Update `fromSqlRowBuildsPokemonSubObject` assertions (lines ~42-43)**

Replace:

```php
        self::assertSame('bulbasaur', $result->pokemon->familyLeadSlug);
        self::assertSame('redgreenblueyellow', $result->pokemon->originalGameBundleSlug);
```

with:

```php
        self::assertInstanceOf(PokemonSlugResponse::class, $result->pokemon->familyLead);
        self::assertSame('bulbasaur', $result->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->originalGameBundle);
        self::assertSame('redgreenblueyellow', $result->pokemon->originalGameBundle->slug);
```

- [ ] **Step 5: Update `fromSqlRowCastsNullableStringFieldsToStrings` assertions (lines ~234-235)**

The test sets `$row['family_lead_slug'] = 123;` and `$row['original_game_bundle_slug'] = 456;` (inputs unchanged). Replace the assertions:

```php
        self::assertSame('123', $result->pokemon->familyLeadSlug);
        self::assertSame('456', $result->pokemon->originalGameBundleSlug);
```

with:

```php
        self::assertInstanceOf(PokemonSlugResponse::class, $result->pokemon->familyLead);
        self::assertSame('123', $result->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->originalGameBundle);
        self::assertSame('456', $result->pokemon->originalGameBundle->slug);
```

- [ ] **Step 6: Add a test for the null branch (required for 100% MSI)**

No existing test of this factory feeds `family_lead_slug = null` / `original_game_bundle_slug = null`. Add this test after `fromSqlRowCastsNullableStringFieldsToStrings`:

```php
    #[Test]
    public function fromSqlRowSetsNullFamilyLeadAndOriginalGameBundleWhenNull(): void
    {
        $row = $this->getBulbasaurRow();
        $row['family_lead_slug'] = null;
        $row['original_game_bundle_slug'] = null;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($result->pokemon->familyLead);
        self::assertNull($result->pokemon->originalGameBundle);
    }
```

---

### Task 4: Update ElectionPokemonResponseFactory and its test

**Files:**
- Modify: `src/Factory/ElectionPokemonResponseFactory.php`
- Modify: `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`

- [ ] **Step 1: Add imports to the factory**

In `src/Factory/ElectionPokemonResponseFactory.php`, after `use App\DTO\Response\FormsResponse;` add (alphabetical order):

```php
use App\DTO\Response\GameBundleSlugResponse;
```

and after `use App\DTO\Response\PokemonDataResponse;` add:

```php
use App\DTO\Response\PokemonSlugResponse;
```

- [ ] **Step 2: Wrap the slugs in `buildPokemon()`**

In the `new PokemonDataResponse(...)` call, replace:

```php
            familyLeadSlug: null !== $familyLeadSlug ? (string) $familyLeadSlug : null,
            originalGameBundleSlug: null !== $originalGameBundleSlug ? (string) $originalGameBundleSlug : null,
```

with:

```php
            familyLead: null !== $familyLeadSlug
                ? new PokemonSlugResponse(slug: (string) $familyLeadSlug)
                : null,
            originalGameBundle: null !== $originalGameBundleSlug
                ? new GameBundleSlugResponse(slug: (string) $originalGameBundleSlug)
                : null,
```

- [ ] **Step 3: Add imports to the test**

In `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`, after `use App\DTO\Response\FormsResponse;` add (alphabetical order):

```php
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\PokemonSlugResponse;
```

- [ ] **Step 4: Update the non-null assertions (lines ~41-42)**

Replace:

```php
        self::assertSame('bulbasaur', $response->pokemon->familyLeadSlug);
        self::assertSame('redgreenblueyellow', $response->pokemon->originalGameBundleSlug);
```

with:

```php
        self::assertInstanceOf(PokemonSlugResponse::class, $response->pokemon->familyLead);
        self::assertSame('bulbasaur', $response->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->originalGameBundle);
        self::assertSame('redgreenblueyellow', $response->pokemon->originalGameBundle->slug);
```

- [ ] **Step 5: Update the null assertions (lines ~284-285)**

The test feeding `'family_lead_slug' => null, 'original_game_bundle_slug' => null` keeps its inputs. Replace its assertions:

```php
        self::assertNull($response->pokemon->familyLeadSlug);
        self::assertNull($response->pokemon->originalGameBundleSlug);
```

with:

```php
        self::assertNull($response->pokemon->familyLead);
        self::assertNull($response->pokemon->originalGameBundle);
```

- [ ] **Step 6: Update the casts assertions (lines ~308-309)**

The test feeding `'family_lead_slug' => 55, 'original_game_bundle_slug' => 12` keeps its inputs. Replace its assertions:

```php
        self::assertSame('55', $response->pokemon->familyLeadSlug);
        self::assertSame('12', $response->pokemon->originalGameBundleSlug);
```

with:

```php
        self::assertInstanceOf(PokemonSlugResponse::class, $response->pokemon->familyLead);
        self::assertSame('55', $response->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->originalGameBundle);
        self::assertSame('12', $response->pokemon->originalGameBundle->slug);
```

---

### Task 5: Update ElectionEloResponseFactory and its test

**Files:**
- Modify: `src/Factory/ElectionEloResponseFactory.php`
- Modify: `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php`

- [ ] **Step 1: Add imports to the factory**

In `src/Factory/ElectionEloResponseFactory.php`, after `use App\DTO\Response\FormsResponse;` add (alphabetical order):

```php
use App\DTO\Response\GameBundleSlugResponse;
```

and after `use App\DTO\Response\PokemonDataResponse;` add:

```php
use App\DTO\Response\PokemonSlugResponse;
```

- [ ] **Step 2: Wrap the slugs in `buildPokemonData()`**

In the `new PokemonDataResponse(...)` call, replace:

```php
            familyLeadSlug: null !== $familyLeadSlug ? (string) $familyLeadSlug : null,
            originalGameBundleSlug: null !== $originalGameBundleSlug ? (string) $originalGameBundleSlug : null,
```

with:

```php
            familyLead: null !== $familyLeadSlug
                ? new PokemonSlugResponse(slug: (string) $familyLeadSlug)
                : null,
            originalGameBundle: null !== $originalGameBundleSlug
                ? new GameBundleSlugResponse(slug: (string) $originalGameBundleSlug)
                : null,
```

- [ ] **Step 3: Add imports to the test**

In `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php`, after `use App\DTO\Response\FormsResponse;` add (alphabetical order):

```php
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\PokemonSlugResponse;
```

- [ ] **Step 4: Update `fromSqlRowCastsNullableStringFieldsFromNonStringValues` assertions (lines ~309-310)**

The test feeding `'family_lead_slug' => 77, 'original_game_bundle_slug' => 55` keeps its inputs. Replace its assertions:

```php
        self::assertSame('77', $response->pokemon->familyLeadSlug);
        self::assertSame('55', $response->pokemon->originalGameBundleSlug);
```

with:

```php
        self::assertInstanceOf(PokemonSlugResponse::class, $response->pokemon->familyLead);
        self::assertSame('77', $response->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->originalGameBundle);
        self::assertSame('55', $response->pokemon->originalGameBundle->slug);
```

- [ ] **Step 5: Add null assertions to `fromSqlRowBuildsFormsWhenOnlyRegionalFormIsPresent` (required for 100% MSI)**

This test (the "vulpix" row, lines ~313+) already feeds `'family_lead_slug' => null, 'original_game_bundle_slug' => null` but asserts nothing about these fields. At the end of its assertion block, add:

```php
        self::assertNull($response->pokemon->familyLead);
        self::assertNull($response->pokemon->originalGameBundle);
```

This kills the mutant that replaces `null !== $familyLeadSlug` with `true` (which would build `PokemonSlugResponse(slug: '')` instead of `null`).

---

### Task 6: Update the remaining unit tests that construct PokemonDataResponse

These tests build `PokemonDataResponse` instances directly with the old named arguments. Each must be updated or PHP will fail with "Unknown named parameter".

**Files:**
- Modify: `tests/src/Unit/DTO/Response/AlbumPokemonResponseTest.php`
- Modify: `tests/src/Unit/DTO/Response/ElectionEloResponseTest.php`
- Modify: `tests/src/Unit/DTO/Response/ElectionPokemonResponseTest.php`
- Modify: `tests/src/Unit/DTO/Response/ElectionPokemonsListResponseTest.php`
- Modify: `tests/src/Unit/Factory/AlbumIndexResponseFactoryTest.php`

- [ ] **Step 1: AlbumPokemonResponseTest — imports**

After `use App\DTO\Response\PokemonDataResponse;` add:

```php
use App\DTO\Response\PokemonSlugResponse;
```

(`GameBundleSlugResponse` is already imported.)

- [ ] **Step 2: AlbumPokemonResponseTest — first construction (lines ~40-41)**

Replace:

```php
            familyLeadSlug: 'bulbasaur',
            originalGameBundleSlug: 'redgreenblueyellow',
```

with:

```php
            familyLead: new PokemonSlugResponse(slug: 'bulbasaur'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'redgreenblueyellow'),
```

- [ ] **Step 3: AlbumPokemonResponseTest — second construction (lines ~86-87)**

Replace:

```php
            familyLeadSlug: 'douze',
            originalGameBundleSlug: 'redgreenblueyellow',
```

with:

```php
            familyLead: new PokemonSlugResponse(slug: 'douze'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'redgreenblueyellow'),
```

- [ ] **Step 4: ElectionEloResponseTest (lines ~79-80)**

Arguments are already `null` — only rename them. Replace:

```php
            familyLeadSlug: null,
            originalGameBundleSlug: null,
```

with:

```php
            familyLead: null,
            originalGameBundle: null,
```

No import needed.

- [ ] **Step 5: ElectionPokemonResponseTest — imports + construction (lines ~83-84)**

After `use App\DTO\Response\FormsResponse;` add (alphabetical order):

```php
use App\DTO\Response\GameBundleSlugResponse;
```

and after `use App\DTO\Response\PokemonDataResponse;` add:

```php
use App\DTO\Response\PokemonSlugResponse;
```

In `buildPokemon()`, replace:

```php
            familyLeadSlug: 'bulbasaur',
            originalGameBundleSlug: 'redgreenblueyellow',
```

with:

```php
            familyLead: new PokemonSlugResponse(slug: 'bulbasaur'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'redgreenblueyellow'),
```

- [ ] **Step 6: ElectionPokemonsListResponseTest — imports + construction (lines ~73-74)**

After `use App\DTO\Response\ElectionPokemonsListResponse;` add (alphabetical order):

```php
use App\DTO\Response\GameBundleSlugResponse;
```

and after `use App\DTO\Response\PokemonDataResponse;` add:

```php
use App\DTO\Response\PokemonSlugResponse;
```

Replace:

```php
            familyLeadSlug: 'bulbasaur',
            originalGameBundleSlug: 'redgreenblueyellow',
```

with:

```php
            familyLead: new PokemonSlugResponse(slug: 'bulbasaur'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'redgreenblueyellow'),
```

- [ ] **Step 7: AlbumIndexResponseFactoryTest (lines ~94-95)**

Arguments are already `null` — only rename them. In `buildAlbumPokemonResponse()`, replace:

```php
                familyLeadSlug: null,
                originalGameBundleSlug: null,
```

with:

```php
                familyLead: null,
                originalGameBundle: null,
```

No import needed.

---

### Task 7: Update the integration-test expectations

**Files:**
- Modify: `tests/src/Common/Data/AlbumData.php`
- Modify: `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`

- [ ] **Step 1: AlbumData — `toNestedFormat()` (lines ~769-770)**

This private helper builds the JSON shape expected by `AlbumIndexControllerTest`. In the `'pokemon' => [...]` array, replace:

```php
                'family_lead_slug' => $flat['family_lead_slug'] ?? null,
                'original_game_bundle_slug' => $flat['original_game_bundle_slug'] ?? null,
```

with:

```php
                'family_lead' => null !== ($flat['family_lead_slug'] ?? null)
                    ? ['slug' => $flat['family_lead_slug']]
                    : null,
                'original_game_bundle' => null !== ($flat['original_game_bundle_slug'] ?? null)
                    ? ['slug' => $flat['original_game_bundle_slug']]
                    : null,
```

The flat methods (`getExpectedRegGreenBlueYellowContent()`, etc.) are **not** touched — they describe the SQL rows returned by the service layer, which keep their flat keys.

- [ ] **Step 2: TrainerPokemonEloControllerTest — `testGetTop` key assertions (lines ~60-61)**

Replace:

```php
            $this->assertArrayHasKey('family_lead_slug', $pokemon);
            $this->assertArrayHasKey('original_game_bundle_slug', $pokemon);
```

with:

```php
            $this->assertArrayHasKey('family_lead', $pokemon);
            $this->assertArrayHasKey('original_game_bundle', $pokemon);
```

---

### Task 8: Update the ElectionElo sample fixture

**Files:**
- Modify: `tests/resources/moco/ElectionElo/top.json`

- [ ] **Step 1: Nest the two fields in both items**

In the first item (pikachu), replace:

```json
      "family_lead_slug": "pichu",
      "original_game_bundle_slug": "red-blue",
```

with:

```json
      "family_lead": { "slug": "pichu" },
      "original_game_bundle": { "slug": "red-blue" },
```

In the second item (charizard), replace:

```json
      "family_lead_slug": "charmander",
      "original_game_bundle_slug": null,
```

with:

```json
      "family_lead": { "slug": "charmander" },
      "original_game_bundle": null,
```

---

### Task 9: Update doc/endpoints.md examples

**Files:**
- Modify: `doc/endpoints.md`

- [ ] **Step 1: Endpoint 13 — `GET /album/{trainerExternalId}/{dexSlug}` example (around line 652-653)**

In the `pokemon` object of the example response, replace:

```json
        "family_lead_slug": "bulbasaur",
        "original_game_bundle_slug": "redgreenblueyellow",
```

with:

```json
        "family_lead": { "slug": "bulbasaur" },
        "original_game_bundle": { "slug": "redgreenblueyellow" },
```

- [ ] **Step 2: Endpoint 16 — `GET /pokemons/to_choose` example (around line 805-806)**

Same replacement:

```json
        "family_lead_slug": "bulbasaur",
        "original_game_bundle_slug": "redgreenblueyellow",
```

becomes:

```json
        "family_lead": { "slug": "bulbasaur" },
        "original_game_bundle": { "slug": "redgreenblueyellow" },
```

- [ ] **Step 3: Endpoint 18 — `GET /election/top` example (around line 941-942)**

Replace:

```json
      "family_lead_slug": "caterpie",
      "original_game_bundle_slug": "redgreenblueyellow",
```

with:

```json
      "family_lead": { "slug": "caterpie" },
      "original_game_bundle": { "slug": "redgreenblueyellow" },
```

---

### Task 10: Final verification sweep

- [ ] **Step 1: No leftover references in src/ and unit tests**

Run: `grep -rn "familyLeadSlug\|originalGameBundleSlug" src/ tests/src/Unit/`

Expected: no output. (The flat SQL keys `family_lead_slug` / `original_game_bundle_slug` legitimately remain in `src/Repository/`, `resources/sql/`, factories' `$row[...]` reads, factory test inputs, `PokemonData.php`, `PokedexTypes.php` and `DataTrait.php` — they describe SQL rows, not API responses.)

- [ ] **Step 2: No leftover flat keys in serialized expectations**

Run: `grep -rn "family_lead_slug\|original_game_bundle_slug" tests/resources/ doc/endpoints.md tests/src/Integration/Controller/`

Expected: no output.

---

## Validation (run manually by the user — not by agents)

The user runs these commands themselves; agents must **not** execute them:

```bash
make tu          # all unit tests pass
make ti          # all integration tests pass (AlbumIndexControllerTest, TrainerPokemonEloControllerTest, PokemonsControllerTest…)
make quality     # cs-fixer, phpmd, psalm, phpstan level 9, deptrac, jsonlint
make measures    # 100% coverage + 100% MSI
```

Expected: everything green. The MSI-sensitive spots are the six new ternary branches (2 per factory), each covered by a non-null test (casts assertions on `->slug`) and a null test (`assertNull`).

---

## Next Steps (not in this plan)

This is a breaking change on `GET /album/{trainerExternalId}/{dexSlug}`, `GET /pokemons/to_choose` and `GET /election/top`. Downstream repos must be updated afterwards, in order:

1. **pokenini-back** — update any BFF code and Moco fixtures (`tests/resources/moco/`) reading `pokemon.family_lead_slug` / `pokemon.original_game_bundle_slug` to read `pokemon.family_lead.slug` / `pokemon.original_game_bundle.slug`.
2. **pokenini-web** — update Twig templates / PHP reading those keys, plus its Moco fixtures.
