# Report par dex sur `GET /dex/{trainerExternalId}/list` — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a per-dex `report` (total/caught/uncaught/detail) field to `GET /dex/{trainerExternalId}/list` in pokenini-api computed via two batched SQL queries (not per-dex), fix the pokenini-back cache invalidation gap this introduces, and surface a progress badge on pokenini-web's dex-list page.

**Architecture:** Two new repository methods grouped by `dex_slug` (`DexAvailabilitiesRepository::getBatchedTotal`, `PokedexRepository::getBatchedCatchStatesCounts`) feed a new `AlbumReportService::getBatch()` that assembles one `Report` per dex. `DexController::list()` wires this into `TrainerDexResponse.report`. pokenini-back's `ModifyTrainerAlbumService` gets a missing cache-invalidation call so the now-report-carrying `/dex/list` cache entry doesn't go stale after a catch. pokenini-web's `DexListItem` gets a nullable `report` property and the dex-list template renders a badge when present.

**Tech Stack:** Symfony 8 / PHP 8.5, PostgreSQL (Doctrine DBAL raw SQL), PHPUnit with Alice fixtures (pokenini-api), PHPUnit with Moco-mocked back client (pokenini-web).

**Spec:** `docs/superpowers/specs/2026-07-09-album-dex-list-report-design.md`

## Global Constraints

- `declare(strict_types=1)` in every PHP file touched.
- Test classes carry `/** @internal */` and `#[CoversClass(TargetClass::class)]`.
- 100% code coverage and 100% Mutation Score Index — every new branch needs a test that would fail if the branch were removed/inverted.
- PHPStan level 9 and Psalm strict — no untyped properties or return types; use precise array-shape docblocks (`@return array<int, array{dex_slug: string, total: int}>` etc.) exactly as shown in each step.
- Deptrac layering: `AppController → AppService → AppRepository` is the allowed chain (already confirmed: `AlbumReportService` depending on `PokedexRepository` + `DexAvailabilitiesRepository`, and `DexController` depending on `AlbumReportService`, are both already-valid edges in this codebase).
- `final` for Controller/DTO; non-`final` for Service/Repository (unchanged by this plan — no new classes are created).
- Docker-only toolchain: every command below runs via `docker compose exec php ...` from inside the relevant repo directory (`make sh` to get a shell, or `make sf c="..."` / direct `docker compose exec php php vendor/bin/phpunit ...`).

---

## pokenini-api (`/home/renaud/projects/pokenini-api`)

### Task 1: `DexAvailabilitiesRepository::getBatchedTotal()`

**Files:**
- Modify: `src/Repository/DexAvailabilitiesRepository.php`
- Test: `tests/src/Integration/Repository/DexAvailabilitiesRepositoryTest.php`

**Interfaces:**
- Produces: `DexAvailabilitiesRepository::getBatchedTotal(string $trainerExternalId): array<int, array{dex_slug: string, total: int}>` — one row per (non-deleted) base dex, `dex_slug` resolved to the trainer's effective slug override (`COALESCE(NULLIF(td.slug, ''), d.slug)`), `total` = distinct pokemon count available in that dex. No `AlbumFilters` — this is the unfiltered overview total, mirroring `getTotal()` without the filter-related joins.

- [ ] **Step 1: Write the failing tests**

Add these two methods to `tests/src/Integration/Repository/DexAvailabilitiesRepositoryTest.php` (inside the existing `DexAvailabilitiesRepositoryTest` class, after `testGetTotalFilters`/`providerGetTotalFilters`, before the closing `}`):

```php
    public function testGetBatchedTotal(): void
    {
        $repo = self::getContainer()->get(DexAvailabilitiesRepository::class);

        $rows = $repo->getBatchedTotal('7b52009b64fd0a2a49e6d8a939753077792b0554');

        $byDexSlug = [];
        foreach ($rows as $row) {
            $byDexSlug[$row['dex_slug']] = $row['total'];
        }

        $this->assertSame(22, $byDexSlug['home']);
        $this->assertSame(9, $byDexSlug['goldsilvercrystal']);
        $this->assertSame(11, $byDexSlug['home_shiny']);
    }

    public function testGetBatchedTotalDifferentTrainer(): void
    {
        $repo = self::getContainer()->get(DexAvailabilitiesRepository::class);

        $rows = $repo->getBatchedTotal('bd307a3ec329e10a2cff8fb87480823da114f8f4');

        $byDexSlug = [];
        foreach ($rows as $row) {
            $byDexSlug[$row['dex_slug']] = $row['total'];
        }

        $this->assertSame(22, $byDexSlug['home']);
    }
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit --filter testGetBatchedTotal tests/src/Integration/Repository/DexAvailabilitiesRepositoryTest.php`
Expected: FAIL — `Call to undefined method App\Repository\DexAvailabilitiesRepository::getBatchedTotal()`

- [ ] **Step 3: Implement `getBatchedTotal()`**

Add this method to `src/Repository/DexAvailabilitiesRepository.php`, right after `getTotal()` (before the closing class brace):

```php
    /**
     * @return array<int, array{dex_slug: string, total: int}>
     */
    public function getBatchedTotal(string $trainerExternalId): array
    {
        $sql = <<<'SQL'
            SELECT      COALESCE(NULLIF(td.slug, ''), d.slug) AS dex_slug,
                        COUNT(DISTINCT da.pokemon_id) AS total
            FROM        dex_availability AS da
                    JOIN dex AS d
                        ON da.dex_id = d.id
                    LEFT JOIN trainer_dex AS td
                        ON d.id = td.dex_id AND td.trainer_external_id = :trainer_external_id
            WHERE       d.deleted_at IS NULL
            GROUP BY    COALESCE(NULLIF(td.slug, ''), d.slug)
            SQL;

        /** @var array<int, array{dex_slug: string, total: int}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            ['trainer_external_id' => $trainerExternalId],
            ['trainer_external_id' => ParameterType::STRING],
        );
    }
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit --filter testGetBatchedTotal tests/src/Integration/Repository/DexAvailabilitiesRepositoryTest.php`
Expected: PASS (2 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Repository/DexAvailabilitiesRepository.php tests/src/Integration/Repository/DexAvailabilitiesRepositoryTest.php
git commit -m "feat: add DexAvailabilitiesRepository::getBatchedTotal for per-dex report batching"
```

---

### Task 2: `PokedexRepository::getBatchedCatchStatesCounts()`

**Files:**
- Modify: `src/Repository/PokedexRepository.php`
- Test: `tests/src/Integration/Repository/PokedexRepositoryCatchStateCountTest.php`

**Interfaces:**
- Produces: `PokedexRepository::getBatchedCatchStatesCounts(string $trainerExternalId): array<int, array{dex_slug: string, slug: string, name: string, french_name: string, color: string, count: int}>` — one row per (dex_slug, catch_state) pair across all non-deleted dexes for the trainer, `dex_slug` resolved the same way as Task 1's `getBatchedTotal`, ordered by `dex_slug` then `cs.order_number`.

- [ ] **Step 1: Write the failing test**

Add this method to `tests/src/Integration/Repository/PokedexRepositoryCatchStateCountTest.php` (inside the existing class, after `testGetCatchStatesCounts`, before `testGetCatchStatesCountsFilters`):

```php
    public function testGetBatchedCatchStatesCounts(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getBatchedCatchStatesCounts('7b52009b64fd0a2a49e6d8a939753077792b0554');

        $byDexSlugAndCatchState = [];
        foreach ($counts as $row) {
            $byDexSlugAndCatchState[$row['dex_slug']][$row['slug']] = $row['count'];
        }

        $this->assertEquals(
            ['no' => 9, 'maybe' => 3, 'maybenot' => 3, 'yes' => 7],
            $byDexSlugAndCatchState['home']
        );
        $this->assertEquals(
            ['no' => 11, 'maybe' => 0, 'maybenot' => 0, 'yes' => 0],
            $byDexSlugAndCatchState['home_shiny']
        );
        $this->assertEquals(
            ['no' => 8, 'maybe' => 0, 'maybenot' => 0, 'yes' => 1],
            $byDexSlugAndCatchState['goldsilvercrystal']
        );
    }
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit --filter testGetBatchedCatchStatesCounts tests/src/Integration/Repository/PokedexRepositoryCatchStateCountTest.php`
Expected: FAIL — `Call to undefined method App\Repository\PokedexRepository::getBatchedCatchStatesCounts()`

- [ ] **Step 3: Implement `getBatchedCatchStatesCounts()`**

Add this method to `src/Repository/PokedexRepository.php`, right after `getCatchStatesCounts()` (before `upsert()`):

```php
    /**
     * @return array<int, array{dex_slug: string, slug: string, name: string, french_name: string, color: string, count: int}>
     */
    public function getBatchedCatchStatesCounts(string $trainerExternalId): array
    {
        $sql = <<<'SQL'
            SELECT      COALESCE(NULLIF(td.slug, ''), d.slug) AS dex_slug,
                        cs.slug AS slug, cs.name AS name, cs.french_name AS french_name, cs.color AS color,
                        COUNT(da.id) AS count
            FROM        dex_availability AS da
                    JOIN dex AS d
                        ON da.dex_id = d.id
                    LEFT JOIN trainer_dex AS td
                        ON d.id = td.dex_id AND td.trainer_external_id = :trainer_external_id
                    LEFT JOIN pokedex AS pd
                        ON pd.trainer_dex_id = td.id AND pd.pokemon_id = da.pokemon_id
                    JOIN catch_state AS cs
                        ON cs.id = COALESCE(
                            pd.catch_state_id,
                            (SELECT id FROM catch_state WHERE slug = 'no')
                        )
            WHERE       d.deleted_at IS NULL
                    AND cs.deleted_at IS NULL
            GROUP BY    COALESCE(NULLIF(td.slug, ''), d.slug), cs.slug, cs.name, cs.french_name, cs.color, cs.order_number
            ORDER BY    dex_slug, cs.order_number
            SQL;

        /** @var array<int, array{dex_slug: string, slug: string, name: string, french_name: string, color: string, count: int}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            ['trainer_external_id' => $trainerExternalId],
            ['trainer_external_id' => ParameterType::STRING],
        );
    }
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit --filter testGetBatchedCatchStatesCounts tests/src/Integration/Repository/PokedexRepositoryCatchStateCountTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Repository/PokedexRepository.php tests/src/Integration/Repository/PokedexRepositoryCatchStateCountTest.php
git commit -m "feat: add PokedexRepository::getBatchedCatchStatesCounts for per-dex report batching"
```

---

### Task 3: `AlbumReportService::getBatch()`

**Files:**
- Modify: `src/Service/Album/AlbumReportService.php`
- Test: `tests/src/Integration/Service/Album/AlbumReportServiceTest.php`

**Interfaces:**
- Consumes: `DexAvailabilitiesRepository::getBatchedTotal(string $trainerExternalId): array<int, array{dex_slug: string, total: int}>` (Task 1), `PokedexRepository::getBatchedCatchStatesCounts(string $trainerExternalId): array<int, array{dex_slug: string, slug: string, name: string, french_name: string, color: string, count: int}>` (Task 2).
- Produces: `AlbumReportService::getBatch(string $trainerExternalId): array<string, Report>` — keyed by effective dex slug, one `App\DTO\AlbumReport\Report` per dex, same total/caught/uncaught computation as `get()`.

- [ ] **Step 1: Write the failing test**

Add this method to `tests/src/Integration/Service/Album/AlbumReportServiceTest.php`, right after `testGetReportFiltered`/`providerGetReportFiltered` and before the private `assertReport()` helper:

```php
    public function testGetBatch(): void
    {
        $service = self::getContainer()->get(AlbumReportService::class);

        $reports = $service->getBatch('7b52009b64fd0a2a49e6d8a939753077792b0554');

        $this->assertArrayHasKey('home', $reports);
        $this->assertReport($reports['home'], 9, 3, 3, 7, 22);

        $this->assertArrayHasKey('home_shiny', $reports);
        $this->assertReport($reports['home_shiny'], 11, 0, 0, 0, 11);

        $this->assertArrayHasKey('goldsilvercrystal', $reports);
        $this->assertReport($reports['goldsilvercrystal'], 8, 0, 0, 1, 9);
    }

    public function testGetBatchDifferentTrainer(): void
    {
        $service = self::getContainer()->get(AlbumReportService::class);

        $reports = $service->getBatch('bd307a3ec329e10a2cff8fb87480823da114f8f4');

        $this->assertArrayHasKey('home', $reports);
        $this->assertIsInt($reports['home']->total);
        $this->assertCount(4, $reports['home']->detail);
    }
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit --filter testGetBatch tests/src/Integration/Service/Album/AlbumReportServiceTest.php`
Expected: FAIL — `Call to undefined method App\Service\Album\AlbumReportService::getBatch()`

- [ ] **Step 3: Implement `getBatch()`**

Add this method to `src/Service/Album/AlbumReportService.php`, right after `get()` (before the closing class brace):

```php
    /**
     * @return array<string, Report>
     */
    public function getBatch(string $trainerExternalId): array
    {
        $totals = $this->dexAvailabilitiesRepository->getBatchedTotal($trainerExternalId);
        $catchStatesCounts = $this->pokedexRepository->getBatchedCatchStatesCounts($trainerExternalId);

        $detailRowsByDexSlug = [];
        foreach ($catchStatesCounts as $row) {
            $detailRowsByDexSlug[(string) $row['dex_slug']][] = $row;
        }

        $reports = [];
        foreach ($totals as $totalRow) {
            $dexSlug = (string) $totalRow['dex_slug'];
            $total = (int) $totalRow['total'];
            $totalCaught = 0;
            $totalUncaught = $total;
            $detail = [];

            foreach ($detailRowsByDexSlug[$dexSlug] ?? [] as $row) {
                $detail[] = new Statistic(
                    slug: (string) $row['slug'],
                    name: (string) $row['name'],
                    frenchName: (string) $row['french_name'],
                    color: (string) $row['color'],
                    count: (int) $row['count'],
                );

                if ('yes' === $row['slug']) {
                    $totalCaught = (int) $row['count'];
                }

                if ('no' !== $row['slug']) {
                    $totalUncaught -= (int) $row['count'];
                }
            }

            $reports[$dexSlug] = new Report($total, $totalCaught, $totalUncaught, $detail);
        }

        return $reports;
    }
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit --filter testGetBatch tests/src/Integration/Service/Album/AlbumReportServiceTest.php`
Expected: PASS (2 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Service/Album/AlbumReportService.php tests/src/Integration/Service/Album/AlbumReportServiceTest.php
git commit -m "feat: add AlbumReportService::getBatch to compute per-dex reports in two queries"
```

---

### Task 4: `TrainerDexResponse.report` + `TrainerDexResponseFactory`

**Files:**
- Modify: `src/DTO/Response/TrainerDexResponse.php`
- Modify: `src/Factory/TrainerDexResponseFactory.php`
- Test: `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php`

**Interfaces:**
- Consumes: `App\DTO\AlbumReport\Report` (Task 3's output type), `AlbumReportResponseFactory::fromReport(Report $report): AlbumReportResponse` (already exists, unchanged).
- Produces: `TrainerDexResponse::$report: AlbumReportResponse` (non-nullable). `TrainerDexResponseFactory::fromSqlRow(array $row, Report $report): TrainerDexResponse` (signature changes — second param now required). `TrainerDexResponseFactory::fromSqlRows(array $rows, array $reports): array` (second param `array<string, Report>` now required, keyed by the row's `slug` — the trainer's effective dex slug — not `dex_slug`).

- [ ] **Step 1: Write the failing tests**

Replace the full contents of `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
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
        $report = new Report(22, 7, 9, [
            new Statistic(slug: 'no', name: 'No', frenchName: 'Non', color: '#e57373', count: 9),
        ]);

        $response = TrainerDexResponseFactory::fromSqlRow($row, $report);

        self::assertSame('home', $response->dex->slug);
        self::assertSame('Home', $response->settings->name);
        self::assertSame('Home', $response->settings->frenchName);
        self::assertSame('home', $response->settings->slug);
        self::assertSame('box', $response->settings->displayTemplate);
        self::assertFalse($response->flags->isShiny);
        self::assertTrue($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
        self::assertSame(22, $response->report->total);
        self::assertSame(7, $response->report->totalCaught);
        self::assertSame(9, $response->report->totalUncaught);
        self::assertCount(1, $response->report->detail);
        self::assertSame('no', $response->report->detail[0]->catchState->slug);
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
        $report = new Report(0, 0, 0, []);

        $response = TrainerDexResponseFactory::fromSqlRow($row, $report);

        self::assertSame('123', $response->dex->slug);
        self::assertSame('456', $response->settings->name);
        self::assertSame('789', $response->settings->frenchName);
        self::assertSame('101', $response->settings->slug);
        self::assertSame('202', $response->settings->displayTemplate);
        self::assertFalse($response->flags->isShiny);
        self::assertTrue($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
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
        $reports = [
            'home' => new Report(22, 7, 9, []),
            'home_shiny' => new Report(11, 0, 11, []),
        ];

        $responses = TrainerDexResponseFactory::fromSqlRows($rows, $reports);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(TrainerDexResponse::class, $responses);
        self::assertSame('home', $responses[0]->dex->slug);
        self::assertFalse($responses[0]->flags->isShiny);
        self::assertSame(22, $responses[0]->report->total);
        self::assertSame('homeshiny', $responses[1]->dex->slug);
        self::assertTrue($responses[1]->flags->isShiny);
        self::assertTrue($responses[1]->flags->isCustom);
        self::assertSame(11, $responses[1]->report->total);
    }

    #[Test]
    public function fromSqlRowsFallsBackToEmptyReportWhenMissingFromMap(): void
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
        ];

        $responses = TrainerDexResponseFactory::fromSqlRows($rows, []);

        self::assertSame(0, $responses[0]->report->total);
        self::assertSame(0, $responses[0]->report->totalCaught);
        self::assertSame(0, $responses[0]->report->totalUncaught);
        self::assertSame([], $responses[0]->report->detail);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = TrainerDexResponseFactory::fromSqlRows([], []);

        self::assertCount(0, $responses);
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php`
Expected: FAIL — `Too few arguments to function App\Factory\TrainerDexResponseFactory::fromSqlRow()` and `Undefined property: TrainerDexResponse::$report`

- [ ] **Step 3: Update `TrainerDexResponse`**

Replace the full contents of `src/DTO/Response/TrainerDexResponse.php` with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class TrainerDexResponse
{
    public function __construct(
        public readonly DexSlugResponse $dex,
        public readonly TrainerDexSettingsResponse $settings,
        public readonly DexFlagsResponse $flags,
        public readonly AlbumReportResponse $report,
    ) {}
}
```

- [ ] **Step 4: Update `TrainerDexResponseFactory`**

Replace the full contents of `src/Factory/TrainerDexResponseFactory.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\AlbumReport\Report;
use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\TrainerDexResponse;
use App\DTO\Response\TrainerDexSettingsResponse;

final class TrainerDexResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row, Report $report): TrainerDexResponse
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
            settings: new TrainerDexSettingsResponse(
                name: (string) $name,
                frenchName: (string) $frenchName,
                slug: (string) $slug,
                displayTemplate: (string) $displayTemplate,
            ),
            flags: new DexFlagsResponse(
                isShiny: (bool) $isShiny,
                isPrivate: (bool) $isPrivate,
                isOnHome: (bool) $isOnHome,
                isDisplayForm: (bool) $isDisplayForm,
                isReleased: (bool) $isReleased,
                isPremium: (bool) $isPremium,
                isCustom: (bool) $isCustom,
            ),
            report: AlbumReportResponseFactory::fromReport($report),
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     * @param array<string, Report>                      $reports keyed by the row's effective dex slug (`slug`, not `dex_slug`)
     *
     * @return TrainerDexResponse[]
     */
    public static function fromSqlRows(array $rows, array $reports): array
    {
        return array_map(
            static fn (array $row): TrainerDexResponse => self::fromSqlRow(
                $row,
                $reports[(string) $row['slug']] ?? new Report(0, 0, 0, []),
            ),
            $rows,
        );
    }
}
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php`
Expected: PASS (5 tests)

- [ ] **Step 6: Commit**

```bash
git add src/DTO/Response/TrainerDexResponse.php src/Factory/TrainerDexResponseFactory.php tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php
git commit -m "feat: add report field to TrainerDexResponse and thread it through the factory"
```

---

### Task 5: Wire `DexController::list()` and update the integration test

**Files:**
- Modify: `src/Controller/DexController.php`
- Test: `tests/src/Integration/Controller/DexControllerTest.php`

**Interfaces:**
- Consumes: `AlbumReportService::getBatch(string $trainerExternalId): array<string, Report>` (Task 3), `TrainerDexResponseFactory::fromSqlRows(array $rows, array $reports): array` (Task 4).

- [ ] **Step 1: Write the failing test assertions**

`DexControllerTestData::getUser12Content()` and its siblings assert the full response shape without a `report` key, so a naive `assertEquals` against the raw response will now fail (every entry gains a `report` object). Rather than hand-computing `report` values for every one of the ~20 dex slugs across all 6 test methods (most have no independently-verified expected catch-state distribution), strip the `report` key before comparing against `DexControllerTestData`, and add narrow, separate assertions only for the three dex slugs with known values from Task 3 (`home`, `home_shiny`, `goldsilvercrystal`).

Replace `testListUser12`, `testListUser12WithUnReleased`, `testListUser12WithPremium`, and `testListUser12WithUnreleasedAndPremium` in `tests/src/Integration/Controller/DexControllerTest.php` with:

```php
    public function testListUser12(): void
    {
        $this->apiRequest('GET', '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list');

        $this->assertResponseIsOK();

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUser12Content(),
            $data
        );
        $this->assertKnownReport('home', 9, 3, 3, 7, 22);
        $this->assertKnownReport('home_shiny', 11, 0, 0, 0, 11);
    }

    public function testListUser12WithUnReleased(): void
    {
        $this->apiRequest(
            'GET',
            '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list',
            [
                'include_unreleased_dex' => '1',
            ]
        );

        $this->assertResponseIsOK();

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUser12ContentWithUnreleased(),
            $data
        );
        $this->assertKnownReport('home', 9, 3, 3, 7, 22);
        $this->assertKnownReport('goldsilvercrystal', 8, 0, 0, 1, 9);
    }

    public function testListUser12WithPremium(): void
    {
        $this->apiRequest(
            'GET',
            '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list',
            [
                'include_premium_dex' => '1',
            ]
        );

        $this->assertResponseIsOK();

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUser12ContentWithPremium(),
            $data
        );
        $this->assertKnownReport('home', 9, 3, 3, 7, 22);
    }

    public function testListUser12WithUnreleasedAndPremium(): void
    {
        $this->apiRequest(
            'GET',
            '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list',
            [
                'include_unreleased_dex' => '1',
                'include_premium_dex' => '1',
            ]
        );

        $this->assertResponseIsOK();

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUser12ContentWithUnreleasedAndPremium(),
            $data
        );
        $this->assertKnownReport('home', 9, 3, 3, 7, 22);
        $this->assertKnownReport('goldsilvercrystal', 8, 0, 0, 1, 9);
    }
```

Also replace `testListUser13` and `testListUserUnknown` with versions that assert `report` is present and well-formed without asserting exact numbers this file has no ground truth for:

```php
    public function testListUser13(): void
    {
        $this->apiRequest('GET', '/dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/list');

        $this->assertResponseIsOK();

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUser13Content(),
            $data
        );
        $this->assertReportShapeIsWellFormed();
    }

    public function testListUserUnknown(): void
    {
        $this->apiRequest('GET', '/dex/46546542313186/list');

        $this->assertResponseIsOK();

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUserUnknownContent(),
            $data
        );
        $this->assertReportShapeIsWellFormed();
    }
```

Add these three private helpers to the same class, right before the final closing `}` of `DexControllerTest`:

```php
    /**
     * @return array<int, array<string, array<string, bool>|string|string[]>>
     */
    private function getJsonDecodedResponseContentStrippedOfReports(): array
    {
        /** @var array<int, array<string, mixed>> $data */
        $data = $this->getJsonDecodedResponseContent();

        $this->lastResponseReportsBySlug = [];
        foreach ($data as $index => $entry) {
            /** @var array{report: array<string, mixed>, settings: array{slug: string}} $entry */
            $this->lastResponseReportsBySlug[$entry['settings']['slug']] = $entry['report'];
            unset($data[$index]['report']);
        }

        /** @var array<int, array<string, array<string, bool>|string|string[]>> */
        return $data;
    }

    private function assertKnownReport(
        string $dexSlug,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes,
        int $countTotal
    ): void {
        $this->assertArrayHasKey($dexSlug, $this->lastResponseReportsBySlug);

        /** @var array{total: int, total_caught: int, total_uncaught: int, detail: array<int, array{catch_state: array{slug: string}, count: int}>} $report */
        $report = $this->lastResponseReportsBySlug[$dexSlug];

        $this->assertSame($countTotal, $report['total']);
        $this->assertSame($countYes, $report['total_caught']);
        $this->assertSame($countTotal - $countMaybe - $countMaybeNot - $countYes, $report['total_uncaught']);

        $countsBySlug = [];
        foreach ($report['detail'] as $line) {
            $countsBySlug[$line['catch_state']['slug']] = $line['count'];
        }
        $this->assertEquals(
            ['no' => $countNo, 'maybe' => $countMaybe, 'maybenot' => $countMaybeNot, 'yes' => $countYes],
            $countsBySlug
        );
    }

    private function assertReportShapeIsWellFormed(): void
    {
        $this->assertNotEmpty($this->lastResponseReportsBySlug);

        foreach ($this->lastResponseReportsBySlug as $report) {
            /** @var array{total: int, total_caught: int, total_uncaught: int, detail: array<int, array{catch_state: array{slug: string}, count: int}>} $report */
            $this->assertGreaterThanOrEqual(0, $report['total']);
            $this->assertCount(4, $report['detail']);
        }
    }
```

Add the backing property right after `use GetTrainerDexTrait;`:

```php
    /** @var array<string, array<string, mixed>> */
    private array $lastResponseReportsBySlug = [];
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/DexControllerTest.php`
Expected: FAIL — the raw response has no `report` key stripped yet vs. what the controller currently returns (500 error, since `TrainerDexResponseFactory::fromSqlRows()` now requires a second argument that `DexController::list()` doesn't pass yet)

- [ ] **Step 3: Wire `DexController::list()`**

In `src/Controller/DexController.php`, add the import and constructor parameter:

```php
use App\Service\Album\AlbumReportService;
```

(insert alphabetically among the existing `use` statements, right before `use App\Service\TrainerDexService;`)

Change the constructor:

```php
    public function __construct(
        private readonly TrainerDexService $trainerDexService,
        private readonly AlbumReportService $albumReportService,
    ) {}
```

Change the body of `list()`:

```php
    /** @return TrainerDexResponse[] */
    #[Route(path: '/{trainerExternalId}/list', methods: ['GET'])]
    #[Serialize]
    public function list(
        string $trainerExternalId,
        Request $request,
    ): array {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        $dex = iterator_to_array(
            $this->trainerDexService->getListQuery($trainerExternalId, $dexQueryOptions)
        );

        $reports = $this->albumReportService->getBatch($trainerExternalId);

        return TrainerDexResponseFactory::fromSqlRows($dex, $reports);
    }
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/DexControllerTest.php`
Expected: PASS (all methods, including `testUpdate*`/`testCreate*`/`testBadArgument`/`testEmptyData` which are unaffected)

- [ ] **Step 5: Run the full pokenini-api test suite and coverage**

Run: `make tests` then `make coverage` (from the pokenini-api directory, i.e. `cd /home/renaud/projects/pokenini-api && make tests && make coverage`)
Expected: all green, 100% coverage maintained

- [ ] **Step 6: Commit**

```bash
git add src/Controller/DexController.php tests/src/Integration/Controller/DexControllerTest.php
git commit -m "feat: wire AlbumReportService::getBatch into DexController::list"
```

---

## pokenini-back (`/home/renaud/projects/pokenini-back`)

### Task 6: Fix cache invalidation for the now-report-carrying `/dex/list` cache entry

**Files:**
- Modify: `src/Service/ModifyTrainerAlbumService.php`
- Test: `tests/src/Unit/Service/ModifyTrainerAlbumServiceTest.php`

**Interfaces:**
- Consumes: `AlbumCacheInvalidatorService::invalidate(string $dexSlug, string $trainerId): void` (already exists, already used by `ModifyTrainerDexService`; internally deletes the single-dex pokedex cache key and invalidates the `getTrainerIdKey($trainerId)` tag — the same tag `GetDexListApiService` tags the `/dex/{trainerId}/list` cache entry with).

- [ ] **Step 1: Write the failing test**

Replace the full contents of `tests/src/Unit/Service/ModifyTrainerAlbumServiceTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Exception\ModifyFailedException;
use App\Security\UserTokenService;
use App\Service\Api\ModifyAlbumApiService;
use App\Service\CacheInvalidator\AlbumCacheInvalidatorService;
use App\Service\CacheInvalidator\AlbumsCacheInvalidatorService;
use App\Service\ModifyTrainerAlbumService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @internal
 */
#[CoversClass(ModifyTrainerAlbumService::class)]
final class ModifyTrainerAlbumServiceTest extends TestCase
{
    public function testModifyAlbum(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService
            ->expects($this->once())
            ->method('getLoggedUserToken')
            ->willReturn('8800088')
        ;

        $modifyAlbumService = $this->createMock(ModifyAlbumApiService::class);
        $modifyAlbumService
            ->expects($this->once())
            ->method('modify')
            ->with(
                'PUT',
                'douze',
                'treize',
                '{"ceci": "est-du-contenu"}',
                '8800088',
            )
        ;

        $albumsCacheInvalidatorService = $this->createMock(AlbumsCacheInvalidatorService::class);
        $albumsCacheInvalidatorService
            ->expects($this->once())
            ->method('invalidate')
        ;

        $albumCacheInvalidatorService = $this->createMock(AlbumCacheInvalidatorService::class);
        $albumCacheInvalidatorService
            ->expects($this->once())
            ->method('invalidate')
            ->with('douze', '8800088')
        ;

        $request = Request::create(
            'test.local',
            'PUT',
        );
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $service = new ModifyTrainerAlbumService(
            $userTokenService,
            $modifyAlbumService,
            $albumsCacheInvalidatorService,
            $albumCacheInvalidatorService,
            $requestStack,
        );
        $service->modifyAlbum('douze', 'treize', '{"ceci": "est-du-contenu"}');
    }

    public function testModifyDexWithHttpException(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService
            ->expects($this->once())
            ->method('getLoggedUserToken')
            ->willReturn('8800088')
        ;

        $exception = $this->createStub(HttpExceptionInterface::class);

        $modifyAlbumService = $this->createMock(ModifyAlbumApiService::class);
        $modifyAlbumService
            ->expects($this->once())
            ->method('modify')
            ->with(
                'PUT',
                'douze',
                'treize',
                '{"ceci": "est-du-contenu"}',
                '8800088',
            )
            ->willThrowException($exception)
        ;

        $albumsCacheInvalidatorService = $this->createMock(AlbumsCacheInvalidatorService::class);
        $albumsCacheInvalidatorService
            ->expects($this->never())
            ->method('invalidate')
        ;

        $albumCacheInvalidatorService = $this->createMock(AlbumCacheInvalidatorService::class);
        $albumCacheInvalidatorService
            ->expects($this->never())
            ->method('invalidate')
        ;

        $request = Request::create(
            'test.local',
            'PUT',
        );
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $service = new ModifyTrainerAlbumService(
            $userTokenService,
            $modifyAlbumService,
            $albumsCacheInvalidatorService,
            $albumCacheInvalidatorService,
            $requestStack,
        );

        $this->expectException(ModifyFailedException::class);

        $service->modifyAlbum('douze', 'treize', '{"ceci": "est-du-contenu"}');
    }

    public function testModifyDexWithNoRequest(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService
            ->expects($this->once())
            ->method('getLoggedUserToken')
            ->willReturn('8800088')
        ;

        $modifyAlbumService = $this->createMock(ModifyAlbumApiService::class);
        $modifyAlbumService
            ->expects($this->never())
            ->method('modify')
        ;

        $albumsCacheInvalidatorService = $this->createMock(AlbumsCacheInvalidatorService::class);
        $albumsCacheInvalidatorService
            ->expects($this->never())
            ->method('invalidate')
        ;

        $albumCacheInvalidatorService = $this->createMock(AlbumCacheInvalidatorService::class);
        $albumCacheInvalidatorService
            ->expects($this->never())
            ->method('invalidate')
        ;

        $requestStack = new RequestStack();

        $service = new ModifyTrainerAlbumService(
            $userTokenService,
            $modifyAlbumService,
            $albumsCacheInvalidatorService,
            $albumCacheInvalidatorService,
            $requestStack,
        );

        $this->expectException(ModifyFailedException::class);

        $service->modifyAlbum('douze', 'treize', '{"ceci": "est-du-contenu"}');
    }

    public function testModifyDexWithTransportException(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService
            ->expects($this->once())
            ->method('getLoggedUserToken')
            ->willReturn('8800088')
        ;

        $exception = $this->createStub(TransportExceptionInterface::class);

        $modifyAlbumService = $this->createMock(ModifyAlbumApiService::class);
        $modifyAlbumService
            ->expects($this->once())
            ->method('modify')
            ->with(
                'PUT',
                'douze',
                'treize',
                '{"ceci": "est-du-contenu"}',
                '8800088',
            )
            ->willThrowException($exception)
        ;

        $albumsCacheInvalidatorService = $this->createMock(AlbumsCacheInvalidatorService::class);
        $albumsCacheInvalidatorService
            ->expects($this->never())
            ->method('invalidate')
        ;

        $albumCacheInvalidatorService = $this->createMock(AlbumCacheInvalidatorService::class);
        $albumCacheInvalidatorService
            ->expects($this->never())
            ->method('invalidate')
        ;

        $request = Request::create(
            'test.local',
            'PUT',
        );
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $service = new ModifyTrainerAlbumService(
            $userTokenService,
            $modifyAlbumService,
            $albumsCacheInvalidatorService,
            $albumCacheInvalidatorService,
            $requestStack,
        );

        $this->expectException(ModifyFailedException::class);

        $service->modifyAlbum('douze', 'treize', '{"ceci": "est-du-contenu"}');
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/ModifyTrainerAlbumServiceTest.php`
Expected: FAIL — `Too few arguments to function App\Service\ModifyTrainerAlbumService::__construct()`

- [ ] **Step 3: Update `ModifyTrainerAlbumService`**

Replace the full contents of `src/Service/ModifyTrainerAlbumService.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ModifyFailedException;
use App\Security\UserTokenService;
use App\Service\Api\ModifyAlbumApiService;
use App\Service\CacheInvalidator\AlbumCacheInvalidatorService;
use App\Service\CacheInvalidator\AlbumsCacheInvalidatorService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ModifyTrainerAlbumService
{
    public function __construct(
        private readonly UserTokenService $userTokenService,
        private readonly ModifyAlbumApiService $modifyAlbumService,
        private readonly AlbumsCacheInvalidatorService $albumsCacheInvalidatorService,
        private readonly AlbumCacheInvalidatorService $albumCacheInvalidatorService,
        private readonly RequestStack $requestStack,
    ) {}

    public function modifyAlbum(
        string $dexSlug,
        string $pokemonSlug,
        string $content,
    ): void {
        $trainerId = $this->userTokenService->getLoggedUserToken();
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new ModifyFailedException();
        }

        try {
            $this->modifyAlbumService->modify(
                $request->getMethod(),
                $dexSlug,
                $pokemonSlug,
                $content,
                $trainerId
            );

            $this->albumsCacheInvalidatorService->invalidate();
            $this->albumCacheInvalidatorService->invalidate($dexSlug, $trainerId);
        } catch (HttpExceptionInterface|TransportExceptionInterface $e) {
            throw new ModifyFailedException();
        }
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/ModifyTrainerAlbumServiceTest.php`
Expected: PASS (4 tests)

- [ ] **Step 5: Run the full pokenini-back test suite and coverage**

Run: `cd /home/renaud/projects/pokenini-back && make tests && make coverage`
Expected: all green, 100% coverage maintained (no other class constructs `ModifyTrainerAlbumService` directly outside DI, since it's a Symfony service — verify with `grep -rn "new ModifyTrainerAlbumService" src/` returns nothing before assuming this)

- [ ] **Step 6: Commit**

```bash
git add src/Service/ModifyTrainerAlbumService.php tests/src/Unit/Service/ModifyTrainerAlbumServiceTest.php
git commit -m "fix: invalidate the trainer-scoped dex-list cache entry when an album entry changes"
```

---

## pokenini-web (`/home/renaud/projects/pokenini-web`)

### Task 7: `DexListItem.report`

**Files:**
- Modify: `src/ResponseObject/Album/DexListItem.php`
- Test: `tests/src/Unit/ResponseObject/Album/DexListItemTest.php`

**Interfaces:**
- Consumes: `App\ResponseObject\Album\Report` (already exists, unchanged — used elsewhere for the single-dex album report).
- Produces: `DexListItem::getReport(): ?Report`. The property is **nullable with a `null` default**, unlike the api-side `TrainerDexResponse.report` — this side deserializes external JSON defensively (older cached responses from pokenini-back may briefly lack `report` during a rolling deploy), so a missing key must not break deserialization.

- [ ] **Step 1: Write the failing test**

Replace the full contents of `tests/src/Unit/ResponseObject/Album/DexListItemTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\ResponseObject\Album;

use App\ResponseObject\Album\DexFlags;
use App\ResponseObject\Album\DexListItem;
use App\ResponseObject\Album\DexListItemRef;
use App\ResponseObject\Album\DexListItemSettings;
use App\ResponseObject\Album\Report;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexListItem::class)]
final class DexListItemTest extends TestCase
{
    public function testGetters(): void
    {
        $ref = new DexListItemRef(slug: 'swordshield');
        $settings = new DexListItemSettings(
            name: 'Sword, Shield',
            frenchName: 'Épée, Bouclier',
            slug: 'swordshield',
            displayTemplate: 'box',
        );
        $flags = new DexFlags(
            isShiny: false,
            isPrivate: false,
            isOnHome: true,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        $item = new DexListItem(dex: $ref, settings: $settings, flags: $flags);

        $this->assertSame($ref, $item->getDex());
        $this->assertSame($settings, $item->getSettings());
        $this->assertSame($flags, $item->getFlags());
        $this->assertNull($item->getReport());
    }

    public function testGettersWithReport(): void
    {
        $ref = new DexListItemRef(slug: 'swordshield');
        $settings = new DexListItemSettings(
            name: 'Sword, Shield',
            frenchName: 'Épée, Bouclier',
            slug: 'swordshield',
            displayTemplate: 'box',
        );
        $flags = new DexFlags(
            isShiny: false,
            isPrivate: false,
            isOnHome: true,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );
        $report = new Report(total: 151, totalCaught: 42, totalUncaught: 109, detail: []);

        $item = new DexListItem(dex: $ref, settings: $settings, flags: $flags, report: $report);

        $this->assertSame($report, $item->getReport());
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/ResponseObject/Album/DexListItemTest.php`
Expected: FAIL — `Call to undefined method App\ResponseObject\Album\DexListItem::getReport()` and unknown constructor param `report`

- [ ] **Step 3: Update `DexListItem`**

Replace the full contents of `src/ResponseObject/Album/DexListItem.php` with:

```php
<?php

declare(strict_types=1);

namespace App\ResponseObject\Album;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexListItem
{
    public function __construct(
        #[SerializedName('dex')]
        private readonly DexListItemRef $dex,
        #[SerializedName('settings')]
        private readonly DexListItemSettings $settings,
        #[SerializedName('flags')]
        private readonly DexFlags $flags,
        #[SerializedName('report')]
        private readonly ?Report $report = null,
    ) {}

    public function getDex(): DexListItemRef
    {
        return $this->dex;
    }

    public function getSettings(): DexListItemSettings
    {
        return $this->settings;
    }

    public function getFlags(): DexFlags
    {
        return $this->flags;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/ResponseObject/Album/DexListItemTest.php`
Expected: PASS (2 tests)

- [ ] **Step 5: Commit**

```bash
git add src/ResponseObject/Album/DexListItem.php tests/src/Unit/ResponseObject/Album/DexListItemTest.php
git commit -m "feat: add nullable report property to DexListItem"
```

---

### Task 8: Progress badge on the dex-list card

**Files:**
- Modify: `templates/AlbumDexList/_macro.html.twig`
- Modify: `translations/messages+intl-icu.en.yaml`
- Modify: `translations/messages+intl-icu.fr.yaml`

**Interfaces:**
- Consumes: `DexListItem::getReport(): ?Report` (Task 7), `Report::getTotalCaught(): ?int` / `Report::getTotal(): ?int` (already exist).

This task is template-only and intentionally has no automated end-to-end assertion: the existing `AlbumDexListTest` browser tests assert exact `.text()` content against Moco-mocked back responses (`tests/resources/moco/Back/responses/dex/*.json`) that don't include a `report` key. Because `DexListItem.report` defaults to `null` (Task 7), those existing fixtures keep deserializing to `report: null`, the badge stays hidden, and none of those tests change — this task must not touch `tests/resources/moco/Back/`. Verify the rendered badge manually instead (Step 3).

- [ ] **Step 1: Add translation keys**

In `translations/messages+intl-icu.en.yaml`, insert this block immediately before the existing `election_dex:` key (find it with `grep -n "^election_dex:" translations/messages+intl-icu.en.yaml`):

```yaml
album_dex_list:
  dex:
    report_badge_suffixe: "caught"

```

In `translations/messages+intl-icu.fr.yaml`, insert this block immediately before the existing `election_dex:` key (find it with `grep -n "^election_dex:" translations/messages+intl-icu.fr.yaml`):

```yaml
album_dex_list:
  dex:
    report_badge_suffixe: "capturés"

```

- [ ] **Step 2: Add the badge to the `item()` macro**

In `templates/AlbumDexList/_macro.html.twig`, the `item()` macro currently ends its title/subtitle block like this:

```twig
      {% if subtitle is not empty %}
      <h3 class="h6 card-subtitle mb-2 text-body-secondary">
        <a href="{{ url }}">
          {{ subtitle }}
        </a>
      </h3>
      {% endif %}

    </div>
  </div>
</div>
{% endmacro %}

{% macro itemElection(dex, locale, forcedTrainerId) %}
```

Replace that exact block with:

```twig
      {% if subtitle is not empty %}
      <h3 class="h6 card-subtitle mb-2 text-body-secondary">
        <a href="{{ url }}">
          {{ subtitle }}
        </a>
      </h3>
      {% endif %}

      {% if dex.report is not null %}
      <span class="badge rounded-pill bg-primary mb-3">
        {{ dex.report.totalCaught|number_format(0, '.', ' ') }} / {{ dex.report.total|number_format(0, '.', ' ') }}
        {{ 'album_dex_list.dex.report_badge_suffixe'|trans }}
      </span>
      {% endif %}

    </div>
  </div>
</div>
{% endmacro %}

{% macro itemElection(dex, locale, forcedTrainerId) %}
```

- [ ] **Step 3: Verify manually**

The `run` skill or `make sh` + a browser can be used to check the badge renders once Tasks 1–7 are deployed end-to-end (api → back → web), since the Moco-mocked pokenini-web test suite doesn't exercise this path. Start the stack (`cd /home/renaud/projects/pokenini-web && make start`, plus pokenini-back and pokenini-api running), log in via the dev fake authenticator, and visit `/fr/album/dex` — dex cards should show a "X / Y capturés" badge. This step has no automated pass/fail; note the observed result when reporting task completion.

- [ ] **Step 4: Run the full pokenini-web test suite**

Run: `cd /home/renaud/projects/pokenini-web && make tests`
Expected: all green, no regressions in `AlbumDexListTest` (Moco fixtures untouched, `report` stays `null` for those scenarios)

- [ ] **Step 5: Commit**

```bash
git add templates/AlbumDexList/_macro.html.twig translations/messages+intl-icu.en.yaml translations/messages+intl-icu.fr.yaml
git commit -m "feat: show a progress badge on dex-list cards when a report is available"
```

---

## Notes on the abandoned worktree

`/home/renaud/projects/pokenini-api/.worktrees/album-catch-state-list-rates` (branch `feature/album-catch-state-list-rates`) already implements a version of Tasks 1–5 with a similar batched-query approach. Per explicit instruction, this plan was written independently without reusing that branch's code or commits — it was not consulted for any code in this document, only its existence was noted and the user chose to disregard it. That worktree is left untouched; cleaning it up (or not) is a separate decision for the user.
