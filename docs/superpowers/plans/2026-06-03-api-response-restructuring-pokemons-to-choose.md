# API Response Restructuring (GET /pokemons/to_choose) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /pokemons/to_choose` endpoint from a flat JSON structure to a nested object-oriented structure using DTOs + Factory + Serializer pattern.

**Architecture:** Create two immutable response DTOs (`ElectionPokemonResponse`, `ElectionPokemonsListResponse`), a Factory to transform the `ElectionPokemonsList` internal object into the new response DTOs, and update the Controller to apply the transformation before serialization. The internal `ElectionPokemonsList` and service layer remain untouched.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## Current response structure (flat)

```json
{
  "type": "pick",
  "items": [
    {
      "pokemon_slug": "bulbasaur",
      "pokemon_name": "Bulbasaur",
      "pokemon_national_dex_number": 1,
      "pokemon_simplified_name": "Bulbasaur",
      "pokemon_forms_label": "",
      "pokemon_french_name": "Bulbizarre",
      "pokemon_simplified_french_name": "Bulbizarre",
      "pokemon_forms_french_label": "",
      "pokemon_icon": "bulbasaur",
      "pokemon_family_order": 0,
      "family_lead_slug": "bulbasaur",
      "category_form_slug": "starter",
      "category_form_name": "Starter",
      "category_form_french_name": null,
      "regional_form_slug": null,
      "regional_form_name": null,
      "regional_form_french_name": null,
      "special_form_slug": null,
      "special_form_name": null,
      "special_form_french_name": null,
      "variant_form_slug": null,
      "variant_form_name": null,
      "variant_form_french_name": null,
      "primary_type_slug": "grass",
      "primary_type_name": "Grass",
      "primary_type_french_name": "Plante",
      "secondary_type_slug": "poison",
      "secondary_type_name": "Poison",
      "secondary_type_french_name": "Poison",
      "original_game_bundle_slug": "redgreenblueyellow",
      "pokemon_order_number": "9999-0001-000"
    }
  ]
}
```

## Target response structure (nested OO)

```json
{
  "type": "pick",
  "items": [
    {
      "pokemon": {
        "slug": "bulbasaur",
        "name": "Bulbasaur",
        "french_name": "Bulbizarre",
        "national_dex_number": 1,
        "regional_dex_number": null,
        "simplified_name": "Bulbasaur",
        "forms_label": "",
        "simplified_french_name": "Bulbizarre",
        "forms_french_label": "",
        "icon": "bulbasaur",
        "family_order": 0,
        "family_lead_slug": "bulbasaur",
        "original_game_bundle_slug": "redgreenblueyellow",
        "order_number": "9999-0001-000",
        "game_bundles": [],
        "game_bundles_shiny": []
      },
      "category_form": { "slug": "starter", "name": "Starter", "french_name": "Starter" },
      "regional_form": null,
      "special_form": null,
      "variant_form": null,
      "primary_type": { "slug": "grass", "name": "Grass", "french_name": "Plante" },
      "secondary_type": { "slug": "poison", "name": "Poison", "french_name": "Poison" }
    }
  ]
}
```

---

## File Structure

**Create:**
- `src/DTO/Response/ElectionPokemonResponse.php` — immutable DTO per item: pokemon + forms + types
- `src/DTO/Response/ElectionPokemonsListResponse.php` — immutable DTO for the top-level list: type + items
- `src/Factory/ElectionPokemonResponseFactory.php` — transforms flat SQL rows + `ElectionPokemonsList` → response DTOs
- `tests/src/Unit/DTO/Response/ElectionPokemonResponseTest.php` — unit tests for the DTO constructor
- `tests/src/Unit/DTO/Response/ElectionPokemonsListResponseTest.php` — unit tests for the DTO constructor
- `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php` — unit tests for the factory

**Modify:**
- `src/Controller/PokemonsController.php` — apply Factory + Serializer
- `tests/src/Integration/Controller/PokemonsControllerTest.php` — update assertions for new structure

**Reuse (no change):**
- `src/DTO/Response/PokemonDataResponse.php` — reused for the `pokemon` sub-object
- `src/DTO/Response/FormResponse.php` — reused for each nullable form field
- `src/DTO/Response/AlbumTypeResponse.php` — reused for each nullable type field (slug + name + french_name, no color)

---

## Tasks

### Task 1: Create ElectionPokemonResponse DTO

**Files:**
- Create: `src/DTO/Response/ElectionPokemonResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionPokemonResponse
{
    public function __construct(
        public readonly PokemonDataResponse $pokemon,
        #[SerializedName('category_form')]
        public readonly ?FormResponse $categoryForm,
        #[SerializedName('regional_form')]
        public readonly ?FormResponse $regionalForm,
        #[SerializedName('special_form')]
        public readonly ?FormResponse $specialForm,
        #[SerializedName('variant_form')]
        public readonly ?FormResponse $variantForm,
        #[SerializedName('primary_type')]
        public readonly ?AlbumTypeResponse $primaryType,
        #[SerializedName('secondary_type')]
        public readonly ?AlbumTypeResponse $secondaryType,
    ) {}
}
```

Save as `src/DTO/Response/ElectionPokemonResponse.php`.

---

### Task 2: Create ElectionPokemonsListResponse DTO

**Files:**
- Create: `src/DTO/Response/ElectionPokemonsListResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionPokemonsListResponse
{
    /**
     * @param ElectionPokemonResponse[] $items
     */
    public function __construct(
        public readonly string $type,
        public readonly array $items,
    ) {}
}
```

Save as `src/DTO/Response/ElectionPokemonsListResponse.php`.

---

### Task 3: Create ElectionPokemonResponseFactory

**Files:**
- Create: `src/Factory/ElectionPokemonResponseFactory.php`

- [ ] **Step 1: Create the factory file**

The factory extracts pokemon data from the flat SQL row (keyed by `pokemon_slug`, `pokemon_name`, etc.) and builds the nested DTOs. The election SQL does not return `regional_dex_number`, `game_bundles`, or `game_bundles_shiny`, so those are set to `null` / `[]`.

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionPokemonsList;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\ElectionPokemonsListResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\PokemonDataResponse;

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
            categoryForm: self::buildForm('category_form', $row),
            regionalForm: self::buildForm('regional_form', $row),
            specialForm: self::buildForm('special_form', $row),
            variantForm: self::buildForm('variant_form', $row),
            primaryType: self::buildType('primary_type', $row),
            secondaryType: self::buildType('secondary_type', $row),
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

Save as `src/Factory/ElectionPokemonResponseFactory.php`.

---

### Task 4: Write unit tests for ElectionPokemonResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`

- [ ] **Step 1: Create unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\ElectionPokemonsList;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\ElectionPokemonsListResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\PokemonDataResponse;
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

    #[Test]
    public function fromSqlRow_returnsPokemonDataResponse(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(ElectionPokemonResponse::class, $response);
        self::assertInstanceOf(PokemonDataResponse::class, $response->pokemon);
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
    public function fromSqlRow_pokemonDataTypesAreCastToCorrectTypes(): void
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
    public function fromSqlRow_withNoForms_returnsNullForms(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->categoryForm);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertNull($response->variantForm);
    }

    #[Test]
    public function fromSqlRow_withCategoryForm_returnsCategoryFormResponse(): void
    {
        $row = $this->buildRow([
            'category_form_slug' => 'starter',
            'category_form_name' => 'Starter',
            'category_form_french_name' => 'Partant',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormResponse::class, $response->categoryForm);
        self::assertSame('starter', $response->categoryForm->slug);
        self::assertSame('Starter', $response->categoryForm->name);
        self::assertSame('Partant', $response->categoryForm->frenchName);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertNull($response->variantForm);
    }

    #[Test]
    public function fromSqlRow_withRegionalForm_returnsRegionalFormResponse(): void
    {
        $row = $this->buildRow([
            'regional_form_slug' => 'alolan',
            'regional_form_name' => 'Alolan',
            'regional_form_french_name' => 'Alolan FR',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->categoryForm);
        self::assertInstanceOf(FormResponse::class, $response->regionalForm);
        self::assertSame('alolan', $response->regionalForm->slug);
        self::assertSame('Alolan', $response->regionalForm->name);
        self::assertSame('Alolan FR', $response->regionalForm->frenchName);
        self::assertNull($response->specialForm);
        self::assertNull($response->variantForm);
    }

    #[Test]
    public function fromSqlRow_withSpecialForm_returnsSpecialFormResponse(): void
    {
        $row = $this->buildRow([
            'special_form_slug' => 'mega',
            'special_form_name' => 'Mega',
            'special_form_french_name' => 'Méga',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->categoryForm);
        self::assertNull($response->regionalForm);
        self::assertInstanceOf(FormResponse::class, $response->specialForm);
        self::assertSame('mega', $response->specialForm->slug);
        self::assertSame('Mega', $response->specialForm->name);
        self::assertSame('Méga', $response->specialForm->frenchName);
        self::assertNull($response->variantForm);
    }

    #[Test]
    public function fromSqlRow_withVariantForm_returnsVariantFormResponse(): void
    {
        $row = $this->buildRow([
            'variant_form_slug' => 'shiny',
            'variant_form_name' => 'Shiny',
            'variant_form_french_name' => 'Chromatique',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->categoryForm);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertInstanceOf(FormResponse::class, $response->variantForm);
        self::assertSame('shiny', $response->variantForm->slug);
        self::assertSame('Shiny', $response->variantForm->name);
        self::assertSame('Chromatique', $response->variantForm->frenchName);
    }

    #[Test]
    public function fromSqlRow_withPrimaryType_returnsPrimaryTypeResponse(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumTypeResponse::class, $response->primaryType);
        self::assertSame('grass', $response->primaryType->slug);
        self::assertSame('Grass', $response->primaryType->name);
        self::assertSame('Plante', $response->primaryType->frenchName);
        self::assertNull($response->secondaryType);
    }

    #[Test]
    public function fromSqlRow_withSecondaryType_returnsSecondaryTypeResponse(): void
    {
        $row = $this->buildRow([
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumTypeResponse::class, $response->secondaryType);
        self::assertSame('poison', $response->secondaryType->slug);
        self::assertSame('Poison', $response->secondaryType->name);
        self::assertSame('Poison', $response->secondaryType->frenchName);
    }

    #[Test]
    public function fromSqlRow_withNoPrimaryType_returnsNullTypes(): void
    {
        $row = $this->buildRow([
            'primary_type_slug' => null,
            'primary_type_name' => null,
            'primary_type_french_name' => null,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->primaryType);
        self::assertNull($response->secondaryType);
    }

    #[Test]
    public function fromSqlRows_transformsMultipleRows(): void
    {
        $rows = [
            $this->buildRow(['pokemon_slug' => 'bulbasaur']),
            $this->buildRow(['pokemon_slug' => 'charmander', 'pokemon_national_dex_number' => 4]),
        ];

        $responses = ElectionPokemonResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnly(ElectionPokemonResponse::class, $responses);
        self::assertSame('bulbasaur', $responses[0]->pokemon->slug);
        self::assertSame('charmander', $responses[1]->pokemon->slug);
    }

    #[Test]
    public function fromSqlRows_handlesEmptyArray(): void
    {
        $responses = ElectionPokemonResponseFactory::fromSqlRows([]);

        self::assertIsArray($responses);
        self::assertCount(0, $responses);
    }

    #[Test]
    public function fromElectionPokemonsList_buildsList(): void
    {
        $rows = [
            $this->buildRow(['pokemon_slug' => 'bulbasaur']),
            $this->buildRow(['pokemon_slug' => 'charmander', 'pokemon_national_dex_number' => 4]),
        ];
        $list = new ElectionPokemonsList('pick', $rows);

        $response = ElectionPokemonResponseFactory::fromElectionPokemonsList($list);

        self::assertInstanceOf(ElectionPokemonsListResponse::class, $response);
        self::assertSame('pick', $response->type);
        self::assertCount(2, $response->items);
        self::assertSame('bulbasaur', $response->items[0]->pokemon->slug);
        self::assertSame('charmander', $response->items[1]->pokemon->slug);
    }

    #[Test]
    public function fromElectionPokemonsList_preservesListType(): void
    {
        $list = new ElectionPokemonsList('vote', []);

        $response = ElectionPokemonResponseFactory::fromElectionPokemonsList($list);

        self::assertSame('vote', $response->type);
        self::assertCount(0, $response->items);
    }

    #[Test]
    public function fromSqlRow_withNullOptionalPokemonFields_returnsNulls(): void
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
}
```

Save as `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`.

---

### Task 5: Update PokemonsController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/PokemonsController.php`

- [ ] **Step 1: Update controller**

Replace the controller with:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TrainerPokemonEloListQueryOptions;
use App\Factory\ElectionPokemonResponseFactory;
use App\Service\GetNPokemonsToChooseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/pokemons')]
final class PokemonsController extends AbstractController
{
    #[Route(path: '/to_choose', methods: ['GET'])]
    public function getNPokemonsToChoose(
        Request $request,
        GetNPokemonsToChooseService $getNPokemonsToChooseService,
        SerializerInterface $serializer,
    ): JsonResponse {
        /** @var array<array<string>|int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloListQueryOptions($params);

        $list = $getNPokemonsToChooseService->getNPokemonsToChoose($queryOptions);

        $response = ElectionPokemonResponseFactory::fromElectionPokemonsList($list);

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }
}
```

---

### Task 6: Update PokemonsControllerTest integration test

**Files:**
- Modify: `tests/src/Integration/Controller/PokemonsControllerTest.php`

- [ ] **Step 1: Update assertions for the new nested structure**

Replace the file with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\PokemonsController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PokemonsController::class)]
final class PokemonsControllerTest extends AbstractTestControllerApi
{
    public function testGetListFromDex(): void
    {
        $this->apiRequest(
            'GET',
            '/pokemons/to_choose',
            [
                'count' => '12',
                'dex_slug' => 'home',
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
            ]
        );

        $this->assertJsonResponseIsOK();

        $this->assertResponseContent(12);
    }

    public function testGetListFromDexBis(): void
    {
        $this->apiRequest(
            'GET',
            '/pokemons/to_choose',
            [
                'count' => '12',
                'dex_slug' => 'redgreenblueyellow',
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
            ],
        );

        $this->assertJsonResponseIsOK();

        $this->assertResponseContent(7);
    }

    public function testGetListFromDexTer(): void
    {
        $this->apiRequest(
            'GET',
            '/pokemons/to_choose',
            [
                'count' => '12',
                'dex_slug' => 'redgreenblueyellow',
                'election_slug' => 'affinee',
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
            ],
        );

        $this->assertJsonResponseIsOK();

        $this->assertResponseContent(1);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest(
            'GET',
            '/pokemons/to_choose',
            [
                'count' => '12',
                'dex_slug' => 'home',
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
            ],
            [
                'PHP_AUTH_USER' => self::AUTH_USER,
                'PHP_AUTH_PW' => self::AUTH_PASSWORD,
            ],
        );

        $this->assertJsonResponseIsOK();

        $this->assertResponseContent(12);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest(
            'GET',
            '/pokemons/to_choose',
            [
                'count' => '12',
                'dex_slug' => 'home',
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
            ],
            [
                'PHP_AUTH_USER' => self::AUTH_USER,
                'PHP_AUTH_PW' => 'treize',
            ],
        );

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }

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
            $this->assertArrayHasKey('slug', $item['pokemon']);
            $this->assertArrayHasKey('french_name', $item['pokemon']);
            $this->assertArrayHasKey('icon', $item['pokemon']);
            $this->assertArrayHasKey('national_dex_number', $item['pokemon']);
            $this->assertArrayHasKey('order_number', $item['pokemon']);
            $this->assertArrayHasKey('game_bundles', $item['pokemon']);
            $this->assertArrayHasKey('game_bundles_shiny', $item['pokemon']);
            $this->assertIsArray($item['pokemon']['game_bundles']);
            $this->assertIsArray($item['pokemon']['game_bundles_shiny']);
            $this->assertArrayHasKey('category_form', $item);
            $this->assertArrayHasKey('regional_form', $item);
            $this->assertArrayHasKey('special_form', $item);
            $this->assertArrayHasKey('variant_form', $item);
            $this->assertArrayHasKey('primary_type', $item);
            $this->assertArrayHasKey('secondary_type', $item);
        }
    }
}
```

---

### Task 7: Run quality checks

- [ ] **Step 1: Run all tests**

```bash
make tests
```

Expected: All unit and integration tests pass, 0 failures.

- [ ] **Step 2: Run code quality**

```bash
make quality
```

Expected: All quality checks pass (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint).

- [ ] **Step 3: Run coverage and mutation**

```bash
make measures
```

Expected: 100% code coverage and 100% MSI for all new code.

---

## Self-Review

**Spec coverage:**
- ✅ `ElectionPokemonResponse` DTO created — wraps pokemon + forms + types
- ✅ `ElectionPokemonsListResponse` DTO created — wraps type + items
- ✅ `ElectionPokemonResponseFactory` created — converts `ElectionPokemonsList` → response DTOs
- ✅ Unit tests for factory — all branches covered (nulls, casts, empty array, both list types)
- ✅ `PokemonsController` updated — uses factory before serializing
- ✅ Integration test updated — asserts new nested structure

**Placeholder scan:** No TBD, no "similar to task N", all code blocks complete.

**Type consistency:**
- `ElectionPokemonResponseFactory::fromElectionPokemonsList` → `ElectionPokemonsListResponse` ✅
- `ElectionPokemonResponseFactory::fromSqlRow` → `ElectionPokemonResponse` ✅
- `ElectionPokemonResponse::pokemon` → `PokemonDataResponse` ✅
- `ElectionPokemonResponse::categoryForm` etc. → `?FormResponse` ✅
- `ElectionPokemonResponse::primaryType` etc. → `?AlbumTypeResponse` ✅
