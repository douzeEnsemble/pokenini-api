# API Response Restructuring (Album Forms french_name) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add `french_name` to album form responses in `GET /album/{trainerExternalId}/{dexSlug}`, aligning them with the election form response structure which already includes it.

**Architecture:** Extend `AlbumFormResponse` DTO with a `frenchName` property, update `AlbumPokemonResponseFactory.buildForm()` to read `{prefix}_french_name` from SQL rows, and add the four `{form}.french_name as {form}_french_name` columns to the `PokedexRepository.getListQuerySQL()` SELECT.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## File Structure

**Modify:**
- `src/DTO/Response/AlbumFormResponse.php` — add `frenchName: string` with `#[SerializedName('french_name')]`
- `src/Factory/AlbumPokemonResponseFactory.php` — update `buildForm()` to read `{prefix}_french_name`
- `src/Repository/PokedexRepository.php` — add `cf/rf/sf/vf.french_name as {form}_french_name` to SELECT in `getListQuerySQL()`
- `tests/src/Unit/DTO/Response/AlbumFormResponseTest.php` — add `frenchName` constructor arg + assertions
- `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php` — add `{form}_french_name` to test rows + assertions + string-cast test
- `tests/src/Common/Data/PokemonData.php` — add `{form}_french_name` to entries that have non-null form slugs
- `tests/src/Common/Data/AlbumData.php` — update `buildNestedForm()` + inline rows for pokemon with non-null form slugs
- `tests/src/Integration/Controller/AlbumIndexControllerTest.php` — add `french_name` assertion on category form
- `doc/endpoints.md` — add `french_name` to album form example

**Create:**
- `docs/api-migration/album-forms-french-name-restructuring.md` — migration documentation

---

## Context: Before / After

### Before (`GET /album/.../redgreenblueyellow`, Bulbasaur)

```json
{
  "forms": {
    "category": { "slug": "starter", "name": "Starter" },
    "regional": null,
    "special": null,
    "variant": null
  }
}
```

### After

```json
{
  "forms": {
    "category": { "slug": "starter", "name": "Starter", "french_name": "de Départ" },
    "regional": null,
    "special": null,
    "variant": null
  }
}
```

---

## Tasks

### Task 1: Update AlbumFormResponse DTO

**Files:**
- Modify: `src/DTO/Response/AlbumFormResponse.php`

- [ ] **Step 1: Add `frenchName` property with `#[SerializedName]`**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumFormResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
    ) {}
}
```

- [ ] **Step 2: Update unit test `tests/src/Unit/DTO/Response/AlbumFormResponseTest.php`**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumFormResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumFormResponse::class)]
final class AlbumFormResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new AlbumFormResponse(
            slug: 'mega',
            name: 'Mega',
            frenchName: 'Mega',
        );

        self::assertSame('mega', $response->slug);
        self::assertSame('Mega', $response->name);
        self::assertSame('Mega', $response->frenchName);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumFormResponse(
            slug: 'starter',
            name: 'Starter',
            frenchName: 'de Départ',
        );

        self::assertSame('starter', $response->slug);
        self::assertSame('Starter', $response->name);
        self::assertSame('de Départ', $response->frenchName);
    }
}
```

- [ ] **Step 3: Run unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/AlbumFormResponseTest.php`

Expected: 2 tests pass.

---

### Task 2: Update AlbumPokemonResponseFactory

**Files:**
- Modify: `src/Factory/AlbumPokemonResponseFactory.php:183-201`

- [ ] **Step 1: Update `buildForm()` to read `{prefix}_french_name`**

Replace the `buildForm` method:

```php
/**
 * @param array<string, mixed> $row
 */
private static function buildForm(string $prefix, array $row): ?AlbumFormResponse
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

    return new AlbumFormResponse(
        slug: (string) $slug,
        name: (string) $name,
        frenchName: (string) $frenchName,
    );
}
```

- [ ] **Step 2: Update `AlbumPokemonResponseFactoryTest.php`**

Add `category_form_french_name: 'de Départ'` to `getBulbasaurRow()`.

Update form assertion tests to verify `frenchName` and add `{form}_french_name` to rows where form slug is set. Add a type-cast test.

See full test content in the implementation notes below.

- [ ] **Step 3: Run unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

Expected: All tests pass.

---

### Task 3: Update PokedexRepository SQL

**Files:**
- Modify: `src/Repository/PokedexRepository.php:273-280`

- [ ] **Step 1: Add `french_name` columns to `getListQuerySQL()` SELECT**

In `getListQuerySQL()`, replace:

```sql
cf.slug as category_form_slug,
cf.name as category_form_name,
rf.slug as regional_form_slug,
rf.name as regional_form_name,
sf.slug as special_form_slug,
sf.name as special_form_name,
vf.slug as variant_form_slug,
vf.name as variant_form_name,
```

With:

```sql
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
```

No unit test needed for Repository (integration tests cover it).

---

### Task 4: Update test data fixtures

**Files:**
- Modify: `tests/src/Common/Data/PokemonData.php`
- Modify: `tests/src/Common/Data/AlbumData.php`

- [ ] **Step 1: Add `{form}_french_name` to PokemonData entries with non-null form slugs**

Entries that need updates (forms map to fixtures — french_names from `fixtures/*.yaml`):

| Method | Form set | French name to add |
|--------|----------|--------------------|
| `getBulbasaurData()` | `category_form_slug: 'starter'` | `category_form_french_name: 'de Départ'` |
| `getCharmanderData()` | `category_form_slug: 'starter'` | `category_form_french_name: 'de Départ'` |
| `getRattataFemaleData()` | `variant_form_slug: 'gender'` | `variant_form_french_name: 'Sexe'` |
| `getRattataAlolanData()` | `regional_form_slug: 'alolan'` | `regional_form_french_name: "d'Alola"` |
| `getRaticateFemaleData()` | `variant_form_slug: 'gender'` | `variant_form_french_name: 'Sexe'` |
| `getRaticateAlolanData()` | `regional_form_slug: 'alolan'` | `regional_form_french_name: "d'Alola"` |
| `getRaticateAlolanTotemData()` | `regional_form_slug: 'alolan'` + `special_form_slug: 'totem'` | `regional_form_french_name: "d'Alola"` + `special_form_french_name: 'Dominant'` |

Entries with all-null form slugs do NOT need `{form}_french_name` fields (factory and `buildNestedForm()` return null before reading them).

- [ ] **Step 2: Update `AlbumData.buildNestedForm()` to include `french_name`**

Replace:

```php
return [
    'slug' => $flat["{$prefix}_slug"],
    'name' => $flat["{$prefix}_name"],
];
```

With:

```php
return [
    'slug' => $flat["{$prefix}_slug"],
    'name' => $flat["{$prefix}_name"],
    'french_name' => $flat["{$prefix}_french_name"],
];
```

- [ ] **Step 3: Add `{form}_french_name` to inline rows in `AlbumData`**

In `getExpectedHomeContent()` and `getExpectedHomeShinyContent()`, inline Pokémon rows that have non-null form slugs need the matching `{form}_french_name`:

| Pokémon slug | Form set | Add |
|---|---|---|
| `venusaur-f` | `variant_form_slug: 'gender'` | `'variant_form_french_name' => 'Sexe'` |
| `venusaur-mega` | `special_form_slug: 'mega'` | `'special_form_french_name' => 'Mega'` |
| `venusaur-gmax` | `special_form_slug: 'gigantamax'` | `'special_form_french_name' => 'Gigamax'` |
| `butterfree-f` | `variant_form_slug: 'gender'` | `'variant_form_french_name' => 'Sexe'` |
| `butterfree-gmax` | `special_form_slug: 'gigantamax'` | `'special_form_french_name' => 'Gigamax'` |

---

### Task 5: Update integration test and endpoint doc

**Files:**
- Modify: `tests/src/Integration/Controller/AlbumIndexControllerTest.php`
- Modify: `doc/endpoints.md`
- Create: `docs/api-migration/album-forms-french-name-restructuring.md`

- [ ] **Step 1: Add form `french_name` assertion in `testListUser12RedGreenBlueYellow`**

After the existing catch_state color assertions (around line 103), add:

```php
/** @var array<string, mixed> $firstPokemonForms */
$firstPokemonForms = $firstPokemon['forms'];
/** @var array<string, mixed> $firstPokemonCategoryForm */
$firstPokemonCategoryForm = $firstPokemonForms['category'];
$this->assertArrayHasKey('french_name', $firstPokemonCategoryForm);
$this->assertIsString($firstPokemonCategoryForm['french_name']);
$this->assertSame('de Départ', $firstPokemonCategoryForm['french_name']);
```

(Bulbasaur is the first Pokémon in `redgreenblueyellow` and has `category_form: starter`.)

- [ ] **Step 2: Update `doc/endpoints.md` line 670**

Change:

```json
"category": { "slug": "starter", "name": "Starter" },
```

To:

```json
"category": { "slug": "starter", "name": "Starter", "french_name": "de Départ" },
```

- [ ] **Step 3: Create migration doc `docs/api-migration/album-forms-french-name-restructuring.md`**

See content in Task 5 implementation.

- [ ] **Step 4: Run full test suite**

Run: `make tests`

Expected: All tests pass.

- [ ] **Step 5: Run quality and coverage checks**

Run: `make quality && make measures`

Expected: All green, 100% coverage, 100% MSI.
