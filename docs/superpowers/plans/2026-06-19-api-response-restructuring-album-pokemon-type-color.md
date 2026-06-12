# API Response Restructuring (GET /album — Type Color) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Populate the `color` field in type objects returned inside `pokemons[].types` for `GET /album/{trainerExternalId}/{dexSlug}` — currently `AlbumTypeResponse` has no `color` property because neither the SQL query in `PokedexRepository::getListQuerySQL()` selects the color columns nor `AlbumPokemonResponseFactory::buildType()` reads them.

**Architecture:** Add `pt.color AS primary_type_color` and `st.color AS secondary_type_color` to the inline SQL SELECT in `PokedexRepository`, add a `color` property to `AlbumTypeResponse`, update `AlbumPokemonResponseFactory::buildType()` to read those columns from the row and pass them to the DTO, then update unit and integration test fixtures to carry the new fields and assert actual hex color values. No changes to any controller, service, or entity.

**Tech Stack:** Symfony 8, PHP 8.5, PostgreSQL, PHPUnit

---

## Response shape change

**Before** — `pokemons[n].types`:
```json
{
  "primary": { "slug": "grass", "name": "Grass", "french_name": "Plante" },
  "secondary": { "slug": "poison", "name": "Poison", "french_name": "Poison" }
}
```

**After** — `pokemons[n].types`:
```json
{
  "primary": { "slug": "grass", "name": "Grass", "french_name": "Plante", "color": "#78C850" },
  "secondary": { "slug": "poison", "name": "Poison", "french_name": "Poison", "color": "#A040A0" }
}
```

When a type is absent (`null`), the whole type object stays `null` — no change to the nullable behaviour.

---

## File Structure

**Modify only (no new files):**
- `src/Repository/PokedexRepository.php` — add `pt.color AS primary_type_color` and `st.color AS secondary_type_color` to the SELECT inside `getListQuerySQL()`
- `src/DTO/Response/AlbumTypeResponse.php` — add `color: string` property
- `src/Factory/AlbumPokemonResponseFactory.php` — `buildType()` reads `{prefix}_color` from row and passes it to `AlbumTypeResponse`
- `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php` — add `primary_type_color` / `secondary_type_color` to both row fixtures; add/update color assertions; add cast test
- `tests/src/Common/Data/PokemonData.php` — add `primary_type_color` and `secondary_type_color` to every pokemon data getter
- `tests/src/Common/Data/AlbumData.php` — add color fields to all inline flat rows; update `buildNestedTypes()` to emit `color` key
- `tests/src/Common/Types/PokedexTypes.php` — add `primary_type_color` and `secondary_type_color` to `PokedexRepositoryItem` and `PokedexResponseItem` Psalm types

---

## Tasks

### Task 1: Add type color columns to the album SQL query

**Files:**
- Modify: `src/Repository/PokedexRepository.php` (around lines 285–290)

- [ ] **Step 1: Open the repository and find the type SELECT block**

Current lines ~285–290 of `src/Repository/PokedexRepository.php` (inside `getListQuerySQL()`):

```sql
                    pt.slug AS primary_type_slug,
                    pt.name AS primary_type_name,
                    pt.french_name AS primary_type_french_name,
                    st.slug AS secondary_type_slug,
                    st.name AS secondary_type_name,
                    st.french_name AS secondary_type_french_name,
```

- [ ] **Step 2: Add the two color aliases**

Replace those lines with:

```sql
                    pt.slug AS primary_type_slug,
                    pt.name AS primary_type_name,
                    pt.french_name AS primary_type_french_name,
                    pt.color AS primary_type_color,
                    st.slug AS secondary_type_slug,
                    st.name AS secondary_type_name,
                    st.french_name AS secondary_type_french_name,
                    st.color AS secondary_type_color,
```

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l src/Repository/PokedexRepository.php`

Expected: "No syntax errors detected".

---

### Task 2: Add `color` property to AlbumTypeResponse DTO

**Files:**
- Modify: `src/DTO/Response/AlbumTypeResponse.php`

- [ ] **Step 1: Read the current DTO**

Current content of `src/DTO/Response/AlbumTypeResponse.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumTypeResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
    ) {}
}
```

- [ ] **Step 2: Add the `color` property**

Replace with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumTypeResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly string $color,
    ) {}
}
```

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l src/DTO/Response/AlbumTypeResponse.php`

Expected: "No syntax errors detected".

---

### Task 3: Update AlbumPokemonResponseFactory to read color

**Files:**
- Modify: `src/Factory/AlbumPokemonResponseFactory.php` — method `buildType()` (around lines 211–238)

- [ ] **Step 1: Read the current `buildType()` method**

Current implementation:

```php
/**
 * @param array<string, mixed> $row
 */
private static function buildType(string $prefix, array $row): ?AlbumTypeResponse
{
    $slugKey = "{$prefix}_slug";
    $nameKey = "{$prefix}_name";
    $frenchNameKey = "{$prefix}_french_name";

    if (empty($row[$slugKey])) {
        return null;
    }

    /** @var scalar $slug */
    $slug = $row[$slugKey];

    /** @var scalar $name */
    $name = $row[$nameKey];

    /** @var scalar $frenchName */
    $frenchName = $row[$frenchNameKey];

    return new AlbumTypeResponse(
        slug: (string) $slug,
        name: (string) $name,
        frenchName: (string) $frenchName,
    );
}
```

- [ ] **Step 2: Add `colorKey` and pass `color` to the constructor**

Replace with:

```php
/**
 * @param array<string, mixed> $row
 */
private static function buildType(string $prefix, array $row): ?AlbumTypeResponse
{
    $slugKey = "{$prefix}_slug";
    $nameKey = "{$prefix}_name";
    $frenchNameKey = "{$prefix}_french_name";
    $colorKey = "{$prefix}_color";

    if (empty($row[$slugKey])) {
        return null;
    }

    /** @var scalar $slug */
    $slug = $row[$slugKey];

    /** @var scalar $name */
    $name = $row[$nameKey];

    /** @var scalar $frenchName */
    $frenchName = $row[$frenchNameKey];

    /** @var scalar $color */
    $color = $row[$colorKey];

    return new AlbumTypeResponse(
        slug: (string) $slug,
        name: (string) $name,
        frenchName: (string) $frenchName,
        color: (string) $color,
    );
}
```

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l src/Factory/AlbumPokemonResponseFactory.php`

Expected: "No syntax errors detected".

---

### Task 4: Update unit tests for AlbumPokemonResponseFactory

**Files:**
- Modify: `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

- [ ] **Step 1: Add color keys to `getBulbasaurRow()` fixture**

In the `getBulbasaurRow()` method, add the two color keys after the existing type fields (after `secondary_type_french_name`):

```php
'primary_type_color' => '#78C850',
'secondary_type_color' => '#A040A0',
```

- [ ] **Step 2: Add color keys to `getDouzeRow()` fixture**

In the `getDouzeRow()` method, add null color keys after the existing type fields (after `secondary_type_french_name`):

```php
'primary_type_color' => null,
'secondary_type_color' => null,
```

- [ ] **Step 3: Update the type assertion test to include color**

In `fromSqlRowBuildsTypesObjectWithPrimaryAndSecondaryType()`, add color assertions after the existing ones:

```php
self::assertSame('#78C850', $result->types->primary->color);
self::assertSame('#A040A0', $result->types->secondary->color);
```

- [ ] **Step 4: Add a cast test for the color field**

Add a new test method to verify that non-string color values are cast correctly:

```php
#[Test]
public function fromSqlRowCastsTypeColorToString(): void
{
    $row = $this->getBulbasaurRow();
    $row['primary_type_color'] = 0x78C850;
    $row['secondary_type_color'] = 0xA040A0;

    $result = AlbumPokemonResponseFactory::fromSqlRow($row);

    self::assertIsString($result->types->primary->color);
    self::assertIsString($result->types->secondary->color);
}
```

- [ ] **Step 5: Run unit tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

Expected: All tests pass, 0 failures.

---

### Task 5: Update PokemonData test helper with color fields

**Files:**
- Modify: `tests/src/Common/Data/PokemonData.php`

- [ ] **Step 1: Add color fields to every `get*Data()` method**

For each pokemon data getter in `PokemonData.php`, add `primary_type_color` and `secondary_type_color` after the existing type fields. Use the real values from the integration test database.

Grass type color: `'#78C850'`
Poison type color: `'#A040A0'`
Bug type color: `'#A8B820'`
Flying type color: `'#A890F0'`
Fire type color: `'#F08030'`
Normal type color: `'#A8A878'`

For pokemon with no types (if any), use `null`.

Example for `getBulbasaurData()` — add after `'secondary_type_french_name' => 'Poison'`:

```php
'primary_type_color' => '#78C850',
'secondary_type_color' => '#A040A0',
```

Apply the same pattern to every pokemon getter (`getIvysaurData()`, `getVenusaurData()`, `getCaterpieData()`, `getMetapodData()`, `getButterfreeData()`, etc.) with the correct color values for each pokemon's types.

- [ ] **Step 2: Verify the file is syntactically correct**

Run: `docker compose exec php php -l tests/src/Common/Data/PokemonData.php`

Expected: "No syntax errors detected".

---

### Task 6: Update AlbumData test helper with color fields

**Files:**
- Modify: `tests/src/Common/Data/AlbumData.php`

- [ ] **Step 1: Update `buildNestedTypes()` to include `color`**

Current `buildNestedTypes()`:

```php
private static function buildNestedTypes(array $flat): array
{
    return [
        'primary' => null !== ($flat['primary_type_slug'] ?? null)
            ? ['slug' => $flat['primary_type_slug'], 'name' => $flat['primary_type_name'], 'french_name' => $flat['primary_type_french_name']]
            : null,
        'secondary' => null !== ($flat['secondary_type_slug'] ?? null)
            ? ['slug' => $flat['secondary_type_slug'], 'name' => $flat['secondary_type_name'], 'french_name' => $flat['secondary_type_french_name']]
            : null,
    ];
}
```

Replace with:

```php
private static function buildNestedTypes(array $flat): array
{
    return [
        'primary' => null !== ($flat['primary_type_slug'] ?? null)
            ? ['slug' => $flat['primary_type_slug'], 'name' => $flat['primary_type_name'], 'french_name' => $flat['primary_type_french_name'], 'color' => $flat['primary_type_color']]
            : null,
        'secondary' => null !== ($flat['secondary_type_slug'] ?? null)
            ? ['slug' => $flat['secondary_type_slug'], 'name' => $flat['secondary_type_name'], 'french_name' => $flat['secondary_type_french_name'], 'color' => $flat['secondary_type_color']]
            : null,
    ];
}
```

- [ ] **Step 2: Add color fields to all inline flat rows in `AlbumData`**

For every inline flat row definition in `AlbumData.php` that has `primary_type_slug` / `secondary_type_slug` fields but no corresponding color fields, add:

```php
'primary_type_color' => '<hex color for primary type>',
'secondary_type_color' => '<hex color for secondary type or null>',
```

Use the same color values as in `PokemonData.php`. These inline rows appear for pokémon variants (Venusaur ♀, Venusaur Mega, etc.) that are not in `PokemonData`.

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l tests/src/Common/Data/AlbumData.php`

Expected: "No syntax errors detected".

---

### Task 7: Update Psalm type definitions

**Files:**
- Modify: `tests/src/Common/Types/PokedexTypes.php`

- [ ] **Step 1: Add color fields to `PokedexRepositoryItem`**

In the `PokedexRepositoryItem` Psalm type (around lines 32–37), add after `primary_type_french_name` and after `secondary_type_french_name`:

```
 *  primary_type_color: string,
```

and after `secondary_type_french_name`:

```
 *  secondary_type_color: string|null,
```

Full updated block:

```
 *  primary_type_slug: string,
 *  primary_type_name: string,
 *  primary_type_french_name: string,
 *  primary_type_color: string,
 *  secondary_type_slug: string|null,
 *  secondary_type_name: string|null,
 *  secondary_type_french_name: string|null,
 *  secondary_type_color: string|null,
```

- [ ] **Step 2: Add color fields to `PokedexResponseItem`**

Apply the same change in the `PokedexResponseItem` Psalm type (around lines 81–87).

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l tests/src/Common/Types/PokedexTypes.php`

Expected: "No syntax errors detected".

---

### Task 8: Verify end-to-end with integration tests

**Files:**
- (all modified files from previous tasks)

- [ ] **Step 1: Run the AlbumIndexController integration tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/AlbumIndexControllerTest.php`

Expected: All tests pass, 0 failures. The response JSON now contains `color` inside each type object.

- [ ] **Step 2: Verify the `color` value matches hex format**

If the integration test does not already assert color values, add at least one assertion in `AlbumIndexControllerTest.php` for the first pokemon's primary type color:

```php
$firstPokemon = $pokemons[0];
$this->assertArrayHasKey('color', $firstPokemon['types']['primary']);
$this->assertIsString($firstPokemon['types']['primary']['color']);
$this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $firstPokemon['types']['primary']['color']);
```

- [ ] **Step 3: Run all unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/`

Expected: All tests pass, 0 failures.

---

### Task 9: Final validation checklist

**Files:**
- All files from previous tasks

- [ ] **Step 1: Verify all modified files compile**

Run: `docker compose exec php php -l src/Repository/PokedexRepository.php src/DTO/Response/AlbumTypeResponse.php src/Factory/AlbumPokemonResponseFactory.php`

Expected: "No syntax errors detected" for each file.

- [ ] **Step 2: Confirm response shape via curl**

Run (with docker running):

```bash
curl -u web:douze -s \
  "http://localhost:8080/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow" \
  | python3 -m json.tool | grep -A6 '"types"' | head -20
```

Expected: JSON output shows `"color"` field with a hex value inside both `primary` and `secondary` type objects.

- [ ] **Step 3: Document completion**

Summary of changes:
- ✅ `PokedexRepository`: added `pt.color AS primary_type_color` and `st.color AS secondary_type_color` to SQL SELECT
- ✅ `AlbumTypeResponse`: added `color: string` property
- ✅ `AlbumPokemonResponseFactory::buildType()`: reads `{prefix}_color` key and casts to string
- ✅ `AlbumPokemonResponseFactoryTest`: updated row fixtures and added color assertions + cast test
- ✅ `PokemonData`: all getters include `primary_type_color` / `secondary_type_color`
- ✅ `AlbumData`: all flat rows include color fields; `buildNestedTypes()` emits `color`
- ✅ `PokedexTypes`: Psalm types updated for both `PokedexRepositoryItem` and `PokedexResponseItem`
- ✅ Integration tests pass with color asserted

**Status:** Album pokemon types now carry `color` — consistent with `GET /pokemons/to_choose` and `GET /election/top`.

---

## Next Steps (not in this plan)

Once this plan is complete:

1. **Update `doc/endpoints.md`** — add `"color": "#78C850"` to the album response example in endpoint #13
2. **Update downstream Moco fixtures** — pokenini-back and pokenini-web will need to update their Moco fixtures if they assert album response shape exactly
3. **Verify pokenini-web rendering** — if the frontend renders type colors in the album view, it can now use the color field directly
