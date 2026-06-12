# API Response Restructuring (GET /pokemons/to_choose — Type Color) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Populate the `color` field in type objects returned by `GET /pokemons/to_choose` — currently hardcoded as `''` in `ElectionPokemonResponseFactory::buildType()` because neither SQL query selects the color columns.

**Architecture:** Add `pt.color` and `st.color` to both existing SQL queries (`pokemons-get_n_to_pick.sql` and `pokemons-get_n_to_vote.sql`), update `buildType()` to read those columns from the row instead of hardcoding an empty string, then tighten unit and integration tests to assert actual hex color values.

**Tech Stack:** Symfony 8, PHP 8.5, PostgreSQL, PHPUnit

---

## File Structure

**Modify:**
- `resources/sql/pokemons-get_n_to_pick.sql` — add `pt.color AS primary_type_color` and `st.color AS secondary_type_color` to SELECT
- `resources/sql/pokemons-get_n_to_vote.sql` — same additions
- `src/Factory/ElectionPokemonResponseFactory.php` — `buildType()` reads the color column instead of hardcoding `''`
- `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php` — add color keys to every SQL row fixture; add/update color value assertions; add cast test
- `tests/src/Integration/Controller/PokemonsControllerTest.php` — strengthen the `color` assertion from `assertSame('', ...)` to value-matches hex format

---

## Tasks

### Task 1: Add type color columns to the pick SQL query

**Files:**
- Modify: `resources/sql/pokemons-get_n_to_pick.sql:24-29`

- [ ] **Step 1: Open the SQL file and find the type SELECT block**

Current lines 24–29 of `resources/sql/pokemons-get_n_to_pick.sql`:

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

The `LEFT JOIN "type" AS pt` and `LEFT JOIN "type" AS st` joins are already in the FROM clause — `type.color` is a real column so no schema change is needed.

---

### Task 2: Add type color columns to the vote SQL query

**Files:**
- Modify: `resources/sql/pokemons-get_n_to_vote.sql:46-51`

- [ ] **Step 1: Open the SQL file and find the type SELECT block**

Current lines 46–51 of `resources/sql/pokemons-get_n_to_vote.sql`:

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

---

### Task 3: Update ElectionPokemonResponseFactory to use the color column

**Files:**
- Modify: `src/Factory/ElectionPokemonResponseFactory.php:184-208`

- [ ] **Step 1: Open the factory and locate `buildType()`**

Current `buildType()` (lines 184–208):

```php
    private static function buildType(string $prefix, array $row): ?TypeResponse
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

        return new TypeResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            color: '',
        );
    }
```

- [ ] **Step 2: Replace with the version that reads the color column**

```php
    private static function buildType(string $prefix, array $row): ?TypeResponse
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

        return new TypeResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            color: (string) $color,
        );
    }
```

---

### Task 4: Update unit tests to cover type color

**Files:**
- Modify: `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`

Every test method that builds a SQL row with type fields must now include `primary_type_color` and `secondary_type_color`. Existing color assertions that check for `''` must be updated to real values, and a new test must verify the `(string)` cast.

- [ ] **Step 1: Update `buildRow()` helper to include color keys**

Current `buildRow()` (lines 323–358) returns an array without color keys. Replace the type-related block (lines 349–355) from:

```php
            'primary_type_slug' => 'grass',
            'primary_type_name' => 'Grass',
            'primary_type_french_name' => 'Plante',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
```

to:

```php
            'primary_type_slug' => 'grass',
            'primary_type_name' => 'Grass',
            'primary_type_french_name' => 'Plante',
            'primary_type_color' => '#78C850',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
```

- [ ] **Step 2: Update `fromSqlRowWithPrimaryTypeReturnsPrimaryTypeResponse`**

The test currently asserts `self::assertSame('', $response->types->primary->color)` (line 179). Change that assertion to:

```php
        self::assertSame('#78C850', $response->types->primary->color);
```

- [ ] **Step 3: Update `fromSqlRowWithSecondaryTypeReturnsSecondaryTypeResponse`**

The test builds a row with a secondary type (poison) but no `secondary_type_color`. Add the color override and update the assertion.

Replace the `buildRow()` call from:

```php
        $row = $this->buildRow([
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
        ]);
```

to:

```php
        $row = $this->buildRow([
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
            'secondary_type_color' => '#A040A0',
        ]);
```

Change the color assertion from `self::assertSame('', $response->types->secondary->color)` to:

```php
        self::assertSame('#A040A0', $response->types->secondary->color);
```

- [ ] **Step 4: Add new test `fromSqlRowCastsColorToString` to verify the `(string)` cast**

Add this test method after `fromSqlRowWithSecondaryTypeReturnsSecondaryTypeResponse`:

```php
    #[Test]
    public function fromSqlRowCastsColorToString(): void
    {
        $row = $this->buildRow([
            'primary_type_color' => 42,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame('42', $response->types->primary->color);
    }
```

This test kills the mutation that removes or changes the `(string)` cast in `buildType()`.

---

### Task 5: Strengthen integration test color assertion

**Files:**
- Modify: `tests/src/Integration/Controller/PokemonsControllerTest.php:156-157`

`assertResponseContent()` currently asserts `$this->assertSame('', $primary['color'])` (lines 156–157). This confirms the broken behavior. Replace with a hex format assertion.

- [ ] **Step 1: Locate the existing `assertSame('')` for color**

Find lines 155–158 inside `assertResponseContent()`:

```php
                $this->assertArrayHasKey('color', $primary);
                $this->assertSame('', $primary['color']);
```

- [ ] **Step 2: Replace with hex format assertion**

Replace those two lines with:

```php
                $this->assertArrayHasKey('color', $primary);
                $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $primary['color']);
```

---

### Task 6: Run quality checks

**Files:**
- All modified files from Tasks 1–5

- [ ] **Step 1: Run all tests**

```bash
make tests
```

Expected: All unit and integration tests pass, 0 failures.

- [ ] **Step 2: Run code quality checks**

```bash
make quality
```

Expected: PHP CS Fixer, PHPStan level 9, Psalm strict, Deptrac, PHPMD — all green.

- [ ] **Step 3: Run coverage and mutation checks**

```bash
make measures
```

Expected: 100% line coverage, 100% MSI for `ElectionPokemonResponseFactory`.
