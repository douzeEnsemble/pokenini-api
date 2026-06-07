# API Response Restructuring (Album Pokémon — Nested Forms & Types) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /album/{trainerExternalId}/{dexSlug}` response by grouping the 4 flat `*_form` fields and the 2 flat `*_type` fields of `AlbumPokemonResponse` into nested `forms` (`AlbumFormsResponse`) and `types` (`AlbumTypesResponse`) objects, mirroring the nested structure already adopted by `ElectionPokemonResponse` (issue #256).

**Architecture:** Create two new immutable DTOs — `AlbumFormsResponse` and `AlbumTypesResponse` — that group the Album-specific form and type sub-objects. Update `AlbumPokemonResponse` to embed `?AlbumFormsResponse $forms` and `AlbumTypesResponse $types` instead of the 6 flat nullable properties. Update `AlbumPokemonResponseFactory` to build those nested objects using private helpers. Update all unit tests and the `AlbumData::toNestedFormat()` helper used by integration tests. No controller or repository changes are needed.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Response shape change

**Before:**
```json
{
  "pokemons": [
    {
      "pokemon": { "slug": "bulbasaur", "national_dex_number": 1, "...": "..." },
      "catch_state": { "slug": "no", "name": "No", "french_name": "Non" },
      "category_form": { "slug": "starter", "name": "Starter" },
      "regional_form": null,
      "special_form": null,
      "variant_form": null,
      "primary_type": { "slug": "grass", "name": "Grass", "french_name": "Plante" },
      "secondary_type": { "slug": "poison", "name": "Poison", "french_name": "Poison" }
    }
  ]
}
```

**After:**
```json
{
  "pokemons": [
    {
      "pokemon": { "slug": "bulbasaur", "national_dex_number": 1, "...": "..." },
      "catch_state": { "slug": "no", "name": "No", "french_name": "Non" },
      "forms": {
        "category": { "slug": "starter", "name": "Starter" },
        "regional": null,
        "special": null,
        "variant": null
      },
      "types": {
        "primary": { "slug": "grass", "name": "Grass", "french_name": "Plante" },
        "secondary": { "slug": "poison", "name": "Poison", "french_name": "Poison" }
      }
    }
  ]
}
```

When a Pokémon has no forms at all (all four form fields null), `forms` itself is `null`:
```json
{
  "forms": null,
  "types": {
    "primary": null,
    "secondary": null
  }
}
```

---

## File Structure

**Create:**
- `src/DTO/Response/AlbumFormsResponse.php` — immutable DTO grouping the 4 Album form sub-objects
- `src/DTO/Response/AlbumTypesResponse.php` — immutable DTO grouping the 2 Album type sub-objects
- `tests/src/Unit/DTO/Response/AlbumFormsResponseTest.php` — unit tests for `AlbumFormsResponse`
- `tests/src/Unit/DTO/Response/AlbumTypesResponseTest.php` — unit tests for `AlbumTypesResponse`

**Modify:**
- `src/DTO/Response/AlbumPokemonResponse.php` — replace 6 flat form/type fields with `?AlbumFormsResponse $forms` and `AlbumTypesResponse $types`
- `src/Factory/AlbumPokemonResponseFactory.php` — build nested objects via private `buildForms` / `buildTypes` helpers
- `tests/src/Unit/DTO/Response/AlbumPokemonResponseTest.php` — update constructor call and assertions
- `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php` — update assertions to access nested properties
- `tests/src/Common/Data/AlbumData.php` — update `toNestedFormat()` to emit the new JSON shape

---

## Tasks

### Task 1: Create AlbumFormsResponse DTO

**Files:**
- Create: `src/DTO/Response/AlbumFormsResponse.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class AlbumFormsResponse
{
    public function __construct(
        public readonly ?AlbumFormResponse $category,
        public readonly ?AlbumFormResponse $regional,
        public readonly ?AlbumFormResponse $special,
        public readonly ?AlbumFormResponse $variant,
    ) {}
}
```

Save as `src/DTO/Response/AlbumFormsResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/AlbumFormsResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/AlbumFormsResponse.php`

---

### Task 2: Create AlbumTypesResponse DTO

**Files:**
- Create: `src/DTO/Response/AlbumTypesResponse.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class AlbumTypesResponse
{
    public function __construct(
        public readonly ?AlbumTypeResponse $primary,
        public readonly ?AlbumTypeResponse $secondary,
    ) {}
}
```

Save as `src/DTO/Response/AlbumTypesResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/AlbumTypesResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/AlbumTypesResponse.php`

---

### Task 3: Write unit tests for AlbumFormsResponse

**Files:**
- Create: `tests/src/Unit/DTO/Response/AlbumFormsResponseTest.php`
- Test: `AlbumFormsResponse` DTO

- [ ] **Step 1: Create the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumFormsResponse::class)]
final class AlbumFormsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $category = new AlbumFormResponse('starter', 'Starter');
        $regional = new AlbumFormResponse('alolan', 'Alolan');
        $special = new AlbumFormResponse('mega', 'Mega');
        $variant = new AlbumFormResponse('gender', 'Gender');

        $response = new AlbumFormsResponse(
            category: $category,
            regional: $regional,
            special: $special,
            variant: $variant,
        );

        self::assertSame($category, $response->category);
        self::assertSame($regional, $response->regional);
        self::assertSame($special, $response->special);
        self::assertSame($variant, $response->variant);
    }

    #[Test]
    public function constructorAcceptsNullProperties(): void
    {
        $response = new AlbumFormsResponse(
            category: null,
            regional: null,
            special: null,
            variant: null,
        );

        self::assertNull($response->category);
        self::assertNull($response->regional);
        self::assertNull($response->special);
        self::assertNull($response->variant);
    }
}
```

Save as `tests/src/Unit/DTO/Response/AlbumFormsResponseTest.php`.

- [ ] **Step 2: Run the tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/AlbumFormsResponseTest.php`

Expected: 2 tests, 0 failures.

---

### Task 4: Write unit tests for AlbumTypesResponse

**Files:**
- Create: `tests/src/Unit/DTO/Response/AlbumTypesResponseTest.php`
- Test: `AlbumTypesResponse` DTO

- [ ] **Step 1: Create the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\AlbumTypesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumTypesResponse::class)]
final class AlbumTypesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $primary = new AlbumTypeResponse('grass', 'Grass', 'Plante');
        $secondary = new AlbumTypeResponse('poison', 'Poison', 'Poison');

        $response = new AlbumTypesResponse(
            primary: $primary,
            secondary: $secondary,
        );

        self::assertSame($primary, $response->primary);
        self::assertSame($secondary, $response->secondary);
    }

    #[Test]
    public function constructorAcceptsNullProperties(): void
    {
        $response = new AlbumTypesResponse(
            primary: null,
            secondary: null,
        );

        self::assertNull($response->primary);
        self::assertNull($response->secondary);
    }
}
```

Save as `tests/src/Unit/DTO/Response/AlbumTypesResponseTest.php`.

- [ ] **Step 2: Run the tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/AlbumTypesResponseTest.php`

Expected: 2 tests, 0 failures.

---

### Task 5: Update AlbumPokemonResponse DTO

**Files:**
- Modify: `src/DTO/Response/AlbumPokemonResponse.php`

- [ ] **Step 1: Replace the 6 flat form/type properties with nested objects**

Replace the entire file content with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumPokemonResponse
{
    public function __construct(
        public readonly PokemonDataResponse $pokemon,
        #[SerializedName('catch_state')]
        public readonly ?AlbumCatchStateResponse $catchState,
        public readonly ?AlbumFormsResponse $forms,
        public readonly AlbumTypesResponse $types,
    ) {}
}
```

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/AlbumPokemonResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/AlbumPokemonResponse.php`

---

### Task 6: Update AlbumPokemonResponseTest

**Files:**
- Modify: `tests/src/Unit/DTO/Response/AlbumPokemonResponseTest.php`

- [ ] **Step 1: Update to use nested DTOs**

Replace the entire file content with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\AlbumTypesResponse;
use App\DTO\Response\PokemonDataResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumPokemonResponse::class)]
final class AlbumPokemonResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $pokemon = new PokemonDataResponse(
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            nationalDexNumber: 1,
            regionalDexNumber: 1,
            simplifiedName: 'Bulbasaur',
            formsLabel: '',
            simplifiedFrenchName: 'Bulbizarre',
            formsFrenchLabel: '',
            icon: 'bulbasaur',
            familyOrder: 0,
            familyLeadSlug: 'bulbasaur',
            originalGameBundleSlug: 'redgreenblueyellow',
            orderNumber: '0001-0001-000',
            gameBundles: ['redgreenblueyellow'],
            gameBundlesShiny: [],
        );
        $catchState = new AlbumCatchStateResponse('no', 'No', 'Non');
        $forms = new AlbumFormsResponse(
            category: new AlbumFormResponse('starter', 'Starter'),
            regional: null,
            special: null,
            variant: null,
        );
        $types = new AlbumTypesResponse(
            primary: new AlbumTypeResponse('grass', 'Grass', 'Plante'),
            secondary: new AlbumTypeResponse('poison', 'Poison', 'Poison'),
        );

        $response = new AlbumPokemonResponse(
            pokemon: $pokemon,
            catchState: $catchState,
            forms: $forms,
            types: $types,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame($catchState, $response->catchState);
        self::assertSame($forms, $response->forms);
        self::assertSame($types, $response->types);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $pokemon = new PokemonDataResponse(
            slug: 'douze',
            name: 'Douze',
            frenchName: 'Douze',
            nationalDexNumber: 9912,
            regionalDexNumber: null,
            simplifiedName: 'Douze',
            formsLabel: '',
            simplifiedFrenchName: 'Douze',
            formsFrenchLabel: '',
            icon: 'douze',
            familyOrder: 0,
            familyLeadSlug: 'douze',
            originalGameBundleSlug: 'redgreenblueyellow',
            orderNumber: '9999-9912-000',
            gameBundles: ['un', 'dos', 'tres'],
            gameBundlesShiny: [],
        );
        $types = new AlbumTypesResponse(primary: null, secondary: null);

        $response = new AlbumPokemonResponse(
            pokemon: $pokemon,
            catchState: null,
            forms: null,
            types: $types,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertNull($response->catchState);
        self::assertNull($response->forms);
        self::assertSame($types, $response->types);
    }
}
```

- [ ] **Step 2: Run the tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/AlbumPokemonResponseTest.php`

Expected: 2 tests, 0 failures.

---

### Task 7: Update AlbumPokemonResponseFactory

**Files:**
- Modify: `src/Factory/AlbumPokemonResponseFactory.php`

- [ ] **Step 1: Replace the factory to build nested objects**

Replace the entire file content with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\AlbumTypesResponse;
use App\DTO\Response\PokemonDataResponse;

final class AlbumPokemonResponseFactory
{
    /**
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): AlbumPokemonResponse
    {
        return new AlbumPokemonResponse(
            pokemon: self::buildPokemon($row),
            catchState: self::buildCatchState($row),
            forms: self::buildForms($row),
            types: self::buildTypes($row),
        );
    }

    /**
     * @param array<array<string, mixed>> $rows
     *
     * @return AlbumPokemonResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildPokemon(array $row): PokemonDataResponse
    {
        /** @var scalar $slug */
        $slug = $row['pokemon_slug'];

        /** @var scalar $name */
        $name = $row['pokemon_name'];

        /** @var scalar $frenchName */
        $frenchName = $row['pokemon_french_name'];

        /** @var scalar $nationalDexNumber */
        $nationalDexNumber = $row['pokemon_national_dex_number'];

        /** @var null|scalar $regionalDexNumber */
        $regionalDexNumber = $row['pokemon_regional_dex_number'] ?? null;

        /** @var null|scalar $simplifiedName */
        $simplifiedName = $row['pokemon_simplified_name'] ?? null;

        /** @var null|scalar $formsLabel */
        $formsLabel = $row['pokemon_forms_label'] ?? null;

        /** @var null|scalar $simplifiedFrenchName */
        $simplifiedFrenchName = $row['pokemon_simplified_french_name'] ?? null;

        /** @var null|scalar $formsFrenchLabel */
        $formsFrenchLabel = $row['pokemon_forms_french_label'] ?? null;

        /** @var null|scalar $icon */
        $icon = $row['pokemon_icon'] ?? null;

        /** @var scalar $familyOrder */
        $familyOrder = $row['pokemon_family_order'];

        /** @var null|scalar $familyLeadSlug */
        $familyLeadSlug = $row['family_lead_slug'] ?? null;

        /** @var null|scalar $originalGameBundleSlug */
        $originalGameBundleSlug = $row['original_game_bundle_slug'] ?? null;

        /** @var scalar $orderNumber */
        $orderNumber = $row['pokemon_order_number'];

        /** @var array<string> $gameBundles */
        $gameBundles = (array) $row['game_bundles'];

        /** @var array<string> $gameBundlesShiny */
        $gameBundlesShiny = (array) $row['game_bundles_shiny'];

        return new PokemonDataResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            nationalDexNumber: (int) $nationalDexNumber,
            regionalDexNumber: null !== $regionalDexNumber ? (int) $regionalDexNumber : null,
            simplifiedName: null !== $simplifiedName ? (string) $simplifiedName : null,
            formsLabel: null !== $formsLabel ? (string) $formsLabel : null,
            simplifiedFrenchName: null !== $simplifiedFrenchName ? (string) $simplifiedFrenchName : null,
            formsFrenchLabel: null !== $formsFrenchLabel ? (string) $formsFrenchLabel : null,
            icon: null !== $icon ? (string) $icon : null,
            familyOrder: (int) $familyOrder,
            familyLeadSlug: null !== $familyLeadSlug ? (string) $familyLeadSlug : null,
            originalGameBundleSlug: null !== $originalGameBundleSlug ? (string) $originalGameBundleSlug : null,
            orderNumber: (string) $orderNumber,
            gameBundles: $gameBundles,
            gameBundlesShiny: $gameBundlesShiny,
        );
    }

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

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForms(array $row): ?AlbumFormsResponse
    {
        $hasAnyForm = !empty($row['category_form_slug'])
            || !empty($row['regional_form_slug'])
            || !empty($row['special_form_slug'])
            || !empty($row['variant_form_slug']);

        if (!$hasAnyForm) {
            return null;
        }

        return new AlbumFormsResponse(
            category: self::buildForm('category_form', $row),
            regional: self::buildForm('regional_form', $row),
            special: self::buildForm('special_form', $row),
            variant: self::buildForm('variant_form', $row),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForm(string $prefix, array $row): ?AlbumFormResponse
    {
        $slugKey = "{$prefix}_slug";
        $nameKey = "{$prefix}_name";

        if (empty($row[$slugKey])) {
            return null;
        }

        /** @var scalar $slug */
        $slug = $row[$slugKey];

        /** @var scalar $name */
        $name = $row[$nameKey];

        return new AlbumFormResponse(
            slug: (string) $slug,
            name: (string) $name,
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildTypes(array $row): AlbumTypesResponse
    {
        return new AlbumTypesResponse(
            primary: self::buildType('primary_type', $row),
            secondary: self::buildType('secondary_type', $row),
        );
    }

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
}
```

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/Factory/AlbumPokemonResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/AlbumPokemonResponseFactory.php`

---

### Task 8: Update AlbumPokemonResponseFactoryTest

**Files:**
- Modify: `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

- [ ] **Step 1: Update to assert against nested structure**

Replace the entire file content with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\AlbumTypesResponse;
use App\Factory\AlbumPokemonResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
 */
#[CoversClass(AlbumPokemonResponseFactory::class)]
final class AlbumPokemonResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowBuildsPokemonSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertSame('bulbasaur', $result->pokemon->slug);
        self::assertSame('Bulbasaur', $result->pokemon->name);
        self::assertSame('Bulbizarre', $result->pokemon->frenchName);
        self::assertSame(1, $result->pokemon->nationalDexNumber);
        self::assertSame(1, $result->pokemon->regionalDexNumber);
        self::assertSame('Bulbasaur', $result->pokemon->simplifiedName);
        self::assertSame('', $result->pokemon->formsLabel);
        self::assertSame('Bulbizarre', $result->pokemon->simplifiedFrenchName);
        self::assertSame('', $result->pokemon->formsFrenchLabel);
        self::assertSame('bulbasaur', $result->pokemon->icon);
        self::assertSame(0, $result->pokemon->familyOrder);
        self::assertSame('bulbasaur', $result->pokemon->familyLeadSlug);
        self::assertSame('redgreenblueyellow', $result->pokemon->originalGameBundleSlug);
        self::assertSame('0001-0001-000', $result->pokemon->orderNumber);
        self::assertSame(['redgreenblueyellow', 'goldsilvercrystal'], $result->pokemon->gameBundles);
        self::assertSame(['redgreenblueyellow'], $result->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowBuildsCatchStateSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumCatchStateResponse::class, $result->catchState);
        self::assertSame('no', $result->catchState->slug);
        self::assertSame('No', $result->catchState->name);
        self::assertSame('Non', $result->catchState->frenchName);
    }

    #[Test]
    public function fromSqlRowSetsNullCatchStateWhenNotSet(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->catchState);
    }

    #[Test]
    public function fromSqlRowBuildsFormsObjectWithCategoryForm(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumFormsResponse::class, $result->forms);
        self::assertInstanceOf(AlbumFormResponse::class, $result->forms->category);
        self::assertSame('starter', $result->forms->category->slug);
        self::assertSame('Starter', $result->forms->category->name);
        self::assertNull($result->forms->regional);
        self::assertNull($result->forms->special);
        self::assertNull($result->forms->variant);
    }

    #[Test]
    public function fromSqlRowSetsNullFormsWhenNoFormsPresent(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->forms);
    }

    #[Test]
    public function fromSqlRowBuildsFormsObjectWithSpecialForm(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['special_form_slug'] = 'mega';
        $row['special_form_name'] = 'Mega';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormsResponse::class, $result->forms);
        self::assertNull($result->forms->category);
        self::assertNull($result->forms->regional);
        self::assertInstanceOf(AlbumFormResponse::class, $result->forms->special);
        self::assertSame('mega', $result->forms->special->slug);
        self::assertSame('Mega', $result->forms->special->name);
        self::assertNull($result->forms->variant);
    }

    #[Test]
    public function fromSqlRowBuildsFormsObjectWithVariantForm(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['variant_form_slug'] = 'gender';
        $row['variant_form_name'] = 'Gender';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormsResponse::class, $result->forms);
        self::assertNull($result->forms->category);
        self::assertNull($result->forms->regional);
        self::assertNull($result->forms->special);
        self::assertInstanceOf(AlbumFormResponse::class, $result->forms->variant);
        self::assertSame('gender', $result->forms->variant->slug);
        self::assertSame('Gender', $result->forms->variant->name);
    }

    #[Test]
    public function fromSqlRowBuildsFormsObjectWithRegionalForm(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['regional_form_slug'] = 'alolan';
        $row['regional_form_name'] = 'Alolan';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormsResponse::class, $result->forms);
        self::assertNull($result->forms->category);
        self::assertInstanceOf(AlbumFormResponse::class, $result->forms->regional);
        self::assertSame('alolan', $result->forms->regional->slug);
        self::assertSame('Alolan', $result->forms->regional->name);
        self::assertNull($result->forms->special);
        self::assertNull($result->forms->variant);
    }

    #[Test]
    public function fromSqlRowBuildsTypesObjectWithPrimaryAndSecondaryType(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumTypesResponse::class, $result->types);
        self::assertInstanceOf(AlbumTypeResponse::class, $result->types->primary);
        self::assertSame('grass', $result->types->primary->slug);
        self::assertSame('Grass', $result->types->primary->name);
        self::assertSame('Plante', $result->types->primary->frenchName);
        self::assertInstanceOf(AlbumTypeResponse::class, $result->types->secondary);
        self::assertSame('poison', $result->types->secondary->slug);
        self::assertSame('Poison', $result->types->secondary->name);
        self::assertSame('Poison', $result->types->secondary->frenchName);
    }

    #[Test]
    public function fromSqlRowBuildsTypesObjectWithNullTypesWhenAbsent(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertInstanceOf(AlbumTypesResponse::class, $result->types);
        self::assertNull($result->types->primary);
        self::assertNull($result->types->secondary);
    }

    #[Test]
    public function fromSqlRowBuildsTypesObjectWithNullSecondaryForSingleTypePokemon(): void
    {
        $row = $this->getBulbasaurRow();
        $row['secondary_type_slug'] = null;
        $row['secondary_type_name'] = null;
        $row['secondary_type_french_name'] = null;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumTypeResponse::class, $result->types->primary);
        self::assertNull($result->types->secondary);
    }

    #[Test]
    public function fromSqlRowSetsNullRegionalDexNumber(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->pokemon->regionalDexNumber);
    }

    #[Test]
    public function fromSqlRowCastsNullGameBundlesToEmptyArray(): void
    {
        $row = $this->getBulbasaurRow();
        $row['game_bundles'] = null;
        $row['game_bundles_shiny'] = null;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertSame([], $result->pokemon->gameBundles);
        self::assertSame([], $result->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowCastsNullableStringFieldsToStrings(): void
    {
        $row = $this->getBulbasaurRow();
        $row['pokemon_simplified_name'] = 42;
        $row['pokemon_forms_label'] = 1;
        $row['pokemon_simplified_french_name'] = 99;
        $row['pokemon_forms_french_label'] = 0;
        $row['pokemon_icon'] = 7;
        $row['family_lead_slug'] = 123;
        $row['original_game_bundle_slug'] = 456;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertSame('42', $result->pokemon->simplifiedName);
        self::assertSame('1', $result->pokemon->formsLabel);
        self::assertSame('99', $result->pokemon->simplifiedFrenchName);
        self::assertSame('0', $result->pokemon->formsFrenchLabel);
        self::assertSame('7', $result->pokemon->icon);
        self::assertSame('123', $result->pokemon->familyLeadSlug);
        self::assertSame('456', $result->pokemon->originalGameBundleSlug);
    }

    #[Test]
    public function fromSqlRowCastsNumericFieldsToCorrectTypes(): void
    {
        $row = $this->getBulbasaurRow();
        $row['pokemon_national_dex_number'] = '1';
        $row['pokemon_regional_dex_number'] = '1';
        $row['pokemon_family_order'] = '0';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertSame(1, $result->pokemon->nationalDexNumber);
        self::assertSame(1, $result->pokemon->regionalDexNumber);
        self::assertSame(0, $result->pokemon->familyOrder);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRows(): void
    {
        $rows = [$this->getBulbasaurRow(), $this->getDouzeRow()];

        $results = AlbumPokemonResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(AlbumPokemonResponse::class, $results);
        self::assertSame('bulbasaur', $results[0]->pokemon->slug);
        self::assertSame('douze', $results[1]->pokemon->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $results = AlbumPokemonResponseFactory::fromSqlRows([]);

        self::assertCount(0, $results);
    }

    /**
     * @return array<string, mixed>
     */
    private function getBulbasaurRow(): array
    {
        return [
            'pokemon_national_dex_number' => 1,
            'pokemon_regional_dex_number' => 1,
            'pokemon_order_number' => '0001-0001-000',
            'pokemon_slug' => 'bulbasaur',
            'pokemon_name' => 'Bulbasaur',
            'pokemon_simplified_name' => 'Bulbasaur',
            'pokemon_forms_label' => '',
            'pokemon_french_name' => 'Bulbizarre',
            'pokemon_simplified_french_name' => 'Bulbizarre',
            'pokemon_forms_french_label' => '',
            'pokemon_icon' => 'bulbasaur',
            'category_form_slug' => 'starter',
            'category_form_name' => 'Starter',
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'catch_state_slug' => 'no',
            'catch_state_name' => 'No',
            'catch_state_french_name' => 'Non',
            'family_lead_slug' => 'bulbasaur',
            'pokemon_family_order' => 0,
            'primary_type_slug' => 'grass',
            'primary_type_name' => 'Grass',
            'primary_type_french_name' => 'Plante',
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
            'original_game_bundle_slug' => 'redgreenblueyellow',
            'game_bundles' => ['redgreenblueyellow', 'goldsilvercrystal'],
            'game_bundles_shiny' => ['redgreenblueyellow'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getDouzeRow(): array
    {
        return [
            'pokemon_national_dex_number' => 9912,
            'pokemon_regional_dex_number' => null,
            'pokemon_order_number' => '9999-9912-000',
            'pokemon_slug' => 'douze',
            'pokemon_name' => 'Douze',
            'pokemon_simplified_name' => 'Douze',
            'pokemon_forms_label' => '',
            'pokemon_french_name' => 'Douze',
            'pokemon_simplified_french_name' => 'Douze',
            'pokemon_forms_french_label' => '',
            'pokemon_icon' => 'douze',
            'category_form_slug' => null,
            'category_form_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'catch_state_slug' => null,
            'catch_state_name' => null,
            'catch_state_french_name' => null,
            'family_lead_slug' => 'douze',
            'pokemon_family_order' => 0,
            'primary_type_slug' => null,
            'primary_type_name' => null,
            'primary_type_french_name' => null,
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'original_game_bundle_slug' => 'redgreenblueyellow',
            'game_bundles' => ['un', 'dos', 'tres'],
            'game_bundles_shiny' => [],
        ];
    }
}
```

- [ ] **Step 2: Run the tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

Expected: All tests pass, 0 failures.

---

### Task 9: Update AlbumData::toNestedFormat()

**Files:**
- Modify: `tests/src/Common/Data/AlbumData.php`

- [ ] **Step 1: Update `toNestedFormat()` to emit nested forms and types**

Find the private `toNestedFormat` method (around line 748). Replace the section that builds `$categoryForm`, `$regionalForm`, `$specialForm`, `$variantForm`, `$primaryType`, `$secondaryType` and the final returned array:

**Before (lines 758–816):**
```php
        $categoryForm = null !== ($flat['category_form_slug'] ?? null)
            ? ['slug' => $flat['category_form_slug'], 'name' => $flat['category_form_name']]
            : null;

        $regionalForm = null !== ($flat['regional_form_slug'] ?? null)
            ? ['slug' => $flat['regional_form_slug'], 'name' => $flat['regional_form_name']]
            : null;

        $specialForm = null !== ($flat['special_form_slug'] ?? null)
            ? ['slug' => $flat['special_form_slug'], 'name' => $flat['special_form_name']]
            : null;

        $variantForm = null !== ($flat['variant_form_slug'] ?? null)
            ? ['slug' => $flat['variant_form_slug'], 'name' => $flat['variant_form_name']]
            : null;

        $primaryType = null !== ($flat['primary_type_slug'] ?? null)
            ? [
                'slug' => $flat['primary_type_slug'],
                'name' => $flat['primary_type_name'],
                'french_name' => $flat['primary_type_french_name'],
            ]
            : null;

        $secondaryType = null !== ($flat['secondary_type_slug'] ?? null)
            ? [
                'slug' => $flat['secondary_type_slug'],
                'name' => $flat['secondary_type_name'],
                'french_name' => $flat['secondary_type_french_name'],
            ]
            : null;

        return [
            'pokemon' => [
                'slug' => $flat['pokemon_slug'],
                'name' => $flat['pokemon_name'],
                'french_name' => $flat['pokemon_french_name'],
                'national_dex_number' => $flat['pokemon_national_dex_number'],
                'regional_dex_number' => $flat['pokemon_regional_dex_number'] ?? null,
                'simplified_name' => $flat['pokemon_simplified_name'] ?? null,
                'forms_label' => $flat['pokemon_forms_label'] ?? null,
                'simplified_french_name' => $flat['pokemon_simplified_french_name'] ?? null,
                'forms_french_label' => $flat['pokemon_forms_french_label'] ?? null,
                'icon' => $flat['pokemon_icon'] ?? null,
                'family_order' => $flat['pokemon_family_order'],
                'family_lead_slug' => $flat['family_lead_slug'] ?? null,
                'original_game_bundle_slug' => $flat['original_game_bundle_slug'] ?? null,
                'order_number' => $flat['pokemon_order_number'],
                'game_bundles' => $flat['game_bundles'],
                'game_bundles_shiny' => $flat['game_bundles_shiny'],
            ],
            'catch_state' => $catchState,
            'category_form' => $categoryForm,
            'regional_form' => $regionalForm,
            'special_form' => $specialForm,
            'variant_form' => $variantForm,
            'primary_type' => $primaryType,
            'secondary_type' => $secondaryType,
        ];
```

**After:**
```php
        $hasAnyForm = null !== ($flat['category_form_slug'] ?? null)
            || null !== ($flat['regional_form_slug'] ?? null)
            || null !== ($flat['special_form_slug'] ?? null)
            || null !== ($flat['variant_form_slug'] ?? null);

        $forms = $hasAnyForm
            ? [
                'category' => null !== ($flat['category_form_slug'] ?? null)
                    ? ['slug' => $flat['category_form_slug'], 'name' => $flat['category_form_name']]
                    : null,
                'regional' => null !== ($flat['regional_form_slug'] ?? null)
                    ? ['slug' => $flat['regional_form_slug'], 'name' => $flat['regional_form_name']]
                    : null,
                'special' => null !== ($flat['special_form_slug'] ?? null)
                    ? ['slug' => $flat['special_form_slug'], 'name' => $flat['special_form_name']]
                    : null,
                'variant' => null !== ($flat['variant_form_slug'] ?? null)
                    ? ['slug' => $flat['variant_form_slug'], 'name' => $flat['variant_form_name']]
                    : null,
            ]
            : null;

        $types = [
            'primary' => null !== ($flat['primary_type_slug'] ?? null)
                ? [
                    'slug' => $flat['primary_type_slug'],
                    'name' => $flat['primary_type_name'],
                    'french_name' => $flat['primary_type_french_name'],
                ]
                : null,
            'secondary' => null !== ($flat['secondary_type_slug'] ?? null)
                ? [
                    'slug' => $flat['secondary_type_slug'],
                    'name' => $flat['secondary_type_name'],
                    'french_name' => $flat['secondary_type_french_name'],
                ]
                : null,
        ];

        return [
            'pokemon' => [
                'slug' => $flat['pokemon_slug'],
                'name' => $flat['pokemon_name'],
                'french_name' => $flat['pokemon_french_name'],
                'national_dex_number' => $flat['pokemon_national_dex_number'],
                'regional_dex_number' => $flat['pokemon_regional_dex_number'] ?? null,
                'simplified_name' => $flat['pokemon_simplified_name'] ?? null,
                'forms_label' => $flat['pokemon_forms_label'] ?? null,
                'simplified_french_name' => $flat['pokemon_simplified_french_name'] ?? null,
                'forms_french_label' => $flat['pokemon_forms_french_label'] ?? null,
                'icon' => $flat['pokemon_icon'] ?? null,
                'family_order' => $flat['pokemon_family_order'],
                'family_lead_slug' => $flat['family_lead_slug'] ?? null,
                'original_game_bundle_slug' => $flat['original_game_bundle_slug'] ?? null,
                'order_number' => $flat['pokemon_order_number'],
                'game_bundles' => $flat['game_bundles'],
                'game_bundles_shiny' => $flat['game_bundles_shiny'],
            ],
            'catch_state' => $catchState,
            'forms' => $forms,
            'types' => $types,
        ];
```

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l tests/src/Common/Data/AlbumData.php`

Expected: `No syntax errors detected in tests/src/Common/Data/AlbumData.php`

---

### Task 10: Run all tests to verify

- [ ] **Step 1: Run unit tests**

Run: `make tests-unit`

Expected: All unit tests pass, 0 failures.

- [ ] **Step 2: Run integration tests**

Run: `make tests-integration`

Expected: All integration tests pass, 0 failures.

- [ ] **Step 3: Run quality checks**

Run: `make quality`

Expected: All quality checks pass (PHPStan level 9, Psalm strict, PHP CS Fixer, PHPMD, Deptrac).

- [ ] **Step 4: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% code coverage, 100% MSI.

---

## Self-Review

**Spec coverage:**

| Requirement | Covered by |
|---|---|
| Group 4 flat `*_form` fields into nested `forms` object | Tasks 1, 5, 7 |
| Group 2 flat `*_type` fields into nested `types` object | Tasks 2, 5, 7 |
| `forms` is `null` when no form is present | Task 7 (`buildForms` returns `null`) |
| `types` is always present (never `null`) | Task 5 (`AlbumTypesResponse $types` non-nullable) |
| Unit tests for new DTOs | Tasks 3, 4 |
| Factory unit tests updated to check nested structure | Task 8 |
| DTO unit test updated | Task 6 |
| Integration test data updated via `toNestedFormat()` | Task 9 |

**Placeholder scan:** No TBDs, no "implement later", all code blocks are complete.

**Type consistency:**
- `AlbumFormsResponse` created in Task 1, used in Tasks 5, 6, 7, 8.
- `AlbumTypesResponse` created in Task 2, used in Tasks 5, 6, 7, 8.
- `AlbumPokemonResponse` updated in Task 5, tested in Task 6.
- `AlbumPokemonResponseFactory` updated in Task 7, tested in Task 8.
- `AlbumData::toNestedFormat()` updated in Task 9 — matches exact serialized property names (`forms`, `types`, `category`, `regional`, `special`, `variant`, `primary`, `secondary`).
