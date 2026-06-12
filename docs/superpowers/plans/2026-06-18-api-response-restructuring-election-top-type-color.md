# API Response Restructuring (GET /election/top — Type Color) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Populate the `color` field in type objects returned by `GET /election/top` — currently hardcoded as `''` in `ElectionEloResponseFactory::buildType()` because the SQL query never selects the color columns.

**Architecture:** Add `pt.color` and `st.color` to the existing SQL query, update `buildType()` to read those columns from the row instead of hardcoding an empty string, then tighten unit and integration tests to assert actual hex color values.

**Tech Stack:** Symfony 8, PHP 8.5, PostgreSQL, PHPUnit

---

## File Structure

**Modify:**
- `resources/sql/trainer_pokemon_elo-get_top_n.sql` — add `pt.color AS primary_type_color` and `st.color AS secondary_type_color` to SELECT
- `src/Factory/ElectionEloResponseFactory.php` — `buildType()` reads the color column instead of hardcoding `''`
- `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php` — add color keys to every SQL row fixture; add color value assertions
- `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php` — strengthen the `color` assertion from key-exists to value-matches hex format

---

## Tasks

### Task 1: Add type color columns to the SQL query

**Files:**
- Modify: `resources/sql/trainer_pokemon_elo-get_top_n.sql:75-80`

- [ ] **Step 1: Open the SQL file and find the type SELECT block**

Current lines 75–80 of `resources/sql/trainer_pokemon_elo-get_top_n.sql`:

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

The `LEFT JOIN "type" AS pt` and `LEFT JOIN "type" AS st` joins are already in the FROM clause (lines 97–98) — `type.color` is a real column so no schema change is needed.

---

### Task 2: Update ElectionEloResponseFactory to use the color column

**Files:**
- Modify: `src/Factory/ElectionEloResponseFactory.php:185-210`

- [ ] **Step 1: Open the factory and locate `buildType()`**

Current `buildType()` (lines 185–210):

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

### Task 3: Update unit tests to cover type color

**Files:**
- Modify: `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php`

Every test method that builds a SQL row with type fields must now include `primary_type_color` and `secondary_type_color`, and must assert the color value so mutation testing cannot flip `(string) $color` back to `''`.

- [ ] **Step 1: Update `fromSqlRowTransformsSingleRowCorrectly`**

Pikachu — electric primary, no secondary. Add the two color keys immediately after the existing `primary_type_french_name` and `secondary_type_french_name` entries:

```php
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
```

Add a color assertion after `self::assertSame('electric', $response->types->primary->slug)`:

```php
        self::assertSame('#FFCC33', $response->types->primary->color);
```

- [ ] **Step 2: Update `fromSqlRowHandlesFormsWhenPresent`**

Rotom — electric primary, fire secondary. Add color keys:

```php
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => 'fire',
            'secondary_type_name' => 'Fire',
            'secondary_type_french_name' => 'Feu',
            'secondary_type_color' => '#F08030',
```

Add color assertions after `self::assertSame('fire', $response->types->secondary->slug)`:

```php
        self::assertSame('#FFCC33', $response->types->primary->color);
        self::assertSame('#F08030', $response->types->secondary->color);
```

- [ ] **Step 3: Update `fromSqlRowCastsBooleanSignificanceCorrectly`**

Bulbasaur — grass primary, poison secondary. Add color keys:

```php
            'primary_type_slug' => 'grass',
            'primary_type_name' => 'Grass',
            'primary_type_french_name' => 'Plante',
            'primary_type_color' => '#78C850',
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
            'secondary_type_color' => '#A040A0',
```

Add color assertions after `self::assertFalse($response->significance)`:

```php
        self::assertSame('#78C850', $response->types->primary->color);
        self::assertSame('#A040A0', $response->types->secondary->color);
```

- [ ] **Step 4: Update `fromSqlRowsTransformsMultipleRowsCorrectly`**

First row (pikachu/electric, no secondary):

```php
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
```

Second row (charizard/fire+flying):

```php
            'primary_type_slug' => 'fire',
            'primary_type_name' => 'Fire',
            'primary_type_french_name' => 'Feu',
            'primary_type_color' => '#F08030',
            'secondary_type_slug' => 'flying',
            'secondary_type_name' => 'Flying',
            'secondary_type_french_name' => 'Vol',
            'secondary_type_color' => '#A890F0',
```

Add color assertions after `self::assertSame(1150.0, $responses[1]->elo)`:

```php
        self::assertSame('#FFCC33', $responses[0]->types->primary->color);
        self::assertSame('#F08030', $responses[1]->types->primary->color);
        self::assertNotNull($responses[1]->types->secondary);
        self::assertSame('#A890F0', $responses[1]->types->secondary->color);
```

- [ ] **Step 5: Update `fromSqlRowCastsNullableStringFieldsFromNonStringValues`**

Eevee — normal primary, no secondary. Use a non-string color value to verify the `(string)` cast is exercised:

```php
            'primary_type_slug' => 'normal',
            'primary_type_name' => 'Normal',
            'primary_type_french_name' => 'Normal',
            'primary_type_color' => 42,
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
```

Add color assertion after `self::assertSame('55', $response->pokemon->originalGameBundle->slug)`:

```php
        self::assertSame('42', $response->types->primary->color);
```

- [ ] **Step 6: Update `fromSqlRowBuildsFormsWhenOnlyRegionalFormIsPresent`**

Vulpix Alola — ice primary, no secondary. Add color keys:

```php
            'primary_type_slug' => 'ice',
            'primary_type_name' => 'Ice',
            'primary_type_french_name' => 'Glace',
            'primary_type_color' => '#98D8D8',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
```

Add color assertion after `self::assertNull($response->pokemon->originalGameBundle)`:

```php
        self::assertSame('#98D8D8', $response->types->primary->color);
```

- [ ] **Step 7: Update `fromSqlRowCastsNumericFieldsCorrectly`**

Alakazam — psychic primary, no secondary. Add color keys:

```php
            'primary_type_slug' => 'psychic',
            'primary_type_name' => 'Psychic',
            'primary_type_french_name' => 'Psy',
            'primary_type_color' => '#F85888',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
```

Add color assertion after `self::assertSame(4, $response->pokemon->familyOrder)`:

```php
        self::assertSame('#F85888', $response->types->primary->color);
```

---

### Task 4: Strengthen integration test color assertion

**Files:**
- Modify: `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php:71`

`testGetTop()` already checks `assertArrayHasKey('color', $types['primary'])` (line 71). Extend it to verify the value is a valid hex color.

- [ ] **Step 1: Locate the existing `assertArrayHasKey` for color**

Find lines 64–72 inside the `foreach ($content as $item)` loop in `testGetTop()`:

```php
            $types = $item['types'];
            $this->assertIsArray($types);
            $this->assertArrayHasKey('primary', $types);
            $this->assertIsArray($types['primary']);
            $this->assertArrayHasKey('slug', $types['primary']);
            $this->assertArrayHasKey('name', $types['primary']);
            $this->assertArrayHasKey('french_name', $types['primary']);
            $this->assertArrayHasKey('color', $types['primary']);
```

- [ ] **Step 2: Add the hex format assertion immediately after**

Replace that block with:

```php
            $types = $item['types'];
            $this->assertIsArray($types);
            $this->assertArrayHasKey('primary', $types);
            $this->assertIsArray($types['primary']);
            $this->assertArrayHasKey('slug', $types['primary']);
            $this->assertArrayHasKey('name', $types['primary']);
            $this->assertArrayHasKey('french_name', $types['primary']);
            $this->assertArrayHasKey('color', $types['primary']);
            $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $types['primary']['color']);
```

---

### Task 5: Run quality checks

**Files:**
- All modified files from Tasks 1–4

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

Expected: 100% line coverage, 100% MSI for `ElectionEloResponseFactory`.
