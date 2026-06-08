# API Response Restructuring (Album Dex — Nested Flags) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Group the 7 flat `is_*` boolean flags of `AlbumDexResponse` into a nested `flags` object (`DexFlagsResponse`), aligning with the pattern already applied to `TrainerDexResponse` (issue #256).

**Architecture:** No new DTOs needed — `DexFlagsResponse` already exists. Update `AlbumDexResponse` to replace 7 flat `is_*` booleans with `DexFlagsResponse $flags`. Update `AlbumDexResponseFactory` to build the nested flags via a private `buildFlags()` helper. Update all unit tests and integration test assertions. No changes to the controller, service, or repository.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Response shape change

**Before:**
```json
{
    "slug": "redgreenblueyellow",
    "original_slug": "redgreenblueyellow",
    "name": "Red / Green / Blue / Yellow",
    "french_name": "Rouge / Vert / Bleu / Jaune",
    "is_shiny": false,
    "is_private": false,
    "is_on_home": false,
    "is_display_form": true,
    "display_template": "box",
    "region": { "name": "Kanto", "french_name": "Kanto" },
    "selection_rule": "...",
    "description": "...",
    "french_description": "...",
    "version": "20230221.085100",
    "is_released": true,
    "is_premium": false,
    "is_custom": false
}
```

**After:**
```json
{
    "slug": "redgreenblueyellow",
    "original_slug": "redgreenblueyellow",
    "name": "Red / Green / Blue / Yellow",
    "french_name": "Rouge / Vert / Bleu / Jaune",
    "flags": {
        "is_shiny": false,
        "is_private": false,
        "is_on_home": false,
        "is_display_form": true,
        "is_released": true,
        "is_premium": false,
        "is_custom": false
    },
    "display_template": "box",
    "region": { "name": "Kanto", "french_name": "Kanto" },
    "selection_rule": "...",
    "description": "...",
    "french_description": "...",
    "version": "20230221.085100"
}
```

---

## File Structure

**No new files** — `DexFlagsResponse` already exists at `src/DTO/Response/DexFlagsResponse.php`.

**Modify:**
- `src/DTO/Response/AlbumDexResponse.php` — replace 7 flat `is_*` booleans with `DexFlagsResponse $flags`
- `src/Factory/AlbumDexResponseFactory.php` — add private `buildFlags()` helper, update imports
- `tests/src/Unit/DTO/Response/AlbumDexResponseTest.php` — update constructor calls to use nested `flags`
- `tests/src/Unit/Factory/AlbumDexResponseFactoryTest.php` — update assertions to traverse `->flags->is*`
- `tests/src/Unit/Factory/AlbumIndexResponseFactoryTest.php` — update `buildAlbumDexResponse()` helper
- `tests/src/Integration/Controller/AlbumIndexControllerTest.php` — replace `$data['dex']['is_*']` with `$data['dex']['flags']['is_*']`

---

## Tasks

### Task 1: Update AlbumDexResponse DTO

**Files:**
- Modify: `src/DTO/Response/AlbumDexResponse.php`

- [ ] **Step 1: Replace the 7 flat boolean flags with `DexFlagsResponse $flags`**

Replace the entire file content with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumDexResponse
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly string $slug,
        #[SerializedName('original_slug')]
        public readonly string $originalSlug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly DexFlagsResponse $flags,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
        public readonly ?AlbumRegionResponse $region,
        #[SerializedName('selection_rule')]
        public readonly string $selectionRule,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        public readonly string $version,
    ) {}
}
```

Save as `src/DTO/Response/AlbumDexResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/AlbumDexResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/AlbumDexResponse.php`

---

### Task 2: Update AlbumDexResponseFactory

**Files:**
- Modify: `src/Factory/AlbumDexResponseFactory.php`

- [ ] **Step 1: Replace the factory with `buildFlags()` private helper**

Replace the entire file content with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumRegionResponse;
use App\DTO\Response\DexFlagsResponse;

final class AlbumDexResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): AlbumDexResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $originalSlug */
        $originalSlug = $row['original_slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $displayTemplate */
        $displayTemplate = $row['display_template'];

        /** @var scalar $selectionRule */
        $selectionRule = $row['selection_rule'];

        /** @var scalar $description */
        $description = $row['description'];

        /** @var scalar $frenchDescription */
        $frenchDescription = $row['french_description'];

        /** @var scalar $version */
        $version = $row['version'];

        return new AlbumDexResponse(
            slug: (string) $slug,
            originalSlug: (string) $originalSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            flags: self::buildFlags($row),
            displayTemplate: (string) $displayTemplate,
            region: self::buildRegion($row),
            selectionRule: (string) $selectionRule,
            description: (string) $description,
            frenchDescription: (string) $frenchDescription,
            version: (string) $version,
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function buildFlags(array $row): DexFlagsResponse
    {
        /** @var scalar $isShiny */
        $isShiny = $row['is_shiny'];

        /** @var scalar $isPrivate */
        $isPrivate = $row['is_private'];

        /** @var scalar $isOnHome */
        $isOnHome = $row['is_on_home'];

        /** @var scalar $isDisplayForm */
        $isDisplayForm = $row['is_display_form'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $isCustom */
        $isCustom = $row['is_custom'];

        return new DexFlagsResponse(
            isShiny: (bool) $isShiny,
            isPrivate: (bool) $isPrivate,
            isOnHome: (bool) $isOnHome,
            isDisplayForm: (bool) $isDisplayForm,
            isReleased: (bool) $isReleased,
            isPremium: (bool) $isPremium,
            isCustom: (bool) $isCustom,
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function buildRegion(array $row): ?AlbumRegionResponse
    {
        if (empty($row['region_name'])) {
            return null;
        }

        /** @var scalar $regionName */
        $regionName = $row['region_name'];

        /** @var scalar $regionFrenchName */
        $regionFrenchName = $row['region_french_name'];

        return new AlbumRegionResponse(
            name: (string) $regionName,
            frenchName: (string) $regionFrenchName,
        );
    }
}
```

Save as `src/Factory/AlbumDexResponseFactory.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/Factory/AlbumDexResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/AlbumDexResponseFactory.php`

---

### Task 3: Update AlbumDexResponseTest

**Files:**
- Modify: `tests/src/Unit/DTO/Response/AlbumDexResponseTest.php`

- [ ] **Step 1: Update constructor calls to pass `DexFlagsResponse $flags`**

Replace the entire file content with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumRegionResponse;
use App\DTO\Response\DexFlagsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumDexResponse::class)]
final class AlbumDexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $region = new AlbumRegionResponse(name: 'Kanto', frenchName: 'Kanto');
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        $response = new AlbumDexResponse(
            slug: 'redgreenblueyellow',
            originalSlug: 'redgreenblueyellow',
            name: 'Red / Green / Blue / Yellow',
            frenchName: 'Rouge / Vert / Bleu / Jaune',
            flags: $flags,
            displayTemplate: 'box',
            region: $region,
            selectionRule: '(p.bankable or p.bankableish) and ba?.redgreenblueyellow',
            description: 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            frenchDescription: 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            version: '20230221.085100',
        );

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('redgreenblueyellow', $response->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $response->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $response->frenchName);
        self::assertSame($flags, $response->flags);
        self::assertSame('box', $response->displayTemplate);
        self::assertSame($region, $response->region);
        self::assertSame('(p.bankable or p.bankableish) and ba?.redgreenblueyellow', $response->selectionRule);
        self::assertSame(
            'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            $response->description,
        );
        self::assertSame(
            'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            $response->frenchDescription,
        );
        self::assertSame('20230221.085100', $response->version);
    }

    #[Test]
    public function constructorAcceptsNullRegion(): void
    {
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: true,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        $response = new AlbumDexResponse(
            slug: 'home',
            originalSlug: 'home',
            name: 'Home',
            frenchName: 'Home',
            flags: $flags,
            displayTemplate: 'box',
            region: null,
            selectionRule: '',
            description: '',
            frenchDescription: '',
            version: '20230421.123456',
        );

        self::assertNull($response->region);
        self::assertSame($flags, $response->flags);
        self::assertTrue($response->flags->isPrivate);
    }
}
```

Save as `tests/src/Unit/DTO/Response/AlbumDexResponseTest.php`.

- [ ] **Step 2: Run unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/AlbumDexResponseTest.php`

Expected: 2 tests, 0 failures.

---

### Task 4: Update AlbumDexResponseFactoryTest

**Files:**
- Modify: `tests/src/Unit/Factory/AlbumDexResponseFactoryTest.php`

- [ ] **Step 1: Update all flag assertions to traverse `->flags->is*` and add dedicated flags test**

Replace the entire file content with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\AlbumRegionResponse;
use App\DTO\Response\DexFlagsResponse;
use App\Factory\AlbumDexResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumDexResponseFactory::class)]
final class AlbumDexResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowBuildsResponseWithCorrectScalarFields(): void
    {
        $result = AlbumDexResponseFactory::fromSqlRow($this->getRedGreenBlueYellowRow());

        self::assertSame('redgreenblueyellow', $result->slug);
        self::assertSame('redgreenblueyellow', $result->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $result->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $result->frenchName);
        self::assertSame('box', $result->displayTemplate);
        self::assertSame('(p.bankable or p.bankableish) and ba?.redgreenblueyellow', $result->selectionRule);
        self::assertSame(
            'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            $result->description,
        );
        self::assertSame(
            'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            $result->frenchDescription,
        );
        self::assertSame('20230221.085100', $result->version);
    }

    #[Test]
    public function fromSqlRowBuildsFlagsSubObjectForReleasedDex(): void
    {
        $result = AlbumDexResponseFactory::fromSqlRow($this->getRedGreenBlueYellowRow());

        self::assertInstanceOf(DexFlagsResponse::class, $result->flags);
        self::assertFalse($result->flags->isShiny);
        self::assertFalse($result->flags->isPrivate);
        self::assertFalse($result->flags->isOnHome);
        self::assertTrue($result->flags->isDisplayForm);
        self::assertTrue($result->flags->isReleased);
        self::assertFalse($result->flags->isPremium);
        self::assertFalse($result->flags->isCustom);
    }

    #[Test]
    public function fromSqlRowBuildsFlagsSubObjectForPrivateDex(): void
    {
        $result = AlbumDexResponseFactory::fromSqlRow($this->getHomeRow());

        self::assertInstanceOf(DexFlagsResponse::class, $result->flags);
        self::assertFalse($result->flags->isShiny);
        self::assertTrue($result->flags->isPrivate);
        self::assertFalse($result->flags->isOnHome);
        self::assertTrue($result->flags->isDisplayForm);
        self::assertTrue($result->flags->isReleased);
        self::assertFalse($result->flags->isPremium);
        self::assertFalse($result->flags->isCustom);
    }

    #[Test]
    public function fromSqlRowBuildsRegionSubObject(): void
    {
        $result = AlbumDexResponseFactory::fromSqlRow($this->getRedGreenBlueYellowRow());

        self::assertInstanceOf(AlbumRegionResponse::class, $result->region);
        self::assertSame('Kanto', $result->region->name);
        self::assertSame('Kanto', $result->region->frenchName);
    }

    #[Test]
    public function fromSqlRowSetsNullRegionWhenRegionNameIsNull(): void
    {
        $result = AlbumDexResponseFactory::fromSqlRow($this->getHomeRow());

        self::assertNull($result->region);
    }

    #[Test]
    public function fromSqlRowSetsNullRegionWhenRegionNameIsEmptyString(): void
    {
        $row = $this->getHomeRow();
        $row['region_name'] = '';
        $row['region_french_name'] = '';

        $result = AlbumDexResponseFactory::fromSqlRow($row);

        self::assertNull($result->region);
    }

    #[Test]
    public function fromSqlRowCastsStringAndBoolFields(): void
    {
        $row = $this->getRedGreenBlueYellowRow();
        $row['slug'] = 123;
        $row['original_slug'] = 456;
        $row['name'] = 789;
        $row['french_name'] = 101;
        $row['is_shiny'] = 0;
        $row['is_private'] = 1;
        $row['is_on_home'] = 0;
        $row['is_display_form'] = 1;
        $row['display_template'] = 202;
        $row['selection_rule'] = 303;
        $row['description'] = 404;
        $row['french_description'] = 505;
        $row['version'] = 606;
        $row['is_released'] = 1;
        $row['is_premium'] = 0;
        $row['is_custom'] = 0;

        $result = AlbumDexResponseFactory::fromSqlRow($row);

        self::assertSame('123', $result->slug);
        self::assertSame('456', $result->originalSlug);
        self::assertSame('789', $result->name);
        self::assertSame('101', $result->frenchName);
        self::assertFalse($result->flags->isShiny);
        self::assertTrue($result->flags->isPrivate);
        self::assertFalse($result->flags->isOnHome);
        self::assertTrue($result->flags->isDisplayForm);
        self::assertSame('202', $result->displayTemplate);
        self::assertSame('303', $result->selectionRule);
        self::assertSame('404', $result->description);
        self::assertSame('505', $result->frenchDescription);
        self::assertSame('606', $result->version);
        self::assertTrue($result->flags->isReleased);
        self::assertFalse($result->flags->isPremium);
        self::assertFalse($result->flags->isCustom);
    }

    #[Test]
    public function fromSqlRowCastsRegionFieldsToStrings(): void
    {
        $row = $this->getRedGreenBlueYellowRow();
        $row['region_name'] = 123;
        $row['region_french_name'] = 456;

        $result = AlbumDexResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumRegionResponse::class, $result->region);
        self::assertSame('123', $result->region->name);
        self::assertSame('456', $result->region->frenchName);
    }

    /**
     * @return array<string, mixed>
     */
    private function getRedGreenBlueYellowRow(): array
    {
        return [
            'slug' => 'redgreenblueyellow',
            'original_slug' => 'redgreenblueyellow',
            'name' => 'Red / Green / Blue / Yellow',
            'french_name' => 'Rouge / Vert / Bleu / Jaune',
            'is_shiny' => false,
            'is_private' => false,
            'is_on_home' => false,
            'is_display_form' => true,
            'display_template' => 'box',
            'region_name' => 'Kanto',
            'region_french_name' => 'Kanto',
            'selection_rule' => '(p.bankable or p.bankableish) and ba?.redgreenblueyellow',
            'description' => 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            'french_description' => 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            'version' => '20230221.085100',
            'is_released' => true,
            'is_premium' => false,
            'is_custom' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getHomeRow(): array
    {
        return [
            'slug' => 'home',
            'original_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'is_shiny' => false,
            'is_private' => true,
            'is_on_home' => false,
            'is_display_form' => true,
            'display_template' => 'box',
            'region_name' => null,
            'region_french_name' => null,
            'selection_rule' => '',
            'description' => '',
            'french_description' => '',
            'version' => '20230421.123456',
            'is_released' => true,
            'is_premium' => false,
            'is_custom' => false,
        ];
    }
}
```

Save as `tests/src/Unit/Factory/AlbumDexResponseFactoryTest.php`.

- [ ] **Step 2: Run unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumDexResponseFactoryTest.php`

Expected: 8 tests, 0 failures.

---

### Task 5: Update AlbumIndexResponseFactoryTest

**Files:**
- Modify: `tests/src/Unit/Factory/AlbumIndexResponseFactoryTest.php`

- [ ] **Step 1: Update `buildAlbumDexResponse()` helper to use `DexFlagsResponse`**

Replace the entire file content with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumReportResponse;
use App\DTO\Response\AlbumTypesResponse;
use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\PokemonDataResponse;
use App\Factory\AlbumIndexResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumIndexResponseFactory::class)]
final class AlbumIndexResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromPartsWithNonNullDexMapsAllParts(): void
    {
        $dex = $this->buildAlbumDexResponse();
        $pokemons = [$this->buildAlbumPokemonResponse()];
        $report = new AlbumReportResponse(total: 10, totalCaught: 5, totalUncaught: 3, detail: []);
        $filteredReport = new AlbumReportResponse(total: 5, totalCaught: 2, totalUncaught: 2, detail: []);

        $result = AlbumIndexResponseFactory::fromParts($dex, $pokemons, $report, $filteredReport);

        self::assertSame($dex, $result->dex);
        self::assertSame($pokemons, $result->pokemons);
        self::assertSame($report, $result->report);
        self::assertSame($filteredReport, $result->filteredReport);
    }

    #[Test]
    public function fromPartsWithNullDexSetsNullDex(): void
    {
        $report = new AlbumReportResponse(total: 0, totalCaught: 0, totalUncaught: 0, detail: []);
        $filteredReport = new AlbumReportResponse(total: 1, totalCaught: 0, totalUncaught: 1, detail: []);

        $result = AlbumIndexResponseFactory::fromParts(null, [], $report, $filteredReport);

        self::assertNull($result->dex);
        self::assertSame([], $result->pokemons);
        self::assertSame($report, $result->report);
        self::assertSame($filteredReport, $result->filteredReport);
    }

    private function buildAlbumDexResponse(): AlbumDexResponse
    {
        return new AlbumDexResponse(
            slug: 'national',
            originalSlug: 'national',
            name: 'National',
            frenchName: 'National',
            flags: new DexFlagsResponse(
                isShiny: false,
                isPrivate: false,
                isOnHome: true,
                isDisplayForm: false,
                isReleased: true,
                isPremium: false,
                isCustom: false,
            ),
            displayTemplate: 'list',
            region: null,
            selectionRule: '',
            description: '',
            frenchDescription: '',
            version: '1.0',
        );
    }

    private function buildAlbumPokemonResponse(): AlbumPokemonResponse
    {
        return new AlbumPokemonResponse(
            pokemon: new PokemonDataResponse(
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
                orderNumber: '001',
                gameBundles: [],
                gameBundlesShiny: [],
            ),
            catchState: null,
            forms: null,
            types: new AlbumTypesResponse(primary: null, secondary: null),
        );
    }
}
```

Save as `tests/src/Unit/Factory/AlbumIndexResponseFactoryTest.php`.

- [ ] **Step 2: Run unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumIndexResponseFactoryTest.php`

Expected: 2 tests, 0 failures.

---

### Task 6: Update AlbumIndexControllerTest

**Files:**
- Modify: `tests/src/Integration/Controller/AlbumIndexControllerTest.php`

- [ ] **Step 1: Replace all flat `$data['dex']['is_*']` assertions with nested `$data['dex']['flags']['is_*']`**

For every test method that currently checks flat boolean fields directly on `$data['dex']`, apply this pattern:

**Before (in each test method):**
```php
$this->assertArrayHasKey('is_shiny', $data['dex']);
$this->assertFalse($data['dex']['is_shiny']);
$this->assertArrayHasKey('is_private', $data['dex']);
$this->assertFalse($data['dex']['is_private']);
$this->assertArrayHasKey('is_on_home', $data['dex']);
$this->assertFalse($data['dex']['is_on_home']);
$this->assertArrayHasKey('is_display_form', $data['dex']);
$this->assertTrue($data['dex']['is_display_form']);
...
$this->assertArrayHasKey('is_released', $data['dex']);
$this->assertTrue($data['dex']['is_released']);
```

**After (in each test method):**
```php
$this->assertArrayHasKey('flags', $data['dex']);
$this->assertIsArray($data['dex']['flags']);
$this->assertFalse($data['dex']['flags']['is_shiny']);
$this->assertFalse($data['dex']['flags']['is_private']);
$this->assertFalse($data['dex']['flags']['is_on_home']);
$this->assertTrue($data['dex']['flags']['is_display_form']);
...
$this->assertTrue($data['dex']['flags']['is_released']);
```

The complete updated file:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\AlbumIndexController;
use App\Tests\Common\Data\AlbumData;
use App\Tests\Common\Traits\ReportTrait\AssertReportTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 *
 * @psalm-import-type PokedexResponse from \App\Tests\Common\Types\PokedexTypes
 */
#[CoversClass(AlbumIndexController::class)]
final class AlbumIndexControllerTest extends AbstractTestControllerApi
{
    use AssertReportTrait;

    public function testListUser12RedGreenBlueYellow(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertIsArray($data['dex']);

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $data['dex']['french_name']);
        $this->assertArrayHasKey('flags', $data['dex']);
        $this->assertIsArray($data['dex']['flags']);
        $this->assertFalse($data['dex']['flags']['is_shiny']);
        $this->assertFalse($data['dex']['flags']['is_private']);
        $this->assertFalse($data['dex']['flags']['is_on_home']);
        $this->assertTrue($data['dex']['flags']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertIsArray($data['dex']['region']);
        $this->assertEquals('Kanto', $data['dex']['region']['name']);
        $this->assertEquals('Kanto', $data['dex']['region']['french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals(
            'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            $data['dex']['description']
        );
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals(
            'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            $data['dex']['french_description']
        );
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230221.085100', $data['dex']['version']);
        $this->assertTrue($data['dex']['flags']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

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

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 4, 1, 2, 0, 7);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 4, 1, 2, 0, 7);
    }

    public function testListUser12GoldSilverCrystal(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/goldsilvercrystal');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertIsArray($data['dex']);

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('goldsilvercrystal', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('goldsilvercrystal', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Gold / Silver / Crystal', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Or / Argent / Cristal', $data['dex']['french_name']);
        $this->assertArrayHasKey('flags', $data['dex']);
        $this->assertIsArray($data['dex']['flags']);
        $this->assertFalse($data['dex']['flags']['is_shiny']);
        $this->assertTrue($data['dex']['flags']['is_private']);
        $this->assertFalse($data['dex']['flags']['is_on_home']);
        $this->assertTrue($data['dex']['flags']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertIsArray($data['dex']['region']);
        $this->assertEquals('Johto', $data['dex']['region']['name']);
        $this->assertEquals('Johto', $data['dex']['region']['french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals(
            'The list of obtainable Pokémons in Gold, Silver and Crystal games',
            $data['dex']['description']
        );
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals(
            'La liste des pokémons obtenable dans les jeux Or, Argent et Cristal.',
            $data['dex']['french_description']
        );
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230221.085100', $data['dex']['version']);
        $this->assertFalse($data['dex']['flags']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

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

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 8, 0, 0, 1, 9);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 8, 0, 0, 1, 9);
    }

    public function testListUser13(): void
    {
        $this->apiRequest('GET', '/album/bd307a3ec329e10a2cff8fb87480823da114f8f4/redgreenblueyellow');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertIsArray($data['dex']);

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $data['dex']['french_name']);
        $this->assertArrayHasKey('flags', $data['dex']);
        $this->assertIsArray($data['dex']['flags']);
        $this->assertFalse($data['dex']['flags']['is_shiny']);
        $this->assertTrue($data['dex']['flags']['is_private']);
        $this->assertFalse($data['dex']['flags']['is_on_home']);
        $this->assertTrue($data['dex']['flags']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertIsArray($data['dex']['region']);
        $this->assertEquals('Kanto', $data['dex']['region']['name']);
        $this->assertEquals('Kanto', $data['dex']['region']['french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals(
            'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            $data['dex']['description']
        );
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals(
            'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            $data['dex']['french_description']
        );
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230221.085100', $data['dex']['version']);
        $this->assertTrue($data['dex']['flags']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

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

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 6, 0, 0, 1, 7);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 6, 0, 0, 1, 7);
    }

    public function testListUserUnknown(): void
    {
        $this->apiRequest('GET', '/album/46546542313186/redgreenblueyellow');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertIsArray($data['dex']);

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $data['dex']['french_name']);
        $this->assertArrayHasKey('flags', $data['dex']);
        $this->assertIsArray($data['dex']['flags']);
        $this->assertFalse($data['dex']['flags']['is_shiny']);
        $this->assertTrue($data['dex']['flags']['is_private']);
        $this->assertFalse($data['dex']['flags']['is_on_home']);
        $this->assertTrue($data['dex']['flags']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertIsArray($data['dex']['region']);
        $this->assertEquals('Kanto', $data['dex']['region']['name']);
        $this->assertEquals('Kanto', $data['dex']['region']['french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals(
            'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            $data['dex']['description']
        );
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals(
            'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            $data['dex']['french_description']
        );
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230221.085100', $data['dex']['version']);
        $this->assertTrue($data['dex']['flags']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

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

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 0, 0, 0, 0, 7);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 0, 0, 0, 0, 7);
    }

    public function testListHome(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data['dex']);
        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('home', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('home', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home', $data['dex']['french_name']);
        $this->assertArrayHasKey('flags', $data['dex']);
        $this->assertIsArray($data['dex']['flags']);
        $this->assertFalse($data['dex']['flags']['is_shiny']);
        $this->assertTrue($data['dex']['flags']['is_private']);
        $this->assertTrue($data['dex']['flags']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNull($data['dex']['region']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230421.123456', $data['dex']['version']);
        $this->assertTrue($data['dex']['flags']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumData::getExpectedHomeNestedContent(),
            $pokemons
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 9, 3, 3, 7, 22);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testListHomeShiny(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home_shiny');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data['dex']);
        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('home_shiny', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('homeshiny', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals("Home\nShiny", $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals("Home\nChromatique", $data['dex']['french_name']);
        $this->assertArrayHasKey('flags', $data['dex']);
        $this->assertIsArray($data['dex']['flags']);
        $this->assertTrue($data['dex']['flags']['is_shiny']);
        $this->assertTrue($data['dex']['flags']['is_private']);
        $this->assertTrue($data['dex']['flags']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNull($data['dex']['region']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230321.123456', $data['dex']['version']);
        $this->assertTrue($data['dex']['flags']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumData::getExpectedHomeShinyNestedContent(),
            $pokemons
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 11, 0, 0, 0, 11);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 11, 0, 0, 0, 11);
    }

    public function testListHomePoGo(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home_pogo');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data['dex']);
        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('home_pogo', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('homepogo', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home PoGo', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home PoGo', $data['dex']['french_name']);
        $this->assertArrayHasKey('flags', $data['dex']);
        $this->assertIsArray($data['dex']['flags']);
        $this->assertFalse($data['dex']['flags']['is_shiny']);
        $this->assertFalse($data['dex']['flags']['is_private']);
        $this->assertFalse($data['dex']['flags']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('list-7', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNull($data['dex']['region']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230321.121212', $data['dex']['version']);
        $this->assertFalse($data['dex']['flags']['is_released']);
    }

    public function testListHomeShinyOT(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/homeshinyot');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data['dex']);
        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('homeshinyot', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('homeshiny', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home Shiny OT', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home Chromatique OT', $data['dex']['french_name']);
        $this->assertArrayHasKey('flags', $data['dex']);
        $this->assertIsArray($data['dex']['flags']);
        $this->assertTrue($data['dex']['flags']['is_shiny']);
        $this->assertTrue($data['dex']['flags']['is_private']);
        $this->assertTrue($data['dex']['flags']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNull($data['dex']['region']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230321.123456', $data['dex']['version']);
        $this->assertTrue($data['dex']['flags']['is_released']);
    }

    public function testListMultipleHomePoGo(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/homepogo');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertNull($data['dex']);
        $this->assertArrayHasKey('pokemons', $data);
        $this->assertEmpty($data['pokemons']);

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 0, 0, 0, 0, 0);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 0, 0, 0, 0, 0);
    }

    public function testListNoSlug(): void
    {
        $this->apiRequest('GET', 'album', []);

        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', 'album', ['dex.slug' => '']);

        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', 'album', ['dex.slug' => 'redgreenblueyellow']);

        $this->assertResponseIsNotFound();
    }

    public function testListNoUser(): void
    {
        $this->apiRequest('GET', '/album/home', []);

        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', '/album/home', []);

        $this->assertResponseIsNotFound();
    }
}
```

Save as `tests/src/Integration/Controller/AlbumIndexControllerTest.php`.

- [ ] **Step 2: Run integration tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/AlbumIndexControllerTest.php`

Expected: All tests pass, 0 failures.

---

### Task 7: Run full quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all unit tests**

Run: `make tests-unit`

Expected: All unit tests pass, 0 failures.

- [ ] **Step 2: Run all integration tests**

Run: `make tests-integration`

Expected: All integration tests pass, 0 failures.

- [ ] **Step 3: Run code quality checks**

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
| Replace 7 flat `is_*` booleans with `DexFlagsResponse $flags` in `AlbumDexResponse` | Task 1 |
| `AlbumDexResponseFactory` builds nested flags via `buildFlags()` helper | Task 2 |
| Unit tests for `AlbumDexResponse` updated | Task 3 |
| Unit tests for `AlbumDexResponseFactory` updated, dedicated flags tests added | Task 4 |
| `AlbumIndexResponseFactoryTest` helper updated | Task 5 |
| Integration test `AlbumIndexControllerTest` updated for all 8 dex-checking methods | Task 6 |

**Placeholder scan:** No TBDs, no "implement later", all code blocks are complete.

**Type consistency:**
- `DexFlagsResponse` used in Tasks 1, 2, 3, 4, 5, 6 — same class from `src/DTO/Response/DexFlagsResponse.php`.
- `AlbumDexResponse` updated in Task 1, factory updated in Task 2, unit test updated in Task 3.
- `AlbumDexResponseFactory::buildFlags()` private method created in Task 2, covered in Task 4.
- Integration test in Task 6 checks `$data['dex']['flags']['is_*']` matching serialized property names from `DexFlagsResponse` (using `#[SerializedName]`).
