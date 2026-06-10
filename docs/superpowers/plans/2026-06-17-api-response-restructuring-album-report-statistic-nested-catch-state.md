# API Response Restructuring (AlbumReportStatisticResponse) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor the `detail` items in `AlbumReportResponse` from a flat `{slug, name, french_name, count}` shape to a nested `{catch_state: {slug, name, french_name}, count}` shape, reusing the existing `AlbumCatchStateResponse` DTO.

**Architecture:** Update `AlbumReportStatisticResponse` to hold a nested `AlbumCatchStateResponse` instead of three flat string fields; update `AlbumReportResponseFactory::fromStatistic()` to build that nested object; update all related unit tests, the integration assertion trait, and the Psalm type definition.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer, PHPUnit

---

## Response shape change

**Before** — `report.detail[n]`:
```json
{ "slug": "no", "name": "No", "french_name": "Non", "count": 4 }
```

**After** — `report.detail[n]`:
```json
{ "catch_state": { "slug": "no", "name": "No", "french_name": "Non" }, "count": 4 }
```

This breaking change affects every caller of `GET /album/{trainerId}/{dexSlug}` that reads the `report` or `filtered_report` fields (downstream: pokenini-back Moco fixtures, then pokenini-web).

---

## File Structure

**Modify only (no new files — `AlbumCatchStateResponse` already exists):**
- `src/DTO/Response/AlbumReportStatisticResponse.php` — replace `{slug, name, french_name, count}` with `{catch_state: AlbumCatchStateResponse, count}`
- `src/Factory/AlbumReportResponseFactory.php` — `fromStatistic()` builds `AlbumCatchStateResponse`; add its `use` import
- `tests/src/Unit/DTO/Response/AlbumReportStatisticResponseTest.php` — update for new constructor
- `tests/src/Unit/DTO/Response/AlbumReportResponseTest.php` — update `AlbumReportStatisticResponse` construction
- `tests/src/Unit/Factory/AlbumReportResponseFactoryTest.php` — update assertions to `catchState->slug` etc.
- `tests/src/Common/Traits/ReportTrait/AssertReportTrait.php` — check `catch_state` key instead of flat keys
- `tests/src/Common/Types/PokedexTypes.php` — update `PokedexResponseReport` Psalm type

---

## Tasks

### Task 1: Update AlbumReportStatisticResponse DTO

**Files:**
- Modify: `src/DTO/Response/AlbumReportStatisticResponse.php`

- [ ] **Step 1: Replace the flat fields with a nested `AlbumCatchStateResponse`**

Replace the full content of `src/DTO/Response/AlbumReportStatisticResponse.php` with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumReportStatisticResponse
{
    public function __construct(
        #[SerializedName('catch_state')]
        public readonly AlbumCatchStateResponse $catchState,
        public readonly int $count,
    ) {}
}
```

`AlbumCatchStateResponse` is in the same namespace `App\DTO\Response` — no `use` import needed.

- [ ] **Step 2: Verify the file has no syntax errors**

Run: `docker compose exec php php -l src/DTO/Response/AlbumReportStatisticResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/AlbumReportStatisticResponse.php`.

---

### Task 2: Update AlbumReportResponseFactory

**Files:**
- Modify: `src/Factory/AlbumReportResponseFactory.php`

- [ ] **Step 1: Add the `AlbumCatchStateResponse` import and update `fromStatistic()`**

Replace the full content of `src/Factory/AlbumReportResponseFactory.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
use App\DTO\Response\AlbumCatchStateResponse;
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
            catchState: new AlbumCatchStateResponse(
                slug: $statistic->slug,
                name: $statistic->name,
                frenchName: $statistic->frenchName,
            ),
            count: $statistic->count,
        );
    }
}
```

`Statistic` exposes `slug`, `name`, `frenchName`, `count` as public properties (the `#[SerializedName]` attributes on them only affect serialization, not direct access).

- [ ] **Step 2: Verify the file has no syntax errors**

Run: `docker compose exec php php -l src/Factory/AlbumReportResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/AlbumReportResponseFactory.php`.

---

### Task 3: Update unit test for AlbumReportStatisticResponse

**Files:**
- Modify: `tests/src/Unit/DTO/Response/AlbumReportStatisticResponseTest.php`

The current test constructs `AlbumReportStatisticResponse(slug:, name:, frenchName:, count:)` — that constructor no longer exists. Rewrite for the new `(catchState:, count:)` constructor.

- [ ] **Step 1: Replace the test file content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumCatchStateResponse;
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
        $catchState = new AlbumCatchStateResponse(slug: 'yes', name: 'Yes', frenchName: 'Oui');
        $response = new AlbumReportStatisticResponse(
            catchState: $catchState,
            count: 7,
        );

        self::assertSame($catchState, $response->catchState);
        self::assertSame(7, $response->count);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $catchState = new AlbumCatchStateResponse(slug: 'no', name: 'No', frenchName: 'Non');
        $response = new AlbumReportStatisticResponse(
            catchState: $catchState,
            count: 3,
        );

        self::assertSame($catchState, $response->catchState);
        self::assertSame(3, $response->count);
    }
}
```

Save this as `tests/src/Unit/DTO/Response/AlbumReportStatisticResponseTest.php`.

- [ ] **Step 2: Run unit tests for this file**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/AlbumReportStatisticResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 4: Update unit test for AlbumReportResponse

**Files:**
- Modify: `tests/src/Unit/DTO/Response/AlbumReportResponseTest.php`

The test creates `AlbumReportStatisticResponse` using the old flat constructor. Update it to use the new `(catchState:, count:)` constructor.

- [ ] **Step 1: Replace the test file content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumCatchStateResponse;
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
        $catchState = new AlbumCatchStateResponse(slug: 'yes', name: 'Yes', frenchName: 'Oui');
        $statistic = new AlbumReportStatisticResponse(
            catchState: $catchState,
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

Save this as `tests/src/Unit/DTO/Response/AlbumReportResponseTest.php`.

- [ ] **Step 2: Run unit tests for this file**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/AlbumReportResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 5: Update unit test for AlbumReportResponseFactory

**Files:**
- Modify: `tests/src/Unit/Factory/AlbumReportResponseFactoryTest.php`

The test currently asserts flat fields `$detail->slug`, `$detail->name`, `$detail->frenchName`. Replace them with `$detail->catchState->slug` etc.

- [ ] **Step 1: Replace the test file content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
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
    public function fromReportMapsAllTotalsCorrectly(): void
    {
        $stat1 = new Statistic('no', 'No', 'Non', 3);
        $stat2 = new Statistic('yes', 'Yes', 'Oui', 5);
        $report = new Report(10, 5, 2, [$stat1, $stat2]);

        $result = AlbumReportResponseFactory::fromReport($report);

        self::assertSame(10, $result->total);
        self::assertSame(5, $result->totalCaught);
        self::assertSame(2, $result->totalUncaught);
        self::assertCount(2, $result->detail);
    }

    #[Test]
    public function fromReportMapsStatisticCatchStateCorrectly(): void
    {
        $stat = new Statistic('maybe', 'Maybe', 'Peut être', 7);
        $report = new Report(7, 0, 7, [$stat]);

        $result = AlbumReportResponseFactory::fromReport($report);

        $detail = $result->detail[0];
        self::assertInstanceOf(AlbumReportStatisticResponse::class, $detail);
        self::assertSame(7, $detail->count);
        self::assertSame('maybe', $detail->catchState->slug);
        self::assertSame('Maybe', $detail->catchState->name);
        self::assertSame('Peut être', $detail->catchState->frenchName);
    }

    #[Test]
    public function fromReportHandlesEmptyDetail(): void
    {
        $report = new Report(0, 0, 0, []);

        $result = AlbumReportResponseFactory::fromReport($report);

        self::assertSame(0, $result->total);
        self::assertEmpty($result->detail);
    }
}
```

Save this as `tests/src/Unit/Factory/AlbumReportResponseFactoryTest.php`.

- [ ] **Step 2: Run unit tests for this file**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumReportResponseFactoryTest.php`

Expected: 3 tests pass, 0 failures.

---

### Task 6: Update AssertReportTrait to use nested catch_state

**Files:**
- Modify: `tests/src/Common/Traits/ReportTrait/AssertReportTrait.php`

This trait is used by `AlbumIndexControllerTest` to assert API response reports. It currently checks flat `$reportDetail[0]['slug']`, `$reportDetail[0]['name']`, etc. After migration those keys live under `$reportDetail[0]['catch_state']`.

- [ ] **Step 1: Replace the full trait content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\ReportTrait;

/**
 * @psalm-import-type PokedexResponseReport from \App\Tests\Common\Types\PokedexTypes
 */
trait AssertReportTrait
{
    /**
     * @param PokedexResponseReport $report
     */
    protected function assertReport(
        array $report,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes,
        int $countTotal
    ): void {
        $this->assertArrayHasKey('detail', $report);

        $reportDetail = $report['detail'];

        $this->assertArrayHasKey(0, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[0]);
        $this->assertEquals($countNo, $reportDetail[0]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[0]);
        $this->assertEquals('no', $reportDetail[0]['catch_state']['slug']);
        $this->assertEquals('No', $reportDetail[0]['catch_state']['name']);
        $this->assertEquals('Non', $reportDetail[0]['catch_state']['french_name']);

        $this->assertArrayHasKey(1, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[1]);
        $this->assertEquals($countMaybe, $reportDetail[1]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[1]);
        $this->assertEquals('maybe', $reportDetail[1]['catch_state']['slug']);
        $this->assertEquals('Maybe', $reportDetail[1]['catch_state']['name']);
        $this->assertEquals('Peut être', $reportDetail[1]['catch_state']['french_name']);

        $this->assertArrayHasKey(2, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[2]);
        $this->assertEquals($countMaybeNot, $reportDetail[2]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[2]);
        $this->assertEquals('maybenot', $reportDetail[2]['catch_state']['slug']);
        $this->assertEquals('Maybe not', $reportDetail[2]['catch_state']['name']);
        $this->assertEquals('Peut être pas', $reportDetail[2]['catch_state']['french_name']);

        $this->assertArrayHasKey(3, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[3]);
        $this->assertEquals($countYes, $reportDetail[3]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[3]);
        $this->assertEquals('yes', $reportDetail[3]['catch_state']['slug']);
        $this->assertEquals('Yes', $reportDetail[3]['catch_state']['name']);
        $this->assertEquals('Oui', $reportDetail[3]['catch_state']['french_name']);

        $this->assertArrayHasKey('total', $report);
        $this->assertEquals($countTotal, $report['total']);

        $this->assertArrayHasKey('total_caught', $report);
        $this->assertEquals($countYes, $report['total_caught']);

        $this->assertArrayHasKey('total_uncaught', $report);
        $this->assertEquals($countTotal - $countMaybe - $countMaybeNot - $countYes, $report['total_uncaught']);
    }
}
```

Save this as `tests/src/Common/Traits/ReportTrait/AssertReportTrait.php`.

---

### Task 7: Update PokedexTypes Psalm type definition

**Files:**
- Modify: `tests/src/Common/Types/PokedexTypes.php`

The `PokedexResponseReport` Psalm type currently declares `detail` items with flat `slug`, `name`, `french_name` keys at the top level. Update it to reflect the new nested `catch_state` structure.

- [ ] **Step 1: Find and replace the `PokedexResponseReport` block**

In `tests/src/Common/Types/PokedexTypes.php`, locate this block:

```php
 * @psalm-type PokedexResponseReport = array{
 *  detail: array<int, array{
 *      count: int,
 *      slug: string,
 *      name: string,
 *      french_name: string
 *  }>,
 *  total: int,
 *  total_caught: int,
 *  total_uncaught: int
 * }
```

Replace it with:

```php
 * @psalm-type PokedexResponseReport = array{
 *  detail: array<int, array{
 *      count: int,
 *      catch_state: array{
 *          slug: string,
 *          name: string,
 *          french_name: string
 *      }
 *  }>,
 *  total: int,
 *  total_caught: int,
 *  total_uncaught: int
 * }
```

---

### Task 8: Run full quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all unit tests**

Run: `make tests-unit`

Expected: All unit tests pass, 0 failures.

- [ ] **Step 2: Run all integration tests**

Run: `make tests-integration`

Expected: All integration tests pass, 0 failures. In particular, `AlbumIndexControllerTest` (which uses `AssertReportTrait`) must pass with the updated nested structure.

- [ ] **Step 3: Run code quality checks**

Run: `make quality`

Expected: All quality checks green (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint).

- [ ] **Step 4: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% line coverage and 100% MSI for all modified classes.

- [ ] **Step 5: Document completion**

Summary of changes:
- ✅ `AlbumReportStatisticResponse` — replaced flat `{slug, name, french_name, count}` with `{catchState: AlbumCatchStateResponse, count}`
- ✅ `AlbumReportResponseFactory` — `fromStatistic()` builds nested `AlbumCatchStateResponse`
- ✅ `AlbumReportStatisticResponseTest` — rewritten for new constructor
- ✅ `AlbumReportResponseTest` — updated statistic construction
- ✅ `AlbumReportResponseFactoryTest` — assertions navigate `catchState` property
- ✅ `AssertReportTrait` — checks `catch_state` key in integration assertions
- ✅ `PokedexTypes` — Psalm type updated to nested shape
- ✅ All quality gates passing (`make quality`, `make measures`)

---

## Next Steps (not in this plan)

This is a breaking change on `GET /album/{trainerId}/{dexSlug}`. Downstream repos must be updated:

1. **pokenini-back** — update Moco fixtures in `tests/resources/moco/` for any fixture that mocks the album endpoint's `report.detail` items; update any BFF code that reads `detail[n].slug` to read `detail[n].catch_state.slug` instead.
2. **pokenini-web** — update Twig templates and any PHP code that accesses `report.detail[n].slug`, `.name`, `.french_name`; update corresponding Moco fixtures in `tests/resources/moco/`.

Follow the workspace-level convention: update pokenini-api first (this plan), then pokenini-back, then pokenini-web.
