# API Response Restructuring (GET /dex/{trainerExternalId}/list — Nested Dex Object) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restructure `TrainerDexResponse` so the flat `dex_slug` string becomes a nested `DexSlugResponse` object under the `dex` key, aligning with the object-oriented response pattern used across the rest of the API (issue #256).

**Architecture:** Create an immutable `DexSlugResponse` DTO (one field: `slug`). Update `TrainerDexResponse` to hold `DexSlugResponse $dex` instead of `string $dexSlug`. Update `TrainerDexResponseFactory` to build `DexSlugResponse` from the `dex_slug` SQL row field. Update all unit tests and integration test data to reflect the new JSON shape. No changes to `DexController`, `TrainerDexService`, or the SQL query.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Current response structure

```json
[
  {
    "dex_slug": "home",
    "name": "Home",
    "french_name": "Home",
    "slug": "home",
    "is_shiny": false,
    "is_private": true,
    "is_display_form": true,
    "display_template": "box",
    "is_on_home": false,
    "is_released": true,
    "is_premium": false,
    "is_custom": false
  }
]
```

## Target response structure

```json
[
  {
    "dex": {"slug": "home"},
    "name": "Home",
    "french_name": "Home",
    "slug": "home",
    "is_shiny": false,
    "is_private": true,
    "is_display_form": true,
    "display_template": "box",
    "is_on_home": false,
    "is_released": true,
    "is_premium": false,
    "is_custom": false
  }
]
```

---

## File Structure

**Create:**
- `src/DTO/Response/DexSlugResponse.php` — immutable DTO wrapping the Dex slug reference
- `tests/src/Unit/DTO/Response/DexSlugResponseTest.php` — unit tests for DexSlugResponse

**Modify:**
- `src/DTO/Response/TrainerDexResponse.php` — replace `$dexSlug: string` with `$dex: DexSlugResponse`
- `src/Factory/TrainerDexResponseFactory.php` — build nested `DexSlugResponse` from `dex_slug` row field
- `tests/src/Unit/DTO/Response/TrainerDexResponseTest.php` — update constructor calls and assertions
- `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php` — update assertions to `->dex->slug`
- `tests/src/Integration/Controller/DexControllerTestData.php` — replace `'dex_slug' => ...` with `'dex' => ['slug' => ...]`

---

## Tasks

### Task 1: Create DexSlugResponse DTO and its unit test

**Files:**
- Create: `src/DTO/Response/DexSlugResponse.php`
- Create: `tests/src/Unit/DTO/Response/DexSlugResponseTest.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class DexSlugResponse
{
    public function __construct(
        public readonly string $slug,
    ) {}
}
```

Save as `src/DTO/Response/DexSlugResponse.php`.

- [ ] **Step 2: Create the unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexSlugResponse::class)]
final class DexSlugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesSlug(): void
    {
        $response = new DexSlugResponse(slug: 'home');

        self::assertSame('home', $response->slug);
    }

    #[Test]
    public function slugIsReadonly(): void
    {
        $response = new DexSlugResponse(slug: 'rubysapphireemerald');

        self::assertSame('rubysapphireemerald', $response->slug);
    }
}
```

Save as `tests/src/Unit/DTO/Response/DexSlugResponseTest.php`.

---

### Task 2: Update TrainerDexResponse DTO and its unit test

**Files:**
- Modify: `src/DTO/Response/TrainerDexResponse.php`
- Modify: `tests/src/Unit/DTO/Response/TrainerDexResponseTest.php`

- [ ] **Step 1: Replace `$dexSlug: string` with `$dex: DexSlugResponse`**

Replace the entire file `src/DTO/Response/TrainerDexResponse.php` with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerDexResponse
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly DexSlugResponse $dex,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly string $slug,
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        #[SerializedName('is_private')]
        public readonly bool $isPrivate,
        #[SerializedName('is_on_home')]
        public readonly bool $isOnHome,
        #[SerializedName('is_display_form')]
        public readonly bool $isDisplayForm,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        #[SerializedName('is_custom')]
        public readonly bool $isCustom,
    ) {}
}
```

- [ ] **Step 2: Update the unit test**

Replace the entire file `tests/src/Unit/DTO/Response/TrainerDexResponseTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\TrainerDexResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexResponse::class)]
final class TrainerDexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $dex = new DexSlugResponse(slug: 'home');

        $response = new TrainerDexResponse(
            dex: $dex,
            name: 'Home',
            frenchName: 'Home',
            slug: 'home',
            isShiny: false,
            isPrivate: true,
            isOnHome: false,
            isDisplayForm: true,
            displayTemplate: 'box',
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        self::assertSame($dex, $response->dex);
        self::assertSame('home', $response->dex->slug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertSame('home', $response->slug);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('box', $response->displayTemplate);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $dex = new DexSlugResponse(slug: 'homepogo');

        $response = new TrainerDexResponse(
            dex: $dex,
            name: 'Home PoGo',
            frenchName: 'Home PoGo',
            slug: 'home_pogo',
            isShiny: false,
            isPrivate: false,
            isOnHome: true,
            isDisplayForm: false,
            displayTemplate: 'list-7',
            isReleased: false,
            isPremium: true,
            isCustom: true,
        );

        self::assertSame($dex, $response->dex);
        self::assertSame('homepogo', $response->dex->slug);
        self::assertSame('Home PoGo', $response->name);
        self::assertSame('Home PoGo', $response->frenchName);
        self::assertSame('home_pogo', $response->slug);
        self::assertFalse($response->isShiny);
        self::assertFalse($response->isPrivate);
        self::assertTrue($response->isOnHome);
        self::assertFalse($response->isDisplayForm);
        self::assertSame('list-7', $response->displayTemplate);
        self::assertFalse($response->isReleased);
        self::assertTrue($response->isPremium);
        self::assertTrue($response->isCustom);
    }
}
```

---

### Task 3: Update TrainerDexResponseFactory and its unit test

**Files:**
- Modify: `src/Factory/TrainerDexResponseFactory.php`
- Modify: `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php`

- [ ] **Step 1: Update the factory to build nested DexSlugResponse**

Replace the entire file `src/Factory/TrainerDexResponseFactory.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\TrainerDexResponse;

final class TrainerDexResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): TrainerDexResponse
    {
        /** @var scalar $dexSlug */
        $dexSlug = $row['dex_slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $isShiny */
        $isShiny = $row['is_shiny'];

        /** @var scalar $isPrivate */
        $isPrivate = $row['is_private'];

        /** @var scalar $isOnHome */
        $isOnHome = $row['is_on_home'];

        /** @var scalar $isDisplayForm */
        $isDisplayForm = $row['is_display_form'];

        /** @var scalar $displayTemplate */
        $displayTemplate = $row['display_template'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $isCustom */
        $isCustom = $row['is_custom'];

        return new TrainerDexResponse(
            dex: new DexSlugResponse(
                slug: (string) $dexSlug,
            ),
            name: (string) $name,
            frenchName: (string) $frenchName,
            slug: (string) $slug,
            isShiny: (bool) $isShiny,
            isPrivate: (bool) $isPrivate,
            isOnHome: (bool) $isOnHome,
            isDisplayForm: (bool) $isDisplayForm,
            displayTemplate: (string) $displayTemplate,
            isReleased: (bool) $isReleased,
            isPremium: (bool) $isPremium,
            isCustom: (bool) $isCustom,
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return TrainerDexResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
```

- [ ] **Step 2: Update the factory unit test**

Replace the entire file `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\TrainerDexResponse;
use App\Factory\TrainerDexResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexResponseFactory::class)]
final class TrainerDexResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'dex_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'slug' => 'home',
            'is_shiny' => false,
            'is_private' => true,
            'is_on_home' => false,
            'is_display_form' => true,
            'display_template' => 'box',
            'is_released' => true,
            'is_premium' => false,
            'is_custom' => false,
        ];

        $response = TrainerDexResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(TrainerDexResponse::class, $response);
        self::assertInstanceOf(DexSlugResponse::class, $response->dex);
        self::assertSame('home', $response->dex->slug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertSame('home', $response->slug);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('box', $response->displayTemplate);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }

    #[Test]
    public function fromSqlRowCastsValuesToCorrectTypes(): void
    {
        $row = [
            'dex_slug' => 123,
            'name' => 456,
            'french_name' => 789,
            'slug' => 101,
            'is_shiny' => 0,
            'is_private' => 1,
            'is_on_home' => 0,
            'is_display_form' => 1,
            'display_template' => 202,
            'is_released' => 1,
            'is_premium' => 0,
            'is_custom' => 0,
        ];

        $response = TrainerDexResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->dex->slug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->frenchName);
        self::assertSame('101', $response->slug);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('202', $response->displayTemplate);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'dex_slug' => 'home',
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_on_home' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            [
                'dex_slug' => 'homeshiny',
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_on_home' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
        ];

        $responses = TrainerDexResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(TrainerDexResponse::class, $responses);
        self::assertSame('home', $responses[0]->dex->slug);
        self::assertFalse($responses[0]->isShiny);
        self::assertSame('homeshiny', $responses[1]->dex->slug);
        self::assertTrue($responses[1]->isShiny);
        self::assertTrue($responses[1]->isCustom);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = TrainerDexResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
```

---

### Task 4: Update DexControllerTestData integration fixtures

**Files:**
- Modify: `tests/src/Integration/Controller/DexControllerTestData.php`

- [ ] **Step 1: Replace all `'dex_slug' => ...` with `'dex' => ['slug' => ...]` in every method**

Replace the entire file `tests/src/Integration/Controller/DexControllerTestData.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

/**
 * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
 */
final class DexControllerTestData
{
    /**
     * @return array<int, array<string, bool|string|string[]>>
     */
    public static function getUser12Content(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            1 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            2 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
            3 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => 'Home Shiny OT',
                'french_name' => 'Home Chromatique OT',
                'slug' => 'homeshinyot',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
            4 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            5 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
        ];
    }

    /**
     * @return array<int, array<string, bool|string|string[]>>
     */
    public static function getUser12ContentWithUnreleased(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'goldsilvercrystal'],
                'name' => 'Gold / Silver / Crystal',
                'french_name' => 'Or / Argent / Cristal',
                'slug' => 'goldsilvercrystal',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => false,
                'is_premium' => false,
                'is_custom' => false,
            ],
            1 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            2 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            3 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
            4 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => 'Home Shiny OT',
                'french_name' => 'Home Chromatique OT',
                'slug' => 'homeshinyot',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
            5 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo',
                'french_name' => 'Home PoGo',
                'slug' => 'home_pogo',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => false,
                'display_template' => 'list-7',
                'is_on_home' => true,
                'is_released' => false,
                'is_premium' => true,
                'is_custom' => true,
            ],
            6 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo OT',
                'french_name' => 'Home PoGo OT',
                'slug' => 'homepogoot',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => false,
                'display_template' => 'list-7',
                'is_on_home' => true,
                'is_released' => false,
                'is_premium' => true,
                'is_custom' => true,
            ],
            7 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo Poké Ball',
                'french_name' => 'Home PoGo Poké Ball',
                'slug' => 'homepogopokeball',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => false,
                'display_template' => 'list-7',
                'is_on_home' => true,
                'is_released' => false,
                'is_premium' => true,
                'is_custom' => true,
            ],
            8 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            9 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
        ];
    }

    /**
     * @return array<int, array<string, bool|string|string[]>>
     */
    public static function getUser12ContentWithPremium(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'redgreenblueyellow'],
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'slug' => 'redgreenblueyellow',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => false,
            ],
            1 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            2 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            3 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
            4 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => 'Home Shiny OT',
                'french_name' => 'Home Chromatique OT',
                'slug' => 'homeshinyot',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
            5 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            6 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
        ];
    }

    /**
     * @return array<int, array<string, bool|string|string[]>>
     */
    public static function getUser12ContentWithUnreleasedAndPremium(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'redgreenblueyellow'],
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'slug' => 'redgreenblueyellow',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => false,
            ],
            1 => [
                'dex' => ['slug' => 'goldsilvercrystal'],
                'name' => 'Gold / Silver / Crystal',
                'french_name' => 'Or / Argent / Cristal',
                'slug' => 'goldsilvercrystal',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => false,
                'is_premium' => false,
                'is_custom' => false,
            ],
            2 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            3 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            4 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
            5 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => 'Home Shiny OT',
                'french_name' => 'Home Chromatique OT',
                'slug' => 'homeshinyot',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
            6 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo',
                'french_name' => 'Home PoGo',
                'slug' => 'home_pogo',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => false,
                'display_template' => 'list-7',
                'is_on_home' => true,
                'is_released' => false,
                'is_premium' => true,
                'is_custom' => true,
            ],
            7 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo OT',
                'french_name' => 'Home PoGo OT',
                'slug' => 'homepogoot',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => false,
                'display_template' => 'list-7',
                'is_on_home' => true,
                'is_released' => false,
                'is_premium' => true,
                'is_custom' => true,
            ],
            8 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo Poké Ball',
                'french_name' => 'Home PoGo Poké Ball',
                'slug' => 'homepogopokeball',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => false,
                'display_template' => 'list-7',
                'is_on_home' => true,
                'is_released' => false,
                'is_premium' => true,
                'is_custom' => true,
            ],
            9 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            10 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
        ];
    }

    /**
     * @return array<int, array<string, bool|string|string[]>>
     */
    public static function getUser13Content(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            1 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            2 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'homeshiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            3 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            4 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
        ];
    }

    /**
     * @return array<int, array<string, bool|string|string[]>>
     */
    public static function getUserUnknownContent(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            1 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            2 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'homeshiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            3 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            4 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
        ];
    }
}
```

---

### Task 5: Run quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all tests**

Run: `make tests`

Expected: All unit + integration tests pass, 0 failures.

- [ ] **Step 2: Run code quality**

Run: `make quality`

Expected: PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint all green.

- [ ] **Step 3: Run coverage and mutation**

Run: `make measures`

Expected: 100% line coverage, 100% MSI for all new/modified code.

---

## Self-Review

**Spec coverage:**
- ✅ New `DexSlugResponse` DTO (`slug: string`) — `src/DTO/Response/DexSlugResponse.php`
- ✅ `DexSlugResponseTest` — 2 test cases covering the constructor
- ✅ `TrainerDexResponse` updated — `$dex: DexSlugResponse` replaces `$dexSlug: string`; `#[SerializedName('dex_slug')]` removed (serializer uses property name `dex` automatically)
- ✅ `TrainerDexResponseTest` updated — 2 test cases asserting on `$response->dex->slug`
- ✅ `TrainerDexResponseFactory` updated — builds `new DexSlugResponse(slug: (string) $dexSlug)` from `$row['dex_slug']`
- ✅ `TrainerDexResponseFactoryTest` updated — 4 test cases, all asserting on `->dex->slug` instead of `->dexSlug`
- ✅ `DexControllerTestData` updated — all 5 methods; every `'dex_slug' => 'xxx'` becomes `'dex' => ['slug' => 'xxx']`; PHPDoc updated to `array<int, array<string, bool|string|string[]>>`
- ✅ No changes to `DexController` (already correct)
- ✅ No changes to `DexControllerTest` (test method bodies unchanged; `DexControllerTestData` provides the new expected data)

**Placeholder scan:** No TBD, no "similar to task N", all code blocks complete.

**Type consistency:**
- `TrainerDexResponseFactory::fromSqlRow()` returns `TrainerDexResponse` ✅
- `TrainerDexResponse::$dex` → `DexSlugResponse` ✅
- `DexSlugResponse::$slug` → `string` ✅
- Integration test data `'dex' => ['slug' => 'xxx']` matches what Symfony Serializer produces for a `DexSlugResponse` with `slug = 'xxx'` ✅
- Factory test assertions use `$response->dex->slug` matching the new DTO property path ✅
