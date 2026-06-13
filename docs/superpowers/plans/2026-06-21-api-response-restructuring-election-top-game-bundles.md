# Election Top — game_bundles & game_bundles_shiny Population Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Populate `game_bundles` and `game_bundles_shiny` in `GET /election/top` responses, which currently hardcode empty arrays `[]`, by joining the `pokemon_availabilities` table in the SQL query and parsing the comma-separated result in the factory.

**Architecture:** The SQL query `trainer_pokemon_elo-get_top_n.sql` gains two LEFT JOINs on `pokemon_availabilities`; the repository `getTopN()` gains two new named parameters; `ElectionEloResponseFactory::buildPokemonData()` replaces the hardcoded `gameBundles: [], gameBundlesShiny: []` with comma-split parsing of the new SQL columns. No new classes are created — only existing files are modified.

**Tech Stack:** PHP 8.5, Symfony 8, Doctrine DBAL, PHPUnit

---

## File Structure

**Modify:**
- `resources/sql/trainer_pokemon_elo-get_top_n.sql` — add LEFT JOINs on `pokemon_availabilities` and SELECT columns
- `src/Repository/TrainerPokemonEloRepository.php` — add `pokemon_availabilities_game_bundle_category` and `pokemon_availabilities_game_bundle_shiny_category` params to `getTopN()`
- `src/Factory/ElectionEloResponseFactory.php` — replace hardcoded `gameBundles: [], gameBundlesShiny: []` with parsed values
- `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php` — add `game_bundle_slugs`/`game_bundle_shiny_slugs` keys to all existing test rows; add 4 new test methods
- `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php` — add `game_bundles`/`game_bundles_shiny` assertions

**Create:**
- `docs/api-migration/election-top-game-bundles.md` — client migration documentation

---

## Tasks

### Task 1: Update SQL query

**Files:**
- Modify: `resources/sql/trainer_pokemon_elo-get_top_n.sql`

Context: the query currently SELECTs `ogb.slug AS original_game_bundle_slug` but does not fetch availability slugs. The `PokedexRepository` (for the album endpoint) shows the exact JOIN pattern to replicate: `LEFT JOIN pokemon_availabilities AS pagb ON p.id = pagb.pokemon_id AND pagb.category = :pokemon_availabilities_game_bundle_category`.

- [ ] **Step 1: Add the two SELECT columns after `ogb.slug AS original_game_bundle_slug,`**

Current line 83:
```sql
    ogb.slug AS original_game_bundle_slug,
```

Replace with:
```sql
    ogb.slug AS original_game_bundle_slug,
    pagb.items AS game_bundle_slugs,
    pagbs.items AS game_bundle_shiny_slugs,
```

- [ ] **Step 2: Add the two LEFT JOINs after `LEFT JOIN game_bundle AS ogb ON p.original_game_bundle_id = ogb.id`**

Current line 102:
```sql
    LEFT JOIN game_bundle AS ogb ON p.original_game_bundle_id = ogb.id
```

Replace with:
```sql
    LEFT JOIN game_bundle AS ogb ON p.original_game_bundle_id = ogb.id
    LEFT JOIN pokemon_availabilities AS pagb
        ON p.id = pagb.pokemon_id AND pagb.category = :pokemon_availabilities_game_bundle_category
    LEFT JOIN pokemon_availabilities AS pagbs
        ON p.id = pagbs.pokemon_id AND pagbs.category = :pokemon_availabilities_game_bundle_shiny_category
```

- [ ] **Step 3: Verify the full final SQL file looks like this**

```sql
WITH total AS (
    SELECT COUNT(1) AS count
    FROM dex_availability AS da
        JOIN dex AS d ON da.dex_id = d.id
        AND d.slug = :dex_slug
),
views AS (
    SELECT COUNT(1) AS count
    FROM trainer_pokemon_elo AS tpe
        JOIN dex AS d ON tpe.dex_id = d.id
        AND d.slug = :dex_slug
    WHERE trainer_external_id = :trainer_external_id
        AND election_slug = :election_slug
),
elo AS (
    SELECT tpe.elo,
        tpe.pokemon_id
    FROM trainer_pokemon_elo tpe
        JOIN dex AS d ON tpe.dex_id = d.id
        AND d.slug = :dex_slug
    WHERE trainer_external_id = :trainer_external_id
        AND election_slug = :election_slug
),
stats AS (
    SELECT AVG(elo) AS avg_elo,
        STDDEV(elo) AS stddev_elo
    FROM elo
),
variables AS (
    SELECT CASE
            WHEN 0 < t.count THEN COALESCE(
                1 / NULLIF(
                    AVG(
                        CASE
                            WHEN t.count > 0 THEN v.count * 1.0 / t.count
                            ELSE NULL
                        END
                    ),
                    0
                ),
                0
            )
            ELSE 0
        END AS multiplier
    FROM views AS v,
        total AS t
    GROUP BY t.count,
        v.count
)
SELECT e.elo AS elo,
    e.elo > (s.avg_elo + (s.stddev_elo * var.multiplier)) AS significance,
    p.slug AS pokemon_slug,
    p.name AS pokemon_name,
    p.national_dex_number AS pokemon_national_dex_number,
    p.simplified_name AS pokemon_simplified_name,
    p.forms_label AS pokemon_forms_label,
    p.french_name AS pokemon_french_name,
    p.simplified_french_name AS pokemon_simplified_french_name,
    p.forms_french_label AS pokemon_forms_french_label,
    p.icon_name AS pokemon_icon,
    p.family_order AS pokemon_family_order,
    pp.slug AS family_lead_slug,
    cf.slug as category_form_slug,
    cf.name as category_form_name,
    cf.french_name as category_form_french_name,
    rf.slug as regional_form_slug,
    rf.name as regional_form_name,
    rf.french_name as regional_form_french_name,
    sf.slug as special_form_slug,
    sf.name as special_form_name,
    sf.french_name as special_form_french_name,
    vf.slug as variant_form_slug,
    vf.name as variant_form_name,
    vf.french_name as variant_form_french_name,
    pt.slug AS primary_type_slug,
    pt.name AS primary_type_name,
    pt.french_name AS primary_type_french_name,
    pt.color AS primary_type_color,
    st.slug AS secondary_type_slug,
    st.name AS secondary_type_name,
    st.french_name AS secondary_type_french_name,
    st.color AS secondary_type_color,
    ogb.slug AS original_game_bundle_slug,
    pagb.items AS game_bundle_slugs,
    pagbs.items AS game_bundle_shiny_slugs,
    CONCAT(
        '9999',
        '-',
        LPAD(CAST(p.national_dex_number AS varchar), 4, '0'),
        '-',
        LPAD(CAST(p.family_order AS varchar), 3, '0')
    ) as pokemon_order_number
FROM stats AS s,
    variables AS var,
    elo AS e
    JOIN pokemon AS p ON e.pokemon_id = p.id
    LEFT JOIN category_form AS cf ON p.category_form_id = cf.id
    LEFT JOIN regional_form AS rf ON p.regional_form_id = rf.id
    LEFT JOIN special_form AS sf ON p.special_form_id = sf.id
    LEFT JOIN variant_form AS vf ON p.variant_form_id = vf.id
    LEFT JOIN "type" AS pt ON p.primary_type_id = pt.id
    LEFT JOIN "type" AS st ON p.secondary_type_id = st.id
    LEFT JOIN pokemon AS pp ON p.family = pp.slug
    LEFT JOIN game_bundle AS ogb ON p.original_game_bundle_id = ogb.id
    LEFT JOIN pokemon_availabilities AS pagb
        ON p.id = pagb.pokemon_id AND pagb.category = :pokemon_availabilities_game_bundle_category
    LEFT JOIN pokemon_availabilities AS pagbs
        ON p.id = pagbs.pokemon_id AND pagbs.category = :pokemon_availabilities_game_bundle_shiny_category
ORDER BY e.elo DESC,
    p.slug ASC
LIMIT :count
```

---

### Task 2: Update repository params

**Files:**
- Modify: `src/Repository/TrainerPokemonEloRepository.php`

Context: `getTopN()` currently passes 4 params/types. Two new string params for the availability category constants are needed. `PokemonAvailabilities::CATEGORY_GAME_BUNDLE = 'game_bundle'` and `CATEGORY_GAME_BUNDLE_SHINY = 'game_bundle_shiny'`.

- [ ] **Step 1: Add the `use` statement for `PokemonAvailabilities`**

In the `use` block at the top of the file, after the existing `use` statements, add:

```php
use App\Entity\PokemonAvailabilities;
```

- [ ] **Step 2: Update the `getTopN()` method — extend `$params` array**

Current `$params` in `getTopN()` (lines 140–145):
```php
        $params = [
            'trainer_external_id' => $trainerExternalId,
            'dex_slug' => $dexSlug,
            'election_slug' => $electionSlug,
            'count' => $count,
        ];
```

Replace with:
```php
        $params = [
            'trainer_external_id' => $trainerExternalId,
            'dex_slug' => $dexSlug,
            'election_slug' => $electionSlug,
            'count' => $count,
            'pokemon_availabilities_game_bundle_category' => PokemonAvailabilities::CATEGORY_GAME_BUNDLE,
            'pokemon_availabilities_game_bundle_shiny_category' => PokemonAvailabilities::CATEGORY_GAME_BUNDLE_SHINY,
        ];
```

- [ ] **Step 3: Extend `$types` array**

Current `$types` in `getTopN()` (lines 147–152):
```php
        $types = [
            'trainer_external_id' => ParameterType::STRING,
            'dex_slug' => ParameterType::STRING,
            'election_slug' => ParameterType::STRING,
            'count' => ParameterType::INTEGER,
        ];
```

Replace with:
```php
        $types = [
            'trainer_external_id' => ParameterType::STRING,
            'dex_slug' => ParameterType::STRING,
            'election_slug' => ParameterType::STRING,
            'count' => ParameterType::INTEGER,
            'pokemon_availabilities_game_bundle_category' => ParameterType::STRING,
            'pokemon_availabilities_game_bundle_shiny_category' => ParameterType::STRING,
        ];
```

---

### Task 3: Update factory

**Files:**
- Modify: `src/Factory/ElectionEloResponseFactory.php`

Context: `buildPokemonData()` currently hardcodes `gameBundles: [], gameBundlesShiny: []` at lines 115–116. The SQL row now contains `game_bundle_slugs` (comma-separated string or `null`) and `game_bundle_shiny_slugs`. Pattern to follow: `AlbumPokemonService::explodesFlatList()` uses `array_filter(explode(',', $slugString))`.

- [ ] **Step 1: Replace the hardcoded empty arrays in `buildPokemonData()`**

Find this block at the end of `buildPokemonData()` (lines 96–117):
```php
        return new PokemonDataResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            nationalDexNumber: (int) $nationalDexNumber,
            regionalDexNumber: null,
            simplifiedName: null !== $simplifiedName ? (string) $simplifiedName : null,
            formsLabel: null !== $formsLabel ? (string) $formsLabel : null,
            simplifiedFrenchName: null !== $simplifiedFrenchName ? (string) $simplifiedFrenchName : null,
            formsFrenchLabel: null !== $formsFrenchLabel ? (string) $formsFrenchLabel : null,
            icon: null !== $icon ? (string) $icon : null,
            familyOrder: (int) $familyOrder,
            familyLead: null !== $familyLeadSlug
                ? new PokemonSlugResponse(slug: (string) $familyLeadSlug)
                : null,
            originalGameBundle: null !== $originalGameBundleSlug
                ? new GameBundleSlugResponse(slug: (string) $originalGameBundleSlug)
                : null,
            orderNumber: (string) $orderNumber,
            gameBundles: [],
            gameBundlesShiny: [],
        );
```

Replace with:
```php
        /** @var array<string> $gameBundleSlugs */
        $gameBundleSlugs = array_filter(explode(',', (string) ($row['game_bundle_slugs'] ?? '')));

        $gameBundles = array_map(
            static fn (string $slug): GameBundleSlugResponse => new GameBundleSlugResponse(slug: $slug),
            $gameBundleSlugs,
        );

        /** @var array<string> $gameBundleShinySlug */
        $gameBundleShinySlug = array_filter(explode(',', (string) ($row['game_bundle_shiny_slugs'] ?? '')));

        $gameBundlesShiny = array_map(
            static fn (string $slug): GameBundleSlugResponse => new GameBundleSlugResponse(slug: $slug),
            $gameBundleShinySlug,
        );

        return new PokemonDataResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            nationalDexNumber: (int) $nationalDexNumber,
            regionalDexNumber: null,
            simplifiedName: null !== $simplifiedName ? (string) $simplifiedName : null,
            formsLabel: null !== $formsLabel ? (string) $formsLabel : null,
            simplifiedFrenchName: null !== $simplifiedFrenchName ? (string) $simplifiedFrenchName : null,
            formsFrenchLabel: null !== $formsFrenchLabel ? (string) $formsFrenchLabel : null,
            icon: null !== $icon ? (string) $icon : null,
            familyOrder: (int) $familyOrder,
            familyLead: null !== $familyLeadSlug
                ? new PokemonSlugResponse(slug: (string) $familyLeadSlug)
                : null,
            originalGameBundle: null !== $originalGameBundleSlug
                ? new GameBundleSlugResponse(slug: (string) $originalGameBundleSlug)
                : null,
            orderNumber: (string) $orderNumber,
            gameBundles: $gameBundles,
            gameBundlesShiny: $gameBundlesShiny,
        );
```

---

### Task 4: Update unit tests

**Files:**
- Modify: `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php`

Context: all 7 existing test rows are missing `game_bundle_slugs` and `game_bundle_shiny_slugs` keys. These must be added (as `null`) to every row so PHP doesn't trigger undefined-key warnings and so rows accurately reflect the shape of SQL results. Four new test methods are needed to achieve 100% coverage and 100% MSI for the new factory logic.

- [ ] **Step 1: Add `game_bundle_slugs => null` and `game_bundle_shiny_slugs => null` to every existing test row**

In every `$row = [...]` array in the 7 existing test methods, append these two entries at the end of the row (before the closing `]`):
```php
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
```

Apply this to all 7 test methods:
- `fromSqlRowTransformsSingleRowCorrectly`
- `fromSqlRowHandlesFormsWhenPresent`
- `fromSqlRowCastsBooleanSignificanceCorrectly`
- `fromSqlRowsTransformsMultipleRowsCorrectly` (both rows in the `$rows` array)
- `fromSqlRowsHandlesEmptyArray` (no row to update)
- `fromSqlRowCastsNullableStringFieldsFromNonStringValues`
- `fromSqlRowBuildsFormsWhenOnlyRegionalFormIsPresent`
- `fromSqlRowCastsNumericFieldsCorrectly`

- [ ] **Step 2: Add 4 new test methods at the end of the class, before the closing `}`**

```php
    #[Test]
    public function fromSqlRowBuildsEmptyGameBundlesWhenSlugsAreNull(): void
    {
        $row = [
            'elo' => 1200.0,
            'significance' => false,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0025-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'category_form_french_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'regional_form_french_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'special_form_french_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'variant_form_french_name' => null,
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles);
        self::assertSame([], $response->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowBuildsEmptyGameBundlesWhenSlugsAreEmptyString(): void
    {
        $row = [
            'elo' => 1200.0,
            'significance' => false,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0025-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'category_form_french_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'regional_form_french_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'special_form_french_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'variant_form_french_name' => null,
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => '',
            'game_bundle_shiny_slugs' => '',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles);
        self::assertSame([], $response->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowBuildsPopulatedGameBundlesFromCommaSeparatedSlugs(): void
    {
        $row = [
            'elo' => 1200.0,
            'significance' => false,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0025-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'category_form_french_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'regional_form_french_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'special_form_french_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'variant_form_french_name' => null,
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => 'redgreenblueyellow,goldsilvercrystal',
            'game_bundle_shiny_slugs' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertCount(2, $response->pokemon->gameBundles);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundles[0]);
        self::assertSame('redgreenblueyellow', $response->pokemon->gameBundles[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundles[1]);
        self::assertSame('goldsilvercrystal', $response->pokemon->gameBundles[1]->slug);
        self::assertSame([], $response->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowBuildsPopulatedGameBundlesShinyFromCommaSeparatedSlugs(): void
    {
        $row = [
            'elo' => 1200.0,
            'significance' => false,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0025-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'category_form_french_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'regional_form_french_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'special_form_french_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'variant_form_french_name' => null,
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => 'heartgoldsoulsilver',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles);
        self::assertCount(1, $response->pokemon->gameBundlesShiny);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundlesShiny[0]);
        self::assertSame('heartgoldsoulsilver', $response->pokemon->gameBundlesShiny[0]->slug);
    }
```

---

### Task 5: Update integration test

**Files:**
- Modify: `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`

Context: `testGetTop` asserts on `pokemon` fields but currently omits `game_bundles` and `game_bundles_shiny`. These must now be verified. The test uses fixture data from the `home` dex with `favorite` election — some Pokémon there will have game_bundles, some won't; we verify the keys exist and are arrays.

- [ ] **Step 1: Add `game_bundles` and `game_bundles_shiny` assertions inside the `foreach` loop of `testGetTop`**

Current `testGetTop` ends its `foreach` loop with `$types` assertions (lines 64–73). After the existing types assertions block and before the closing `}` of the `foreach`, add:

```php
            $this->assertArrayHasKey('game_bundles', $pokemon);
            $this->assertIsArray($pokemon['game_bundles']);
            $this->assertArrayHasKey('game_bundles_shiny', $pokemon);
            $this->assertIsArray($pokemon['game_bundles_shiny']);

            foreach ($pokemon['game_bundles'] as $gameBundle) {
                $this->assertArrayHasKey('slug', $gameBundle);
                $this->assertIsString($gameBundle['slug']);
            }

            foreach ($pokemon['game_bundles_shiny'] as $gameBundleShiny) {
                $this->assertArrayHasKey('slug', $gameBundleShiny);
                $this->assertIsString($gameBundleShiny['slug']);
            }
```

- [ ] **Step 2: Add `ElectionEloResponseFactory` to the `#[CoversClass]` list**

At the top of `TrainerPokemonEloControllerTest`, add the import and attribute:

```php
use App\Factory\ElectionEloResponseFactory;
```

```php
#[CoversClass(ElectionEloResponseFactory::class)]
```

---

### Task 6: Create client migration documentation

**Files:**
- Create: `docs/api-migration/election-top-game-bundles.md`

- [ ] **Step 1: Create the migration doc**

```markdown
# Election Top — game_bundles & game_bundles_shiny Population

**Endpoint:** `GET /election/top`
**Change type:** Additive (new fields populated — was always present but always empty `[]`)
**Status:** Live

## Summary

The `game_bundles` and `game_bundles_shiny` arrays in `GET /election/top` Pokémon objects were previously always returned as empty arrays `[]`. They now contain the actual game-bundle slugs from the `pokemon_availabilities` table, matching the behavior already live in `GET /album/{trainerExternalId}/{dexSlug}`.

## Response Comparison

### Before

```json
{
  "pokemon": {
    "slug": "pikachu",
    "game_bundles": [],
    "game_bundles_shiny": []
  }
}
```

### After

```json
{
  "pokemon": {
    "slug": "pikachu",
    "game_bundles": [
      { "slug": "redgreenblueyellow" },
      { "slug": "goldsilvercrystal" }
    ],
    "game_bundles_shiny": [
      { "slug": "heartgoldsoulsilver" }
    ]
  }
}
```

## Impact Assessment

### pokenini-back

**Change required:** Forward the populated arrays — update Moco fixture for `GET /election/top` if it hard-codes `"game_bundles": []`.

### pokenini-web

**Change required:** Optionally render game bundle badges for top-ELO Pokémon. The field was always present so no structural change needed.

## Questions?

See the design spec: `docs/superpowers/specs/2026-05-25-api-response-restructuring-design.md`
```

---

## Self-Review

**Spec coverage:**
- SQL JOINs → Task 1 ✓
- Repository params → Task 2 ✓
- Factory parsing → Task 3 ✓
- Unit tests (100% coverage + 100% MSI) → Task 4 ✓
  - null slugs → `fromSqlRowBuildsEmptyGameBundlesWhenSlugsAreNull` (kills `?? 'X'` mutation)
  - empty string → `fromSqlRowBuildsEmptyGameBundlesWhenSlugsAreEmptyString` (kills `array_filter` removal mutation)
  - comma-separated → `fromSqlRowBuildsPopulatedGameBundlesFromCommaSeparatedSlugs` (kills `','` delimiter mutation and array_map mutations)
  - shiny path → `fromSqlRowBuildsPopulatedGameBundlesShinyFromCommaSeparatedSlugs` (verifies shiny code independently)
- Integration test → Task 5 ✓
- Migration doc → Task 6 ✓

**Placeholder scan:** No TBDs. All code blocks contain runnable code.

**Type consistency:** `GameBundleSlugResponse` used in factory (Task 3) and imported in test file (already imported in `ElectionEloResponseFactoryTest.php`). `PokemonAvailabilities` added to repository use block (Task 2).
