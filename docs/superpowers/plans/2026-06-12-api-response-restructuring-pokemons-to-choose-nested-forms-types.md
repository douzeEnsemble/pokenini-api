# API Response Restructuring (Pokemons to Choose — Nested Forms & Types) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /pokemons/to_choose` response by grouping the 4 flat `*_form` fields and the 2 flat `*_type` fields of `ElectionPokemonResponse` into nested `forms` (`FormsResponse`) and `types` (`TypesResponse`) objects, mirroring the structure already used by `ElectionEloResponse`.

**Architecture:** Update the existing `ElectionPokemonResponse` DTO to embed `?FormsResponse $forms` and `TypesResponse $types` instead of the 6 flat nullable properties; update `ElectionPokemonResponseFactory` to build those nested objects using the same private helpers already present in `ElectionEloResponseFactory`; update all unit tests and the integration test accordingly. No controller or Moco changes are needed.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Response shape change

**Before:**
```json
{
  "type": "pick",
  "items": [
    {
      "pokemon": { "slug": "bulbasaur", "national_dex_number": 1, "..." : "..." },
      "category_form": null,
      "regional_form": null,
      "special_form": null,
      "variant_form": null,
      "primary_type": { "slug": "grass", "name": "Grass", "french_name": "Plante" },
      "secondary_type": null
    }
  ]
}
```

**After:**
```json
{
  "type": "pick",
  "items": [
    {
      "pokemon": { "slug": "bulbasaur", "national_dex_number": 1, "...": "..." },
      "forms": null,
      "types": {
        "primary": { "slug": "grass", "name": "Grass", "french_name": "Plante", "color": "" },
        "secondary": null
      }
    }
  ]
}
```

When at least one form is present, `forms` is a non-null object:
```json
{
  "forms": {
    "category": null,
    "regional": { "slug": "alolan", "name": "Alolan", "french_name": "Alolan FR" },
    "special": null,
    "variant": null
  }
}
```

---

## File Structure

**Modify:**
- `src/DTO/Response/ElectionPokemonResponse.php` — replace 6 flat fields with `?FormsResponse $forms` + `TypesResponse $types`
- `src/Factory/ElectionPokemonResponseFactory.php` — replace flat construction with `buildForms`/`buildTypes` private helpers and swap `AlbumTypeResponse` for `TypeResponse`
- `tests/src/Unit/DTO/Response/ElectionPokemonResponseTest.php` — update constructor call and assertions
- `tests/src/Unit/DTO/Response/ElectionPokemonsListResponseTest.php` — update `buildElectionPokemonResponse` helper
- `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php` — update assertions to access nested properties
- `tests/src/Integration/Controller/PokemonsControllerTest.php` — update `assertResponseContent` to check nested `forms`/`types`

---

## Tasks

### Task 1: Update ElectionPokemonResponse DTO

**Files:**
- Modify: `src/DTO/Response/ElectionPokemonResponse.php`

- [ ] **Step 1: Replace the 6 flat form/type properties with nested objects**

Replace the entire file with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionPokemonResponse
{
    public function __construct(
        public readonly PokemonDataResponse $pokemon,
        public readonly ?FormsResponse $forms,
        public readonly TypesResponse $types,
    ) {}
}
```

- [ ] **Step 2: Verify the file is syntactically correct**

Run: `docker compose exec php php -l src/DTO/Response/ElectionPokemonResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/ElectionPokemonResponse.php`

---

### Task 2: Update ElectionPokemonResponseFactory

**Files:**
- Modify: `src/Factory/ElectionPokemonResponseFactory.php`

- [ ] **Step 1: Replace the factory with nested construction helpers**

Replace the entire file with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionPokemonsList;
use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\ElectionPokemonsListResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\TypeResponse;
use App\DTO\Response\TypesResponse;

final class ElectionPokemonResponseFactory
{
    public static function fromElectionPokemonsList(ElectionPokemonsList $list): ElectionPokemonsListResponse
    {
        return new ElectionPokemonsListResponse(
            type: $list->getListType(),
            items: self::fromSqlRows($list->getItems()),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): ElectionPokemonResponse
    {
        return new ElectionPokemonResponse(
            pokemon: self::buildPokemon($row),
            forms: self::buildForms($row),
            types: self::buildTypes($row),
        );
    }

    /**
     * @param array<array<string, mixed>> $rows
     *
     * @return ElectionPokemonResponse[]
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
            familyLeadSlug: null !== $familyLeadSlug ? (string) $familyLeadSlug : null,
            originalGameBundleSlug: null !== $originalGameBundleSlug ? (string) $originalGameBundleSlug : null,
            orderNumber: (string) $orderNumber,
            gameBundles: [],
            gameBundlesShiny: [],
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForms(array $row): ?FormsResponse
    {
        $hasAnyForm = !empty($row['category_form_slug'])
            || !empty($row['regional_form_slug'])
            || !empty($row['special_form_slug'])
            || !empty($row['variant_form_slug']);

        if (!$hasAnyForm) {
            return null;
        }

        return new FormsResponse(
            category: self::buildForm('category_form', $row),
            regional: self::buildForm('regional_form', $row),
            special: self::buildForm('special_form', $row),
            variant: self::buildForm('variant_form', $row),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForm(string $prefix, array $row): ?FormResponse
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

        return new FormResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildTypes(array $row): TypesResponse
    {
        return new TypesResponse(
            primary: self::buildType('primary_type', $row),
            secondary: self::buildType('secondary_type', $row),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
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
}
```

- [ ] **Step 2: Verify the file is syntactically correct**

Run: `docker compose exec php php -l src/Factory/ElectionPokemonResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/ElectionPokemonResponseFactory.php`

---

### Task 3: Update unit test for ElectionPokemonResponse DTO

**Files:**
- Modify: `tests/src/Unit/DTO/Response/ElectionPokemonResponseTest.php`

- [ ] **Step 1: Rewrite the test file to match the new DTO signature**

Replace the entire file with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\TypeResponse;
use App\DTO\Response\TypesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionPokemonResponse::class)]
final class ElectionPokemonResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $pokemon = $this->buildPokemon();
        $forms = new FormsResponse(
            category: new FormResponse('starter', 'Starter', 'Partant'),
            regional: null,
            special: null,
            variant: null,
        );
        $types = new TypesResponse(
            primary: new TypeResponse('grass', 'Grass', 'Plante', ''),
            secondary: new TypeResponse('poison', 'Poison', 'Poison', ''),
        );

        $response = new ElectionPokemonResponse(
            pokemon: $pokemon,
            forms: $forms,
            types: $types,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame($forms, $response->forms);
        self::assertSame($types, $response->types);
    }

    #[Test]
    public function constructorAcceptsNullForms(): void
    {
        $pokemon = $this->buildPokemon();
        $types = new TypesResponse(
            primary: new TypeResponse('grass', 'Grass', 'Plante', ''),
            secondary: null,
        );

        $response = new ElectionPokemonResponse(
            pokemon: $pokemon,
            forms: null,
            types: $types,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertNull($response->forms);
        self::assertSame($types, $response->types);
    }

    private function buildPokemon(): PokemonDataResponse
    {
        return new PokemonDataResponse(
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            nationalDexNumber: 1,
            regionalDexNumber: null,
            simplifiedName: 'Bulbasaur',
            formsLabel: '',
            simplifiedFrenchName: 'Bulbizarre',
            formsFrenchLabel: '',
            icon: 'bulbasaur',
            familyOrder: 0,
            familyLeadSlug: 'bulbasaur',
            originalGameBundleSlug: 'redgreenblueyellow',
            orderNumber: '9999-0001-000',
            gameBundles: [],
            gameBundlesShiny: [],
        );
    }
}
```

---

### Task 4: Update ElectionPokemonsListResponseTest helper

**Files:**
- Modify: `tests/src/Unit/DTO/Response/ElectionPokemonsListResponseTest.php`

The `buildElectionPokemonResponse` private helper constructs `ElectionPokemonResponse` using the old flat signature. This must be updated so the file compiles.

- [ ] **Step 1: Update `buildElectionPokemonResponse` to match the new DTO signature**

Replace only the `buildElectionPokemonResponse` method (lines 58–88) with:

```php
    private function buildElectionPokemonResponse(): ElectionPokemonResponse
    {
        $pokemon = new PokemonDataResponse(
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            nationalDexNumber: 1,
            regionalDexNumber: null,
            simplifiedName: 'Bulbasaur',
            formsLabel: '',
            simplifiedFrenchName: 'Bulbizarre',
            formsFrenchLabel: '',
            icon: 'bulbasaur',
            familyOrder: 0,
            familyLeadSlug: 'bulbasaur',
            originalGameBundleSlug: 'redgreenblueyellow',
            orderNumber: '9999-0001-000',
            gameBundles: [],
            gameBundlesShiny: [],
        );

        return new ElectionPokemonResponse(
            pokemon: $pokemon,
            forms: null,
            types: new \App\DTO\Response\TypesResponse(primary: null, secondary: null),
        );
    }
```

Also add the missing import at the top of the file (after the existing `use` statements):

```php
use App\DTO\Response\TypesResponse;
```

And remove the unused import for `AlbumTypeResponse` and `FormResponse` if they were imported (check the file header).

The full updated file becomes:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\ElectionPokemonsListResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\TypesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionPokemonsListResponse::class)]
final class ElectionPokemonsListResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $item = $this->buildElectionPokemonResponse();

        $response = new ElectionPokemonsListResponse(
            type: 'pick',
            items: [$item],
        );

        self::assertSame('pick', $response->type);
        self::assertCount(1, $response->items);
        self::assertSame($item, $response->items[0]);
    }

    #[Test]
    public function constructorAcceptsVoteType(): void
    {
        $response = new ElectionPokemonsListResponse(
            type: 'vote',
            items: [],
        );

        self::assertSame('vote', $response->type);
        self::assertSame([], $response->items);
    }

    #[Test]
    public function constructorAcceptsEmptyItems(): void
    {
        $response = new ElectionPokemonsListResponse(
            type: 'pick',
            items: [],
        );

        self::assertCount(0, $response->items);
    }

    private function buildElectionPokemonResponse(): ElectionPokemonResponse
    {
        $pokemon = new PokemonDataResponse(
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            nationalDexNumber: 1,
            regionalDexNumber: null,
            simplifiedName: 'Bulbasaur',
            formsLabel: '',
            simplifiedFrenchName: 'Bulbizarre',
            formsFrenchLabel: '',
            icon: 'bulbasaur',
            familyOrder: 0,
            familyLeadSlug: 'bulbasaur',
            originalGameBundleSlug: 'redgreenblueyellow',
            orderNumber: '9999-0001-000',
            gameBundles: [],
            gameBundlesShiny: [],
        );

        return new ElectionPokemonResponse(
            pokemon: $pokemon,
            forms: null,
            types: new TypesResponse(primary: null, secondary: null),
        );
    }
}
```

---

### Task 5: Update unit tests for ElectionPokemonResponseFactory

**Files:**
- Modify: `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`

The existing tests access flat properties (`$response->categoryForm`, `$response->primaryType`, etc.) that no longer exist. Every assertion on those properties must be rewritten to navigate the nested structure (`$response->forms->category`, `$response->types->primary`, etc.).

- [ ] **Step 1: Replace the entire test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\ElectionPokemonsList;
use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\TypeResponse;
use App\DTO\Response\TypesResponse;
use App\Factory\ElectionPokemonResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionPokemonResponseFactory::class)]
final class ElectionPokemonResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowReturnsPokemonDataResponse(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame('bulbasaur', $response->pokemon->slug);
        self::assertSame('Bulbasaur', $response->pokemon->name);
        self::assertSame('Bulbizarre', $response->pokemon->frenchName);
        self::assertSame(1, $response->pokemon->nationalDexNumber);
        self::assertNull($response->pokemon->regionalDexNumber);
        self::assertSame('Bulbasaur', $response->pokemon->simplifiedName);
        self::assertSame('', $response->pokemon->formsLabel);
        self::assertSame('Bulbizarre', $response->pokemon->simplifiedFrenchName);
        self::assertSame('', $response->pokemon->formsFrenchLabel);
        self::assertSame('bulbasaur', $response->pokemon->icon);
        self::assertSame(0, $response->pokemon->familyOrder);
        self::assertSame('bulbasaur', $response->pokemon->familyLeadSlug);
        self::assertSame('redgreenblueyellow', $response->pokemon->originalGameBundleSlug);
        self::assertSame('9999-0001-000', $response->pokemon->orderNumber);
        self::assertSame([], $response->pokemon->gameBundles);
        self::assertSame([], $response->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowPokemonDataTypesAreCastToCorrectTypes(): void
    {
        $row = $this->buildRow([
            'pokemon_slug' => 1,
            'pokemon_name' => 2,
            'pokemon_french_name' => 3,
            'pokemon_national_dex_number' => '42',
            'pokemon_family_order' => '5',
            'pokemon_order_number' => 99,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame('1', $response->pokemon->slug);
        self::assertSame('2', $response->pokemon->name);
        self::assertSame('3', $response->pokemon->frenchName);
        self::assertSame(42, $response->pokemon->nationalDexNumber);
        self::assertSame(5, $response->pokemon->familyOrder);
        self::assertSame('99', $response->pokemon->orderNumber);
    }

    #[Test]
    public function fromSqlRowWithNoFormsReturnsNullForms(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->forms);
    }

    #[Test]
    public function fromSqlRowWithCategoryFormBuildsFormsResponse(): void
    {
        $row = $this->buildRow([
            'category_form_slug' => 'starter',
            'category_form_name' => 'Starter',
            'category_form_french_name' => 'Partant',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertInstanceOf(FormResponse::class, $response->forms->category);
        self::assertSame('starter', $response->forms->category->slug);
        self::assertSame('Starter', $response->forms->category->name);
        self::assertSame('Partant', $response->forms->category->frenchName);
        self::assertNull($response->forms->regional);
        self::assertNull($response->forms->special);
        self::assertNull($response->forms->variant);
    }

    #[Test]
    public function fromSqlRowWithRegionalFormBuildsFormsResponse(): void
    {
        $row = $this->buildRow([
            'regional_form_slug' => 'alolan',
            'regional_form_name' => 'Alolan',
            'regional_form_french_name' => 'Alolan FR',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertNull($response->forms->category);
        self::assertInstanceOf(FormResponse::class, $response->forms->regional);
        self::assertSame('alolan', $response->forms->regional->slug);
        self::assertSame('Alolan', $response->forms->regional->name);
        self::assertSame('Alolan FR', $response->forms->regional->frenchName);
        self::assertNull($response->forms->special);
        self::assertNull($response->forms->variant);
    }

    #[Test]
    public function fromSqlRowWithSpecialFormBuildsFormsResponse(): void
    {
        $row = $this->buildRow([
            'special_form_slug' => 'mega',
            'special_form_name' => 'Mega',
            'special_form_french_name' => 'Méga',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertNull($response->forms->category);
        self::assertNull($response->forms->regional);
        self::assertInstanceOf(FormResponse::class, $response->forms->special);
        self::assertSame('mega', $response->forms->special->slug);
        self::assertSame('Mega', $response->forms->special->name);
        self::assertSame('Méga', $response->forms->special->frenchName);
        self::assertNull($response->forms->variant);
    }

    #[Test]
    public function fromSqlRowWithVariantFormBuildsFormsResponse(): void
    {
        $row = $this->buildRow([
            'variant_form_slug' => 'shiny',
            'variant_form_name' => 'Shiny',
            'variant_form_french_name' => 'Chromatique',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertNull($response->forms->category);
        self::assertNull($response->forms->regional);
        self::assertNull($response->forms->special);
        self::assertInstanceOf(FormResponse::class, $response->forms->variant);
        self::assertSame('shiny', $response->forms->variant->slug);
        self::assertSame('Shiny', $response->forms->variant->name);
        self::assertSame('Chromatique', $response->forms->variant->frenchName);
    }

    #[Test]
    public function fromSqlRowAlwaysReturnsTypesResponse(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(TypesResponse::class, $response->types);
    }

    #[Test]
    public function fromSqlRowWithPrimaryTypeReturnsPrimaryTypeResponse(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(TypeResponse::class, $response->types->primary);
        self::assertSame('grass', $response->types->primary->slug);
        self::assertSame('Grass', $response->types->primary->name);
        self::assertSame('Plante', $response->types->primary->frenchName);
        self::assertSame('', $response->types->primary->color);
        self::assertNull($response->types->secondary);
    }

    #[Test]
    public function fromSqlRowWithSecondaryTypeReturnsSecondaryTypeResponse(): void
    {
        $row = $this->buildRow([
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(TypeResponse::class, $response->types->secondary);
        self::assertSame('poison', $response->types->secondary->slug);
        self::assertSame('Poison', $response->types->secondary->name);
        self::assertSame('Poison', $response->types->secondary->frenchName);
        self::assertSame('', $response->types->secondary->color);
    }

    #[Test]
    public function fromSqlRowWithNoPrimaryTypeReturnsBothTypesNull(): void
    {
        $row = $this->buildRow([
            'primary_type_slug' => null,
            'primary_type_name' => null,
            'primary_type_french_name' => null,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->types->primary);
        self::assertNull($response->types->secondary);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRows(): void
    {
        $rows = [
            $this->buildRow(['pokemon_slug' => 'bulbasaur']),
            $this->buildRow(['pokemon_slug' => 'charmander', 'pokemon_national_dex_number' => 4]),
        ];

        $responses = ElectionPokemonResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(ElectionPokemonResponse::class, $responses);
        self::assertSame('bulbasaur', $responses[0]->pokemon->slug);
        self::assertSame('charmander', $responses[1]->pokemon->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = ElectionPokemonResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }

    #[Test]
    public function fromElectionPokemonsListBuildsList(): void
    {
        $rows = [
            $this->buildRow(['pokemon_slug' => 'bulbasaur']),
            $this->buildRow(['pokemon_slug' => 'charmander', 'pokemon_national_dex_number' => 4]),
        ];
        $list = new ElectionPokemonsList('pick', $rows);

        $response = ElectionPokemonResponseFactory::fromElectionPokemonsList($list);

        self::assertSame('pick', $response->type);
        self::assertCount(2, $response->items);
        self::assertSame('bulbasaur', $response->items[0]->pokemon->slug);
        self::assertSame('charmander', $response->items[1]->pokemon->slug);
    }

    #[Test]
    public function fromElectionPokemonsListPreservesListType(): void
    {
        $list = new ElectionPokemonsList('vote', []);

        $response = ElectionPokemonResponseFactory::fromElectionPokemonsList($list);

        self::assertSame('vote', $response->type);
        self::assertCount(0, $response->items);
    }

    #[Test]
    public function fromSqlRowWithNullOptionalPokemonFieldsReturnsNulls(): void
    {
        $row = $this->buildRow([
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => null,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->pokemon->simplifiedName);
        self::assertNull($response->pokemon->formsLabel);
        self::assertNull($response->pokemon->simplifiedFrenchName);
        self::assertNull($response->pokemon->formsFrenchLabel);
        self::assertNull($response->pokemon->icon);
        self::assertNull($response->pokemon->familyLeadSlug);
        self::assertNull($response->pokemon->originalGameBundleSlug);
    }

    #[Test]
    public function fromSqlRowCastsNullableStringFieldsFromNonStringValues(): void
    {
        $row = $this->buildRow([
            'pokemon_simplified_name' => 42,
            'pokemon_forms_label' => 7,
            'pokemon_simplified_french_name' => 99,
            'pokemon_forms_french_label' => 3,
            'pokemon_icon' => 1,
            'family_lead_slug' => 55,
            'original_game_bundle_slug' => 12,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame('42', $response->pokemon->simplifiedName);
        self::assertSame('7', $response->pokemon->formsLabel);
        self::assertSame('99', $response->pokemon->simplifiedFrenchName);
        self::assertSame('3', $response->pokemon->formsFrenchLabel);
        self::assertSame('1', $response->pokemon->icon);
        self::assertSame('55', $response->pokemon->familyLeadSlug);
        self::assertSame('12', $response->pokemon->originalGameBundleSlug);
    }

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
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'original_game_bundle_slug' => 'redgreenblueyellow',
            'pokemon_order_number' => '9999-0001-000',
        ], $overrides);
    }
}
```

---

### Task 6: Update integration test for PokemonsController

**Files:**
- Modify: `tests/src/Integration/Controller/PokemonsControllerTest.php`

The private `assertResponseContent` method currently checks for flat form/type keys at the item level. Replace those assertions with checks for the nested `forms` and `types` structure.

- [ ] **Step 1: Update the `assertResponseContent` method**

Replace only the `assertResponseContent` method (from `private function assertResponseContent` to the closing `}`) with:

```php
    private function assertResponseContent(int $expectedCount): void
    {
        /** @var array<string, mixed> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertArrayHasKey('type', $content);
        $this->assertSame('pick', $content['type']);

        $this->assertArrayHasKey('items', $content);

        /** @var array<array<string, mixed>> $items */
        $items = $content['items'];
        $this->assertCount($expectedCount, $items);

        foreach ($items as $item) {
            $this->assertArrayHasKey('pokemon', $item);
            $this->assertIsArray($item['pokemon']);

            /** @var array<string, mixed> $pokemon */
            $pokemon = $item['pokemon'];
            $this->assertArrayHasKey('slug', $pokemon);
            $this->assertArrayHasKey('french_name', $pokemon);
            $this->assertArrayHasKey('icon', $pokemon);
            $this->assertArrayHasKey('national_dex_number', $pokemon);
            $this->assertArrayHasKey('order_number', $pokemon);
            $this->assertArrayHasKey('game_bundles', $pokemon);
            $this->assertArrayHasKey('game_bundles_shiny', $pokemon);
            $this->assertIsArray($pokemon['game_bundles']);
            $this->assertIsArray($pokemon['game_bundles_shiny']);

            $this->assertArrayHasKey('forms', $item);

            $this->assertArrayHasKey('types', $item);
            $this->assertIsArray($item['types']);

            /** @var array<string, mixed> $types */
            $types = $item['types'];
            $this->assertArrayHasKey('primary', $types);
            $this->assertArrayHasKey('secondary', $types);

            if (null !== $types['primary']) {
                $this->assertIsArray($types['primary']);

                /** @var array<string, mixed> $primary */
                $primary = $types['primary'];
                $this->assertArrayHasKey('slug', $primary);
                $this->assertArrayHasKey('name', $primary);
                $this->assertArrayHasKey('french_name', $primary);
                $this->assertArrayHasKey('color', $primary);
                $this->assertSame('', $primary['color']);
            }
        }
    }
```

---

### Task 7: Run unit tests

- [ ] **Step 1: Run the unit tests for the changed classes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/ElectionPokemonResponseTest.php tests/src/Unit/DTO/Response/ElectionPokemonsListResponseTest.php tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`

Expected: All tests pass, 0 failures, 0 errors.

- [ ] **Step 2: Verify no other unit tests are broken by the signature change**

Run: `make tests-unit`

Expected: All unit tests pass.

---

### Task 8: Run integration tests

- [ ] **Step 1: Run integration tests for PokemonsController**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/PokemonsControllerTest.php`

Expected: All tests pass, 0 failures.

- [ ] **Step 2: Run all integration tests to check for regressions**

Run: `make tests-integration`

Expected: All integration tests pass.

---

### Task 9: Run full quality and coverage checks

- [ ] **Step 1: Run full test suite**

Run: `make tests`

Expected: All unit and integration tests pass, 0 failures.

- [ ] **Step 2: Run code quality checks**

Run: `make quality`

Expected: All checks pass (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint).

- [ ] **Step 3: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% code coverage, 100% MSI on all changed files.
