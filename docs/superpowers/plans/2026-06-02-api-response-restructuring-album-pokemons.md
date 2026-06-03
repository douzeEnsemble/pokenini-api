# API Response Restructuring (Album Pokémons) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /album/{trainerExternalId}/{dexSlug}` so the `pokemons` array items are nested objects (pokemon, catch_state, forms, types) instead of a single flat object with 30+ `pokemon_*`-prefixed fields.

**Architecture:** Create four new immutable Response DTOs (`AlbumCatchStateResponse`, `AlbumFormResponse`, `AlbumTypeResponse`, `AlbumPokemonResponse`), extend `PokemonDataResponse` with three new fields, create `AlbumPokemonResponseFactory` to transform flat SQL rows → nested DTOs, and update `AlbumIndexController` to call the factory before serialization. `AlbumPokemonService` stays unchanged and continues to return flat arrays.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/AlbumCatchStateResponse.php` — slim catch-state DTO (slug, name, french_name — no color)
- `src/DTO/Response/AlbumFormResponse.php` — slim form DTO (slug, name — no french_name)
- `src/DTO/Response/AlbumTypeResponse.php` — slim type DTO (slug, name, french_name — no color)
- `src/DTO/Response/AlbumPokemonResponse.php` — top-level DTO aggregating all sub-objects
- `src/Factory/AlbumPokemonResponseFactory.php` — transforms flat processed rows → AlbumPokemonResponse[]
- `tests/src/Unit/DTO/Response/AlbumCatchStateResponseTest.php`
- `tests/src/Unit/DTO/Response/AlbumFormResponseTest.php`
- `tests/src/Unit/DTO/Response/AlbumTypeResponseTest.php`
- `tests/src/Unit/DTO/Response/AlbumPokemonResponseTest.php`
- `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

**Modify:**
- `src/DTO/Response/PokemonDataResponse.php` — add `regionalDexNumber`, `gameBundles`, `gameBundlesShiny`
- `src/Controller/AlbumIndexController.php` — call `AlbumPokemonResponseFactory::fromSqlRows()`
- `tests/src/Unit/DTO/Response/PokemonDataResponseTest.php` — cover new fields
- `tests/src/Common/Data/AlbumData.php` — add `toNestedFormat()` helper + nested public methods
- `tests/src/Integration/Controller/AlbumIndexControllerTest.php` — update `pokemons` assertions

**Unchanged (service still returns flat arrays):**
- `src/Service/Album/AlbumPokemonService.php`
- `tests/src/Integration/Service/Album/AlbumPokemonServiceTest.php`
- `tests/src/Integration/Service/Album/AlbumPokemonServiceFilteredTest.php`
- `tests/src/Common/Traits/PokemonListTrait.php`

---

## Target JSON Structure for a Single Pokémon Item

```json
{
  "pokemon": {
    "slug": "bulbasaur",
    "name": "Bulbasaur",
    "french_name": "Bulbizarre",
    "national_dex_number": 1,
    "regional_dex_number": 1,
    "simplified_name": "Bulbasaur",
    "forms_label": "",
    "simplified_french_name": "Bulbizarre",
    "forms_french_label": "",
    "icon": "bulbasaur",
    "family_order": 0,
    "family_lead_slug": "bulbasaur",
    "original_game_bundle_slug": "redgreenblueyellow",
    "order_number": "0001-0001-000",
    "game_bundles": ["redgreenblueyellow", "goldsilvercrystal"],
    "game_bundles_shiny": ["redgreenblueyellow", "goldsilvercrystal"]
  },
  "catch_state": {
    "slug": "no",
    "name": "No",
    "french_name": "Non"
  },
  "category_form": {
    "slug": "starter",
    "name": "Starter"
  },
  "regional_form": null,
  "special_form": null,
  "variant_form": null,
  "primary_type": {
    "slug": "grass",
    "name": "Grass",
    "french_name": "Plante"
  },
  "secondary_type": {
    "slug": "poison",
    "name": "Poison",
    "french_name": "Poison"
  }
}
```

Note: `catch_state` is `null` when not set. `primary_type` is `null` for custom Pokémon like Douze (which has no type). `secondary_type` is `null` for single-type Pokémon.

---

## Tasks

### Task 1: Modify PokemonDataResponse to add three new fields

**Files:**
- Modify: `src/DTO/Response/PokemonDataResponse.php`

- [ ] **Step 1: Replace the file content**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonDataResponse
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('national_dex_number')]
        public readonly int $nationalDexNumber,
        #[SerializedName('regional_dex_number')]
        public readonly ?int $regionalDexNumber,
        #[SerializedName('simplified_name')]
        public readonly ?string $simplifiedName,
        #[SerializedName('forms_label')]
        public readonly ?string $formsLabel,
        #[SerializedName('simplified_french_name')]
        public readonly ?string $simplifiedFrenchName,
        #[SerializedName('forms_french_label')]
        public readonly ?string $formsFrenchLabel,
        public readonly ?string $icon,
        #[SerializedName('family_order')]
        public readonly int $familyOrder,
        #[SerializedName('family_lead_slug')]
        public readonly ?string $familyLeadSlug,
        #[SerializedName('original_game_bundle_slug')]
        public readonly ?string $originalGameBundleSlug,
        #[SerializedName('order_number')]
        public readonly string $orderNumber,
        #[SerializedName('game_bundles')]
        public readonly array $gameBundles,
        #[SerializedName('game_bundles_shiny')]
        public readonly array $gameBundlesShiny,
    ) {}
}
```

- [ ] **Step 2: Update the existing unit test to cover the new fields**

Replace `tests/src/Unit/DTO/Response/PokemonDataResponseTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonDataResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDataResponse::class)]
final class PokemonDataResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $response = new PokemonDataResponse(
            slug: 'pikachu',
            name: 'Pikachu',
            frenchName: 'Pikachu',
            nationalDexNumber: 25,
            regionalDexNumber: 35,
            simplifiedName: 'Pikachu Base',
            formsLabel: 'Original Cap',
            simplifiedFrenchName: 'Pikachu Base FR',
            formsFrenchLabel: 'Casquette Originale',
            icon: 'pikachu.png',
            familyOrder: 1,
            familyLeadSlug: 'pichu',
            originalGameBundleSlug: 'rby',
            orderNumber: '0025.001',
            gameBundles: ['rby', 'gsc'],
            gameBundlesShiny: ['rby'],
        );

        self::assertSame('pikachu', $response->slug);
        self::assertSame('Pikachu', $response->name);
        self::assertSame('Pikachu', $response->frenchName);
        self::assertSame(25, $response->nationalDexNumber);
        self::assertSame(35, $response->regionalDexNumber);
        self::assertSame('Pikachu Base', $response->simplifiedName);
        self::assertSame('Original Cap', $response->formsLabel);
        self::assertSame('Pikachu Base FR', $response->simplifiedFrenchName);
        self::assertSame('Casquette Originale', $response->formsFrenchLabel);
        self::assertSame('pikachu.png', $response->icon);
        self::assertSame(1, $response->familyOrder);
        self::assertSame('pichu', $response->familyLeadSlug);
        self::assertSame('rby', $response->originalGameBundleSlug);
        self::assertSame('0025.001', $response->orderNumber);
        self::assertSame(['rby', 'gsc'], $response->gameBundles);
        self::assertSame(['rby'], $response->gameBundlesShiny);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new PokemonDataResponse(
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            nationalDexNumber: 1,
            regionalDexNumber: null,
            simplifiedName: null,
            formsLabel: null,
            simplifiedFrenchName: null,
            formsFrenchLabel: null,
            icon: null,
            familyOrder: 1,
            familyLeadSlug: null,
            originalGameBundleSlug: null,
            orderNumber: '0001.001',
            gameBundles: [],
            gameBundlesShiny: [],
        );

        self::assertNull($response->regionalDexNumber);
        self::assertNull($response->simplifiedName);
        self::assertNull($response->formsLabel);
        self::assertNull($response->simplifiedFrenchName);
        self::assertNull($response->formsFrenchLabel);
        self::assertNull($response->icon);
        self::assertNull($response->familyLeadSlug);
        self::assertNull($response->originalGameBundleSlug);
        self::assertSame([], $response->gameBundles);
        self::assertSame([], $response->gameBundlesShiny);
    }
}
```

---

### Task 2: Create AlbumCatchStateResponse DTO and its test

**Files:**
- Create: `src/DTO/Response/AlbumCatchStateResponse.php`
- Create: `tests/src/Unit/DTO/Response/AlbumCatchStateResponseTest.php`

- [ ] **Step 1: Create the DTO**

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

Save as `src/DTO/Response/AlbumCatchStateResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumCatchStateResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumCatchStateResponse::class)]
final class AlbumCatchStateResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new AlbumCatchStateResponse(
            slug: 'yes',
            name: 'Yes',
            frenchName: 'Oui',
        );

        self::assertSame('yes', $response->slug);
        self::assertSame('Yes', $response->name);
        self::assertSame('Oui', $response->frenchName);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumCatchStateResponse(
            slug: 'maybe',
            name: 'Maybe',
            frenchName: 'Peut être',
        );

        self::assertSame('maybe', $response->slug);
        self::assertSame('Maybe', $response->name);
        self::assertSame('Peut être', $response->frenchName);
    }
}
```

Save as `tests/src/Unit/DTO/Response/AlbumCatchStateResponseTest.php`.

---

### Task 3: Create AlbumFormResponse DTO and its test

**Files:**
- Create: `src/DTO/Response/AlbumFormResponse.php`
- Create: `tests/src/Unit/DTO/Response/AlbumFormResponseTest.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class AlbumFormResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
    ) {}
}
```

Save as `src/DTO/Response/AlbumFormResponse.php`.

- [ ] **Step 2: Create the unit test**

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
        );

        self::assertSame('mega', $response->slug);
        self::assertSame('Mega', $response->name);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumFormResponse(
            slug: 'starter',
            name: 'Starter',
        );

        self::assertSame('starter', $response->slug);
        self::assertSame('Starter', $response->name);
    }
}
```

Save as `tests/src/Unit/DTO/Response/AlbumFormResponseTest.php`.

---

### Task 4: Create AlbumTypeResponse DTO and its test

**Files:**
- Create: `src/DTO/Response/AlbumTypeResponse.php`
- Create: `tests/src/Unit/DTO/Response/AlbumTypeResponseTest.php`

- [ ] **Step 1: Create the DTO**

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

Save as `src/DTO/Response/AlbumTypeResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumTypeResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumTypeResponse::class)]
final class AlbumTypeResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new AlbumTypeResponse(
            slug: 'grass',
            name: 'Grass',
            frenchName: 'Plante',
        );

        self::assertSame('grass', $response->slug);
        self::assertSame('Grass', $response->name);
        self::assertSame('Plante', $response->frenchName);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumTypeResponse(
            slug: 'poison',
            name: 'Poison',
            frenchName: 'Poison',
        );

        self::assertSame('poison', $response->slug);
        self::assertSame('Poison', $response->name);
        self::assertSame('Poison', $response->frenchName);
    }
}
```

Save as `tests/src/Unit/DTO/Response/AlbumTypeResponseTest.php`.

---

### Task 5: Create AlbumPokemonResponse DTO and its test

**Files:**
- Create: `src/DTO/Response/AlbumPokemonResponse.php`
- Create: `tests/src/Unit/DTO/Response/AlbumPokemonResponseTest.php`

- [ ] **Step 1: Create the DTO**

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
        #[SerializedName('category_form')]
        public readonly ?AlbumFormResponse $categoryForm,
        #[SerializedName('regional_form')]
        public readonly ?AlbumFormResponse $regionalForm,
        #[SerializedName('special_form')]
        public readonly ?AlbumFormResponse $specialForm,
        #[SerializedName('variant_form')]
        public readonly ?AlbumFormResponse $variantForm,
        #[SerializedName('primary_type')]
        public readonly ?AlbumTypeResponse $primaryType,
        #[SerializedName('secondary_type')]
        public readonly ?AlbumTypeResponse $secondaryType,
    ) {}
}
```

Save as `src/DTO/Response/AlbumPokemonResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
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
        $categoryForm = new AlbumFormResponse('starter', 'Starter');
        $primaryType = new AlbumTypeResponse('grass', 'Grass', 'Plante');
        $secondaryType = new AlbumTypeResponse('poison', 'Poison', 'Poison');

        $response = new AlbumPokemonResponse(
            pokemon: $pokemon,
            catchState: $catchState,
            categoryForm: $categoryForm,
            regionalForm: null,
            specialForm: null,
            variantForm: null,
            primaryType: $primaryType,
            secondaryType: $secondaryType,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame($catchState, $response->catchState);
        self::assertSame($categoryForm, $response->categoryForm);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertNull($response->variantForm);
        self::assertSame($primaryType, $response->primaryType);
        self::assertSame($secondaryType, $response->secondaryType);
    }

    #[Test]
    public function constructorAcceptsAllNullablePropertiesAsNull(): void
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

        $response = new AlbumPokemonResponse(
            pokemon: $pokemon,
            catchState: null,
            categoryForm: null,
            regionalForm: null,
            specialForm: null,
            variantForm: null,
            primaryType: null,
            secondaryType: null,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertNull($response->catchState);
        self::assertNull($response->categoryForm);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertNull($response->variantForm);
        self::assertNull($response->primaryType);
        self::assertNull($response->secondaryType);
    }
}
```

Save as `tests/src/Unit/DTO/Response/AlbumPokemonResponseTest.php`.

---

### Task 6: Create AlbumPokemonResponseFactory and its unit tests

**Files:**
- Create: `src/Factory/AlbumPokemonResponseFactory.php`
- Create: `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`

- [ ] **Step 1: Create the factory**

The factory receives rows already processed by `AlbumPokemonService::explodesFlatList()`, meaning:
- `game_bundles` and `game_bundles_shiny` are already arrays (not CSV strings)
- All SQL columns are present (catch_state, forms, types may be null)

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\PokemonDataResponse;

final class AlbumPokemonResponseFactory
{
    /**
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): AlbumPokemonResponse
    {
        $pokemon = new PokemonDataResponse(
            slug: (string) $row['pokemon_slug'],
            name: (string) $row['pokemon_name'],
            frenchName: (string) $row['pokemon_french_name'],
            nationalDexNumber: (int) $row['pokemon_national_dex_number'],
            regionalDexNumber: null !== $row['pokemon_regional_dex_number'] ? (int) $row['pokemon_regional_dex_number'] : null,
            simplifiedName: null !== ($row['pokemon_simplified_name'] ?? null) ? (string) $row['pokemon_simplified_name'] : null,
            formsLabel: null !== ($row['pokemon_forms_label'] ?? null) ? (string) $row['pokemon_forms_label'] : null,
            simplifiedFrenchName: null !== ($row['pokemon_simplified_french_name'] ?? null) ? (string) $row['pokemon_simplified_french_name'] : null,
            formsFrenchLabel: null !== ($row['pokemon_forms_french_label'] ?? null) ? (string) $row['pokemon_forms_french_label'] : null,
            icon: null !== ($row['pokemon_icon'] ?? null) ? (string) $row['pokemon_icon'] : null,
            familyOrder: (int) $row['pokemon_family_order'],
            familyLeadSlug: null !== ($row['family_lead_slug'] ?? null) ? (string) $row['family_lead_slug'] : null,
            originalGameBundleSlug: null !== ($row['original_game_bundle_slug'] ?? null) ? (string) $row['original_game_bundle_slug'] : null,
            orderNumber: (string) $row['pokemon_order_number'],
            gameBundles: (array) $row['game_bundles'],
            gameBundlesShiny: (array) $row['game_bundles_shiny'],
        );

        $catchState = null !== ($row['catch_state_slug'] ?? null)
            ? new AlbumCatchStateResponse(
                slug: (string) $row['catch_state_slug'],
                name: (string) $row['catch_state_name'],
                frenchName: (string) $row['catch_state_french_name'],
            )
            : null;

        $categoryForm = null !== ($row['category_form_slug'] ?? null)
            ? new AlbumFormResponse(
                slug: (string) $row['category_form_slug'],
                name: (string) $row['category_form_name'],
            )
            : null;

        $regionalForm = null !== ($row['regional_form_slug'] ?? null)
            ? new AlbumFormResponse(
                slug: (string) $row['regional_form_slug'],
                name: (string) $row['regional_form_name'],
            )
            : null;

        $specialForm = null !== ($row['special_form_slug'] ?? null)
            ? new AlbumFormResponse(
                slug: (string) $row['special_form_slug'],
                name: (string) $row['special_form_name'],
            )
            : null;

        $variantForm = null !== ($row['variant_form_slug'] ?? null)
            ? new AlbumFormResponse(
                slug: (string) $row['variant_form_slug'],
                name: (string) $row['variant_form_name'],
            )
            : null;

        $primaryType = null !== ($row['primary_type_slug'] ?? null)
            ? new AlbumTypeResponse(
                slug: (string) $row['primary_type_slug'],
                name: (string) $row['primary_type_name'],
                frenchName: (string) $row['primary_type_french_name'],
            )
            : null;

        $secondaryType = null !== ($row['secondary_type_slug'] ?? null)
            ? new AlbumTypeResponse(
                slug: (string) $row['secondary_type_slug'],
                name: (string) $row['secondary_type_name'],
                frenchName: (string) $row['secondary_type_french_name'],
            )
            : null;

        return new AlbumPokemonResponse(
            pokemon: $pokemon,
            catchState: $catchState,
            categoryForm: $categoryForm,
            regionalForm: $regionalForm,
            specialForm: $specialForm,
            variantForm: $variantForm,
            primaryType: $primaryType,
            secondaryType: $secondaryType,
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
}
```

Save as `src/Factory/AlbumPokemonResponseFactory.php`.

- [ ] **Step 2: Create the unit tests**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\PokemonDataResponse;
use App\Factory\AlbumPokemonResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumPokemonResponseFactory::class)]
final class AlbumPokemonResponseFactoryTest extends TestCase
{
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

    #[Test]
    public function fromSqlRow_buildsPokemonSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(PokemonDataResponse::class, $result->pokemon);
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
    public function fromSqlRow_buildsCatchStateSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumCatchStateResponse::class, $result->catchState);
        self::assertSame('no', $result->catchState->slug);
        self::assertSame('No', $result->catchState->name);
        self::assertSame('Non', $result->catchState->frenchName);
    }

    #[Test]
    public function fromSqlRow_setsNullCatchStateWhenNotSet(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->catchState);
    }

    #[Test]
    public function fromSqlRow_buildsCategoryFormSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumFormResponse::class, $result->categoryForm);
        self::assertSame('starter', $result->categoryForm->slug);
        self::assertSame('Starter', $result->categoryForm->name);
    }

    #[Test]
    public function fromSqlRow_setsNullFormsWhenNotSet(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertNull($result->regionalForm);
        self::assertNull($result->specialForm);
        self::assertNull($result->variantForm);
    }

    #[Test]
    public function fromSqlRow_buildsSpecialFormSubObject(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['special_form_slug'] = 'mega';
        $row['special_form_name'] = 'Mega';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($result->categoryForm);
        self::assertInstanceOf(AlbumFormResponse::class, $result->specialForm);
        self::assertSame('mega', $result->specialForm->slug);
        self::assertSame('Mega', $result->specialForm->name);
    }

    #[Test]
    public function fromSqlRow_buildsVariantFormSubObject(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['variant_form_slug'] = 'gender';
        $row['variant_form_name'] = 'Gender';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormResponse::class, $result->variantForm);
        self::assertSame('gender', $result->variantForm->slug);
        self::assertSame('Gender', $result->variantForm->name);
    }

    #[Test]
    public function fromSqlRow_buildsRegionalFormSubObject(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['regional_form_slug'] = 'alolan';
        $row['regional_form_name'] = 'Alolan';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormResponse::class, $result->regionalForm);
        self::assertSame('alolan', $result->regionalForm->slug);
        self::assertSame('Alolan', $result->regionalForm->name);
    }

    #[Test]
    public function fromSqlRow_buildsPrimaryTypeSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumTypeResponse::class, $result->primaryType);
        self::assertSame('grass', $result->primaryType->slug);
        self::assertSame('Grass', $result->primaryType->name);
        self::assertSame('Plante', $result->primaryType->frenchName);
    }

    #[Test]
    public function fromSqlRow_buildsSecondaryTypeSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumTypeResponse::class, $result->secondaryType);
        self::assertSame('poison', $result->secondaryType->slug);
        self::assertSame('Poison', $result->secondaryType->name);
        self::assertSame('Poison', $result->secondaryType->frenchName);
    }

    #[Test]
    public function fromSqlRow_setsNullPrimaryTypeWhenAbsent(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->primaryType);
        self::assertNull($result->secondaryType);
    }

    #[Test]
    public function fromSqlRow_setsNullSecondaryTypeForSingleTypePokemon(): void
    {
        $row = $this->getBulbasaurRow();
        $row['secondary_type_slug'] = null;
        $row['secondary_type_name'] = null;
        $row['secondary_type_french_name'] = null;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumTypeResponse::class, $result->primaryType);
        self::assertNull($result->secondaryType);
    }

    #[Test]
    public function fromSqlRow_setsNullRegionalDexNumber(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->pokemon->regionalDexNumber);
    }

    #[Test]
    public function fromSqlRow_castsNumericFieldsToCorrectTypes(): void
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
    public function fromSqlRows_transformsMultipleRows(): void
    {
        $rows = [$this->getBulbasaurRow(), $this->getDouzeRow()];

        $results = AlbumPokemonResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $results);
        self::assertContainsOnly(AlbumPokemonResponse::class, $results);
        self::assertSame('bulbasaur', $results[0]->pokemon->slug);
        self::assertSame('douze', $results[1]->pokemon->slug);
    }

    #[Test]
    public function fromSqlRows_handlesEmptyArray(): void
    {
        $results = AlbumPokemonResponseFactory::fromSqlRows([]);

        self::assertIsArray($results);
        self::assertCount(0, $results);
    }
}
```

Save as `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`.

---

### Task 7: Update AlbumIndexController to use AlbumPokemonResponseFactory

**Files:**
- Modify: `src/Controller/AlbumIndexController.php`

- [ ] **Step 1: Replace the file content**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumFilter\AlbumFiltersRequest;
use App\Factory\AlbumPokemonResponseFactory;
use App\Service\Album\AlbumDexService;
use App\Service\Album\AlbumPokemonService;
use App\Service\Album\AlbumReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/album')]
final class AlbumIndexController extends AbstractController
{
    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    public function index(
        AlbumPokemonService $albumPokemonService,
        AlbumDexService $albumDexService,
        AlbumReportService $albumReportService,
        string $trainerExternalId,
        string $dexSlug,
        Request $request,
        SerializerInterface $serializer,
    ): JsonResponse {
        $albumsFilters = AlbumFiltersRequest::albumFiltersFromRequest($request);

        $pokemons = $albumPokemonService->get(
            $trainerExternalId,
            $dexSlug,
            $albumsFilters
        );

        $report = $albumReportService->get(
            $trainerExternalId,
            $dexSlug,
            AlbumFilters::createFromArray([])
        );
        $filteredReport = $albumReportService->get(
            $trainerExternalId,
            $dexSlug,
            $albumsFilters
        );

        $dex = $albumDexService->get($trainerExternalId, $dexSlug);

        return JsonResponse::fromJsonString(
            $serializer->serialize(
                [
                    'dex' => $dex,
                    'pokemons' => AlbumPokemonResponseFactory::fromSqlRows($pokemons),
                    'report' => $report,
                    'filtered_report' => $filteredReport,
                ],
                'json',
            ),
        );
    }
}
```

---

### Task 8: Add nested format helpers to AlbumData

**Files:**
- Modify: `tests/src/Common/Data/AlbumData.php`

The existing flat-format methods (`getExpectedRegGreenBlueYellowContent`, etc.) must remain unchanged — they are used by `AlbumPokemonServiceTest`. Add a private `toNestedFormat()` converter and new public methods returning the nested format for controller tests.

- [ ] **Step 1: Add methods at the end of the class, before the closing `}`**

Add the following methods to `AlbumData`:

```php
    /**
     * @return array<array<string, mixed>>
     */
    public static function getExpectedRegGreenBlueYellowNestedContent(
        ?string $bulbasaurCatchState,
        ?string $ivysaurCatchState,
        ?string $venusaurCatchState,
        ?string $caterpieCatchState,
        ?string $metapodCatchState,
        ?string $butterfreeCatchState,
        ?string $douzeCatchState,
    ): array {
        return array_map(
            static fn (array $flat) => self::toNestedFormat($flat),
            self::getExpectedRegGreenBlueYellowContent(
                $bulbasaurCatchState,
                $ivysaurCatchState,
                $venusaurCatchState,
                $caterpieCatchState,
                $metapodCatchState,
                $butterfreeCatchState,
                $douzeCatchState,
            )
        );
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function getExpectedGoldSilverCrystalNestedContent(
        ?string $bulbasaurCatchState,
        ?string $ivysaurCatchState,
        ?string $venusaurCatchState,
        ?string $charmanderCatchState,
        ?string $charmeleonCatchState,
        ?string $charizardCatchState,
        ?string $caterpieCatchState,
        ?string $metapodCatchState,
        ?string $butterfreeCatchState,
    ): array {
        return array_map(
            static fn (array $flat) => self::toNestedFormat($flat),
            self::getExpectedGoldSilverCrystalContent(
                $bulbasaurCatchState,
                $ivysaurCatchState,
                $venusaurCatchState,
                $charmanderCatchState,
                $charmeleonCatchState,
                $charizardCatchState,
                $caterpieCatchState,
                $metapodCatchState,
                $butterfreeCatchState,
            )
        );
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function getExpectedHomeNestedContent(): array
    {
        return array_map(
            static fn (array $flat) => self::toNestedFormat($flat),
            self::getExpectedHomeContent()
        );
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function getExpectedHomeShinyNestedContent(): array
    {
        return array_map(
            static fn (array $flat) => self::toNestedFormat($flat),
            self::getExpectedHomeShinyContent()
        );
    }

    /**
     * @param array<string, mixed> $flat
     *
     * @return array<string, mixed>
     */
    private static function toNestedFormat(array $flat): array
    {
        $catchState = null !== ($flat['catch_state_slug'] ?? null)
            ? [
                'slug' => $flat['catch_state_slug'],
                'name' => $flat['catch_state_name'],
                'french_name' => $flat['catch_state_french_name'],
            ]
            : null;

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
    }
```

The `@SuppressWarnings("PHPMD.ExcessiveMethodLength")` docblock already on the class covers the new long methods. Add it to the class docblock if the linter complains, or add it to the individual methods.

---

### Task 9: Update AlbumIndexControllerTest to assert nested format

**Files:**
- Modify: `tests/src/Integration/Controller/AlbumIndexControllerTest.php`

Five tests compare `$data['pokemons']` against `AlbumData::getExpected...Content()`. Each must switch to the `...NestedContent()` equivalent. The `dex` and `report` assertions are unchanged.

- [ ] **Step 1: Update `testListUser12RedGreenBlueYellow`**

Find this block:
```php
        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowContent(
                'no',
                'maybe',
                'maybenot',
                'maybenot',
                null,
                null,
                null
            ),
            $pokemons
        );
```

Replace with:
```php
        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowNestedContent(
                'no',
                'maybe',
                'maybenot',
                'maybenot',
                null,
                null,
                null
            ),
            $pokemons
        );
```

- [ ] **Step 2: Update `testListUser12GoldSilverCrystal`**

Find:
```php
        $this->assertEquals(
            AlbumData::getExpectedGoldSilverCrystalContent(
                'yes',
                'no',
                'no',
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
```

Replace with:
```php
        $this->assertEquals(
            AlbumData::getExpectedGoldSilverCrystalNestedContent(
                'yes',
                'no',
                'no',
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
```

- [ ] **Step 3: Update `testListUser13`**

Find:
```php
        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowContent(
                'yes',
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
```

Replace with:
```php
        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowNestedContent(
                'yes',
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
```

- [ ] **Step 4: Update `testListUserUnknown`**

Find:
```php
        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowContent(
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
```

Replace with:
```php
        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowNestedContent(
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
```

- [ ] **Step 5: Update `testListHome`**

Find:
```php
        $this->assertEquals(
            AlbumData::getExpectedHomeContent(),
            $pokemons
        );
```

Replace with:
```php
        $this->assertEquals(
            AlbumData::getExpectedHomeNestedContent(),
            $pokemons
        );
```

- [ ] **Step 6: Update `testListHomeShiny`**

Find:
```php
        $this->assertEquals(
            AlbumData::getExpectedHomeShinyContent(),
            $pokemons
        );
```

Replace with:
```php
        $this->assertEquals(
            AlbumData::getExpectedHomeShinyNestedContent(),
            $pokemons
        );
```

---

### Task 10: Run quality checks

**Files:** All files from previous tasks

- [ ] **Step 1: Run all tests**

```bash
make tests
```

Expected: All unit and integration tests pass, 0 failures. Pay special attention to `AlbumIndexControllerTest` and `AlbumPokemonServiceTest` — both must pass (service tests still use flat format, controller tests now use nested format).

- [ ] **Step 2: Run code quality checks**

```bash
make quality
```

Expected: All quality checks pass (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint). Common issues to watch for:
- PHPStan may flag the `array` type on `gameBundles`/`gameBundlesShiny` — add `@var list<string>` PHPDoc if needed
- PHPMD ExcessiveMethodLength on `AlbumPokemonResponseFactoryTest` — add `@SuppressWarnings("PHPMD.ExcessiveMethodLength")` to the class docblock if triggered

- [ ] **Step 3: Run coverage and mutation checks**

```bash
make measures
```

Expected: 100% line coverage and 100% MSI for all new classes.

---

## Summary of Changes

- **New DTOs**: `AlbumCatchStateResponse`, `AlbumFormResponse`, `AlbumTypeResponse`, `AlbumPokemonResponse`
- **Extended DTO**: `PokemonDataResponse` gains `regionalDexNumber`, `gameBundles`, `gameBundlesShiny`
- **New Factory**: `AlbumPokemonResponseFactory` transforms flat processed rows → nested `AlbumPokemonResponse[]`
- **Updated Controller**: `AlbumIndexController` calls the factory on the `pokemons` array
- **Updated Tests**: `AlbumData` gains nested helpers; controller test assertions use them
- **Unchanged**: `AlbumPokemonService`, service tests, `PokemonListTrait`
