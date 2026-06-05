# API Response Restructuring (Album Index) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /album/{trainerExternalId}/{dexSlug}` to return a fully typed `AlbumIndexResponse` DTO instead of a raw PHP array, and migrate the `report`/`filtered_report` fields from the internal `AlbumReport\Report` DTO to proper Response DTOs.

**Architecture:** Create three new immutable Response DTOs (`AlbumReportStatisticResponse`, `AlbumReportResponse`, `AlbumIndexResponse`), two Factories (`AlbumReportResponseFactory`, `AlbumIndexResponseFactory`), and update `AlbumIndexController` to assemble the full response through the new factories.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Context

The `AlbumIndexController` was partially migrated in two previous plans:
- `2026-06-02-api-response-restructuring-album-pokemons.md`: migrated the `pokemons` field.
- `2026-06-04-api-response-restructuring-album-dex.md`: migrated the `dex` field.

What remains unmigrated:
1. The outer response is still a raw PHP array `['dex' => ..., 'pokemons' => ..., 'report' => ..., 'filtered_report' => ...]`.
2. The `report` and `filtered_report` fields use `App\DTO\AlbumReport\Report` (internal service DTO in a non-Response namespace, with mutable properties and a mutation method `increment()`).

---

## File Structure

**Create:**
- `src/DTO/Response/AlbumReportStatisticResponse.php` — readonly Response DTO for a single catch-state statistic row
- `src/DTO/Response/AlbumReportResponse.php` — readonly Response DTO for an album report (total, totalCaught, totalUncaught, detail[])
- `src/DTO/Response/AlbumIndexResponse.php` — readonly Response DTO wrapping the full GET /album response
- `src/Factory/AlbumReportResponseFactory.php` — converts `AlbumReport\Report` → `AlbumReportResponse` (including mapping `Statistic[]` → `AlbumReportStatisticResponse[]`)
- `src/Factory/AlbumIndexResponseFactory.php` — assembles `AlbumIndexResponse` from already-converted Response DTOs
- `tests/src/Unit/DTO/Response/AlbumReportStatisticResponseTest.php`
- `tests/src/Unit/DTO/Response/AlbumReportResponseTest.php`
- `tests/src/Unit/DTO/Response/AlbumIndexResponseTest.php`
- `tests/src/Unit/Factory/AlbumReportResponseFactoryTest.php`
- `tests/src/Unit/Factory/AlbumIndexResponseFactoryTest.php`

**Modify:**
- `src/Controller/AlbumIndexController.php` — use `AlbumReportResponseFactory` and `AlbumIndexResponseFactory`, return serialized `AlbumIndexResponse`

---

## Tasks

### Task 1: Create AlbumReportStatisticResponse DTO

**Files:**
- Create: `src/DTO/Response/AlbumReportStatisticResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumReportStatisticResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly int $count,
    ) {}
}
```

Save to `src/DTO/Response/AlbumReportStatisticResponse.php`.

- [ ] **Step 2: Write unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumReportStatisticResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumReportStatisticResponse::class)]
final class AlbumReportStatisticResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new AlbumReportStatisticResponse(
            slug: 'yes',
            name: 'Yes',
            frenchName: 'Oui',
            count: 7,
        );

        self::assertSame('yes', $response->slug);
        self::assertSame('Yes', $response->name);
        self::assertSame('Oui', $response->frenchName);
        self::assertSame(7, $response->count);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumReportStatisticResponse(
            slug: 'no',
            name: 'No',
            frenchName: 'Non',
            count: 3,
        );

        self::assertSame('no', $response->slug);
        self::assertSame('No', $response->name);
        self::assertSame('Non', $response->frenchName);
        self::assertSame(3, $response->count);
    }
}
```

Save to `tests/src/Unit/DTO/Response/AlbumReportStatisticResponseTest.php`.

---

### Task 2: Create AlbumReportResponse DTO

**Files:**
- Create: `src/DTO/Response/AlbumReportResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumReportResponse
{
    /**
     * @param AlbumReportStatisticResponse[] $detail
     */
    public function __construct(
        public readonly int $total,
        #[SerializedName('total_caught')]
        public readonly int $totalCaught,
        #[SerializedName('total_uncaught')]
        public readonly int $totalUncaught,
        public readonly array $detail,
    ) {}
}
```

Save to `src/DTO/Response/AlbumReportResponse.php`.

- [ ] **Step 2: Write unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumReportResponse;
use App\DTO\Response\AlbumReportStatisticResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumReportResponse::class)]
final class AlbumReportResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $statistic = new AlbumReportStatisticResponse(
            slug: 'yes',
            name: 'Yes',
            frenchName: 'Oui',
            count: 5,
        );
        $response = new AlbumReportResponse(
            total: 10,
            totalCaught: 5,
            totalUncaught: 3,
            detail: [$statistic],
        );

        self::assertSame(10, $response->total);
        self::assertSame(5, $response->totalCaught);
        self::assertSame(3, $response->totalUncaught);
        self::assertSame([$statistic], $response->detail);
    }

    #[Test]
    public function constructorAcceptsEmptyDetail(): void
    {
        $response = new AlbumReportResponse(
            total: 0,
            totalCaught: 0,
            totalUncaught: 0,
            detail: [],
        );

        self::assertSame(0, $response->total);
        self::assertSame(0, $response->totalCaught);
        self::assertSame(0, $response->totalUncaught);
        self::assertSame([], $response->detail);
    }
}
```

Save to `tests/src/Unit/DTO/Response/AlbumReportResponseTest.php`.

---

### Task 3: Create AlbumIndexResponse DTO

**Files:**
- Create: `src/DTO/Response/AlbumIndexResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumIndexResponse
{
    /**
     * @param AlbumPokemonResponse[] $pokemons
     */
    public function __construct(
        public readonly ?AlbumDexResponse $dex,
        public readonly array $pokemons,
        public readonly AlbumReportResponse $report,
        #[SerializedName('filtered_report')]
        public readonly AlbumReportResponse $filteredReport,
    ) {}
}
```

Save to `src/DTO/Response/AlbumIndexResponse.php`.

- [ ] **Step 2: Write unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumIndexResponse;
use App\DTO\Response\AlbumReportResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumIndexResponse::class)]
final class AlbumIndexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $dex = new AlbumDexResponse(
            slug: 'national',
            originalSlug: 'national',
            name: 'National',
            frenchName: 'National',
            isShiny: false,
            isPrivate: false,
            isOnHome: true,
            isDisplayForm: false,
            displayTemplate: 'list',
            region: null,
            selectionRule: '',
            description: 'Test dex',
            frenchDescription: 'Dex de test',
            version: '1.0',
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );
        $report = new AlbumReportResponse(total: 10, totalCaught: 5, totalUncaught: 3, detail: []);
        $filteredReport = new AlbumReportResponse(total: 5, totalCaught: 2, totalUncaught: 2, detail: []);

        $response = new AlbumIndexResponse(
            dex: $dex,
            pokemons: [],
            report: $report,
            filteredReport: $filteredReport,
        );

        self::assertSame($dex, $response->dex);
        self::assertSame([], $response->pokemons);
        self::assertSame($report, $response->report);
        self::assertSame($filteredReport, $response->filteredReport);
    }

    #[Test]
    public function constructorAcceptsNullDex(): void
    {
        $report = new AlbumReportResponse(total: 0, totalCaught: 0, totalUncaught: 0, detail: []);
        $filteredReport = new AlbumReportResponse(total: 1, totalCaught: 0, totalUncaught: 1, detail: []);

        $response = new AlbumIndexResponse(
            dex: null,
            pokemons: [],
            report: $report,
            filteredReport: $filteredReport,
        );

        self::assertNull($response->dex);
        self::assertSame([], $response->pokemons);
        self::assertSame($report, $response->report);
        self::assertSame($filteredReport, $response->filteredReport);
    }
}
```

Save to `tests/src/Unit/DTO/Response/AlbumIndexResponseTest.php`.

---

### Task 4: Create AlbumReportResponseFactory

**Files:**
- Create: `src/Factory/AlbumReportResponseFactory.php`

- [ ] **Step 1: Create the factory**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
use App\DTO\Response\AlbumReportResponse;
use App\DTO\Response\AlbumReportStatisticResponse;

final class AlbumReportResponseFactory
{
    public static function fromReport(Report $report): AlbumReportResponse
    {
        return new AlbumReportResponse(
            total: $report->total,
            totalCaught: $report->totalCaught,
            totalUncaught: $report->totalUncaught,
            detail: array_map(self::fromStatistic(...), $report->detail),
        );
    }

    private static function fromStatistic(Statistic $statistic): AlbumReportStatisticResponse
    {
        return new AlbumReportStatisticResponse(
            slug: $statistic->slug,
            name: $statistic->name,
            frenchName: $statistic->frenchName,
            count: $statistic->count,
        );
    }
}
```

Save to `src/Factory/AlbumReportResponseFactory.php`.

- [ ] **Step 2: Write unit tests**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
use App\DTO\Response\AlbumReportResponse;
use App\DTO\Response\AlbumReportStatisticResponse;
use App\Factory\AlbumReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumReportResponseFactory::class)]
final class AlbumReportResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromReport_mapsAllTotalsCorrectly(): void
    {
        $stat1 = new Statistic('no', 'No', 'Non', 3);
        $stat2 = new Statistic('yes', 'Yes', 'Oui', 5);
        $report = new Report(10, 5, 2, [$stat1, $stat2]);

        $result = AlbumReportResponseFactory::fromReport($report);

        self::assertInstanceOf(AlbumReportResponse::class, $result);
        self::assertSame(10, $result->total);
        self::assertSame(5, $result->totalCaught);
        self::assertSame(2, $result->totalUncaught);
        self::assertCount(2, $result->detail);
    }

    #[Test]
    public function fromReport_mapsStatisticFieldsCorrectly(): void
    {
        $stat = new Statistic('maybe', 'Maybe', 'Peut être', 7);
        $report = new Report(7, 0, 7, [$stat]);

        $result = AlbumReportResponseFactory::fromReport($report);

        $detail = $result->detail[0];
        self::assertInstanceOf(AlbumReportStatisticResponse::class, $detail);
        self::assertSame('maybe', $detail->slug);
        self::assertSame('Maybe', $detail->name);
        self::assertSame('Peut être', $detail->frenchName);
        self::assertSame(7, $detail->count);
    }

    #[Test]
    public function fromReport_handlesEmptyDetail(): void
    {
        $report = new Report(0, 0, 0, []);

        $result = AlbumReportResponseFactory::fromReport($report);

        self::assertSame(0, $result->total);
        self::assertIsArray($result->detail);
        self::assertEmpty($result->detail);
    }
}
```

Save to `tests/src/Unit/Factory/AlbumReportResponseFactoryTest.php`.

---

### Task 5: Create AlbumIndexResponseFactory

**Files:**
- Create: `src/Factory/AlbumIndexResponseFactory.php`

Deptrac rule `AppFactory: - AppDTO` allows factory → DTO but not factory → factory. This factory takes already-converted Response DTOs and assembles the final wrapper. The conversion of raw SQL rows and internal DTOs into Response DTOs happens in the controller via the existing factories.

- [ ] **Step 1: Create the factory**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumIndexResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumReportResponse;

final class AlbumIndexResponseFactory
{
    /**
     * @param AlbumPokemonResponse[] $pokemons
     */
    public static function fromParts(
        ?AlbumDexResponse $dex,
        array $pokemons,
        AlbumReportResponse $report,
        AlbumReportResponse $filteredReport,
    ): AlbumIndexResponse {
        return new AlbumIndexResponse(
            dex: $dex,
            pokemons: $pokemons,
            report: $report,
            filteredReport: $filteredReport,
        );
    }
}
```

Save to `src/Factory/AlbumIndexResponseFactory.php`.

- [ ] **Step 2: Write unit tests**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumIndexResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumReportResponse;
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
    public function fromParts_withNonNullDex_mapsAllParts(): void
    {
        $dex = $this->buildAlbumDexResponse();
        $pokemons = [$this->buildAlbumPokemonResponse()];
        $report = new AlbumReportResponse(total: 10, totalCaught: 5, totalUncaught: 3, detail: []);
        $filteredReport = new AlbumReportResponse(total: 5, totalCaught: 2, totalUncaught: 2, detail: []);

        $result = AlbumIndexResponseFactory::fromParts($dex, $pokemons, $report, $filteredReport);

        self::assertInstanceOf(AlbumIndexResponse::class, $result);
        self::assertSame($dex, $result->dex);
        self::assertSame($pokemons, $result->pokemons);
        self::assertSame($report, $result->report);
        self::assertSame($filteredReport, $result->filteredReport);
    }

    #[Test]
    public function fromParts_withNullDex_setsNullDex(): void
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
            isShiny: false,
            isPrivate: false,
            isOnHome: true,
            isDisplayForm: false,
            displayTemplate: 'list',
            region: null,
            selectionRule: '',
            description: '',
            frenchDescription: '',
            version: '1.0',
            isReleased: true,
            isPremium: false,
            isCustom: false,
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
            categoryForm: null,
            regionalForm: null,
            specialForm: null,
            variantForm: null,
            primaryType: null,
            secondaryType: null,
        );
    }
}
```

Save to `tests/src/Unit/Factory/AlbumIndexResponseFactoryTest.php`.

---

### Task 6: Update AlbumIndexController

**Files:**
- Modify: `src/Controller/AlbumIndexController.php`

- [ ] **Step 1: Read the current controller**

Current content of `src/Controller/AlbumIndexController.php` (end of `index()` method):

```php
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
```

- [ ] **Step 2: Replace controller with updated version**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumFilter\AlbumFiltersRequest;
use App\Factory\AlbumDexResponseFactory;
use App\Factory\AlbumIndexResponseFactory;
use App\Factory\AlbumPokemonResponseFactory;
use App\Factory\AlbumReportResponseFactory;
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

        $response = AlbumIndexResponseFactory::fromParts(
            dex: empty($dex) ? null : AlbumDexResponseFactory::fromSqlRow($dex),
            pokemons: AlbumPokemonResponseFactory::fromSqlRows($pokemons),
            report: AlbumReportResponseFactory::fromReport($report),
            filteredReport: AlbumReportResponseFactory::fromReport($filteredReport),
        );

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }
}
```

---

### Task 7: Final validation checklist

- [ ] **Step 1: Verify all new files exist**

Run: `ls -la src/DTO/Response/AlbumReportStatisticResponse.php src/DTO/Response/AlbumReportResponse.php src/DTO/Response/AlbumIndexResponse.php src/Factory/AlbumReportResponseFactory.php src/Factory/AlbumIndexResponseFactory.php`

Expected: 5 files exist.

- [ ] **Step 2: Run unit tests**

Run: `make tests-unit`

Expected: All pass.

- [ ] **Step 3: Run integration tests**

Run: `make tests-integration`

Expected: AlbumIndexControllerTest still passes (JSON structure is identical, just typed differently).

- [ ] **Step 4: Run quality checks**

Run: `make quality`

Expected: PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint all green.

- [ ] **Step 5: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% coverage, 100% MSI.
