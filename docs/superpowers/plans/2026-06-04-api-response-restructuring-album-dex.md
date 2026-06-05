# API Response Restructuring (Album Dex) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor the `dex` field of `GET /album/{trainerExternalId}/{dexSlug}` from a flat raw SQL array to a nested object-oriented structure using DTOs + Factory + Serializer pattern, nesting `region_name`/`region_french_name` into a nested `region` object.

**Architecture:** Create two immutable response DTOs (`AlbumRegionResponse`, `AlbumDexResponse`), a Factory to transform the flat SQL row from `DexRepository::getData()` into a typed DTO, and update `AlbumIndexController` to call the factory before serialization. Update integration tests to assert the new `region` nested structure instead of `region_name`/`region_french_name` flat fields.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/AlbumRegionResponse.php` — immutable DTO for the nested region (name + french_name)
- `src/DTO/Response/AlbumDexResponse.php` — immutable DTO for the album dex, containing `?AlbumRegionResponse $region`
- `src/Factory/AlbumDexResponseFactory.php` — transforms flat SQL row → `AlbumDexResponse`, builds nested region or `null`
- `tests/src/Unit/DTO/Response/AlbumRegionResponseTest.php` — unit tests for `AlbumRegionResponse`
- `tests/src/Unit/DTO/Response/AlbumDexResponseTest.php` — unit tests for `AlbumDexResponse`
- `tests/src/Unit/Factory/AlbumDexResponseFactoryTest.php` — unit tests for `AlbumDexResponseFactory`

**Modify:**
- `src/Controller/AlbumIndexController.php` — use `AlbumDexResponseFactory::fromSqlRow()` for the `dex` field
- `tests/src/Integration/Controller/AlbumIndexControllerTest.php` — replace `region_name`/`region_french_name` flat assertions with nested `region.name`/`region.french_name` assertions

---

## Context

`DexRepository::getData()` returns a single flat SQL row (or empty array `[]` when no dex found) with these fields:

| SQL alias | PHP type | Note |
|-----------|----------|------|
| `slug` | string | COALESCE of trainer slug and dex slug |
| `original_slug` | string | dex slug |
| `name` | string | COALESCE of trainer name and dex name |
| `french_name` | string | COALESCE |
| `is_shiny` | bool | |
| `is_display_form` | bool | |
| `display_template` | string | |
| `region_name` | ?string | NULL when dex has no region |
| `region_french_name` | ?string | NULL when dex has no region |
| `selection_rule` | string | |
| `is_private` | bool | COALESCE with default true |
| `is_on_home` | bool | COALESCE with default false |
| `description` | string | |
| `french_description` | string | |
| `version` | string | formatted date |
| `is_released` | bool | |
| `is_premium` | bool | |
| `is_custom` | bool | |

`AlbumDexService::get()` wraps this repository call and returns `$data[0] ?? []`. When no dex is found, it returns `[]`.

The controller currently serializes `$dex` as-is: `'dex' => $dex`. When empty, JSON becomes `"dex": []`. After this migration, `"dex"` will be an `AlbumDexResponse` object or `null` (when empty). `assertEmpty(null)` passes in PHPUnit so the existing `testListMultipleHomePoGo` test needs no change.

---

## Tasks

### Task 1: Create AlbumRegionResponse DTO

**Files:**
- Create: `src/DTO/Response/AlbumRegionResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumRegionResponse
{
    public function __construct(
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
    ) {}
}
```

Save as `src/DTO/Response/AlbumRegionResponse.php`.

---

### Task 2: Create AlbumDexResponse DTO

**Files:**
- Create: `src/DTO/Response/AlbumDexResponse.php`

- [ ] **Step 1: Create the DTO file**

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
        public readonly ?AlbumRegionResponse $region,
        #[SerializedName('selection_rule')]
        public readonly string $selectionRule,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        public readonly string $version,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        #[SerializedName('is_custom')]
        public readonly bool $isCustom,
    ) {}
}
```

Save as `src/DTO/Response/AlbumDexResponse.php`.

---

### Task 3: Write unit tests for AlbumRegionResponse DTO

**Files:**
- Create: `tests/src/Unit/DTO/Response/AlbumRegionResponseTest.php`
- Test: `AlbumRegionResponse`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumRegionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumRegionResponse::class)]
final class AlbumRegionResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new AlbumRegionResponse(
            name: 'Kanto',
            frenchName: 'Kanto',
        );

        self::assertSame('Kanto', $response->name);
        self::assertSame('Kanto', $response->frenchName);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumRegionResponse(
            name: 'Johto',
            frenchName: 'Johto',
        );

        self::assertSame('Johto', $response->name);
        self::assertSame('Johto', $response->frenchName);
    }
}
```

Save as `tests/src/Unit/DTO/Response/AlbumRegionResponseTest.php`.

---

### Task 4: Write unit tests for AlbumDexResponse DTO

**Files:**
- Create: `tests/src/Unit/DTO/Response/AlbumDexResponseTest.php`
- Test: `AlbumDexResponse`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumRegionResponse;
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

        $response = new AlbumDexResponse(
            slug: 'redgreenblueyellow',
            originalSlug: 'redgreenblueyellow',
            name: 'Red / Green / Blue / Yellow',
            frenchName: 'Rouge / Vert / Bleu / Jaune',
            isShiny: false,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: true,
            displayTemplate: 'box',
            region: $region,
            selectionRule: '(p.bankable or p.bankableish) and ba?.redgreenblueyellow',
            description: 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            frenchDescription: 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            version: '20230221.085100',
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('redgreenblueyellow', $response->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $response->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $response->frenchName);
        self::assertFalse($response->isShiny);
        self::assertFalse($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
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
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }

    #[Test]
    public function constructorAcceptsNullRegion(): void
    {
        $response = new AlbumDexResponse(
            slug: 'home',
            originalSlug: 'home',
            name: 'Home',
            frenchName: 'Home',
            isShiny: false,
            isPrivate: true,
            isOnHome: false,
            isDisplayForm: true,
            displayTemplate: 'box',
            region: null,
            selectionRule: '',
            description: '',
            frenchDescription: '',
            version: '20230421.123456',
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        self::assertNull($response->region);
        self::assertTrue($response->isPrivate);
    }
}
```

Save as `tests/src/Unit/DTO/Response/AlbumDexResponseTest.php`.

---

### Task 5: Create AlbumDexResponseFactory

**Files:**
- Create: `src/Factory/AlbumDexResponseFactory.php`

- [ ] **Step 1: Create the factory file**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumRegionResponse;

final class AlbumDexResponseFactory
{
    /**
     * @param array<string, mixed> $row
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

        /** @var scalar $selectionRule */
        $selectionRule = $row['selection_rule'];

        /** @var scalar $description */
        $description = $row['description'];

        /** @var scalar $frenchDescription */
        $frenchDescription = $row['french_description'];

        /** @var scalar $version */
        $version = $row['version'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $isCustom */
        $isCustom = $row['is_custom'];

        return new AlbumDexResponse(
            slug: (string) $slug,
            originalSlug: (string) $originalSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            isShiny: (bool) $isShiny,
            isPrivate: (bool) $isPrivate,
            isOnHome: (bool) $isOnHome,
            isDisplayForm: (bool) $isDisplayForm,
            displayTemplate: (string) $displayTemplate,
            region: self::buildRegion($row),
            selectionRule: (string) $selectionRule,
            description: (string) $description,
            frenchDescription: (string) $frenchDescription,
            version: (string) $version,
            isReleased: (bool) $isReleased,
            isPremium: (bool) $isPremium,
            isCustom: (bool) $isCustom,
        );
    }

    /**
     * @param array<string, mixed> $row
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

---

### Task 6: Write unit tests for AlbumDexResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/AlbumDexResponseFactoryTest.php`
- Test: `AlbumDexResponseFactory`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumRegionResponse;
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

        self::assertInstanceOf(AlbumDexResponse::class, $result);
        self::assertSame('redgreenblueyellow', $result->slug);
        self::assertSame('redgreenblueyellow', $result->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $result->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $result->frenchName);
        self::assertFalse($result->isShiny);
        self::assertFalse($result->isPrivate);
        self::assertFalse($result->isOnHome);
        self::assertTrue($result->isDisplayForm);
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
        self::assertTrue($result->isReleased);
        self::assertFalse($result->isPremium);
        self::assertFalse($result->isCustom);
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
        self::assertFalse($result->isShiny);
        self::assertTrue($result->isPrivate);
        self::assertFalse($result->isOnHome);
        self::assertTrue($result->isDisplayForm);
        self::assertSame('202', $result->displayTemplate);
        self::assertSame('303', $result->selectionRule);
        self::assertSame('404', $result->description);
        self::assertSame('505', $result->frenchDescription);
        self::assertSame('606', $result->version);
        self::assertTrue($result->isReleased);
        self::assertFalse($result->isPremium);
        self::assertFalse($result->isCustom);
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

---

### Task 7: Update AlbumIndexController to use AlbumDexResponseFactory

**Files:**
- Modify: `src/Controller/AlbumIndexController.php`

- [ ] **Step 1: Read the current controller**

Current content of `src/Controller/AlbumIndexController.php`:

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

- [ ] **Step 2: Replace `'dex' => $dex` with the factory call**

Replace the file with:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumFilter\AlbumFiltersRequest;
use App\Factory\AlbumDexResponseFactory;
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
                    'dex' => empty($dex) ? null : AlbumDexResponseFactory::fromSqlRow($dex),
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

The only change is:
- Added `use App\Factory\AlbumDexResponseFactory;`
- Changed `'dex' => $dex` to `'dex' => empty($dex) ? null : AlbumDexResponseFactory::fromSqlRow($dex)`

---

### Task 8: Update AlbumIndexControllerTest integration tests

**Files:**
- Modify: `tests/src/Integration/Controller/AlbumIndexControllerTest.php`

The existing test methods assert `region_name` and `region_french_name` as flat keys under `$data['dex']`. These must be replaced with a nested `region` structure.

**Mapping of changes:**

For dexes **with** a region (redgreenblueyellow → Kanto, goldsilvercrystal → Johto):

Before:
```php
$this->assertArrayHasKey('region_name', $data['dex']);
$this->assertEquals('Kanto', $data['dex']['region_name']);
$this->assertArrayHasKey('region_french_name', $data['dex']);
$this->assertEquals('Kanto', $data['dex']['region_french_name']);
```

After:
```php
$this->assertArrayHasKey('region', $data['dex']);
$this->assertNotNull($data['dex']['region']);
$this->assertEquals('Kanto', $data['dex']['region']['name']);
$this->assertEquals('Kanto', $data['dex']['region']['french_name']);
```

For dexes **without** a region (home, home_shiny, home_pogo, homeshinyot):

Before:
```php
$this->assertArrayHasKey('region_name', $data['dex']);
$this->assertNull($data['dex']['region_name']);
$this->assertArrayHasKey('region_french_name', $data['dex']);
$this->assertNull($data['dex']['region_french_name']);
```

After:
```php
$this->assertArrayHasKey('region', $data['dex']);
$this->assertNull($data['dex']['region']);
```

The `testListMultipleHomePoGo` test only calls `$this->assertEmpty($data['dex'])` — no change needed since `assertEmpty(null)` passes.

- [ ] **Step 1: Replace complete file with updated content**

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

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertFalse($data['dex']['is_private']);
        $this->assertArrayHasKey('is_on_home', $data['dex']);
        $this->assertFalse($data['dex']['is_on_home']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNotNull($data['dex']['region']);
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
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

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

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('goldsilvercrystal', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('goldsilvercrystal', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Gold / Silver / Crystal', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Or / Argent / Cristal', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_on_home', $data['dex']);
        $this->assertFalse($data['dex']['is_on_home']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNotNull($data['dex']['region']);
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
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertFalse($data['dex']['is_released']);

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

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_on_home', $data['dex']);
        $this->assertFalse($data['dex']['is_on_home']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNotNull($data['dex']['region']);
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
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

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

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_on_home', $data['dex']);
        $this->assertFalse($data['dex']['is_on_home']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNotNull($data['dex']['region']);
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
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

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

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('home', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('home', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
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
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

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

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('home_shiny', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('homeshiny', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals("Home\nShiny", $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals("Home\nChromatique", $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertTrue($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
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
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

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

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('home_pogo', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('homepogo', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home PoGo', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home PoGo', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertFalse($data['dex']['is_private']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertFalse($data['dex']['is_display_form']);
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
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertFalse($data['dex']['is_released']);
    }

    public function testListHomeShinyOT(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/homeshinyot');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('homeshinyot', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('homeshiny', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home Shiny OT', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home Chromatique OT', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertTrue($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
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
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);
    }

    public function testListMultipleHomePoGo(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/homepogo');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertEmpty($data['dex']);
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

---

## Self-Review

**Spec coverage:**

| Requirement | Implemented |
|-------------|-------------|
| Create `AlbumRegionResponse` DTO | Task 1 |
| Create `AlbumDexResponse` DTO with nested `?AlbumRegionResponse $region` | Task 2 |
| Unit test `AlbumRegionResponse` | Task 3 |
| Unit test `AlbumDexResponse` | Task 4 |
| Create `AlbumDexResponseFactory::fromSqlRow()` with null-region branch | Task 5 |
| Unit test factory: happy path, null region, type casts, region casts | Task 6 |
| Update controller: `empty($dex) ? null : AlbumDexResponseFactory::fromSqlRow($dex)` | Task 7 |
| Update integration tests: `region` nested vs old `region_name`/`region_french_name` flat | Task 8 |

**Placeholder scan:** No TBD, TODO, or "similar to task N" references found.

**Type consistency:**
- `AlbumDexResponse::$region` is `?AlbumRegionResponse` throughout
- Factory `buildRegion()` returns `?AlbumRegionResponse`
- Controller uses `empty($dex) ? null : AlbumDexResponseFactory::fromSqlRow($dex)` — type is `AlbumDexResponse|null`

---

## Execution Options

**Plan complete and saved to `docs/superpowers/plans/2026-06-04-api-response-restructuring-album-dex.md`.**

**Two execution options:**

**1. Subagent-Driven (recommended)** — fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** — execute tasks in this session using executing-plans, batch execution with checkpoints

**Which approach?**
