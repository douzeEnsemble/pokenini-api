# API Response Restructuring (Pokemons To Choose — game_bundles) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Populate `game_bundles` and `game_bundles_shiny` in `GET /pokemons/to_choose` responses — currently hardcoded as `[]` in `ElectionPokemonResponseFactory::buildPokemon()`.

**Architecture:** Add `LEFT JOIN pokemon_availabilities` to both SQL files (`pokemons-get_n_to_pick.sql` and `pokemons-get_n_to_vote.sql`), pass the two category parameters to both `PokemonsRepository` methods, and update `ElectionPokemonResponseFactory::buildPokemon()` to parse the comma-separated slug strings exactly as `ElectionEloResponseFactory` does. The `PokemonDataResponse` DTO already accepts `gameBundles` and `gameBundlesShiny`; no DTO changes needed.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, PostgreSQL, PHPUnit

---

## File Structure

**Modify:**
- `resources/sql/pokemons-get_n_to_pick.sql` — add `pagb.items AS game_bundle_slugs`, `pagbs.items AS game_bundle_shiny_slugs` to SELECT and two LEFT JOINs on `pokemon_availabilities`
- `resources/sql/pokemons-get_n_to_vote.sql` — identical changes as pick SQL
- `src/Repository/PokemonsRepository.php` — add `PokemonAvailabilities` import; add `pokemon_availabilities_game_bundle_category` and `pokemon_availabilities_game_bundle_shiny_category` to `$params` and `$types` in both `getNToPick()` and `getNToVote()`
- `src/Factory/ElectionPokemonResponseFactory.php` — replace `gameBundles: [], gameBundlesShiny: []` with the comma-split parsing logic
- `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php` — add `game_bundle_slugs: null, game_bundle_shiny_slugs: null` to `buildRow()` defaults; add 4 new test methods covering null, empty-string, populated game_bundles, and populated game_bundles_shiny
- `tests/src/Integration/Controller/PokemonsControllerTest.php` — add slug-structure assertions inside the `game_bundles` / `game_bundles_shiny` arrays

---

## Context: Before / After

### Before (`GET /pokemons/to_choose`, any Pokémon)

```json
{
  "pokemon": {
    "slug": "bulbasaur",
    "game_bundles": [],
    "game_bundles_shiny": []
  }
}
```

### After

```json
{
  "pokemon": {
    "slug": "bulbasaur",
    "game_bundles": [{"slug": "redgreenblueyellow"}],
    "game_bundles_shiny": [{"slug": "redgreenblueyellow"}]
  }
}
```

---

## Tasks

### Task 1: Update SQL files

**Files:**
- Modify: `resources/sql/pokemons-get_n_to_pick.sql`
- Modify: `resources/sql/pokemons-get_n_to_vote.sql`

Both files need the same two changes: add `game_bundle_slugs` and `game_bundle_shiny_slugs` to the SELECT, and add two LEFT JOINs on `pokemon_availabilities`.

- [ ] **Step 1: Update `resources/sql/pokemons-get_n_to_pick.sql`**

Replace:
```sql
    ogb.slug AS original_game_bundle_slug,
    CONCAT(
```

With:
```sql
    ogb.slug AS original_game_bundle_slug,
    pagb.items AS game_bundle_slugs,
    pagbs.items AS game_bundle_shiny_slugs,
    CONCAT(
```

Then replace:
```sql
    LEFT JOIN game_bundle AS ogb ON p.original_game_bundle_id = ogb.id
    JOIN dex_availability AS da ON p.id = da.pokemon_id
```

With:
```sql
    LEFT JOIN game_bundle AS ogb ON p.original_game_bundle_id = ogb.id
    LEFT JOIN pokemon_availabilities AS pagb
        ON p.id = pagb.pokemon_id AND pagb.category = :pokemon_availabilities_game_bundle_category
    LEFT JOIN pokemon_availabilities AS pagbs
        ON p.id = pagbs.pokemon_id AND pagbs.category = :pokemon_availabilities_game_bundle_shiny_category
    JOIN dex_availability AS da ON p.id = da.pokemon_id
```

Final file should be:
```sql
SELECT p.slug AS pokemon_slug,
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
FROM pokemon AS p
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
    JOIN dex_availability AS da ON p.id = da.pokemon_id
    JOIN dex AS d ON da.dex_id = d.id
    AND d.slug = :dex_slug
WHERE NOT EXISTS (
        SELECT 1
        FROM trainer_pokemon_elo AS tpe
        WHERE p.id = tpe.pokemon_id
            AND tpe.trainer_external_id = :trainer_external_id
            AND tpe.dex_id = d.id
            AND tpe.election_slug = :election_slug
    ) -- {album_filters}
ORDER BY RANDOM()
LIMIT :count
```

- [ ] **Step 2: Update `resources/sql/pokemons-get_n_to_vote.sql`**

Apply the same two changes to the vote SQL (same SELECT additions and same LEFT JOINs after `LEFT JOIN game_bundle AS ogb`). The vote SQL is a WITH/CTE query but the main SELECT and FROM clause are identical to the pick SQL and receive the same additions.

Final file should be:
```sql
WITH stats AS (
    SELECT MAX(view_count) AS max_view
    FROM trainer_pokemon_elo AS tpe
        JOIN dex AS d ON tpe.dex_id = d.id
        AND d.slug = :dex_slug
    WHERE tpe.trainer_external_id = :trainer_external_id
        AND tpe.election_slug = :election_slug
),
variables AS (
    SELECT COUNT(
            CASE
                WHEN tpe.view_count = s.max_view - 1
                AND tpe.view_count = tpe.win_count THEN 1
            END
        ) AS under_max_view_count
    FROM trainer_pokemon_elo AS tpe
        JOIN dex AS d ON tpe.dex_id = d.id
        AND d.slug = :dex_slug
        CROSS JOIN stats s
    WHERE tpe.trainer_external_id = :trainer_external_id
        AND tpe.election_slug = :election_slug
)
SELECT p.slug AS pokemon_slug,
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
FROM pokemon AS p
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
    JOIN dex_availability AS da ON p.id = da.pokemon_id
    JOIN dex AS d ON da.dex_id = d.id
    AND d.slug = :dex_slug
WHERE EXISTS (
        SELECT 1
        FROM stats AS s,
            variables as v,
            trainer_pokemon_elo AS tpe
        WHERE p.id = tpe.pokemon_id
            AND tpe.trainer_external_id = :trainer_external_id
            AND tpe.dex_id = d.id
            AND tpe.election_slug = :election_slug
            AND tpe.view_count = CASE
                WHEN 0 = v.under_max_view_count THEN s.max_view
                ELSE s.max_view - 1
            END
            AND tpe.view_count = tpe.win_count
    ) -- {album_filters}
ORDER BY RANDOM()
LIMIT :count
```

---

### Task 2: Update PokemonsRepository

**Files:**
- Modify: `src/Repository/PokemonsRepository.php`

Add the `PokemonAvailabilities` import and pass the two category parameters to both `getNToPick()` and `getNToVote()`.

- [ ] **Step 1: Add `PokemonAvailabilities` import**

In `src/Repository/PokemonsRepository.php`, add the import after the existing `use` statements:

```php
use App\DTO\AlbumFilter\AlbumFilters;
use App\Entity\Pokemon;
use App\Entity\PokemonAvailabilities;
use App\Repository\Trait\FiltersTrait;
use App\Service\SqlFileLoader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
```

- [ ] **Step 2: Update `getNToPick()` params and types**

In `getNToPick()`, replace the `$params = array_merge(...)` block:

```php
        $params = array_merge(
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'election_slug' => $electionSlug,
                'count' => $count,
                'default_elo' => $defaultElo,
                'pokemon_availabilities_game_bundle_category' => PokemonAvailabilities::CATEGORY_GAME_BUNDLE,
                'pokemon_availabilities_game_bundle_shiny_category' => PokemonAvailabilities::CATEGORY_GAME_BUNDLE_SHINY,
            ],
            $this->getFiltersParameters($filters),
        );
```

And replace the `$types = array_merge(...)` block:

```php
        $types = array_merge(
            [
                'trainer_external_id' => ParameterType::STRING,
                'election_slug' => ParameterType::STRING,
                'dex_slug' => ParameterType::STRING,
                'count' => ParameterType::INTEGER,
                'default_elo' => ParameterType::INTEGER,
                'pokemon_availabilities_game_bundle_category' => ParameterType::STRING,
                'pokemon_availabilities_game_bundle_shiny_category' => ParameterType::STRING,
            ],
            $this->getFiltersTypes(),
        );
```

- [ ] **Step 3: Update `getNToVote()` params and types**

Apply the same `$params` and `$types` changes in `getNToVote()`. The parameter names are identical — both methods query the same `pokemon_availabilities` table with the same category constants.

```php
        $params = array_merge(
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'election_slug' => $electionSlug,
                'count' => $count,
                'default_elo' => $defaultElo,
                'pokemon_availabilities_game_bundle_category' => PokemonAvailabilities::CATEGORY_GAME_BUNDLE,
                'pokemon_availabilities_game_bundle_shiny_category' => PokemonAvailabilities::CATEGORY_GAME_BUNDLE_SHINY,
            ],
            $this->getFiltersParameters($filters),
        );

        $types = array_merge(
            [
                'trainer_external_id' => ParameterType::STRING,
                'election_slug' => ParameterType::STRING,
                'dex_slug' => ParameterType::STRING,
                'count' => ParameterType::INTEGER,
                'default_elo' => ParameterType::INTEGER,
                'pokemon_availabilities_game_bundle_category' => ParameterType::STRING,
                'pokemon_availabilities_game_bundle_shiny_category' => ParameterType::STRING,
            ],
            $this->getFiltersTypes(),
        );
```

No unit test for Repository — integration tests cover it.

---

### Task 3: Update ElectionPokemonResponseFactory and unit tests

**Files:**
- Modify: `src/Factory/ElectionPokemonResponseFactory.php`
- Modify: `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`

- [ ] **Step 1: Update `buildPokemon()` to parse `game_bundle_slugs`**

In `src/Factory/ElectionPokemonResponseFactory.php`, inside `buildPokemon()`, replace:

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

With:

```php
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

- [ ] **Step 2: Add `game_bundle_slugs` and `game_bundle_shiny_slugs` to `buildRow()` defaults**

In `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`, in the `buildRow()` method, add after `'pokemon_order_number' => '9999-0001-000',`:

```php
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
```

Full updated `buildRow()`:

```php
    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function buildRow(array $overrides = []): array
    {
        return array_merge([
            'pokemon_slug' => 'bulbasaur',
            'pokemon_name' => 'Bulbasaur',
            'pokemon_french_name' => 'Bulbizarre',
            'pokemon_national_dex_number' => 1,
            'pokemon_simplified_name' => 'Bulbasaur',
            'pokemon_forms_label' => '',
            'pokemon_simplified_french_name' => 'Bulbizarre',
            'pokemon_forms_french_label' => '',
            'pokemon_icon' => 'bulbasaur',
            'pokemon_family_order' => 0,
            'family_lead_slug' => 'bulbasaur',
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
            'primary_type_slug' => 'grass',
            'primary_type_name' => 'Grass',
            'primary_type_french_name' => 'Plante',
            'primary_type_color' => '#78C850',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'original_game_bundle_slug' => 'redgreenblueyellow',
            'pokemon_order_number' => '9999-0001-000',
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ], $overrides);
    }
```

- [ ] **Step 3: Add 4 new test methods for `game_bundles` and `game_bundles_shiny`**

Add these four test methods to `ElectionPokemonResponseFactoryTest`:

```php
    #[Test]
    public function fromSqlRowBuildsEmptyGameBundlesWhenSlugsAreNull(): void
    {
        $row = $this->buildRow([
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles);
        self::assertSame([], $response->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowBuildsEmptyGameBundlesWhenSlugsAreEmptyString(): void
    {
        $row = $this->buildRow([
            'game_bundle_slugs' => '',
            'game_bundle_shiny_slugs' => '',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles);
        self::assertSame([], $response->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowBuildsPopulatedGameBundlesFromCommaSeparatedSlugs(): void
    {
        $row = $this->buildRow([
            'game_bundle_slugs' => 'redgreenblueyellow,goldsilvercrystal',
            'game_bundle_shiny_slugs' => null,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

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
        $row = $this->buildRow([
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => 'redgreenblueyellow,goldsilvercrystal',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles);
        self::assertCount(2, $response->pokemon->gameBundlesShiny);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundlesShiny[0]);
        self::assertSame('redgreenblueyellow', $response->pokemon->gameBundlesShiny[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundlesShiny[1]);
        self::assertSame('goldsilvercrystal', $response->pokemon->gameBundlesShiny[1]->slug);
    }
```

Note: `GameBundleSlugResponse` is already imported in the test file.

---

### Task 4: Update integration test

**Files:**
- Modify: `tests/src/Integration/Controller/PokemonsControllerTest.php`

The `assertResponseContent()` method already checks that `game_bundles` and `game_bundles_shiny` keys exist and are arrays. Add iteration assertions to verify each item in those arrays has a `slug` string key — the same pattern used in `TrainerPokemonEloControllerTest`.

- [ ] **Step 1: Add slug-structure assertions inside `game_bundles` and `game_bundles_shiny` loops**

In `assertResponseContent()`, replace:

```php
            $this->assertArrayHasKey('game_bundles', $pokemon);
            $this->assertArrayHasKey('game_bundles_shiny', $pokemon);
            $this->assertIsArray($pokemon['game_bundles']);
            $this->assertIsArray($pokemon['game_bundles_shiny']);
```

With:

```php
            $this->assertArrayHasKey('game_bundles', $pokemon);
            $this->assertArrayHasKey('game_bundles_shiny', $pokemon);
            $this->assertIsArray($pokemon['game_bundles']);
            $this->assertIsArray($pokemon['game_bundles_shiny']);

            /** @var array<string, mixed> $gameBundle */
            foreach ($pokemon['game_bundles'] as $gameBundle) {
                $this->assertArrayHasKey('slug', $gameBundle);
                $this->assertIsString($gameBundle['slug']);
            }

            /** @var array<string, mixed> $gameBundleShiny */
            foreach ($pokemon['game_bundles_shiny'] as $gameBundleShiny) {
                $this->assertArrayHasKey('slug', $gameBundleShiny);
                $this->assertIsString($gameBundleShiny['slug']);
            }
```
