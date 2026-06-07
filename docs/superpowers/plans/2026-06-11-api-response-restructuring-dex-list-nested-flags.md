# API Response Restructuring (Dex List — Nested Flags) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /dex/{trainerExternalId}/list` response by grouping the 7 flat `is_*` boolean fields of `TrainerDexResponse` into a nested `flags` object.

**Architecture:** Create an immutable `DexFlagsResponse` DTO holding the 7 booleans, update `TrainerDexResponse` to embed `DexFlagsResponse $flags` instead of the 7 flat booleans, update `TrainerDexResponseFactory` to build the nested DTO, and update all tests and test data accordingly.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Response shape change

**Before:**
```json
{
    "dex": {"slug": "home"},
    "name": "Home",
    "french_name": "Home",
    "slug": "home",
    "is_shiny": false,
    "is_private": true,
    "is_on_home": false,
    "is_display_form": true,
    "is_released": true,
    "is_premium": false,
    "is_custom": false,
    "display_template": "box"
}
```

**After:**
```json
{
    "dex": {"slug": "home"},
    "name": "Home",
    "french_name": "Home",
    "slug": "home",
    "flags": {
        "is_shiny": false,
        "is_private": true,
        "is_on_home": false,
        "is_display_form": true,
        "is_released": true,
        "is_premium": false,
        "is_custom": false
    },
    "display_template": "box"
}
```

---

## File Structure

**Create:**
- `src/DTO/Response/DexFlagsResponse.php` — immutable DTO grouping the 7 boolean flags
- `tests/src/Unit/DTO/Response/DexFlagsResponseTest.php` — unit tests for `DexFlagsResponse`

**Modify:**
- `src/DTO/Response/TrainerDexResponse.php` — replace 7 flat booleans with `DexFlagsResponse $flags`
- `src/Factory/TrainerDexResponseFactory.php` — build `DexFlagsResponse` from SQL row
- `tests/src/Unit/DTO/Response/TrainerDexResponseTest.php` — update to use nested `flags`
- `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php` — update assertions to traverse `flags`
- `tests/src/Integration/Controller/DexControllerTest.php` — add `#[CoversClass(DexFlagsResponse::class)]`
- `tests/src/Integration/Controller/DexControllerTestData.php` — replace all flat `is_*` fields with nested `flags` across all 6 static methods

---

## Tasks

### Task 1: Create DexFlagsResponse DTO

**Files:**
- Create: `src/DTO/Response/DexFlagsResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexFlagsResponse
{
    public function __construct(
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        #[SerializedName('is_private')]
        public readonly bool $isPrivate,
        #[SerializedName('is_on_home')]
        public readonly bool $isOnHome,
        #[SerializedName('is_display_form')]
        public readonly bool $isDisplayForm,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        #[SerializedName('is_custom')]
        public readonly bool $isCustom,
    ) {}
}
```

Save as `src/DTO/Response/DexFlagsResponse.php`.

---

### Task 2: Write unit tests for DexFlagsResponse

**Files:**
- Create: `tests/src/Unit/DTO/Response/DexFlagsResponseTest.php`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexFlagsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexFlagsResponse::class)]
final class DexFlagsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new DexFlagsResponse(
            isShiny: false,
            isPrivate: true,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        self::assertFalse($response->isShiny);
        self::assertTrue($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }

    #[Test]
    public function constructorHandlesAllTrue(): void
    {
        $response = new DexFlagsResponse(
            isShiny: true,
            isPrivate: true,
            isOnHome: true,
            isDisplayForm: true,
            isReleased: true,
            isPremium: true,
            isCustom: true,
        );

        self::assertTrue($response->isShiny);
        self::assertTrue($response->isPrivate);
        self::assertTrue($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
        self::assertTrue($response->isReleased);
        self::assertTrue($response->isPremium);
        self::assertTrue($response->isCustom);
    }

    #[Test]
    public function constructorHandlesAllFalse(): void
    {
        $response = new DexFlagsResponse(
            isShiny: false,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: false,
            isReleased: false,
            isPremium: false,
            isCustom: false,
        );

        self::assertFalse($response->isShiny);
        self::assertFalse($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertFalse($response->isDisplayForm);
        self::assertFalse($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }
}
```

Save as `tests/src/Unit/DTO/Response/DexFlagsResponseTest.php`.

---

### Task 3: Update TrainerDexResponse DTO

**Files:**
- Modify: `src/DTO/Response/TrainerDexResponse.php`

Current content of `src/DTO/Response/TrainerDexResponse.php`:

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

- [ ] **Step 1: Replace the file content**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerDexResponse
{
    public function __construct(
        public readonly DexSlugResponse $dex,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly string $slug,
        public readonly DexFlagsResponse $flags,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
    ) {}
}
```

Save as `src/DTO/Response/TrainerDexResponse.php`.

---

### Task 4: Update TrainerDexResponseTest

**Files:**
- Modify: `tests/src/Unit/DTO/Response/TrainerDexResponseTest.php`

- [ ] **Step 1: Replace the file content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexFlagsResponse;
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
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: true,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        $response = new TrainerDexResponse(
            dex: $dex,
            name: 'Home',
            frenchName: 'Home',
            slug: 'home',
            flags: $flags,
            displayTemplate: 'box',
        );

        self::assertSame($dex, $response->dex);
        self::assertSame('home', $response->dex->slug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertSame('home', $response->slug);
        self::assertSame($flags, $response->flags);
        self::assertFalse($response->flags->isShiny);
        self::assertTrue($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
        self::assertSame('box', $response->displayTemplate);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $dex = new DexSlugResponse(slug: 'homepogo');
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: false,
            isOnHome: true,
            isDisplayForm: false,
            isReleased: false,
            isPremium: true,
            isCustom: true,
        );

        $response = new TrainerDexResponse(
            dex: $dex,
            name: 'Home PoGo',
            frenchName: 'Home PoGo',
            slug: 'home_pogo',
            flags: $flags,
            displayTemplate: 'list-7',
        );

        self::assertSame($dex, $response->dex);
        self::assertSame('homepogo', $response->dex->slug);
        self::assertSame('Home PoGo', $response->name);
        self::assertSame('Home PoGo', $response->frenchName);
        self::assertSame('home_pogo', $response->slug);
        self::assertSame($flags, $response->flags);
        self::assertFalse($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertTrue($response->flags->isOnHome);
        self::assertFalse($response->flags->isDisplayForm);
        self::assertFalse($response->flags->isReleased);
        self::assertTrue($response->flags->isPremium);
        self::assertTrue($response->flags->isCustom);
        self::assertSame('list-7', $response->displayTemplate);
    }
}
```

Save as `tests/src/Unit/DTO/Response/TrainerDexResponseTest.php`.

---

### Task 5: Update TrainerDexResponseFactory

**Files:**
- Modify: `src/Factory/TrainerDexResponseFactory.php`

- [ ] **Step 1: Replace the file content**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexFlagsResponse;
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
            flags: new DexFlagsResponse(
                isShiny: (bool) $isShiny,
                isPrivate: (bool) $isPrivate,
                isOnHome: (bool) $isOnHome,
                isDisplayForm: (bool) $isDisplayForm,
                isReleased: (bool) $isReleased,
                isPremium: (bool) $isPremium,
                isCustom: (bool) $isCustom,
            ),
            displayTemplate: (string) $displayTemplate,
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

Save as `src/Factory/TrainerDexResponseFactory.php`.

---

### Task 6: Update TrainerDexResponseFactoryTest

**Files:**
- Modify: `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php`

- [ ] **Step 1: Replace the file content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\DexFlagsResponse;
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

        self::assertSame('home', $response->dex->slug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertSame('home', $response->slug);
        self::assertInstanceOf(DexFlagsResponse::class, $response->flags);
        self::assertFalse($response->flags->isShiny);
        self::assertTrue($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
        self::assertSame('box', $response->displayTemplate);
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
        self::assertFalse($response->flags->isShiny);
        self::assertTrue($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
        self::assertSame('202', $response->displayTemplate);
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
        self::assertFalse($responses[0]->flags->isShiny);
        self::assertSame('homeshiny', $responses[1]->dex->slug);
        self::assertTrue($responses[1]->flags->isShiny);
        self::assertTrue($responses[1]->flags->isCustom);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = TrainerDexResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
```

Save as `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php`.

---

### Task 7: Update DexControllerTest

**Files:**
- Modify: `tests/src/Integration/Controller/DexControllerTest.php`

- [ ] **Step 1: Add the DexFlagsResponse import and CoversClass annotation**

Current file header (lines 1–17):

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexController;
use App\Factory\TrainerDexResponseFactory;
use App\Tests\Common\Traits\GetterTrait\GetTrainerDexTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DexController::class)]
#[CoversClass(TrainerDexResponseFactory::class)]
final class DexControllerTest extends AbstractTestControllerApi
{
```

Replace with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexController;
use App\DTO\Response\DexFlagsResponse;
use App\Factory\TrainerDexResponseFactory;
use App\Tests\Common\Traits\GetterTrait\GetTrainerDexTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DexController::class)]
#[CoversClass(TrainerDexResponseFactory::class)]
#[CoversClass(DexFlagsResponse::class)]
final class DexControllerTest extends AbstractTestControllerApi
{
```

---

### Task 8: Update DexControllerTestData

**Files:**
- Modify: `tests/src/Integration/Controller/DexControllerTestData.php`

- [ ] **Step 1: Replace the entire file content with the nested flags version**

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
     * @return array<int, array<string, string|string[]|array<string, bool>>>
     */
    public static function getUser12Content(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            1 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            2 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'box',
            ],
            3 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => 'Home Shiny OT',
                'french_name' => 'Home Chromatique OT',
                'slug' => 'homeshinyot',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'box',
            ],
            4 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            5 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|string[]|array<string, bool>>>
     */
    public static function getUser12ContentWithUnreleased(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'goldsilvercrystal'],
                'name' => 'Gold / Silver / Crystal',
                'french_name' => 'Or / Argent / Cristal',
                'slug' => 'goldsilvercrystal',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => false,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            1 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            2 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            3 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'box',
            ],
            4 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => 'Home Shiny OT',
                'french_name' => 'Home Chromatique OT',
                'slug' => 'homeshinyot',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'box',
            ],
            5 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo',
                'french_name' => 'Home PoGo',
                'slug' => 'home_pogo',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'list-7',
            ],
            6 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo OT',
                'french_name' => 'Home PoGo OT',
                'slug' => 'homepogoot',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'list-7',
            ],
            7 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo Poké Ball',
                'french_name' => 'Home PoGo Poké Ball',
                'slug' => 'homepogopokeball',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'list-7',
            ],
            8 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            9 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|string[]|array<string, bool>>>
     */
    public static function getUser12ContentWithPremium(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'redgreenblueyellow'],
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'slug' => 'redgreenblueyellow',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            1 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            2 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            3 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'box',
            ],
            4 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => 'Home Shiny OT',
                'french_name' => 'Home Chromatique OT',
                'slug' => 'homeshinyot',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'box',
            ],
            5 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            6 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|string[]|array<string, bool>>>
     */
    public static function getUser12ContentWithUnreleasedAndPremium(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'redgreenblueyellow'],
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'slug' => 'redgreenblueyellow',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            1 => [
                'dex' => ['slug' => 'goldsilvercrystal'],
                'name' => 'Gold / Silver / Crystal',
                'french_name' => 'Or / Argent / Cristal',
                'slug' => 'goldsilvercrystal',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => false,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            2 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            3 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            4 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'box',
            ],
            5 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => 'Home Shiny OT',
                'french_name' => 'Home Chromatique OT',
                'slug' => 'homeshinyot',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'box',
            ],
            6 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo',
                'french_name' => 'Home PoGo',
                'slug' => 'home_pogo',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'list-7',
            ],
            7 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo OT',
                'french_name' => 'Home PoGo OT',
                'slug' => 'homepogoot',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'list-7',
            ],
            8 => [
                'dex' => ['slug' => 'homepogo'],
                'name' => 'Home PoGo Poké Ball',
                'french_name' => 'Home PoGo Poké Ball',
                'slug' => 'homepogopokeball',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'display_template' => 'list-7',
            ],
            9 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            10 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|string[]|array<string, bool>>>
     */
    public static function getUser13Content(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            1 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            2 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'homeshiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            3 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            4 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|string[]|array<string, bool>>>
     */
    public static function getUserUnknownContent(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            1 => [
                'dex' => ['slug' => 'home'],
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            2 => [
                'dex' => ['slug' => 'homeshiny'],
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'homeshiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            3 => [
                'dex' => ['slug' => 'demo'],
                'name' => 'Demo',
                'french_name' => 'Démo',
                'slug' => 'demo',
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
            4 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'name' => 'Ruby / Sapphire / Emerald: Shiny',
                'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                'slug' => 'rubysapphireemeraldshiny',
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'display_template' => 'box',
            ],
        ];
    }
}
```

Save as `tests/src/Integration/Controller/DexControllerTestData.php`.

---

## Self-Review

### Spec coverage

| Requirement | Covered by |
|---|---|
| Create `DexFlagsResponse` DTO with 7 boolean properties | Task 1 |
| Unit tests for `DexFlagsResponse` (all-true, all-false, mixed) | Task 2 |
| Update `TrainerDexResponse` to embed `DexFlagsResponse $flags` | Task 3 |
| Update `TrainerDexResponseTest` to assert nested flags | Task 4 |
| Update `TrainerDexResponseFactory` to build `DexFlagsResponse` | Task 5 |
| Update `TrainerDexResponseFactoryTest` to traverse `flags` | Task 6 |
| Add `#[CoversClass(DexFlagsResponse::class)]` to integration test | Task 7 |
| Update all 6 static methods of `DexControllerTestData` | Task 8 |

### Placeholder scan

No TBD, TODO, "similar to", or "handle edge cases" placeholders. All code blocks are complete.

### Type consistency

- `DexFlagsResponse` properties: 7 booleans with camelCase PHP names and snake_case `#[SerializedName]` — used consistently across Tasks 1, 2, 4, 5, 6, 7, 8.
- `TrainerDexResponse` after change: `dex: DexSlugResponse`, `name: string`, `frenchName: string`, `slug: string`, `flags: DexFlagsResponse`, `displayTemplate: string` — used consistently in Tasks 3, 4, 5, 6, 8.
- Factory `fromSqlRow()` reads same 12 SQL keys as before; builds `DexFlagsResponse` from 7 of them — consistent with Task 5.
- `DexControllerTestData` return type updated from `array<string, bool|string|string[]>` to `array<string, string|string[]|array<string, bool>>` in all 6 methods.
