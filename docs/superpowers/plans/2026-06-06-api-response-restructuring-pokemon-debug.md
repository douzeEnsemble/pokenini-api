# API Response Restructuring (Pokemon Debug) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /debogage/pokemon/{slug}` from direct Doctrine entity serialization to the project-standard DTO + Factory + Serializer pattern.

**Architecture:** Create 5 immutable response DTOs (FormDebugResponse, TypeDebugResponse, GameGenerationDebugResponse, GameBundleDebugResponse, PokemonDebugResponse), one Factory transforming a `Pokemon` entity into those nested DTOs, and update `DebugPokemonController::pokemon()` to use the factory. Since `AbstractDebugController::serialize()` is no longer called by anyone after this migration, delete `AbstractDebugController` and make `DebugPokemonController` extend `AbstractController` directly.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine ORM entities, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/FormDebugResponse.php` — shared DTO for all 4 form entity types
- `src/DTO/Response/TypeDebugResponse.php` — DTO for primary/secondary Type entities
- `src/DTO/Response/GameGenerationDebugResponse.php` — DTO for GameGeneration entity
- `src/DTO/Response/GameBundleDebugResponse.php` — DTO for GameBundle entity (includes generation)
- `src/DTO/Response/PokemonDebugResponse.php` — root DTO for the full debug view
- `src/Factory/PokemonDebugResponseFactory.php` — transforms Pokemon entity → nested DTOs
- `tests/src/Unit/DTO/Response/FormDebugResponseTest.php`
- `tests/src/Unit/DTO/Response/TypeDebugResponseTest.php`
- `tests/src/Unit/DTO/Response/GameGenerationDebugResponseTest.php`
- `tests/src/Unit/DTO/Response/GameBundleDebugResponseTest.php`
- `tests/src/Unit/DTO/Response/PokemonDebugResponseTest.php`
- `tests/src/Unit/Factory/PokemonDebugResponseFactoryTest.php`

**Modify:**
- `src/Controller/Debug/DebugPokemonController.php` — use Factory + Serializer; extend `AbstractController` instead of `AbstractDebugController`
- `tests/src/Unit/Controller/Debug/DebugPokemonControllerTest.php` — remove Serializer constructor arg; remove incorrect `#[CoversClass]` on `PokedexService`
- `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php` — update 3 assertions from camelCase to snake_case; add `#[CoversClass(PokemonDebugResponseFactory::class)]`; remove incorrect `#[CoversClass(DexAvailabilitiesService::class)]`

**Delete:**
- `src/Controller/Debug/AbstractDebugController.php` — no longer referenced after migration

---

## Tasks

### Task 1: Create `FormDebugResponse` DTO

**Files:**
- Create: `src/DTO/Response/FormDebugResponse.php`
- Create: `tests/src/Unit/DTO/Response/FormDebugResponseTest.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class FormDebugResponse
{
    public function __construct(
        public readonly ?string $identifier,
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('order_number')]
        public readonly int $orderNumber,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
```

Save as `src/DTO/Response/FormDebugResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FormDebugResponse::class)]
final class FormDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new FormDebugResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440000',
            slug: 'mega',
            name: 'Mega',
            frenchName: 'Méga',
            orderNumber: 2,
            deletedAt: '2024-03-01T00:00:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $response->identifier);
        self::assertSame('mega', $response->slug);
        self::assertSame('Mega', $response->name);
        self::assertSame('Méga', $response->frenchName);
        self::assertSame(2, $response->orderNumber);
        self::assertSame('2024-03-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new FormDebugResponse(
            identifier: null,
            slug: 'totem',
            name: 'Totem',
            frenchName: 'Totem',
            orderNumber: 5,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->deletedAt);
    }
}
```

Save as `tests/src/Unit/DTO/Response/FormDebugResponseTest.php`.

---

### Task 2: Create `TypeDebugResponse` DTO

**Files:**
- Create: `src/DTO/Response/TypeDebugResponse.php`
- Create: `tests/src/Unit/DTO/Response/TypeDebugResponseTest.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TypeDebugResponse
{
    public function __construct(
        public readonly ?string $identifier,
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('order_number')]
        public readonly int $orderNumber,
        public readonly string $color,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
```

Save as `src/DTO/Response/TypeDebugResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TypeDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TypeDebugResponse::class)]
final class TypeDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new TypeDebugResponse(
            identifier: '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            slug: 'grass',
            name: 'Grass',
            frenchName: 'Plante',
            orderNumber: 3,
            color: '#78C850',
            deletedAt: '2024-04-01T00:00:00+00:00',
        );

        self::assertSame('6ba7b810-9dad-11d1-80b4-00c04fd430c8', $response->identifier);
        self::assertSame('grass', $response->slug);
        self::assertSame('Grass', $response->name);
        self::assertSame('Plante', $response->frenchName);
        self::assertSame(3, $response->orderNumber);
        self::assertSame('#78C850', $response->color);
        self::assertSame('2024-04-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new TypeDebugResponse(
            identifier: null,
            slug: 'poison',
            name: 'Poison',
            frenchName: 'Poison',
            orderNumber: 4,
            color: '#A040A0',
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->deletedAt);
    }
}
```

Save as `tests/src/Unit/DTO/Response/TypeDebugResponseTest.php`.

---

### Task 3: Create `GameGenerationDebugResponse` DTO

**Files:**
- Create: `src/DTO/Response/GameGenerationDebugResponse.php`
- Create: `tests/src/Unit/DTO/Response/GameGenerationDebugResponseTest.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameGenerationDebugResponse
{
    public function __construct(
        public readonly ?string $identifier,
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
```

Save as `src/DTO/Response/GameGenerationDebugResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameGenerationDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameGenerationDebugResponse::class)]
final class GameGenerationDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new GameGenerationDebugResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440001',
            slug: '6',
            name: '6',
            deletedAt: '2024-05-01T00:00:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440001', $response->identifier);
        self::assertSame('6', $response->slug);
        self::assertSame('6', $response->name);
        self::assertSame('2024-05-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new GameGenerationDebugResponse(
            identifier: null,
            slug: '1',
            name: '1',
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->deletedAt);
    }
}
```

Save as `tests/src/Unit/DTO/Response/GameGenerationDebugResponseTest.php`.

---

### Task 4: Create `GameBundleDebugResponse` DTO

**Files:**
- Create: `src/DTO/Response/GameBundleDebugResponse.php`
- Create: `tests/src/Unit/DTO/Response/GameBundleDebugResponseTest.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameBundleDebugResponse
{
    public function __construct(
        public readonly ?string $identifier,
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('order_number')]
        public readonly int $orderNumber,
        public readonly GameGenerationDebugResponse $generation,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
```

Save as `src/DTO/Response/GameBundleDebugResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleDebugResponse::class)]
final class GameBundleDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $generation = new GameGenerationDebugResponse(
            identifier: null,
            slug: '6',
            name: '6',
            deletedAt: null,
        );

        $response = new GameBundleDebugResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440002',
            slug: 'xy',
            name: 'X/Y',
            frenchName: 'X/Y',
            orderNumber: 6,
            generation: $generation,
            deletedAt: '2024-06-01T00:00:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440002', $response->identifier);
        self::assertSame('xy', $response->slug);
        self::assertSame('X/Y', $response->name);
        self::assertSame('X/Y', $response->frenchName);
        self::assertSame(6, $response->orderNumber);
        self::assertSame($generation, $response->generation);
        self::assertSame('2024-06-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $generation = new GameGenerationDebugResponse(
            identifier: null,
            slug: '1',
            name: '1',
            deletedAt: null,
        );

        $response = new GameBundleDebugResponse(
            identifier: null,
            slug: 'redgreenblueyellow',
            name: 'Red/Green/Blue/Yellow',
            frenchName: 'Rouge/Vert/Bleu/Jaune',
            orderNumber: 1,
            generation: $generation,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->deletedAt);
    }
}
```

Save as `tests/src/Unit/DTO/Response/GameBundleDebugResponseTest.php`.

---

### Task 5: Create `PokemonDebugResponse` DTO

**Files:**
- Create: `src/DTO/Response/PokemonDebugResponse.php`
- Create: `tests/src/Unit/DTO/Response/PokemonDebugResponseTest.php`

- [ ] **Step 1: Create the DTO**

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
        #[SerializedName('variant_form')]
        public readonly ?FormDebugResponse $variantForm,
        #[SerializedName('regional_form')]
        public readonly ?FormDebugResponse $regionalForm,
        #[SerializedName('special_form')]
        public readonly ?FormDebugResponse $specialForm,
        #[SerializedName('category_form')]
        public readonly ?FormDebugResponse $categoryForm,
        #[SerializedName('primary_type')]
        public readonly ?TypeDebugResponse $primaryType,
        #[SerializedName('secondary_type')]
        public readonly ?TypeDebugResponse $secondaryType,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
```

Save as `src/DTO/Response/PokemonDebugResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use App\DTO\Response\PokemonDebugResponse;
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
            variantForm: null,
            regionalForm: null,
            specialForm: $form,
            categoryForm: null,
            primaryType: $type,
            secondaryType: null,
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
        self::assertNull($response->variantForm);
        self::assertNull($response->regionalForm);
        self::assertSame($form, $response->specialForm);
        self::assertNull($response->categoryForm);
        self::assertSame($type, $response->primaryType);
        self::assertNull($response->secondaryType);
        self::assertSame('2024-03-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $generation = new GameGenerationDebugResponse(null, '1', '1', null);
        $gameBundle = new GameBundleDebugResponse(null, 'redgreenblueyellow', 'RBY', 'RBY', 1, $generation, null);

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
            variantForm: null,
            regionalForm: null,
            specialForm: null,
            categoryForm: null,
            primaryType: null,
            secondaryType: null,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->bankableish);
        self::assertNull($response->variantForm);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertNull($response->categoryForm);
        self::assertNull($response->primaryType);
        self::assertNull($response->secondaryType);
        self::assertNull($response->deletedAt);
    }
}
```

Save as `tests/src/Unit/DTO/Response/PokemonDebugResponseTest.php`.

---

### Task 6: Create `PokemonDebugResponseFactory`

**Files:**
- Create: `src/Factory/PokemonDebugResponseFactory.php`
- Create: `tests/src/Unit/Factory/PokemonDebugResponseFactoryTest.php`

- [ ] **Step 1: Create the Factory**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use App\DTO\Response\PokemonDebugResponse;
use App\DTO\Response\TypeDebugResponse;
use App\Entity\CategoryForm;
use App\Entity\GameBundle;
use App\Entity\GameGeneration;
use App\Entity\Pokemon;
use App\Entity\RegionalForm;
use App\Entity\SpecialForm;
use App\Entity\Type;
use App\Entity\VariantForm;

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
            variantForm: null !== $pokemon->variantForm ? self::buildForm($pokemon->variantForm) : null,
            regionalForm: null !== $pokemon->regionalForm ? self::buildForm($pokemon->regionalForm) : null,
            specialForm: null !== $pokemon->specialForm ? self::buildForm($pokemon->specialForm) : null,
            categoryForm: null !== $pokemon->categoryForm ? self::buildForm($pokemon->categoryForm) : null,
            primaryType: null !== $pokemon->primaryType ? self::buildType($pokemon->primaryType) : null,
            secondaryType: null !== $pokemon->secondaryType ? self::buildType($pokemon->secondaryType) : null,
            deletedAt: $pokemon->deletedAt?->format(\DateTime::ATOM),
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
            deletedAt: $gameBundle->deletedAt?->format(\DateTime::ATOM),
        );
    }

    private static function buildGeneration(GameGeneration $generation): GameGenerationDebugResponse
    {
        return new GameGenerationDebugResponse(
            identifier: $generation->getIdentifier()?->toRfc4122(),
            slug: $generation->slug,
            name: $generation->name,
            deletedAt: $generation->deletedAt?->format(\DateTime::ATOM),
        );
    }

    private static function buildForm(VariantForm|RegionalForm|SpecialForm|CategoryForm $form): FormDebugResponse
    {
        return new FormDebugResponse(
            identifier: $form->getIdentifier()?->toRfc4122(),
            slug: $form->slug,
            name: $form->name,
            frenchName: $form->frenchName,
            orderNumber: $form->orderNumber,
            deletedAt: $form->deletedAt?->format(\DateTime::ATOM),
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
            deletedAt: $type->deletedAt?->format(\DateTime::ATOM),
        );
    }
}
```

Save as `src/Factory/PokemonDebugResponseFactory.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\PokemonDebugResponse;
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

    #[Test]
    public function fromPokemon_mapsAllScalarFields(): void
    {
        $pokemon = $this->buildBasePokemon();

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(PokemonDebugResponse::class, $result);
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
        self::assertNull($result->variantForm);
        self::assertNull($result->regionalForm);
        self::assertNull($result->specialForm);
        self::assertNull($result->categoryForm);
        self::assertNull($result->primaryType);
        self::assertNull($result->secondaryType);
        self::assertNull($result->deletedAt);
    }

    #[Test]
    public function fromPokemon_mapsGameBundleAndGeneration(): void
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
    public function fromPokemon_withVariantForm_mapsFormFields(): void
    {
        $variantForm = new VariantForm();
        $variantForm->slug = 'gender';
        $variantForm->name = 'Gender';
        $variantForm->frenchName = 'Genre';
        $variantForm->orderNumber = 1;

        $pokemon = $this->buildBasePokemon();
        $pokemon->variantForm = $variantForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(FormDebugResponse::class, $result->variantForm);
        self::assertSame('gender', $result->variantForm->slug);
        self::assertSame('Gender', $result->variantForm->name);
        self::assertSame('Genre', $result->variantForm->frenchName);
        self::assertSame(1, $result->variantForm->orderNumber);
        self::assertNull($result->variantForm->identifier);
        self::assertNull($result->variantForm->deletedAt);
    }

    #[Test]
    public function fromPokemon_withRegionalForm_mapsFormFields(): void
    {
        $regionalForm = new RegionalForm();
        $regionalForm->slug = 'alolan';
        $regionalForm->name = 'Alolan';
        $regionalForm->frenchName = "d'Alola";
        $regionalForm->orderNumber = 2;

        $pokemon = $this->buildBasePokemon();
        $pokemon->regionalForm = $regionalForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(FormDebugResponse::class, $result->regionalForm);
        self::assertSame('alolan', $result->regionalForm->slug);
    }

    #[Test]
    public function fromPokemon_withSpecialForm_mapsFormFields(): void
    {
        $specialForm = new SpecialForm();
        $specialForm->slug = 'mega';
        $specialForm->name = 'Mega';
        $specialForm->frenchName = 'Méga';
        $specialForm->orderNumber = 3;

        $pokemon = $this->buildBasePokemon();
        $pokemon->specialForm = $specialForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(FormDebugResponse::class, $result->specialForm);
        self::assertSame('mega', $result->specialForm->slug);
    }

    #[Test]
    public function fromPokemon_withCategoryForm_mapsFormFields(): void
    {
        $categoryForm = new CategoryForm();
        $categoryForm->slug = 'starter';
        $categoryForm->name = 'Starter';
        $categoryForm->frenchName = 'Starter';
        $categoryForm->orderNumber = 4;

        $pokemon = $this->buildBasePokemon();
        $pokemon->categoryForm = $categoryForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertInstanceOf(FormDebugResponse::class, $result->categoryForm);
        self::assertSame('starter', $result->categoryForm->slug);
    }

    #[Test]
    public function fromPokemon_withPrimaryType_mapsTypeFields(): void
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

        self::assertInstanceOf(TypeDebugResponse::class, $result->primaryType);
        self::assertSame('grass', $result->primaryType->slug);
        self::assertSame('Grass', $result->primaryType->name);
        self::assertSame('Plante', $result->primaryType->frenchName);
        self::assertSame(3, $result->primaryType->orderNumber);
        self::assertSame('#78C850', $result->primaryType->color);
        self::assertNull($result->primaryType->identifier);
        self::assertNull($result->primaryType->deletedAt);
    }

    #[Test]
    public function fromPokemon_withSecondaryType_mapsTypeFields(): void
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

        self::assertInstanceOf(TypeDebugResponse::class, $result->secondaryType);
        self::assertSame('poison', $result->secondaryType->slug);
    }

    #[Test]
    public function fromPokemon_withBankableish_mapsBoolValue(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->bankableish = true;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertTrue($result->bankableish);
    }

    #[Test]
    public function fromPokemon_withDeletedAt_formatsAtomDate(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->deletedAt = new \DateTime('2024-03-15T12:00:00+00:00');

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('2024-03-15T12:00:00+00:00', $result->deletedAt);
    }

    #[Test]
    public function fromPokemon_withIdentifier_returnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $pokemon = $this->buildBasePokemon();

        $reflection = new \ReflectionProperty(Pokemon::class, 'identifier');
        $reflection->setValue($pokemon, $uuid);

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $result->identifier);
    }

    #[Test]
    public function fromPokemon_gameBundleWithDeletedAt_formatsAtomDate(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->originalGameBundle->deletedAt = new \DateTime('2024-04-20T08:00:00+00:00');

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('2024-04-20T08:00:00+00:00', $result->originalGameBundle->deletedAt);
    }

    #[Test]
    public function fromPokemon_gameBundleWithIdentifier_returnsUuidString(): void
    {
        $uuid = Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');
        $pokemon = $this->buildBasePokemon();

        $reflection = new \ReflectionProperty(GameBundle::class, 'identifier');
        $reflection->setValue($pokemon->originalGameBundle, $uuid);

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('6ba7b810-9dad-11d1-80b4-00c04fd430c8', $result->originalGameBundle->identifier);
    }

    #[Test]
    public function fromPokemon_generationWithDeletedAt_formatsAtomDate(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->originalGameBundle->generation->deletedAt = new \DateTime('2024-05-10T00:00:00+00:00');

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('2024-05-10T00:00:00+00:00', $result->originalGameBundle->generation->deletedAt);
    }

    #[Test]
    public function fromPokemon_generationWithIdentifier_returnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440099');
        $pokemon = $this->buildBasePokemon();

        $reflection = new \ReflectionProperty(GameGeneration::class, 'identifier');
        $reflection->setValue($pokemon->originalGameBundle->generation, $uuid);

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('550e8400-e29b-41d4-a716-446655440099', $result->originalGameBundle->generation->identifier);
    }

    #[Test]
    public function fromPokemon_formWithDeletedAt_formatsAtomDate(): void
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

        self::assertNotNull($result->variantForm);
        self::assertSame('2024-06-01T00:00:00+00:00', $result->variantForm->deletedAt);
    }

    #[Test]
    public function fromPokemon_formWithIdentifier_returnsUuidString(): void
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

        self::assertNotNull($result->variantForm);
        self::assertSame('550e8400-e29b-41d4-a716-446655440011', $result->variantForm->identifier);
    }

    #[Test]
    public function fromPokemon_typeWithDeletedAt_formatsAtomDate(): void
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

        self::assertNotNull($result->primaryType);
        self::assertSame('2024-07-01T00:00:00+00:00', $result->primaryType->deletedAt);
    }

    #[Test]
    public function fromPokemon_typeWithIdentifier_returnsUuidString(): void
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

        self::assertNotNull($result->primaryType);
        self::assertSame('550e8400-e29b-41d4-a716-446655440022', $result->primaryType->identifier);
    }
}
```

Save as `tests/src/Unit/Factory/PokemonDebugResponseFactoryTest.php`.

---

### Task 7: Update `DebugPokemonController` and delete `AbstractDebugController`

**Files:**
- Modify: `src/Controller/Debug/DebugPokemonController.php`
- Delete: `src/Controller/Debug/AbstractDebugController.php`

- [ ] **Step 1: Replace `DebugPokemonController` with the migrated version**

```php
<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\Entity\Pokemon;
use App\Factory\PokemonAvailabilitiesResponseFactory;
use App\Factory\PokemonDebugResponseFactory;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/debogage/pokemon')]
final class DebugPokemonController extends AbstractController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    public function pokemon(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
        SerializerInterface $serializer,
    ): JsonResponse {
        $response = PokemonDebugResponseFactory::fromPokemon($pokemon);

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }

    #[Route(path: '/{slug}/caches', methods: ['DELETE'])]
    public function pokemonCaches(
        GamesAvailabilitiesService $gamesAvailabilitiesService,
        GamesShiniesAvailabilitiesService $gamesShiniesAvailabilitiesService,
        GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
        CollectionsAvailabilitiesService $collectionsAvailabilitiesService,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): Response {
        $gamesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gamesShiniesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gameBundlesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gameBundlesShiniesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $collectionsAvailabilitiesService->cleanCacheFromPokemon($pokemon);

        return new Response();
    }

    #[Route(path: '/{slug}/availabilities', methods: ['GET'])]
    public function pokemonAvailabilities(
        GamesAvailabilitiesService $gamesAvailabilitiesService,
        GamesShiniesAvailabilitiesService $gamesShiniesAvailabilitiesService,
        GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
        SerializerInterface $serializer,
    ): JsonResponse {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            $gamesAvailabilitiesService->getFromPokemon($pokemon),
            $gamesShiniesAvailabilitiesService->getFromPokemon($pokemon),
            $gameBundlesAvailabilitiesService->getFromPokemon($pokemon),
            $gameBundlesShiniesAvailabilitiesService->getFromPokemon($pokemon),
        );

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }
}
```

Save as `src/Controller/Debug/DebugPokemonController.php`.

- [ ] **Step 2: Delete `AbstractDebugController`**

Run: `rm src/Controller/Debug/AbstractDebugController.php`

Verify: `ls src/Controller/Debug/`

Expected: Only `DebugDexController.php` and `DebugPokemonController.php` remain.

---

### Task 8: Update existing unit test for `DebugPokemonController`

**Files:**
- Modify: `tests/src/Unit/Controller/Debug/DebugPokemonControllerTest.php`

- [ ] **Step 1: Update the test to remove Serializer constructor arg and incorrect CoversClass**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Debug;

use App\Controller\Debug\DebugPokemonController;
use App\Entity\Pokemon;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DebugPokemonController::class)]
final class DebugPokemonControllerTest extends TestCase
{
    public function testPokemonCleanCaches(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'zaertyuiop';

        $gamesAvailabilitiesService = $this->createMock(GamesAvailabilitiesService::class);
        $gamesAvailabilitiesService
            ->expects($this->once())
            ->method('cleanCacheFromPokemon')
            ->with($pokemon)
        ;

        $gamesShiniesAvailabilitiesService = $this->createMock(GamesShiniesAvailabilitiesService::class);
        $gamesShiniesAvailabilitiesService
            ->expects($this->once())
            ->method('cleanCacheFromPokemon')
            ->with($pokemon)
        ;

        $gameBundlesAvailabilitiesService = $this->createMock(GameBundlesAvailabilitiesService::class);
        $gameBundlesAvailabilitiesService
            ->expects($this->once())
            ->method('cleanCacheFromPokemon')
            ->with($pokemon)
        ;

        $gameBundlesShiniesAvailabilitiesService = $this->createMock(GameBundlesShiniesAvailabilitiesService::class);
        $gameBundlesShiniesAvailabilitiesService
            ->expects($this->once())
            ->method('cleanCacheFromPokemon')
            ->with($pokemon)
        ;

        $collectionsAvailabilitiesService = $this->createMock(CollectionsAvailabilitiesService::class);
        $collectionsAvailabilitiesService
            ->expects($this->once())
            ->method('cleanCacheFromPokemon')
            ->with($pokemon)
        ;

        $controller = new DebugPokemonController();

        $response = $controller->pokemonCaches(
            $gamesAvailabilitiesService,
            $gamesShiniesAvailabilitiesService,
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
            $collectionsAvailabilitiesService,
            $pokemon
        );

        $this->assertEmpty($response->getContent());
    }
}
```

Save as `tests/src/Unit/Controller/Debug/DebugPokemonControllerTest.php`.

---

### Task 9: Update integration test for `DebugPokemonController`

**Files:**
- Modify: `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php`

- [ ] **Step 1: Update `#[CoversClass]` attributes and update 3 camelCase assertions to snake_case**

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
        $this->assertStringContainsString('"variant_form":null,', $content);
        $this->assertStringContainsString('"regional_form":null,', $content);
        $this->assertStringContainsString('"slug":"mega",', $content);
        $this->assertStringContainsString('"category_form":null,', $content);
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

Save as `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php`.

---

### Task 10: Final validation

- [ ] **Step 1: Verify all new files exist**

Run:
```bash
ls -la src/DTO/Response/FormDebugResponse.php \
        src/DTO/Response/TypeDebugResponse.php \
        src/DTO/Response/GameGenerationDebugResponse.php \
        src/DTO/Response/GameBundleDebugResponse.php \
        src/DTO/Response/PokemonDebugResponse.php \
        src/Factory/PokemonDebugResponseFactory.php
```

Expected: All 6 files exist.

- [ ] **Step 2: Verify `AbstractDebugController` is deleted**

Run: `ls src/Controller/Debug/`

Expected: Only `DebugDexController.php` and `DebugPokemonController.php`.

- [ ] **Step 3: Run tests**

Run: `make tests`

Expected: All tests pass, 0 failures.

- [ ] **Step 4: Run quality**

Run: `make quality`

Expected: All quality checks pass (PHPStan, Psalm, CS Fixer, PHPMD, Deptrac).

- [ ] **Step 5: Run measures**

Run: `make measures`

Expected: 100% coverage, 100% MSI.

---

## Self-Review

### Spec coverage

| Requirement | Task |
|---|---|
| Migrate `GET /debogage/pokemon/{slug}` to DTO + Factory + Serializer | Tasks 6, 7 |
| Create immutable DTOs for all entity types | Tasks 1-5 |
| Unit tests for all new classes (not Controller/Repository) | Tasks 1-6 |
| 100% coverage and MSI | 16 factory test methods covering all branches |
| Remove `AbstractDebugController` (no longer needed) | Task 7 |
| Update existing tests | Tasks 8, 9 |

### Placeholder scan

No TBD, TODO, or placeholder content found.

### Type consistency

- `FormDebugResponse` used in `PokemonDebugResponse` (variantForm, regionalForm, specialForm, categoryForm) ✓
- `TypeDebugResponse` used in `PokemonDebugResponse` (primaryType, secondaryType) ✓
- `GameGenerationDebugResponse` used in `GameBundleDebugResponse` (generation) ✓
- `GameBundleDebugResponse` used in `PokemonDebugResponse` (originalGameBundle) ✓
- All factory methods use the same type names as the DTOs ✓
