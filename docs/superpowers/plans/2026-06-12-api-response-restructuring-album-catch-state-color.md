# API Response Restructuring (GET /album — Catch State Color) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add `color` to catch state objects embedded in `GET /album/{trainerExternalId}/{dexSlug}` responses — both in `pokemons[].catch_state` and in `report.detail[].catch_state` / `filtered_report.detail[].catch_state`.

**Architecture:** Two independent code paths both produce `AlbumCatchStateResponse` objects: (1) the pokemon list path via `PokedexRepository::getListQuerySQL()` → `AlbumPokemonResponseFactory::buildCatchState()`, and (2) the report path via `PokedexRepository::getCatchStatesCounts()` → `AlbumReportService` → `AlbumReport\Statistic` → `AlbumReportResponseFactory`. Add `cs.color` to both SQL queries, add `color` to `AlbumCatchStateResponse` and `Statistic`, wire it through both factories, and update all tests. No changes to any controller or entity.

**Tech Stack:** Symfony 8, PHP 8.5, PostgreSQL, PHPUnit

---

## Response shape change

**Before** — `pokemons[n].catch_state`:
```json
{ "slug": "no", "name": "No", "french_name": "Non" }
```

**After** — `pokemons[n].catch_state`:
```json
{ "slug": "no", "name": "No", "french_name": "Non", "color": "#e57373" }
```

Same change applies to `report.detail[n].catch_state` and `filtered_report.detail[n].catch_state`.

When catch_state is absent (`null`), the whole catch_state object stays `null` — no change to the nullable behaviour.

---

## Catch state color values (from integration test DB)

| slug | color |
|------|-------|
| `no` | `#e57373` |
| `maybe` | `blue` |
| `maybenot` | `yellow` |
| `yes` | `#66bb6a` |

---

## File Structure

**Modify only (no new files):**
- `src/Repository/PokedexRepository.php` — add `cs.color AS catch_state_color` in `getListQuerySQL()` and `cs.color AS color` + GROUP BY in `getCatchStatesCounts()`
- `src/DTO/Response/AlbumCatchStateResponse.php` — add `color: string` property
- `src/DTO/AlbumReport/Statistic.php` — add `color: string` property (4th positional param, before `count`)
- `src/Service/Album/AlbumReportService.php` — pass `color` when constructing `Statistic`
- `src/Factory/AlbumReportResponseFactory.php` — pass `color: $statistic->color` to `AlbumCatchStateResponse`
- `src/Factory/AlbumPokemonResponseFactory.php` — read `catch_state_color` in `buildCatchState()` and pass to `AlbumCatchStateResponse`
- `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php` — add `catch_state_color` to row fixtures; add color assertion + cast test
- `tests/src/Unit/Factory/AlbumReportResponseFactoryTest.php` — update `Statistic` constructors to include color; add color assertions
- `tests/src/Common/Data/AlbumData.php` — add `catch_state_color` to all flat rows; update `buildNestedCatchState()` to emit `color`
- `tests/src/Common/Types/PokedexTypes.php` — add `catch_state_color: string|null` to `PokedexRepositoryItem` and `PokedexResponseItem`; add `color: string` to the catch_state shape in `PokedexResponseReport`
- `tests/src/Common/Traits/ReportTrait/AssertReportTrait.php` — add color assertions for each catch_state in `assertReport()`

---

## Tasks

### Task 1: Add `cs.color` to album pokemon list SQL query

**Files:**
- Modify: `src/Repository/PokedexRepository.php` (around lines 281–283, inside `getListQuerySQL()`)

- [ ] **Step 1: Open the repository and find the catch_state SELECT block**

Current lines ~281–283 of `src/Repository/PokedexRepository.php`:

```sql
                    cs.slug AS catch_state_slug,
                    cs.name AS catch_state_name,
                    cs.french_name AS catch_state_french_name,
```

- [ ] **Step 2: Add the color alias**

Replace those lines with:

```sql
                    cs.slug AS catch_state_slug,
                    cs.name AS catch_state_name,
                    cs.french_name AS catch_state_french_name,
                    cs.color AS catch_state_color,
```

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l src/Repository/PokedexRepository.php`

Expected: "No syntax errors detected".

---

### Task 2: Add `cs.color` to the catch state counts SQL query (for report)

**Files:**
- Modify: `src/Repository/PokedexRepository.php` (around lines 82–83 and 125, inside `getCatchStatesCounts()`)

- [ ] **Step 1: Find the SELECT line**

Current line ~83:

```sql
                    cs.slug AS slug, cs.name AS name, cs.french_name AS french_name
```

Replace with:

```sql
                    cs.slug AS slug, cs.name AS name, cs.french_name AS french_name, cs.color AS color
```

- [ ] **Step 2: Find the GROUP BY line**

Current line ~125:

```sql
            GROUP BY cs.slug, cs.name, cs.french_name, cs.order_number
```

Replace with:

```sql
            GROUP BY cs.slug, cs.name, cs.french_name, cs.color, cs.order_number
```

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l src/Repository/PokedexRepository.php`

Expected: "No syntax errors detected".

---

### Task 3: Add `color` property to `AlbumCatchStateResponse` DTO

**Files:**
- Modify: `src/DTO/Response/AlbumCatchStateResponse.php`

- [ ] **Step 1: Read the current DTO**

Current content:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumCatchStateResponse
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

final class AlbumCatchStateResponse
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

Run: `docker compose exec php php -l src/DTO/Response/AlbumCatchStateResponse.php`

Expected: "No syntax errors detected".

---

### Task 4: Add `color` property to `AlbumReport\Statistic` DTO

**Files:**
- Modify: `src/DTO/AlbumReport/Statistic.php`

- [ ] **Step 1: Read the current DTO**

Current content:

```php
<?php

declare(strict_types=1);

namespace App\DTO\AlbumReport;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class Statistic
{
    public function __construct(
        #[SerializedName('slug')]
        public string $slug,
        #[SerializedName('name')]
        public string $name,
        #[SerializedName('french_name')]
        public string $frenchName,
        #[SerializedName('count')]
        public int $count = 0,
    ) {}

    public function increment(): int
    {
        return ++$this->count;
    }
}
```

- [ ] **Step 2: Add the `color` property as the 4th positional parameter**

Replace with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\AlbumReport;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class Statistic
{
    public function __construct(
        #[SerializedName('slug')]
        public string $slug,
        #[SerializedName('name')]
        public string $name,
        #[SerializedName('french_name')]
        public string $frenchName,
        #[SerializedName('color')]
        public string $color,
        #[SerializedName('count')]
        public int $count = 0,
    ) {}

    public function increment(): int
    {
        return ++$this->count;
    }
}
```

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l src/DTO/AlbumReport/Statistic.php`

Expected: "No syntax errors detected".

---

### Task 5: Update `AlbumReportService` to pass color to `Statistic`

**Files:**
- Modify: `src/Service/Album/AlbumReportService.php` (around lines 39–44)

- [ ] **Step 1: Read the current construction block**

Current lines ~39–44:

```php
            $detail[] = new Statistic(
                (string) $catchStatesCount['slug'],
                (string) $catchStatesCount['name'],
                (string) $catchStatesCount['french_name'],
                (int) $catchStatesCount['count'],
            );
```

- [ ] **Step 2: Add named arguments with the `color` field**

Replace with:

```php
            $detail[] = new Statistic(
                slug: (string) $catchStatesCount['slug'],
                name: (string) $catchStatesCount['name'],
                frenchName: (string) $catchStatesCount['french_name'],
                color: (string) $catchStatesCount['color'],
                count: (int) $catchStatesCount['count'],
            );
```

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l src/Service/Album/AlbumReportService.php`

Expected: "No syntax errors detected".

---

### Task 6: Update `AlbumReportResponseFactory` to pass color to `AlbumCatchStateResponse`

**Files:**
- Modify: `src/Factory/AlbumReportResponseFactory.php` (the `fromStatistic()` method)

- [ ] **Step 1: Read the current `fromStatistic()` method**

Current implementation:

```php
private static function fromStatistic(Statistic $statistic): AlbumReportStatisticResponse
{
    return new AlbumReportStatisticResponse(
        catchState: new AlbumCatchStateResponse(
            slug: $statistic->slug,
            name: $statistic->name,
            frenchName: $statistic->frenchName,
        ),
        count: $statistic->count,
    );
}
```

- [ ] **Step 2: Add `color` to the `AlbumCatchStateResponse` constructor**

Replace with:

```php
private static function fromStatistic(Statistic $statistic): AlbumReportStatisticResponse
{
    return new AlbumReportStatisticResponse(
        catchState: new AlbumCatchStateResponse(
            slug: $statistic->slug,
            name: $statistic->name,
            frenchName: $statistic->frenchName,
            color: $statistic->color,
        ),
        count: $statistic->count,
    );
}
```

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l src/Factory/AlbumReportResponseFactory.php`

Expected: "No syntax errors detected".

---

### Task 7: Update `AlbumPokemonResponseFactory::buildCatchState()` to read color

**Files:**
- Modify: `src/Factory/AlbumPokemonResponseFactory.php` — method `buildCatchState()` (around lines 132–152)

- [ ] **Step 1: Read the current `buildCatchState()` method**

Current implementation:

```php
/**
 * @param array<string, mixed> $row
 */
private static function buildCatchState(array $row): ?AlbumCatchStateResponse
{
    if (empty($row['catch_state_slug'])) {
        return null;
    }

    /** @var scalar $slug */
    $slug = $row['catch_state_slug'];

    /** @var scalar $name */
    $name = $row['catch_state_name'];

    /** @var scalar $frenchName */
    $frenchName = $row['catch_state_french_name'];

    return new AlbumCatchStateResponse(
        slug: (string) $slug,
        name: (string) $name,
        frenchName: (string) $frenchName,
    );
}
```

- [ ] **Step 2: Add `color` key and pass it to the constructor**

Replace with:

```php
/**
 * @param array<string, mixed> $row
 */
private static function buildCatchState(array $row): ?AlbumCatchStateResponse
{
    if (empty($row['catch_state_slug'])) {
        return null;
    }

    /** @var scalar $slug */
    $slug = $row['catch_state_slug'];

    /** @var scalar $name */
    $name = $row['catch_state_name'];

    /** @var scalar $frenchName */
    $frenchName = $row['catch_state_french_name'];

    /** @var scalar $color */
    $color = $row['catch_state_color'];

    return new AlbumCatchStateResponse(
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

### Task 8: Update unit tests for `AlbumPokemonResponseFactory`

**Files:**
- Modify: `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

- [ ] **Step 1: Add `catch_state_color` to `getBulbasaurRow()` fixture**

In `getBulbasaurRow()`, add after `'catch_state_french_name' => 'Non'`:

```php
'catch_state_color' => '#e57373',
```

- [ ] **Step 2: Add `catch_state_color` to `getDouzeRow()` fixture**

In `getDouzeRow()`, add after `'catch_state_french_name' => null`:

```php
'catch_state_color' => null,
```

- [ ] **Step 3: Update `fromSqlRowBuildsCatchStateSubObject` to assert color**

In `fromSqlRowBuildsCatchStateSubObject()`, add after the existing `frenchName` assertion:

```php
self::assertSame('#e57373', $result->catchState->color);
```

- [ ] **Step 4: Add a cast test for `catch_state_color`**

Add a new test method:

```php
#[Test]
public function fromSqlRowCastsCatchStateColorToString(): void
{
    $row = $this->getBulbasaurRow();
    $row['catch_state_color'] = 0xe57373;

    $result = AlbumPokemonResponseFactory::fromSqlRow($row);

    self::assertInstanceOf(AlbumCatchStateResponse::class, $result->catchState);
    self::assertIsString($result->catchState->color);
}
```

- [ ] **Step 5: Verify the file is syntactically correct**

Run: `docker compose exec php php -l tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

Expected: "No syntax errors detected".

---

### Task 9: Update unit tests for `AlbumReportResponseFactory`

**Files:**
- Modify: `tests/src/Unit/Factory/AlbumReportResponseFactoryTest.php`

- [ ] **Step 1: Update all `Statistic` constructors to use named arguments with color**

Current test code (3 occurrences of `new Statistic(...)`):

```php
$stat1 = new Statistic('no', 'No', 'Non', 3);
$stat2 = new Statistic('yes', 'Yes', 'Oui', 5);
// and
$stat = new Statistic('maybe', 'Maybe', 'Peut être', 7);
// and
// empty detail test — no Statistic needed
```

Replace each with named arguments including `color`:

```php
$stat1 = new Statistic(slug: 'no', name: 'No', frenchName: 'Non', color: '#e57373', count: 3);
$stat2 = new Statistic(slug: 'yes', name: 'Yes', frenchName: 'Oui', color: '#66bb6a', count: 5);
```

```php
$stat = new Statistic(slug: 'maybe', name: 'Maybe', frenchName: 'Peut être', color: 'blue', count: 7);
```

- [ ] **Step 2: Add color assertion in `fromReportMapsStatisticCatchStateCorrectly`**

After the existing assertions (`self::assertSame('Peut être', $detail->catchState->frenchName)`), add:

```php
self::assertSame('blue', $detail->catchState->color);
```

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l tests/src/Unit/Factory/AlbumReportResponseFactoryTest.php`

Expected: "No syntax errors detected".

---

### Task 10: Update `AlbumData` test helper with catch_state color fields

**Files:**
- Modify: `tests/src/Common/Data/AlbumData.php`

- [ ] **Step 1: Update `buildNestedCatchState()` to include `color`**

Current `buildNestedCatchState()` (around line 810):

```php
private static function buildNestedCatchState(array $flat): ?array
{
    if (null === ($flat['catch_state_slug'] ?? null)) {
        return null;
    }

    return [
        'slug' => $flat['catch_state_slug'],
        'name' => $flat['catch_state_name'],
        'french_name' => $flat['catch_state_french_name'],
    ];
}
```

Replace with:

```php
private static function buildNestedCatchState(array $flat): ?array
{
    if (null === ($flat['catch_state_slug'] ?? null)) {
        return null;
    }

    return [
        'slug' => $flat['catch_state_slug'],
        'name' => $flat['catch_state_name'],
        'french_name' => $flat['catch_state_french_name'],
        'color' => $flat['catch_state_color'],
    ];
}
```

- [ ] **Step 2: Add `catch_state_color` to all inline flat rows that have a non-null `catch_state_slug`**

For every flat row in `AlbumData.php` that contains `catch_state_slug`, add `catch_state_color` immediately after `catch_state_french_name`. Use the correct color for the catch state slug:

| `catch_state_slug` | `catch_state_color` |
|--------------------|---------------------|
| `'no'` | `'#e57373'` |
| `'yes'` | `'#66bb6a'` |
| `'maybe'` | `'blue'` |
| `'maybenot'` | `'yellow'` |
| `null` | `null` |

Example — for a row with `catch_state_slug' => 'no'`:

```php
'catch_state_slug' => 'no',
'catch_state_name' => 'No',
'catch_state_french_name' => 'Non',
'catch_state_color' => '#e57373',
```

Apply to all matching rows (both `null` and non-null catch_state rows).

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l tests/src/Common/Data/AlbumData.php`

Expected: "No syntax errors detected".

---

### Task 11: Update Psalm type definitions in `PokedexTypes`

**Files:**
- Modify: `tests/src/Common/Types/PokedexTypes.php`

- [ ] **Step 1: Add `catch_state_color` to `PokedexRepositoryItem`**

In the `PokedexRepositoryItem` Psalm type (around lines 28–30), add after `catch_state_french_name`:

```
 *  catch_state_color: string|null,
```

Full updated block (lines 28–31):

```
 *  catch_state_slug: string|null,
 *  catch_state_name: string|null,
 *  catch_state_french_name: string|null,
 *  catch_state_color: string|null,
```

- [ ] **Step 2: Add `catch_state_color` to `PokedexResponseItem`**

In the `PokedexResponseItem` Psalm type (around lines 79–81), apply the same change after `catch_state_french_name`:

```
 *  catch_state_slug: string|null,
 *  catch_state_name: string|null,
 *  catch_state_french_name: string|null,
 *  catch_state_color: string|null,
```

- [ ] **Step 3: Add `color` to the catch_state shape in `PokedexResponseReport`**

In the `PokedexResponseReport` Psalm type (around lines 48–53), the current catch_state shape is:

```
 *      catch_state: array{
 *          slug: string,
 *          name: string,
 *          french_name: string
 *      }
```

Replace with:

```
 *      catch_state: array{
 *          slug: string,
 *          name: string,
 *          french_name: string,
 *          color: string
 *      }
```

- [ ] **Step 4: Verify the file is syntactically correct**

Run: `docker compose exec php php -l tests/src/Common/Types/PokedexTypes.php`

Expected: "No syntax errors detected".

---

### Task 12: Add color assertions to `AssertReportTrait`

**Files:**
- Modify: `tests/src/Common/Traits/ReportTrait/AssertReportTrait.php`

- [ ] **Step 1: Read the current trait**

Current assertions for each catch_state detail (4 entries: no, maybe, maybenot, yes) — each block looks like:

```php
$this->assertArrayHasKey('catch_state', $reportDetail[0]);
$this->assertEquals('no', $reportDetail[0]['catch_state']['slug']);
$this->assertEquals('No', $reportDetail[0]['catch_state']['name']);
$this->assertEquals('Non', $reportDetail[0]['catch_state']['french_name']);
```

- [ ] **Step 2: Add `color` assertions to each catch_state detail block**

After each `french_name` assertion, add the corresponding `color` assertion:

For `reportDetail[0]` (slug = `'no'`):
```php
$this->assertEquals('#e57373', $reportDetail[0]['catch_state']['color']);
```

For `reportDetail[1]` (slug = `'maybe'`):
```php
$this->assertEquals('blue', $reportDetail[1]['catch_state']['color']);
```

For `reportDetail[2]` (slug = `'maybenot'`):
```php
$this->assertEquals('yellow', $reportDetail[2]['catch_state']['color']);
```

For `reportDetail[3]` (slug = `'yes'`):
```php
$this->assertEquals('#66bb6a', $reportDetail[3]['catch_state']['color']);
```

- [ ] **Step 3: Verify the file is syntactically correct**

Run: `docker compose exec php php -l tests/src/Common/Traits/ReportTrait/AssertReportTrait.php`

Expected: "No syntax errors detected".

---

### Task 13: Verify end-to-end with integration tests

**Files:**
- (all modified files from previous tasks)

- [ ] **Step 1: Run the `AlbumPokemonResponseFactory` unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

Expected: All tests pass, 0 failures.

- [ ] **Step 2: Run the `AlbumReportResponseFactory` unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumReportResponseFactoryTest.php`

Expected: All tests pass, 0 failures.

- [ ] **Step 3: Verify that the album integration controller test asserts catch_state color**

In `tests/src/Integration/Controller/AlbumIndexControllerTest.php`, check whether there is already an assertion on `catch_state.color` in the pokemon list. If not, add at least one assertion on the first pokemon's catch_state color:

```php
$firstPokemon = $pokemons[0];
$this->assertArrayHasKey('color', $firstPokemon['catch_state']);
$this->assertIsString($firstPokemon['catch_state']['color']);
$this->assertSame('#e57373', $firstPokemon['catch_state']['color']);
```

- [ ] **Step 4: Run the `AlbumIndexController` integration tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/AlbumIndexControllerTest.php`

Expected: All tests pass, 0 failures.

- [ ] **Step 5: Run all unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/`

Expected: All unit tests pass, 0 failures.

---

### Task 14: Final validation checklist

**Files:**
- All files from previous tasks

- [ ] **Step 1: Verify all modified files compile**

Run:
```bash
docker compose exec php php -l \
  src/Repository/PokedexRepository.php \
  src/DTO/Response/AlbumCatchStateResponse.php \
  src/DTO/AlbumReport/Statistic.php \
  src/Service/Album/AlbumReportService.php \
  src/Factory/AlbumReportResponseFactory.php \
  src/Factory/AlbumPokemonResponseFactory.php
```

Expected: "No syntax errors detected" for each file.

- [ ] **Step 2: Document completion**

Summary of changes:
- ✅ `PokedexRepository::getListQuerySQL()`: added `cs.color AS catch_state_color`
- ✅ `PokedexRepository::getCatchStatesCounts()`: added `cs.color AS color` to SELECT and GROUP BY
- ✅ `AlbumCatchStateResponse`: added `color: string` property
- ✅ `AlbumReport\Statistic`: added `color: string` property as 4th positional param
- ✅ `AlbumReportService`: passes `color` (from SQL) to `Statistic` constructor
- ✅ `AlbumReportResponseFactory::fromStatistic()`: passes `color: $statistic->color` to `AlbumCatchStateResponse`
- ✅ `AlbumPokemonResponseFactory::buildCatchState()`: reads `catch_state_color` key and casts to string
- ✅ `AlbumPokemonResponseFactoryTest`: updated row fixtures with `catch_state_color`, added color assertion + cast test
- ✅ `AlbumReportResponseFactoryTest`: updated `Statistic` constructors with named args + color, added color assertion
- ✅ `AlbumData`: all flat rows have `catch_state_color`; `buildNestedCatchState()` emits `color`
- ✅ `PokedexTypes`: `catch_state_color` added to both repository and response item types; report catch_state shape includes `color`
- ✅ `AssertReportTrait`: color assertions added for all 4 catch states

**Status:** Album catch state objects now carry `color` — consistent with `GET /catch_states` standalone endpoint.

---

## Next Steps (not in this plan)

Once this plan is complete:

1. **Update `doc/endpoints.md`** — add `"color": "#e57373"` (and others) to the catch_state shapes in the album response example (endpoint #13)
2. **Update downstream Moco fixtures** — pokenini-back and pokenini-web will need to update their Moco fixtures if they assert album response shape exactly
