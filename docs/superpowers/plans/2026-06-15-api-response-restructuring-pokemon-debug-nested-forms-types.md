# API Response Restructuring (Pokemon Debug — Nested Forms & Types) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /debogage/pokemon/{slug}` response by grouping the 4 flat `*_form` fields and the 2 flat `*_type` fields of `PokemonDebugResponse` into nested `forms` (`PokemonDebugFormsResponse`) and `types` (`PokemonDebugTypesResponse`) objects, mirroring the pattern already applied to `AlbumPokemonResponse` (issue #256).

**Architecture:** Create two new immutable DTOs — `PokemonDebugFormsResponse` and `PokemonDebugTypesResponse` — that group the debug-specific form and type sub-objects (`FormDebugResponse`, `TypeDebugResponse`). Update `PokemonDebugResponse` to embed `?PokemonDebugFormsResponse $forms` and `PokemonDebugTypesResponse $types` instead of the 6 flat nullable properties. Update `PokemonDebugResponseFactory` to build those nested objects using private helpers `buildForms()` and `buildTypes()`. Update all unit tests and integration test assertions. No changes to the controller, service, or repository.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Response shape change

**Before:**
```json
{
  "identifier": null,
  "slug": "venusaur-mega",
  "name": "Mega Venusaur",
  "french_name": "Méga-Florizarre",
  "simplified_name": "Venusaur",
  "simplified_french_name": "Florizarre",
  "forms_label": "Mega",
  "forms_french_label": "Méga",
  "national_dex_number": 3,
  "family": "bulbasaur",
  "bankable": true,
  "bankableish": null,
  "icon_name": "venusaur-mega",
  "family_order": 3,
  "original_game_bundle": { "slug": "xy", "...": "..." },
  "variant_form": null,
  "regional_form": null,
  "special_form": { "identifier": null, "slug": "mega", "name": "Mega", "french_name": "Méga", "order_number": 2, "deleted_at": null },
  "category_form": null,
  "primary_type": { "identifier": null, "slug": "grass", "name": "Grass", "french_name": "Plante", "order_number": 3, "color": "#78C850", "deleted_at": null },
  "secondary_type": { "identifier": null, "slug": "poison", "name": "Poison", "french_name": "Poison", "order_number": 4, "color": "#A040A0", "deleted_at": null },
  "deleted_at": null
}
```

**After:**
```json
{
  "identifier": null,
  "slug": "venusaur-mega",
  "name": "Mega Venusaur",
  "french_name": "Méga-Florizarre",
  "simplified_name": "Venusaur",
  "simplified_french_name": "Florizarre",
  "forms_label": "Mega",
  "forms_french_label": "Méga",
  "national_dex_number": 3,
  "family": "bulbasaur",
  "bankable": true,
  "bankableish": null,
  "icon_name": "venusaur-mega",
  "family_order": 3,
  "original_game_bundle": { "slug": "xy", "...": "..." },
  "forms": {
    "category": null,
    "regional": null,
    "special": { "identifier": null, "slug": "mega", "name": "Mega", "french_name": "Méga", "order_number": 2, "deleted_at": null },
    "variant": null
  },
  "types": {
    "primary": { "identifier": null, "slug": "grass", "name": "Grass", "french_name": "Plante", "order_number": 3, "color": "#78C850", "deleted_at": null },
    "secondary": { "identifier": null, "slug": "poison", "name": "Poison", "french_name": "Poison", "order_number": 4, "color": "#A040A0", "deleted_at": null }
  },
  "deleted_at": null
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
- `src/DTO/Response/PokemonDebugFormsResponse.php` — groups 4 `?FormDebugResponse` sub-objects
- `src/DTO/Response/PokemonDebugTypesResponse.php` — groups 2 `?TypeDebugResponse` sub-objects
- `tests/src/Unit/DTO/Response/PokemonDebugFormsResponseTest.php` — unit test for new DTO
- `tests/src/Unit/DTO/Response/PokemonDebugTypesResponseTest.php` — unit test for new DTO

**Modify:**
- `src/DTO/Response/PokemonDebugResponse.php` — replace 6 flat form/type fields with `?PokemonDebugFormsResponse $forms` and `PokemonDebugTypesResponse $types`
- `src/Factory/PokemonDebugResponseFactory.php` — add private `buildForms()` and `buildTypes()` helpers; update `fromPokemon()` to use them
- `tests/src/Unit/DTO/Response/PokemonDebugResponseTest.php` — update constructor calls and assertions to new structure
- `tests/src/Unit/Factory/PokemonDebugResponseFactoryTest.php` — update assertions to access `forms.*` and `types.*` instead of flat fields
- `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php` — update string assertions for new JSON keys

---

## Tasks

### Task 1: Create `PokemonDebugFormsResponse` DTO

**Files:**
- Create: `src/DTO/Response/PokemonDebugFormsResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonDebugFormsResponse
{
    public function __construct(
        public readonly ?FormDebugResponse $category,
        public readonly ?FormDebugResponse $regional,
        public readonly ?FormDebugResponse $special,
        public readonly ?FormDebugResponse $variant,
    ) {}
}
```

Save as `src/DTO/Response/PokemonDebugFormsResponse.php`.

---

### Task 2: Create `PokemonDebugTypesResponse` DTO

**Files:**
- Create: `src/DTO/Response/PokemonDebugTypesResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonDebugTypesResponse
{
    public function __construct(
        public readonly ?TypeDebugResponse $primary,
        public readonly ?TypeDebugResponse $secondary,
    ) {}
}
```

Save as `src/DTO/Response/PokemonDebugTypesResponse.php`.

---

### Task 3: Write unit tests for the two new DTOs

**Files:**
- Create: `tests/src/Unit/DTO/Response/PokemonDebugFormsResponseTest.php`
- Create: `tests/src/Unit/DTO/Response/PokemonDebugTypesResponseTest.php`

- [ ] **Step 1: Create `PokemonDebugFormsResponseTest.php`**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\PokemonDebugFormsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDebugFormsResponse::class)]
final class PokemonDebugFormsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $category = new FormDebugResponse(null, 'starter', 'Starter', 'Starter', 1, null);
        $regional = new FormDebugResponse(null, 'alolan', 'Alolan', "d'Alola", 2, null);
        $special = new FormDebugResponse(null, 'mega', 'Mega', 'Méga', 3, null);
        $variant = new FormDebugResponse(null, 'gender', 'Gender', 'Genre', 4, null);

        $response = new PokemonDebugFormsResponse(
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
        $response = new PokemonDebugFormsResponse(
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

Save as `tests/src/Unit/DTO/Response/PokemonDebugFormsResponseTest.php`.

- [ ] **Step 2: Create `PokemonDebugTypesResponseTest.php`**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonDebugTypesResponse;
use App\DTO\Response\TypeDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDebugTypesResponse::class)]
final class PokemonDebugTypesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $primary = new TypeDebugResponse(null, 'grass', 'Grass', 'Plante', 3, '#78C850', null);
        $secondary = new TypeDebugResponse(null, 'poison', 'Poison', 'Poison', 4, '#A040A0', null);

        $response = new PokemonDebugTypesResponse(
            primary: $primary,
            secondary: $secondary,
        );

        self::assertSame($primary, $response->primary);
        self::assertSame($secondary, $response->secondary);
    }

    #[Test]
    public function constructorAcceptsNullProperties(): void
    {
        $response = new PokemonDebugTypesResponse(
            primary: null,
            secondary: null,
        );

        self::assertNull($response->primary);
        self::assertNull($response->secondary);
    }
}
```

Save as `tests/src/Unit/DTO/Response/PokemonDebugTypesResponseTest.php`.

---

### Task 4: Update `PokemonDebugResponse` to use nested objects

**Files:**
- Modify: `src/DTO/Response/PokemonDebugResponse.php`

- [ ] **Step 1: Replace the 6 flat form/type parameters with 2 nested objects**

Replace the full file content with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonDebugResponse
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly ?string $identifier,
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('simplified_name')]
        public readonly string $simplifiedName,
        #[SerializedName('simplified_french_name')]
        public readonly string $simplifiedFrenchName,
        #[SerializedName('forms_label')]
        public readonly string $formsLabel,
        #[SerializedName('forms_french_label')]
        public readonly string $formsFrenchLabel,
        #[SerializedName('national_dex_number')]
        public readonly int $nationalDexNumber,
        public readonly string $family,
        public readonly bool $bankable,
        public readonly ?bool $bankableish,
        #[SerializedName('icon_name')]
        public readonly string $iconName,
        #[SerializedName('family_order')]
        public readonly int $familyOrder,
        #[SerializedName('original_game_bundle')]
        public readonly GameBundleDebugResponse $originalGameBundle,
        public readonly ?PokemonDebugFormsResponse $forms,
        public readonly PokemonDebugTypesResponse $types,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
```

---

### Task 5: Update `PokemonDebugResponseTest`

**Files:**
- Modify: `tests/src/Unit/DTO/Response/PokemonDebugResponseTest.php`

- [ ] **Step 1: Replace the file with the updated tests**

The test must now pass `?PokemonDebugFormsResponse $forms` and `PokemonDebugTypesResponse $types` instead of the 6 flat form/type arguments.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use App\DTO\Response\PokemonDebugFormsResponse;
use App\DTO\Response\PokemonDebugResponse;
use App\DTO\Response\PokemonDebugTypesResponse;
use App\DTO\Response\TypeDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDebugResponse::class)]
final class PokemonDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $generation = new GameGenerationDebugResponse(null, '6', '6', null);
        $gameBundle = new GameBundleDebugResponse(null, 'xy', 'X/Y', 'X/Y', 6, $generation, null);
        $form = new FormDebugResponse(null, 'mega', 'Mega', 'Méga', 2, null);
        $type = new TypeDebugResponse(null, 'grass', 'Grass', 'Plante', 3, '#78C850', null);
        $forms = new PokemonDebugFormsResponse(category: null, regional: null, special: $form, variant: null);
        $types = new PokemonDebugTypesResponse(primary: $type, secondary: null);

        $response = new PokemonDebugResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440000',
            slug: 'venusaur-mega',
            name: 'Mega Venusaur',
            frenchName: 'Méga-Florizarre',
            simplifiedName: 'Venusaur',
            simplifiedFrenchName: 'Florizarre',
            formsLabel: 'Mega',
            formsFrenchLabel: 'Méga',
            nationalDexNumber: 3,
            family: 'bulbasaur',
            bankable: true,
            bankableish: false,
            iconName: 'venusaur-mega',
            familyOrder: 3,
            originalGameBundle: $gameBundle,
            forms: $forms,
            types: $types,
            deletedAt: '2024-03-01T00:00:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $response->identifier);
        self::assertSame('venusaur-mega', $response->slug);
        self::assertSame('Mega Venusaur', $response->name);
        self::assertSame('Méga-Florizarre', $response->frenchName);
        self::assertSame('Venusaur', $response->simplifiedName);
        self::assertSame('Florizarre', $response->simplifiedFrenchName);
        self::assertSame('Mega', $response->formsLabel);
        self::assertSame('Méga', $response->formsFrenchLabel);
        self::assertSame(3, $response->nationalDexNumber);
        self::assertSame('bulbasaur', $response->family);
        self::assertTrue($response->bankable);
        self::assertFalse($response->bankableish);
        self::assertSame('venusaur-mega', $response->iconName);
        self::assertSame(3, $response->familyOrder);
        self::assertSame($gameBundle, $response->originalGameBundle);
        self::assertSame($forms, $response->forms);
        self::assertSame($types, $response->types);
        self::assertSame('2024-03-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $generation = new GameGenerationDebugResponse(null, '1', '1', null);
        $gameBundle = new GameBundleDebugResponse(null, 'redgreenblueyellow', 'RBY', 'RBY', 1, $generation, null);
        $types = new PokemonDebugTypesResponse(primary: null, secondary: null);

        $response = new PokemonDebugResponse(
            identifier: null,
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            simplifiedName: 'Bulbasaur',
            simplifiedFrenchName: 'Bulbizarre',
            formsLabel: '',
            formsFrenchLabel: '',
            nationalDexNumber: 1,
            family: 'bulbasaur',
            bankable: true,
            bankableish: null,
            iconName: 'bulbasaur',
            familyOrder: 0,
            originalGameBundle: $gameBundle,
            forms: null,
            types: $types,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->bankableish);
        self::assertNull($response->forms);
        self::assertNull($response->deletedAt);
    }
}
```

---

### Task 6: Update `PokemonDebugResponseFactory`

**Files:**
- Modify: `src/Factory/PokemonDebugResponseFactory.php`

- [ ] **Step 1: Add imports and refactor `fromPokemon()` to use private helpers**

Replace the full file content with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use App\DTO\Response\PokemonDebugFormsResponse;
use App\DTO\Response\PokemonDebugResponse;
use App\DTO\Response\PokemonDebugTypesResponse;
use App\DTO\Response\TypeDebugResponse;
use App\Entity\CategoryForm;
use App\Entity\GameBundle;
use App\Entity\GameGeneration;
use App\Entity\Pokemon;
use App\Entity\RegionalForm;
use App\Entity\SpecialForm;
use App\Entity\Type;
use App\Entity\VariantForm;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
final class PokemonDebugResponseFactory
{
    public static function fromPokemon(Pokemon $pokemon): PokemonDebugResponse
    {
        return new PokemonDebugResponse(
            identifier: $pokemon->getIdentifier()?->toRfc4122(),
            slug: $pokemon->slug,
            name: $pokemon->name,
            frenchName: $pokemon->frenchName,
            simplifiedName: $pokemon->simplifiedName,
            simplifiedFrenchName: $pokemon->simplifiedFrenchName,
            formsLabel: $pokemon->formsLabel,
            formsFrenchLabel: $pokemon->formsFrenchLabel,
            nationalDexNumber: $pokemon->nationalDexNumber,
            family: $pokemon->family,
            bankable: $pokemon->bankable,
            bankableish: $pokemon->bankableish,
            iconName: $pokemon->iconName,
            familyOrder: $pokemon->familyOrder,
            originalGameBundle: self::buildGameBundle($pokemon->originalGameBundle),
            forms: self::buildForms($pokemon),
            types: self::buildTypes($pokemon),
            deletedAt: $pokemon->deletedAt?->format(DATE_ATOM),
        );
    }

    private static function buildForms(Pokemon $pokemon): ?PokemonDebugFormsResponse
    {
        if (
            null === $pokemon->variantForm
            && null === $pokemon->regionalForm
            && null === $pokemon->specialForm
            && null === $pokemon->categoryForm
        ) {
            return null;
        }

        return new PokemonDebugFormsResponse(
            category: null !== $pokemon->categoryForm ? self::buildForm($pokemon->categoryForm) : null,
            regional: null !== $pokemon->regionalForm ? self::buildForm($pokemon->regionalForm) : null,
            special: null !== $pokemon->specialForm ? self::buildForm($pokemon->specialForm) : null,
            variant: null !== $pokemon->variantForm ? self::buildForm($pokemon->variantForm) : null,
        );
    }

    private static function buildTypes(Pokemon $pokemon): PokemonDebugTypesResponse
    {
        return new PokemonDebugTypesResponse(
            primary: null !== $pokemon->primaryType ? self::buildType($pokemon->primaryType) : null,
            secondary: null !== $pokemon->secondaryType ? self::buildType($pokemon->secondaryType) : null,
        );
    }

    private static function buildGameBundle(GameBundle $gameBundle): GameBundleDebugResponse
    {
        return new GameBundleDebugResponse(
            identifier: $gameBundle->getIdentifier()?->toRfc4122(),
            slug: $gameBundle->slug,
            name: $gameBundle->name,
            frenchName: $gameBundle->frenchName,
            orderNumber: $gameBundle->orderNumber,
            generation: self::buildGeneration($gameBundle->generation),
            deletedAt: $gameBundle->deletedAt?->format(DATE_ATOM),
        );
    }

    private static function buildGeneration(GameGeneration $generation): GameGenerationDebugResponse
    {
        return new GameGenerationDebugResponse(
            identifier: $generation->getIdentifier()?->toRfc4122(),
            slug: $generation->slug,
            name: $generation->name,
            deletedAt: $generation->deletedAt?->format(DATE_ATOM),
        );
    }

    private static function buildForm(CategoryForm|RegionalForm|SpecialForm|VariantForm $form): FormDebugResponse
    {
        return new FormDebugResponse(
            identifier: $form->getIdentifier()?->toRfc4122(),
            slug: $form->slug,
            name: $form->name,
            frenchName: $form->frenchName,
            orderNumber: $form->orderNumber,
            deletedAt: $form->deletedAt?->format(DATE_ATOM),
        );
    }

    private static function buildType(Type $type): TypeDebugResponse
    {
        return new TypeDebugResponse(
            identifier: $type->getIdentifier()?->toRfc4122(),
            slug: $type->slug,
            name: $type->name,
            frenchName: $type->frenchName,
            orderNumber: $type->orderNumber,
            color: $type->color,
            deletedAt: $type->deletedAt?->format(DATE_ATOM),
        );
    }
}
```

---

### Task 7: Update `PokemonDebugResponseFactoryTest`

**Files:**
- Modify: `tests/src/Unit/Factory/PokemonDebugResponseFactoryTest.php`

- [ ] **Step 1: Replace the file with updated assertions**

All form/type assertions change from `$result->variantForm`, `$result->primaryType`, etc. to `$result->forms->variant`, `$result->types->primary`, etc.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\PokemonDebugFormsResponse;
use App\DTO\Response\PokemonDebugTypesResponse;
use App\DTO\Response\TypeDebugResponse;
use App\Entity\CategoryForm;
use App\Entity\GameBundle;
use App\Entity\GameGeneration;
use App\Entity\Pokemon;
use App\Entity\RegionalForm;
use App\Entity\SpecialForm;
use App\Entity\Type;
use App\Entity\VariantForm;
use App\Factory\PokemonDebugResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(PokemonDebugResponseFactory::class)]
final class PokemonDebugResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromPokemonMapsAllScalarFields(): void
    {
        $pokemon = $this->buildBasePokemon();

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNull($result->identifier);
        self::assertSame('bulbasaur', $result->slug);
        self::assertSame('Bulbasaur', $result->name);
        self::assertSame('Bulbizarre', $result->frenchName);
        self::assertSame('Bulbasaur', $result->simplifiedName);
        self::assertSame('Bulbizarre', $result->simplifiedFrenchName);
        self::assertSame('', $result->formsLabel);
        self::assertSame('', $result->formsFrenchLabel);
        self::assertSame(1, $result->nationalDexNumber);
        self::assertSame('bulbasaur', $result->family);
        self::assertTrue($result->bankable);
        self::assertNull($result->bankableish);
        self::assertSame('bulbasaur', $result->iconName);
        self::assertSame(0, $result->familyOrder);
        self::assertNull($result->deletedAt);
    }

    #[Test]
    public function fromPokemonWithNoFormsSetsFormsToNull(): void
    {
        $pokemon = $this->buildBasePokemon();

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNull($result->forms);
    }

    #[Test]
    public function fromPokemonAlwaysBuildsTypesObject(): void
    {
        $pokemon = $this->buildBasePokemon();

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(PokemonDebugTypesResponse::class, $result->types);
        self::assertNull($result->types->primary);
        self::assertNull($result->types->secondary);
    }

    #[Test]
    public function fromPokemonMapsGameBundleAndGeneration(): void
    {
        $pokemon = $this->buildBasePokemon();

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('redgreenblueyellow', $result->originalGameBundle->slug);
        self::assertSame('Red/Green/Blue/Yellow', $result->originalGameBundle->name);
        self::assertSame('Rouge/Vert/Bleu/Jaune', $result->originalGameBundle->frenchName);
        self::assertSame(1, $result->originalGameBundle->orderNumber);
        self::assertNull($result->originalGameBundle->identifier);
        self::assertNull($result->originalGameBundle->deletedAt);
        self::assertSame('1', $result->originalGameBundle->generation->slug);
        self::assertSame('1', $result->originalGameBundle->generation->name);
        self::assertNull($result->originalGameBundle->generation->identifier);
        self::assertNull($result->originalGameBundle->generation->deletedAt);
    }

    #[Test]
    public function fromPokemonWithVariantFormBuildsFormsObject(): void
    {
        $variantForm = new VariantForm();
        $variantForm->slug = 'gender';
        $variantForm->name = 'Gender';
        $variantForm->frenchName = 'Genre';
        $variantForm->orderNumber = 1;

        $pokemon = $this->buildBasePokemon();
        $pokemon->variantForm = $variantForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(PokemonDebugFormsResponse::class, $result->forms);
        self::assertInstanceOf(FormDebugResponse::class, $result->forms->variant);
        self::assertSame('gender', $result->forms->variant->slug);
        self::assertSame('Gender', $result->forms->variant->name);
        self::assertSame('Genre', $result->forms->variant->frenchName);
        self::assertSame(1, $result->forms->variant->orderNumber);
        self::assertNull($result->forms->variant->identifier);
        self::assertNull($result->forms->variant->deletedAt);
        self::assertNull($result->forms->category);
        self::assertNull($result->forms->regional);
        self::assertNull($result->forms->special);
    }

    #[Test]
    public function fromPokemonWithRegionalFormBuildsFormsObject(): void
    {
        $regionalForm = new RegionalForm();
        $regionalForm->slug = 'alolan';
        $regionalForm->name = 'Alolan';
        $regionalForm->frenchName = "d'Alola";
        $regionalForm->orderNumber = 2;

        $pokemon = $this->buildBasePokemon();
        $pokemon->regionalForm = $regionalForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(PokemonDebugFormsResponse::class, $result->forms);
        self::assertInstanceOf(FormDebugResponse::class, $result->forms->regional);
        self::assertSame('alolan', $result->forms->regional->slug);
        self::assertSame('Alolan', $result->forms->regional->name);
        self::assertSame("d'Alola", $result->forms->regional->frenchName);
        self::assertSame(2, $result->forms->regional->orderNumber);
        self::assertNull($result->forms->category);
        self::assertNull($result->forms->special);
        self::assertNull($result->forms->variant);
    }

    #[Test]
    public function fromPokemonWithSpecialFormBuildsFormsObject(): void
    {
        $specialForm = new SpecialForm();
        $specialForm->slug = 'mega';
        $specialForm->name = 'Mega';
        $specialForm->frenchName = 'Méga';
        $specialForm->orderNumber = 3;

        $pokemon = $this->buildBasePokemon();
        $pokemon->specialForm = $specialForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(PokemonDebugFormsResponse::class, $result->forms);
        self::assertInstanceOf(FormDebugResponse::class, $result->forms->special);
        self::assertSame('mega', $result->forms->special->slug);
        self::assertSame('Mega', $result->forms->special->name);
        self::assertSame('Méga', $result->forms->special->frenchName);
        self::assertSame(3, $result->forms->special->orderNumber);
        self::assertNull($result->forms->category);
        self::assertNull($result->forms->regional);
        self::assertNull($result->forms->variant);
    }

    #[Test]
    public function fromPokemonWithCategoryFormBuildsFormsObject(): void
    {
        $categoryForm = new CategoryForm();
        $categoryForm->slug = 'starter';
        $categoryForm->name = 'Starter';
        $categoryForm->frenchName = 'Starter';
        $categoryForm->orderNumber = 4;

        $pokemon = $this->buildBasePokemon();
        $pokemon->categoryForm = $categoryForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(PokemonDebugFormsResponse::class, $result->forms);
        self::assertInstanceOf(FormDebugResponse::class, $result->forms->category);
        self::assertSame('starter', $result->forms->category->slug);
        self::assertSame('Starter', $result->forms->category->name);
        self::assertSame('Starter', $result->forms->category->frenchName);
        self::assertSame(4, $result->forms->category->orderNumber);
        self::assertNull($result->forms->regional);
        self::assertNull($result->forms->special);
        self::assertNull($result->forms->variant);
    }

    #[Test]
    public function fromPokemonWithPrimaryTypeBuildsTypesObject(): void
    {
        $type = new Type();
        $type->slug = 'grass';
        $type->name = 'Grass';
        $type->frenchName = 'Plante';
        $type->orderNumber = 3;
        $type->color = '#78C850';

        $pokemon = $this->buildBasePokemon();
        $pokemon->primaryType = $type;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(TypeDebugResponse::class, $result->types->primary);
        self::assertSame('grass', $result->types->primary->slug);
        self::assertSame('Grass', $result->types->primary->name);
        self::assertSame('Plante', $result->types->primary->frenchName);
        self::assertSame(3, $result->types->primary->orderNumber);
        self::assertSame('#78C850', $result->types->primary->color);
        self::assertNull($result->types->primary->identifier);
        self::assertNull($result->types->primary->deletedAt);
        self::assertNull($result->types->secondary);
    }

    #[Test]
    public function fromPokemonWithSecondaryTypeBuildsTypesObject(): void
    {
        $type = new Type();
        $type->slug = 'poison';
        $type->name = 'Poison';
        $type->frenchName = 'Poison';
        $type->orderNumber = 4;
        $type->color = '#A040A0';

        $pokemon = $this->buildBasePokemon();
        $pokemon->secondaryType = $type;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(TypeDebugResponse::class, $result->types->secondary);
        self::assertSame('poison', $result->types->secondary->slug);
        self::assertSame('Poison', $result->types->secondary->name);
        self::assertSame('Poison', $result->types->secondary->frenchName);
        self::assertSame(4, $result->types->secondary->orderNumber);
        self::assertSame('#A040A0', $result->types->secondary->color);
        self::assertNull($result->types->primary);
    }

    #[Test]
    public function fromPokemonWithBankableishMapsBoolValue(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->bankableish = true;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertTrue($result->bankableish);
    }

    #[Test]
    public function fromPokemonWithDeletedAtFormatsAtomDate(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->deletedAt = new \DateTime('2024-03-15T12:00:00+00:00');

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('2024-03-15T12:00:00+00:00', $result->deletedAt);
    }

    #[Test]
    public function fromPokemonWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $pokemon = $this->buildBasePokemon();

        $reflection = new \ReflectionProperty(Pokemon::class, 'identifier');
        $reflection->setValue($pokemon, $uuid);

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $result->identifier);
    }

    #[Test]
    public function fromPokemonGameBundleWithDeletedAtFormatsAtomDate(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->originalGameBundle->deletedAt = new \DateTime('2024-04-20T08:00:00+00:00');

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('2024-04-20T08:00:00+00:00', $result->originalGameBundle->deletedAt);
    }

    #[Test]
    public function fromPokemonGameBundleWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');
        $pokemon = $this->buildBasePokemon();

        $reflection = new \ReflectionProperty(GameBundle::class, 'identifier');
        $reflection->setValue($pokemon->originalGameBundle, $uuid);

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('6ba7b810-9dad-11d1-80b4-00c04fd430c8', $result->originalGameBundle->identifier);
    }

    #[Test]
    public function fromPokemonGenerationWithDeletedAtFormatsAtomDate(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->originalGameBundle->generation->deletedAt = new \DateTime('2024-05-10T00:00:00+00:00');

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('2024-05-10T00:00:00+00:00', $result->originalGameBundle->generation->deletedAt);
    }

    #[Test]
    public function fromPokemonGenerationWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440099');
        $pokemon = $this->buildBasePokemon();

        $reflection = new \ReflectionProperty(GameGeneration::class, 'identifier');
        $reflection->setValue($pokemon->originalGameBundle->generation, $uuid);

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('550e8400-e29b-41d4-a716-446655440099', $result->originalGameBundle->generation->identifier);
    }

    #[Test]
    public function fromPokemonFormWithDeletedAtFormatsAtomDate(): void
    {
        $variantForm = new VariantForm();
        $variantForm->slug = 'gender';
        $variantForm->name = 'Gender';
        $variantForm->frenchName = 'Genre';
        $variantForm->orderNumber = 1;
        $variantForm->deletedAt = new \DateTime('2024-06-01T00:00:00+00:00');

        $pokemon = $this->buildBasePokemon();
        $pokemon->variantForm = $variantForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->forms);
        self::assertNotNull($result->forms->variant);
        self::assertSame('2024-06-01T00:00:00+00:00', $result->forms->variant->deletedAt);
    }

    #[Test]
    public function fromPokemonFormWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440011');

        $variantForm = new VariantForm();
        $variantForm->slug = 'gender';
        $variantForm->name = 'Gender';
        $variantForm->frenchName = 'Genre';
        $variantForm->orderNumber = 1;

        $reflection = new \ReflectionProperty(VariantForm::class, 'identifier');
        $reflection->setValue($variantForm, $uuid);

        $pokemon = $this->buildBasePokemon();
        $pokemon->variantForm = $variantForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->forms);
        self::assertNotNull($result->forms->variant);
        self::assertSame('550e8400-e29b-41d4-a716-446655440011', $result->forms->variant->identifier);
    }

    #[Test]
    public function fromPokemonTypeWithDeletedAtFormatsAtomDate(): void
    {
        $type = new Type();
        $type->slug = 'grass';
        $type->name = 'Grass';
        $type->frenchName = 'Plante';
        $type->orderNumber = 3;
        $type->color = '#78C850';
        $type->deletedAt = new \DateTime('2024-07-01T00:00:00+00:00');

        $pokemon = $this->buildBasePokemon();
        $pokemon->primaryType = $type;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->types->primary);
        self::assertSame('2024-07-01T00:00:00+00:00', $result->types->primary->deletedAt);
    }

    #[Test]
    public function fromPokemonTypeWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440022');

        $type = new Type();
        $type->slug = 'grass';
        $type->name = 'Grass';
        $type->frenchName = 'Plante';
        $type->orderNumber = 3;
        $type->color = '#78C850';

        $reflection = new \ReflectionProperty(Type::class, 'identifier');
        $reflection->setValue($type, $uuid);

        $pokemon = $this->buildBasePokemon();
        $pokemon->primaryType = $type;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->types->primary);
        self::assertSame('550e8400-e29b-41d4-a716-446655440022', $result->types->primary->identifier);
    }

    private function buildBaseGameBundle(): GameBundle
    {
        $generation = new GameGeneration();
        $generation->slug = '1';
        $generation->name = '1';

        $gameBundle = new GameBundle();
        $gameBundle->slug = 'redgreenblueyellow';
        $gameBundle->name = 'Red/Green/Blue/Yellow';
        $gameBundle->frenchName = 'Rouge/Vert/Bleu/Jaune';
        $gameBundle->orderNumber = 1;
        $gameBundle->generation = $generation;

        return $gameBundle;
    }

    private function buildBasePokemon(): Pokemon
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'bulbasaur';
        $pokemon->name = 'Bulbasaur';
        $pokemon->frenchName = 'Bulbizarre';
        $pokemon->simplifiedName = 'Bulbasaur';
        $pokemon->simplifiedFrenchName = 'Bulbizarre';
        $pokemon->formsLabel = '';
        $pokemon->formsFrenchLabel = '';
        $pokemon->nationalDexNumber = 1;
        $pokemon->family = 'bulbasaur';
        $pokemon->bankable = true;
        $pokemon->bankableish = null;
        $pokemon->iconName = 'bulbasaur';
        $pokemon->familyOrder = 0;
        $pokemon->originalGameBundle = $this->buildBaseGameBundle();
        $pokemon->variantForm = null;
        $pokemon->regionalForm = null;
        $pokemon->specialForm = null;
        $pokemon->categoryForm = null;
        $pokemon->primaryType = null;
        $pokemon->secondaryType = null;

        return $pokemon;
    }
}
```

---

### Task 8: Update integration test `DebugPokemonControllerTest`

**Files:**
- Modify: `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php`

- [ ] **Step 1: Update JSON string assertions to reflect new nested structure**

The `testPokemon` method currently checks for `"variant_form":null,`, `"regional_form":null,`, and `"category_form":null,`. After migration the response has `"forms":` with nested keys. Replace the `testPokemon` method:

```php
public function testPokemon(): void
{
    $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega');

    $this->assertResponseIsOK();

    $content = $this->getClientResponseContent();

    $this->assertStringNotContainsString('__', $content);

    $this->assertJson($content);

    $this->assertStringContainsString('"slug":"venusaur-mega",', $content);
    $this->assertStringContainsString('"slug":"6",', $content);
    $this->assertStringContainsString('"slug":"xy",', $content);
    $this->assertStringContainsString('"forms":{', $content);
    $this->assertStringContainsString('"variant":null', $content);
    $this->assertStringContainsString('"regional":null', $content);
    $this->assertStringContainsString('"category":null', $content);
    $this->assertStringContainsString('"slug":"mega",', $content);
    $this->assertStringContainsString('"types":{', $content);
    $this->assertStringContainsString('"slug":"grass",', $content);
    $this->assertStringContainsString('"slug":"poison",', $content);
}
```

The full file after the change:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Debug;

use App\Controller\Debug\DebugPokemonController;
use App\Factory\PokemonDebugResponseFactory;
use App\Service\DexAvailabilitiesService;
use App\Tests\Integration\Controller\AbstractTestControllerApi;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DebugPokemonController::class)]
#[CoversClass(DexAvailabilitiesService::class)]
#[CoversClass(PokemonDebugResponseFactory::class)]
final class DebugPokemonControllerTest extends AbstractTestControllerApi
{
    public function testPokemon(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        $this->assertStringContainsString('"slug":"venusaur-mega",', $content);
        $this->assertStringContainsString('"slug":"6",', $content);
        $this->assertStringContainsString('"slug":"xy",', $content);
        $this->assertStringContainsString('"forms":{', $content);
        $this->assertStringContainsString('"variant":null', $content);
        $this->assertStringContainsString('"regional":null', $content);
        $this->assertStringContainsString('"category":null', $content);
        $this->assertStringContainsString('"slug":"mega",', $content);
        $this->assertStringContainsString('"types":{', $content);
        $this->assertStringContainsString('"slug":"grass",', $content);
        $this->assertStringContainsString('"slug":"poison",', $content);
    }

    public function testPokemonNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega-x');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testPokemonCleanCaches(): void
    {
        $this->apiRequest('DELETE', '/debogage/pokemon/venusaur-mega/caches');

        $this->assertResponseIsOK();

        $this->assertEmpty($this->getClientResponseContent());
    }

    public function testPokemonCleanCachesNotFound(): void
    {
        $this->apiRequest('DELETE', '/debogage/pokemon/venusaur-mega-x/caches');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testPokemonAvailabilities(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega/availabilities');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var ?bool[][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('gamesAvailabilities', $data);
        $this->assertArrayNotHasKey('blue', $data['gamesAvailabilities']);
        $this->assertArrayNotHasKey('gold', $data['gamesAvailabilities']);
        $this->assertArrayHasKey('x', $data['gamesAvailabilities']);

        $this->assertArrayHasKey('gamesShiniesAvailabilities', $data);
        $this->assertArrayNotHasKey('blue', $data['gamesShiniesAvailabilities']);
        $this->assertArrayNotHasKey('gold', $data['gamesShiniesAvailabilities']);
        $this->assertArrayHasKey('x', $data['gamesShiniesAvailabilities']);

        $this->assertArrayHasKey('gameBundlesAvailabilities', $data);
        $this->assertArrayHasKey('goldsilvercrystal', $data['gameBundlesAvailabilities']);

        $this->assertArrayHasKey('gameBundlesShiniesAvailabilities', $data);
        $this->assertArrayHasKey('goldsilvercrystal', $data['gameBundlesShiniesAvailabilities']);
    }

    public function testPokemonAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega-x/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
```
