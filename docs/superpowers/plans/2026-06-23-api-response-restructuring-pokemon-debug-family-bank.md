# API Response Restructuring (Pokemon Debug — Family & Bank) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /debogage/pokemon/{slug}` response by turning the flat `family` string field into a `PokemonDebugFamilyResponse` object (with `slug`), and grouping `bankable` + `bankableish` under a new `bank: PokemonDebugBankResponse` object.

**Architecture:** Create two new immutable DTOs — `PokemonDebugFamilyResponse` and `PokemonDebugBankResponse`. Update `PokemonDebugResponse` to embed `PokemonDebugFamilyResponse $family` and `PokemonDebugBankResponse $bank` instead of the 3 flat primitive fields. Update `PokemonDebugResponseFactory::fromPokemon()` to build those objects. Update unit tests and integration test assertions. No changes to the controller.

**Current state:** `PokemonDebugResponse` already has `?PokemonDebugFormsResponse $forms` and `PokemonDebugTypesResponse $types` (nested forms/types plan already implemented). This plan adds the `family` and `bank` nesting on top.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Response shape change

**Before:**
```json
{
  "slug": "venusaur-mega",
  "national_dex_number": 3,
  "family": "bulbasaur",
  "bankable": true,
  "bankableish": null,
  "family_order": 3,
  "original_game_bundle": { "slug": "xy" },
  "forms": { "category": null, "regional": null, "special": { "slug": "mega" }, "variant": null },
  "types": { "primary": { "slug": "grass" }, "secondary": { "slug": "poison" } },
  "deleted_at": null
}
```

**After:**
```json
{
  "slug": "venusaur-mega",
  "national_dex_number": 3,
  "family": { "slug": "bulbasaur" },
  "bank": { "bankable": true, "bankableish": null },
  "family_order": 3,
  "original_game_bundle": { "slug": "xy" },
  "forms": { "category": null, "regional": null, "special": { "slug": "mega" }, "variant": null },
  "types": { "primary": { "slug": "grass" }, "secondary": { "slug": "poison" } },
  "deleted_at": null
}
```

---

## File Structure

**Create:**
- `src/DTO/Response/PokemonDebugFamilyResponse.php` — object with `slug: string`
- `src/DTO/Response/PokemonDebugBankResponse.php` — object with `bankable: bool` and `bankableish: ?bool`
- `tests/src/Unit/DTO/Response/PokemonDebugFamilyResponseTest.php`
- `tests/src/Unit/DTO/Response/PokemonDebugBankResponseTest.php`

**Modify:**
- `src/DTO/Response/PokemonDebugResponse.php` — replace 3 flat primitives (`family: string`, `bankable: bool`, `bankableish: ?bool`) with `family: PokemonDebugFamilyResponse` and `bank: PokemonDebugBankResponse`
- `src/Factory/PokemonDebugResponseFactory.php` — update `fromPokemon()` to build the two nested objects
- `tests/src/Unit/DTO/Response/PokemonDebugResponseTest.php` — update constructor calls and assertions
- `tests/src/Unit/Factory/PokemonDebugResponseFactoryTest.php` — update assertions to access `family->slug`, `bank->bankable`, `bank->bankableish`
- `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php` — update `testPokemon()` to assert new nested structure

---

## Tasks

### Task 1: Create `PokemonDebugFamilyResponse` DTO + unit test

**Files:**
- Create: `src/DTO/Response/PokemonDebugFamilyResponse.php`
- Create: `tests/src/Unit/DTO/Response/PokemonDebugFamilyResponseTest.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonDebugFamilyResponse
{
    public function __construct(
        public readonly string $slug,
    ) {}
}
```

Save as `src/DTO/Response/PokemonDebugFamilyResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonDebugFamilyResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDebugFamilyResponse::class)]
final class PokemonDebugFamilyResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new PokemonDebugFamilyResponse(slug: 'bulbasaur');

        self::assertSame('bulbasaur', $response->slug);
    }
}
```

Save as `tests/src/Unit/DTO/Response/PokemonDebugFamilyResponseTest.php`.

---

### Task 2: Create `PokemonDebugBankResponse` DTO + unit test

**Files:**
- Create: `src/DTO/Response/PokemonDebugBankResponse.php`
- Create: `tests/src/Unit/DTO/Response/PokemonDebugBankResponseTest.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonDebugBankResponse
{
    public function __construct(
        public readonly bool $bankable,
        public readonly ?bool $bankableish,
    ) {}
}
```

Save as `src/DTO/Response/PokemonDebugBankResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonDebugBankResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDebugBankResponse::class)]
final class PokemonDebugBankResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new PokemonDebugBankResponse(bankable: true, bankableish: false);

        self::assertTrue($response->bankable);
        self::assertFalse($response->bankableish);
    }

    #[Test]
    public function constructorAcceptsBankableishAsNull(): void
    {
        $response = new PokemonDebugBankResponse(bankable: false, bankableish: null);

        self::assertFalse($response->bankable);
        self::assertNull($response->bankableish);
    }
}
```

Save as `tests/src/Unit/DTO/Response/PokemonDebugBankResponseTest.php`.

---

### Task 3: Update `PokemonDebugResponse` + unit test

**Files:**
- Modify: `src/DTO/Response/PokemonDebugResponse.php`
- Modify: `tests/src/Unit/DTO/Response/PokemonDebugResponseTest.php`

- [ ] **Step 1: Replace flat primitives with nested objects in the DTO**

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
        public readonly PokemonDebugFamilyResponse $family,
        public readonly PokemonDebugBankResponse $bank,
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

- [ ] **Step 2: Update the unit test**

Replace the full file content with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use App\DTO\Response\PokemonDebugBankResponse;
use App\DTO\Response\PokemonDebugFamilyResponse;
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
        $family = new PokemonDebugFamilyResponse(slug: 'bulbasaur');
        $bank = new PokemonDebugBankResponse(bankable: true, bankableish: false);

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
            family: $family,
            bank: $bank,
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
        self::assertSame($family, $response->family);
        self::assertSame($bank, $response->bank);
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
        $family = new PokemonDebugFamilyResponse(slug: 'bulbasaur');
        $bank = new PokemonDebugBankResponse(bankable: true, bankableish: null);

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
            family: $family,
            bank: $bank,
            iconName: 'bulbasaur',
            familyOrder: 0,
            originalGameBundle: $gameBundle,
            forms: null,
            types: $types,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->bank->bankableish);
        self::assertNull($response->forms);
        self::assertNull($response->deletedAt);
    }
}
```

---

### Task 4: Update `PokemonDebugResponseFactory` + unit test

**Files:**
- Modify: `src/Factory/PokemonDebugResponseFactory.php`
- Modify: `tests/src/Unit/Factory/PokemonDebugResponseFactoryTest.php`

- [ ] **Step 1: Update the factory**

Replace the full file content with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use App\DTO\Response\PokemonDebugBankResponse;
use App\DTO\Response\PokemonDebugFamilyResponse;
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
            family: new PokemonDebugFamilyResponse(slug: $pokemon->family),
            bank: new PokemonDebugBankResponse(bankable: $pokemon->bankable, bankableish: $pokemon->bankableish),
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

- [ ] **Step 2: Update the factory unit test**

Replace the full file content with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\PokemonDebugBankResponse;
use App\DTO\Response\PokemonDebugFamilyResponse;
use App\DTO\Response\PokemonDebugFormsResponse;
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
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
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
        self::assertSame('bulbasaur', $result->iconName);
        self::assertSame(0, $result->familyOrder);
        self::assertNull($result->deletedAt);
    }

    #[Test]
    public function fromPokemonBuildsFamilyObject(): void
    {
        $pokemon = $this->buildBasePokemon();

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(PokemonDebugFamilyResponse::class, $result->family);
        self::assertSame('bulbasaur', $result->family->slug);
    }

    #[Test]
    public function fromPokemonBuildsBankObject(): void
    {
        $pokemon = $this->buildBasePokemon();

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(PokemonDebugBankResponse::class, $result->bank);
        self::assertTrue($result->bank->bankable);
        self::assertNull($result->bank->bankableish);
    }

    #[Test]
    public function fromPokemonWithBankableishMapsBoolValue(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->bankableish = true;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertTrue($result->bank->bankableish);
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
    public function fromPokemonWithAllFormsBuildsFormsObjectWithAllSlotsFilled(): void
    {
        $variantForm = new VariantForm();
        $variantForm->slug = 'gender';
        $variantForm->name = 'Gender';
        $variantForm->frenchName = 'Genre';
        $variantForm->orderNumber = 1;

        $regionalForm = new RegionalForm();
        $regionalForm->slug = 'alolan';
        $regionalForm->name = 'Alolan';
        $regionalForm->frenchName = "d'Alola";
        $regionalForm->orderNumber = 2;

        $specialForm = new SpecialForm();
        $specialForm->slug = 'mega';
        $specialForm->name = 'Mega';
        $specialForm->frenchName = 'Méga';
        $specialForm->orderNumber = 3;

        $categoryForm = new CategoryForm();
        $categoryForm->slug = 'starter';
        $categoryForm->name = 'Starter';
        $categoryForm->frenchName = 'Starter';
        $categoryForm->orderNumber = 4;

        $pokemon = $this->buildBasePokemon();
        $pokemon->variantForm = $variantForm;
        $pokemon->regionalForm = $regionalForm;
        $pokemon->specialForm = $specialForm;
        $pokemon->categoryForm = $categoryForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(PokemonDebugFormsResponse::class, $result->forms);
        self::assertInstanceOf(FormDebugResponse::class, $result->forms->category);
        self::assertInstanceOf(FormDebugResponse::class, $result->forms->regional);
        self::assertInstanceOf(FormDebugResponse::class, $result->forms->special);
        self::assertInstanceOf(FormDebugResponse::class, $result->forms->variant);
        self::assertSame('starter', $result->forms->category->slug);
        self::assertSame('alolan', $result->forms->regional->slug);
        self::assertSame('mega', $result->forms->special->slug);
        self::assertSame('gender', $result->forms->variant->slug);
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

### Task 5: Update integration test `DebugPokemonControllerTest`

**Files:**
- Modify: `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php`

- [ ] **Step 1: Update `testPokemon()` to assert new nested structure**

Update only the `testPokemon()` method — add assertions for `family` as an object and `bank`, remove any flat `bankable`/`bankableish` or `family` string assertions if present. The full updated file:

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
        $this->assertStringContainsString('"family":{"slug":', $content);
        $this->assertStringContainsString('"bank":{"bankable":', $content);
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

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);

        $this->assertArrayHasKey('games_availabilities', $data);
        $gamesSlugs = $this->getGameSlugs($data['games_availabilities']);
        $this->assertNotContains('blue', $gamesSlugs);
        $this->assertNotContains('gold', $gamesSlugs);
        $this->assertContains('x', $gamesSlugs);

        $this->assertArrayHasKey('games_shinies_availabilities', $data);
        $gamesShiniesSlugs = $this->getGameSlugs($data['games_shinies_availabilities']);
        $this->assertNotContains('blue', $gamesShiniesSlugs);
        $this->assertNotContains('gold', $gamesShiniesSlugs);
        $this->assertContains('x', $gamesShiniesSlugs);

        $this->assertArrayHasKey('game_bundles_availabilities', $data);
        $gameBundlesSlugs = $this->getGameBundleSlugs($data['game_bundles_availabilities']);
        $this->assertContains('goldsilvercrystal', $gameBundlesSlugs);

        $this->assertArrayHasKey('game_bundles_shinies_availabilities', $data);
        $gameBundlesShiniesSlugs = $this->getGameBundleSlugs($data['game_bundles_shinies_availabilities']);
        $this->assertContains('goldsilvercrystal', $gameBundlesShiniesSlugs);

        /** @var mixed $availabilities */
        foreach ($data as $availabilities) {
            $this->assertIsArray($availabilities);

            /** @var mixed $availability */
            foreach ($availabilities as $availability) {
                $this->assertIsArray($availability);
                $this->assertArrayHasKey('is_available', $availability);
                $this->assertIsBool($availability['is_available']);
            }
        }
    }

    public function testPokemonAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega-x/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @return string[]
     */
    private function getGameSlugs(mixed $availabilities): array
    {
        $this->assertIsArray($availabilities);

        $slugs = [];

        /** @var mixed $availability */
        foreach ($availabilities as $availability) {
            $this->assertIsArray($availability);
            $this->assertArrayHasKey('game', $availability);
            $this->assertIsArray($availability['game']);
            $this->assertArrayHasKey('slug', $availability['game']);
            $this->assertIsString($availability['game']['slug']);
            $slugs[] = $availability['game']['slug'];
        }

        return $slugs;
    }

    /**
     * @return string[]
     */
    private function getGameBundleSlugs(mixed $availabilities): array
    {
        $this->assertIsArray($availabilities);

        $slugs = [];

        /** @var mixed $availability */
        foreach ($availabilities as $availability) {
            $this->assertIsArray($availability);
            $this->assertArrayHasKey('game_bundle', $availability);
            $this->assertIsArray($availability['game_bundle']);
            $this->assertArrayHasKey('slug', $availability['game_bundle']);
            $this->assertIsString($availability['game_bundle']['slug']);
            $slugs[] = $availability['game_bundle']['slug'];
        }

        return $slugs;
    }
}
```
