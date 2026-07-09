# Election dex report (list + single-dex, top+metrics) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace pokenini-api's separate `/election/top` + `/election/metrics` with one merged `GET /election/{trainerExternalId}/{dexSlug}` endpoint, and replace `/dex/can_hold_election` with a new trainer-scoped `GET /election/{trainerExternalId}/list` that returns every election-eligible dex with its own top+metrics report — then thread both changes through pokenini-back (merge orchestration, trainer-scoped caching + invalidation on vote) and pokenini-web (a completion badge on the election dex-list cards).

**Architecture:** pokenini-api gets a new `ElectionReportService` (in `src/Service/Election/`) that wraps the existing, unchanged `TrainerPokemonEloRepository::getTopN()`/`::getMetrics()` and the existing `DexRepository::getCanHoldElection()`, assembled by a new `ElectionReportController` (replacing `TrainerPokemonEloController` and `DexCanHoldElectionController`). pokenini-back's `ElectionIndexController` swaps its two orchestration services for one `GetElectionReportService`; `ElectionDexListController` becomes trainer-aware and its cache gains a per-trainer dimension, invalidated by a new `ElectionCacheInvalidatorService` called from `ModifyElectionVoteService::vote()`. pokenini-web's `ElectionDexListItem` gains a `report` value object and the `itemElection()` macro gains a completion badge, mirroring the album dex-list badge already shipped.

**Tech Stack:** Symfony 8 / PHP ≥ 8.5 (pokenini-api), Symfony 8 / PHP ≥ 8.5 (pokenini-back), Symfony 8 / PHP 8.4 + Twig (pokenini-web). PostgreSQL + Doctrine DBAL raw SQL (api). Redis/APCu tagged cache (back). Moco-mocked HTTP in integration tests (back, web). PHPUnit + Alice fixtures (api integration tests). Docker-only toolchain everywhere — every command below runs via `docker compose exec php ...` (or the repo's `make` targets) from inside the relevant repo directory.

**Spec:** `docs/superpowers/specs/2026-07-09-election-dex-report-design.md`

## Global Constraints

- `declare(strict_types=1)` in every PHP file touched, in all three repos.
- Test classes carry `/** @internal */` and `#[CoversClass(TargetClass::class)]`.
- pokenini-api: 100% code coverage and 100% Mutation Score Index — every new branch needs a test that would fail if the branch were removed/inverted. PHPStan level 9 and Psalm strict — no untyped properties or return types; use precise array-shape docblocks exactly as shown in each step. Deptrac layering: `Controller → Service → Repository` (already-valid edges: `ElectionReportController → ElectionReportService`, `ElectionReportService → TrainerPokemonEloRepository`/`DexRepository`).
- `final` for Controller/DTO/Command/Message/Exception classes in pokenini-api; non-`final` for Service/Calculator/Repository/Updater so PHPUnit can mock them. Same final/non-final split applies conceptually in pokenini-back/pokenini-web for their own Controller vs Service classes, matching each repo's existing style shown in the code below.
- **Do NOT run `git commit` for any task in this plan.** Stage changes with `git add` only (as shown in each task's final step) and leave them uncommitted — the user commits explicitly when ready. Treat every "Commit" step heading below as "stage the files" — do not invoke `git commit`.

---

## Part A — pokenini-api (`/home/renaud/projects/pokenini-api`)

### Task 1: `ElectionReport\Report` DTO + `ElectionReportQueryOptions` DTO (delete the two dead/obsolete query-option DTOs)

**Files:**
- Create: `src/DTO/ElectionReport/Report.php`
- Create: `src/DTO/ElectionReportQueryOptions.php`
- Test: `tests/src/Unit/DTO/ElectionReport/ReportTest.php`
- Test: `tests/src/Unit/DTO/ElectionReportQueryOptionsTest.php`
- Delete: `src/DTO/TrainerPokemonEloQueryOptions.php`, `tests/src/Unit/DTO/TrainerPokemonEloQueryOptionsTest.php`
- Delete: `src/DTO/TrainerPokemonEloTopQueryOptions.php`, `tests/src/Unit/DTO/TrainerPokemonEloTopQueryOptionsTest.php`

**Interfaces:**
- Produces: `App\DTO\ElectionReport\Report` — plain readonly holder, `top: array<array-key, array<string, mixed>>` (raw SQL rows straight from `TrainerPokemonEloRepository::getTopN()`), `metrics: array{view_count_sum: int, win_count_sum: int, view_count_max: int, win_count_max: int, under_max_view_count: int, max_view_count: int, dex_total_count: int}` (raw shape returned by `TrainerPokemonEloRepository::getMetrics()`).
- Produces: `App\DTO\ElectionReportQueryOptions` — `{ electionSlug: string = '', count: int = 5 }`, constructed from an explicit `['election_slug' => ..., 'count' => ...]` array (never a raw `$request->query->all()` blob, so it can coexist with other query options built from the same request).

Note: neither `TrainerPokemonEloQueryOptions` nor `TrainerPokemonEloTopQueryOptions` has any other caller in `src/` besides the controllers being deleted in Task 6 — confirmed by repo-wide grep during design. `TrainerPokemonEloListQueryOptions` is a **different, unrelated** class (used by `PokemonsController`'s `/to_choose` endpoint) — do not touch it.

- [ ] **Step 1: Write the failing tests**

Create `tests/src/Unit/DTO/ElectionReport/ReportTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\ElectionReport;

use App\DTO\ElectionReport\Report;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Report::class)]
final class ReportTest extends TestCase
{
    public function testConstructor(): void
    {
        $top = [
            ['pokemon_slug' => 'pikachu'],
        ];
        $metrics = [
            'view_count_sum' => 9,
            'win_count_sum' => 6,
            'view_count_max' => 3,
            'win_count_max' => 3,
            'under_max_view_count' => 1,
            'max_view_count' => 1,
            'dex_total_count' => 7,
        ];

        $report = new Report($top, $metrics);

        $this->assertSame($top, $report->top);
        $this->assertSame($metrics, $report->metrics);
    }
}
```

Create `tests/src/Unit/DTO/ElectionReportQueryOptionsTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ElectionReportQueryOptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
final class ElectionReportQueryOptionsTest extends TestCase
{
    public function testDefaults(): void
    {
        $options = new ElectionReportQueryOptions();

        $this->assertSame('', $options->electionSlug);
        $this->assertSame(5, $options->count);
    }

    public function testExplicitValues(): void
    {
        $options = new ElectionReportQueryOptions([
            'election_slug' => 'favorite',
            'count' => '10',
        ]);

        $this->assertSame('favorite', $options->electionSlug);
        $this->assertSame(10, $options->count);
    }

    public function testInvalidElectionSlugType(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new ElectionReportQueryOptions(['election_slug' => 12]);
    }

    public function testUnknownOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        new ElectionReportQueryOptions(['unknown' => 'value']);
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/ElectionReport/ReportTest.php tests/src/Unit/DTO/ElectionReportQueryOptionsTest.php`
Expected: FAIL — `Class "App\DTO\ElectionReport\Report" not found` / `Class "App\DTO\ElectionReportQueryOptions" not found`

- [ ] **Step 3: Implement the DTOs**

Create `src/DTO/ElectionReport/Report.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO\ElectionReport;

final class Report
{
    /**
     * @param array<array-key, array<string, mixed>>                                                                                              $top
     * @param array{view_count_sum: int, win_count_sum: int, view_count_max: int, win_count_max: int, under_max_view_count: int, max_view_count: int, dex_total_count: int} $metrics
     */
    public function __construct(
        public readonly array $top,
        public readonly array $metrics,
    ) {}
}
```

Create `src/DTO/ElectionReportQueryOptions.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ElectionReportQueryOptions
{
    public string $electionSlug;
    public int $count;

    /**
     * @param int[]|string[] $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        /**
         * @var array{
         *  election_slug: string,
         *  count: int,
         * }
         */
        $options = $resolver->resolve($values);

        $this->electionSlug = $options['election_slug'];
        $this->count = $options['count'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('election_slug', '');
        $resolver->setAllowedTypes('election_slug', 'string');

        $resolver->setDefault('count', 5);
        $resolver->setAllowedTypes('count', ['int', 'string']);
        $resolver->setNormalizer('count', function (Options $options, string $value): int {
            unset($options); // To remove PHPMD.UnusedFormalParameter warning

            return intval($value);
        });
    }
}
```

- [ ] **Step 4: Delete the two obsolete DTOs and their tests**

```bash
rm src/DTO/TrainerPokemonEloQueryOptions.php
rm src/DTO/TrainerPokemonEloTopQueryOptions.php
rm tests/src/Unit/DTO/TrainerPokemonEloQueryOptionsTest.php
rm tests/src/Unit/DTO/TrainerPokemonEloTopQueryOptionsTest.php
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/ElectionReport/ReportTest.php tests/src/Unit/DTO/ElectionReportQueryOptionsTest.php`
Expected: PASS (5 tests). `TrainerPokemonEloController` still references the two deleted classes at this point — that's expected, it gets fixed in Task 6; do not run the full suite yet.

- [ ] **Step 6: Stage the files**

```bash
git add src/DTO/ElectionReport/Report.php src/DTO/ElectionReportQueryOptions.php \
  tests/src/Unit/DTO/ElectionReport/ReportTest.php tests/src/Unit/DTO/ElectionReportQueryOptionsTest.php \
  src/DTO/TrainerPokemonEloQueryOptions.php src/DTO/TrainerPokemonEloTopQueryOptions.php \
  tests/src/Unit/DTO/TrainerPokemonEloQueryOptionsTest.php tests/src/Unit/DTO/TrainerPokemonEloTopQueryOptionsTest.php
```

---

### Task 2: `ElectionReportService`

**Files:**
- Create: `src/Service/Election/ElectionReportService.php`
- Test: `tests/src/Integration/Service/Election/ElectionReportServiceTest.php`

**Interfaces:**
- Consumes: `TrainerPokemonEloRepository::getTopN(string $trainerExternalId, string $dexSlug, string $electionSlug, int $count): array<array-key, array<string, mixed>>` (existing, unchanged), `TrainerPokemonEloRepository::getMetrics(string $trainerExternalId, string $dexSlug, string $electionSlug): array{...}` (existing, unchanged), `DexRepository::getCanHoldElection(DexQueryOptions $options): array<array-key, array<string, mixed>>` (existing, unchanged).
- Produces: `ElectionReportService::get(string $trainerExternalId, string $dexSlug, string $electionSlug, int $count): Report`, `ElectionReportService::getEligibleDex(DexQueryOptions $options): array<array-key, array<string, mixed>>`, `ElectionReportService::getBatch(string $trainerExternalId, string[] $dexSlugs, string $electionSlug, int $count): array<string, Report>` (keyed by dex slug).

- [ ] **Step 1: Write the failing tests**

Create `tests/src/Integration/Service/Election/ElectionReportServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service\Election;

use App\DTO\DexQueryOptions;
use App\Service\Election\ElectionReportService;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(ElectionReportService::class)]
final class ElectionReportServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TRAINER_U12 = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetReturnsTopAndMetricsForDemoDex(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $report = $service->get(self::TRAINER_U12, 'demo', '', 5);

        $this->assertCount(5, $report->top);
        $this->assertEquals(
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
                'under_max_view_count' => 15,
                'max_view_count' => 15,
                'dex_total_count' => 21,
            ],
            $report->metrics,
        );
    }

    public function testGetReturnsTopAndMetricsForAffineeElection(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $report = $service->get(self::TRAINER_U12, 'redgreenblueyellow', 'affinee', 5);

        $this->assertEquals(
            [
                'view_count_sum' => 9,
                'win_count_sum' => 6,
                'view_count_max' => 3,
                'win_count_max' => 3,
                'under_max_view_count' => 1,
                'max_view_count' => 1,
                'dex_total_count' => 7,
            ],
            $report->metrics,
        );
    }

    public function testGetReturnsZeroedMetricsForUnknownElectionSlug(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $report = $service->get(self::TRAINER_U12, 'redgreenblueyellow', 'doesntexists', 5);

        $this->assertEquals(
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
                'under_max_view_count' => 7,
                'max_view_count' => 0,
                'dex_total_count' => 7,
            ],
            $report->metrics,
        );
    }

    public function testGetEligibleDexDefaultsToReleasedNonPremium(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $rows = $service->getEligibleDex(new DexQueryOptions());

        $slugs = array_map(static fn (array $row): string => (string) $row['slug'], $rows);

        $this->assertSame(['home'], $slugs);
    }

    public function testGetEligibleDexWithAllOptions(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $options = new DexQueryOptions([
            'include_unreleased_dex' => true,
            'include_premium_dex' => true,
        ]);

        $rows = $service->getEligibleDex($options);

        $slugs = array_map(static fn (array $row): string => (string) $row['slug'], $rows);

        $this->assertSame(['homepogo', 'home', 'redgreenblueyellow', 'spoon'], $slugs);
    }

    public function testGetBatchKeyedByDexSlug(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $reports = $service->getBatch(
            self::TRAINER_U12,
            ['home', 'redgreenblueyellow'],
            'favorite',
            5,
        );

        $this->assertArrayHasKey('home', $reports);
        $this->assertArrayHasKey('redgreenblueyellow', $reports);
        $this->assertCount(5, $reports['home']->top);
        $this->assertCount(5, $reports['redgreenblueyellow']->top);
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Service/Election/ElectionReportServiceTest.php`
Expected: FAIL — `Class "App\Service\Election\ElectionReportService" not found`

- [ ] **Step 3: Implement `ElectionReportService`**

Create `src/Service/Election/ElectionReportService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Service\Election;

use App\DTO\DexQueryOptions;
use App\DTO\ElectionReport\Report;
use App\Repository\DexRepository;
use App\Repository\TrainerPokemonEloRepository;

class ElectionReportService
{
    public function __construct(
        private readonly TrainerPokemonEloRepository $trainerPokemonEloRepository,
        private readonly DexRepository $dexRepository,
    ) {}

    public function get(string $trainerExternalId, string $dexSlug, string $electionSlug, int $count): Report
    {
        $top = $this->trainerPokemonEloRepository->getTopN($trainerExternalId, $dexSlug, $electionSlug, $count);
        $metrics = $this->trainerPokemonEloRepository->getMetrics($trainerExternalId, $dexSlug, $electionSlug);

        return new Report($top, $metrics);
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getEligibleDex(DexQueryOptions $options): array
    {
        return $this->dexRepository->getCanHoldElection($options);
    }

    /**
     * @param string[] $dexSlugs
     *
     * @return array<string, Report>
     */
    public function getBatch(string $trainerExternalId, array $dexSlugs, string $electionSlug, int $count): array
    {
        $reports = [];

        foreach ($dexSlugs as $dexSlug) {
            $reports[$dexSlug] = $this->get($trainerExternalId, $dexSlug, $electionSlug, $count);
        }

        return $reports;
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Service/Election/ElectionReportServiceTest.php`
Expected: PASS (6 tests)

- [ ] **Step 5: Stage the files**

```bash
git add src/Service/Election/ElectionReportService.php tests/src/Integration/Service/Election/ElectionReportServiceTest.php
```

---

### Task 3: `ElectionReportResponse` + `ElectionReportResponseFactory`

**Files:**
- Create: `src/DTO/Response/ElectionReportResponse.php`
- Create: `src/Factory/ElectionReportResponseFactory.php`
- Test: `tests/src/Unit/Factory/ElectionReportResponseFactoryTest.php`

**Interfaces:**
- Consumes: `App\DTO\ElectionReport\Report` (Task 2), `ElectionEloResponseFactory::fromSqlRows(array $rows): ElectionEloResponse[]` (existing, unchanged), `ElectionMetricsResponseFactory::fromArray(array $data): ElectionMetricsResponse` (existing, unchanged).
- Produces: `ElectionReportResponse { top: ElectionEloResponse[], metrics: ElectionMetricsResponse }`, `ElectionReportResponseFactory::fromReport(Report $report): ElectionReportResponse`.

- [ ] **Step 1: Write the failing test**

Create `tests/src/Unit/Factory/ElectionReportResponseFactoryTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\ElectionReport\Report;
use App\DTO\Response\ElectionReportResponse;
use App\Factory\ElectionReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionReportResponseFactory::class)]
final class ElectionReportResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromReportTransformsTopAndMetrics(): void
    {
        $topRow = [
            'elo' => 1200.5,
            'significance' => true,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'pichu',
            'original_game_bundle_slug' => 'red-blue',
            'pokemon_order_number' => '9999-0025-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'category_form_french_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'regional_form_french_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'special_form_french_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'variant_form_french_name' => null,
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ];

        $report = new Report(
            [$topRow],
            [
                'view_count_sum' => 9,
                'win_count_sum' => 6,
                'view_count_max' => 3,
                'win_count_max' => 3,
                'under_max_view_count' => 1,
                'max_view_count' => 1,
                'dex_total_count' => 7,
            ],
        );

        $response = ElectionReportResponseFactory::fromReport($report);

        $this->assertInstanceOf(ElectionReportResponse::class, $response);
        $this->assertCount(1, $response->top);
        $this->assertSame('pikachu', $response->top[0]->pokemon->slug);
        $this->assertSame(1200.5, $response->top[0]->score->elo);
        $this->assertSame(9, $response->metrics->viewCount->sum);
        $this->assertSame(3, $response->metrics->viewCount->max);
        $this->assertSame(6, $response->metrics->winCount->sum);
        $this->assertSame(3, $response->metrics->winCount->max);
        $this->assertSame(1, $response->metrics->completion->atMaxCount);
        $this->assertSame(1, $response->metrics->completion->underMaxCount);
        $this->assertSame(7, $response->metrics->dexTotalCount);
    }

    #[Test]
    public function fromReportHandlesEmptyTop(): void
    {
        $report = new Report(
            [],
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
                'under_max_view_count' => 0,
                'max_view_count' => 0,
                'dex_total_count' => 0,
            ],
        );

        $response = ElectionReportResponseFactory::fromReport($report);

        $this->assertSame([], $response->top);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/ElectionReportResponseFactoryTest.php`
Expected: FAIL — `Class "App\DTO\Response\ElectionReportResponse" not found`

- [ ] **Step 3: Implement `ElectionReportResponse` and its factory**

Create `src/DTO/Response/ElectionReportResponse.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionReportResponse
{
    /**
     * @param ElectionEloResponse[] $top
     */
    public function __construct(
        public readonly array $top,
        public readonly ElectionMetricsResponse $metrics,
    ) {}
}
```

Create `src/Factory/ElectionReportResponseFactory.php`:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionReport\Report;
use App\DTO\Response\ElectionReportResponse;

final class ElectionReportResponseFactory
{
    public static function fromReport(Report $report): ElectionReportResponse
    {
        return new ElectionReportResponse(
            top: ElectionEloResponseFactory::fromSqlRows($report->top),
            metrics: ElectionMetricsResponseFactory::fromArray($report->metrics),
        );
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/ElectionReportResponseFactoryTest.php`
Expected: PASS (2 tests)

- [ ] **Step 5: Stage the files**

```bash
git add src/DTO/Response/ElectionReportResponse.php src/Factory/ElectionReportResponseFactory.php \
  tests/src/Unit/Factory/ElectionReportResponseFactoryTest.php
```

---

### Task 4: Add `report` to `DexResponse` and thread it through `DexResponseFactory`

`DexResponse`/`DexResponseFactory` have exactly one caller today — `DexCanHoldElectionController` (deleted in Task 5) — confirmed by a repo-wide grep during design. Rather than wrapping `DexResponse` in a new composite type (which would nest the JSON under a `dex` key and silently break pokenini-web's existing flat `ElectionDexListItem` deserialization), this task extends `DexResponse` in place — the exact same move Album made on `TrainerDexResponse` for its own list-report feature.

**Files:**
- Modify: `src/DTO/Response/DexResponse.php`
- Modify: `src/Factory/DexResponseFactory.php`
- Modify: `tests/src/Unit/DTO/Response/DexResponseTest.php`
- Modify: `tests/src/Unit/Factory/DexResponseFactoryTest.php`

**Interfaces:**
- Consumes: `App\DTO\ElectionReport\Report` (Task 2), `ElectionReportResponseFactory::fromReport(Report $report): ElectionReportResponse` (Task 3).
- Produces: `DexResponse::$report: ElectionReportResponse` (non-nullable — mirrors `TrainerDexResponse::$report`). `DexResponseFactory::fromSqlRow(array $row, Report $report): DexResponse` (signature changes — second param now required). `DexResponseFactory::fromSqlRows(array $rows, array $reports): DexResponse[]` (second param `array<string, Report>` now required, keyed by the row's `slug`; falls back to an empty `Report([], <all-zero metrics>)` if a dex slug has no entry in the map).

- [ ] **Step 1: Update the failing tests**

Replace the full contents of `tests/src/Unit/DTO/Response/DexResponseTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\ElectionReport\Report;
use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\DexResponse;
use App\Factory\ElectionReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexResponse::class)]
final class DexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );
        $report = ElectionReportResponseFactory::fromReport(new Report([], [
            'view_count_sum' => 0,
            'win_count_sum' => 0,
            'view_count_max' => 0,
            'win_count_max' => 0,
            'under_max_view_count' => 22,
            'max_view_count' => 0,
            'dex_total_count' => 22,
        ]));

        $response = new DexResponse(
            slug: 'home',
            originalSlug: 'home',
            name: 'Home',
            frenchName: 'Home',
            flags: $flags,
            description: 'The National Dex in Home',
            frenchDescription: 'Le Pokédex National dans Home',
            dexTotalCount: 22,
            report: $report,
        );

        self::assertSame('home', $response->slug);
        self::assertSame('home', $response->originalSlug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertSame($flags, $response->flags);
        self::assertSame('The National Dex in Home', $response->description);
        self::assertSame('Le Pokédex National dans Home', $response->frenchDescription);
        self::assertSame(22, $response->dexTotalCount);
        self::assertSame($report, $response->report);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $flags = new DexFlagsResponse(
            isShiny: true,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: false,
            isReleased: true,
            isPremium: true,
            isCustom: false,
        );
        $report = ElectionReportResponseFactory::fromReport(new Report([], [
            'view_count_sum' => 0,
            'win_count_sum' => 0,
            'view_count_max' => 0,
            'win_count_max' => 0,
            'under_max_view_count' => 7,
            'max_view_count' => 0,
            'dex_total_count' => 7,
        ]));

        $response = new DexResponse(
            slug: 'redgreenblueyellow',
            originalSlug: 'redgreenblueyellow',
            name: 'Red / Green / Blue / Yellow',
            frenchName: 'Rouge / Vert / Bleu / Jaune',
            flags: $flags,
            description: '',
            frenchDescription: '',
            dexTotalCount: 7,
            report: $report,
        );

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('redgreenblueyellow', $response->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $response->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $response->frenchName);
        self::assertTrue($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertFalse($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertTrue($response->flags->isPremium);
        self::assertSame(7, $response->dexTotalCount);
        self::assertSame($report, $response->report);
    }
}
```

Replace the full contents of `tests/src/Unit/Factory/DexResponseFactoryTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\ElectionReport\Report;
use App\DTO\Response\DexResponse;
use App\Factory\DexResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexResponseFactory::class)]
final class DexResponseFactoryTest extends TestCase
{
    private const array ZERO_METRICS = [
        'view_count_sum' => 0,
        'win_count_sum' => 0,
        'view_count_max' => 0,
        'win_count_max' => 0,
        'under_max_view_count' => 0,
        'max_view_count' => 0,
        'dex_total_count' => 0,
    ];

    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'home',
            'original_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'is_shiny' => false,
            'is_private' => false,
            'is_on_home' => false,
            'is_display_form' => true,
            'is_released' => true,
            'is_premium' => false,
            'is_custom' => false,
            'description' => 'The National Dex in Home',
            'french_description' => 'Le Pokédex National dans Home',
            'dex_total_count' => 22,
        ];
        $report = new Report([], array_merge(self::ZERO_METRICS, ['dex_total_count' => 22]));

        $response = DexResponseFactory::fromSqlRow($row, $report);

        self::assertSame('home', $response->slug);
        self::assertSame('home', $response->originalSlug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertFalse($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
        self::assertSame('The National Dex in Home', $response->description);
        self::assertSame('Le Pokédex National dans Home', $response->frenchDescription);
        self::assertSame(22, $response->dexTotalCount);
        self::assertSame(22, $response->report->metrics->dexTotalCount);
    }

    #[Test]
    public function fromSqlRowCastsValuesToCorrectTypes(): void
    {
        $row = [
            'slug' => 123,
            'original_slug' => 456,
            'name' => 789,
            'french_name' => 101,
            'is_shiny' => 0,
            'is_private' => 0,
            'is_on_home' => 0,
            'is_display_form' => 1,
            'is_released' => 1,
            'is_premium' => 0,
            'is_custom' => 0,
            'description' => 202,
            'french_description' => 303,
            'dex_total_count' => '7',
        ];
        $report = new Report([], self::ZERO_METRICS);

        $response = DexResponseFactory::fromSqlRow($row, $report);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->originalSlug);
        self::assertSame('789', $response->name);
        self::assertSame('101', $response->frenchName);
        self::assertFalse($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
        self::assertSame('202', $response->description);
        self::assertSame('303', $response->frenchDescription);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'home',
                'original_slug' => 'home',
                'name' => 'Home',
                'french_name' => 'Home',
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
                'description' => '',
                'french_description' => '',
                'dex_total_count' => 22,
            ],
            [
                'slug' => 'redgreenblueyellow',
                'original_slug' => 'redgreenblueyellow',
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => false,
                'description' => '',
                'french_description' => '',
                'dex_total_count' => 7,
            ],
        ];
        $reports = [
            'home' => new Report([], array_merge(self::ZERO_METRICS, ['dex_total_count' => 22])),
            'redgreenblueyellow' => new Report([], array_merge(self::ZERO_METRICS, ['dex_total_count' => 7])),
        ];

        $responses = DexResponseFactory::fromSqlRows($rows, $reports);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(DexResponse::class, $responses);
        self::assertSame('home', $responses[0]->slug);
        self::assertSame(22, $responses[0]->dexTotalCount);
        self::assertSame(22, $responses[0]->report->metrics->dexTotalCount);
        self::assertFalse($responses[0]->flags->isPrivate);
        self::assertSame('redgreenblueyellow', $responses[1]->slug);
        self::assertSame(7, $responses[1]->dexTotalCount);
        self::assertSame(7, $responses[1]->report->metrics->dexTotalCount);
        self::assertTrue($responses[1]->flags->isPremium);
    }

    #[Test]
    public function fromSqlRowsFallsBackToEmptyReportWhenMissingFromMap(): void
    {
        $rows = [
            [
                'slug' => 'spoon',
                'original_slug' => 'spoon',
                'name' => 'Spoon',
                'french_name' => 'Cuillière',
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => false,
                'is_premium' => true,
                'is_custom' => false,
                'description' => '',
                'french_description' => '',
                'dex_total_count' => 1,
            ],
        ];

        $responses = DexResponseFactory::fromSqlRows($rows, []);

        self::assertSame([], $responses[0]->report->top);
        self::assertSame(0, $responses[0]->report->metrics->dexTotalCount);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = DexResponseFactory::fromSqlRows([], []);

        self::assertCount(0, $responses);
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/DexResponseTest.php tests/src/Unit/Factory/DexResponseFactoryTest.php`
Expected: FAIL — `Unknown named parameter $report` / `Too few arguments to function App\Factory\DexResponseFactory::fromSqlRow()`

- [ ] **Step 3: Update `DexResponse`**

Replace the full contents of `src/DTO/Response/DexResponse.php` with:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexResponse
{
    public function __construct(
        public readonly string $slug,
        #[SerializedName('original_slug')]
        public readonly string $originalSlug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly DexFlagsResponse $flags,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        #[SerializedName('dex_total_count')]
        public readonly int $dexTotalCount,
        public readonly ElectionReportResponse $report,
    ) {}
}
```

- [ ] **Step 4: Update `DexResponseFactory`**

Replace the full contents of `src/Factory/DexResponseFactory.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionReport\Report;
use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\DexResponse;

final class DexResponseFactory
{
    private const array EMPTY_METRICS = [
        'view_count_sum' => 0,
        'win_count_sum' => 0,
        'view_count_max' => 0,
        'win_count_max' => 0,
        'under_max_view_count' => 0,
        'max_view_count' => 0,
        'dex_total_count' => 0,
    ];

    /**
     * Transform a single SQL row into DexResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row, Report $report): DexResponse
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

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $isCustom */
        $isCustom = $row['is_custom'];

        /** @var scalar $description */
        $description = $row['description'];

        /** @var scalar $frenchDescription */
        $frenchDescription = $row['french_description'];

        /** @var scalar $dexTotalCount */
        $dexTotalCount = $row['dex_total_count'];

        return new DexResponse(
            slug: (string) $slug,
            originalSlug: (string) $originalSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            flags: new DexFlagsResponse(
                isShiny: (bool) $isShiny,
                isPrivate: (bool) $isPrivate,
                isOnHome: (bool) $isOnHome,
                isDisplayForm: (bool) $isDisplayForm,
                isReleased: (bool) $isReleased,
                isPremium: (bool) $isPremium,
                isCustom: (bool) $isCustom,
            ),
            description: (string) $description,
            frenchDescription: (string) $frenchDescription,
            dexTotalCount: (int) $dexTotalCount,
            report: ElectionReportResponseFactory::fromReport($report),
        );
    }

    /**
     * Transform multiple SQL rows into DexResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     * @param array<string, Report>                      $reports keyed by dex slug
     *
     * @return DexResponse[]
     */
    public static function fromSqlRows(array $rows, array $reports): array
    {
        return array_map(
            static fn (array $row): DexResponse => self::fromSqlRow(
                $row,
                $reports[(string) $row['slug']] ?? new Report([], self::EMPTY_METRICS),
            ),
            $rows,
        );
    }
}
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/DexResponseTest.php tests/src/Unit/Factory/DexResponseFactoryTest.php`
Expected: PASS (7 tests)

- [ ] **Step 6: Stage the files**

```bash
git add src/DTO/Response/DexResponse.php src/Factory/DexResponseFactory.php \
  tests/src/Unit/DTO/Response/DexResponseTest.php tests/src/Unit/Factory/DexResponseFactoryTest.php
```

---

### Task 5: `ElectionReportController` (list + show), delete the three obsolete controllers/service

**Files:**
- Create: `src/Controller/ElectionReportController.php`
- Delete: `src/Controller/TrainerPokemonEloController.php`, `src/Controller/DexCanHoldElectionController.php`, `src/Service/DexCanHoldElectionService.php`
- Delete: `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`, `tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php`, `tests/src/Unit/Service/DexCanHoldElectionServiceTest.php`
- Delete: `tests/resources/fixtures/election_metrics_response.json`, `tests/resources/fixtures/dex_can_hold_election_response.json`
- Test: `tests/src/Integration/Controller/ElectionReportControllerTest.php`

**Interfaces:**
- Consumes: `ElectionReportService::get()`/`::getEligibleDex()`/`::getBatch()` (Task 2), `ElectionReportResponseFactory::fromReport()` (Task 3), `DexResponseFactory::fromSqlRows()` (Task 4), `ElectionReportQueryOptions` (Task 1), `DexQueryOptions` (existing, unchanged).
- Produces: `GET /election/{trainerExternalId}/list` → `DexResponse[]` (each entry flat: `slug`, `original_slug`, `name`, `french_name`, `flags`, `description`, `french_description`, `dex_total_count`, `report: {top, metrics}`); `GET /election/{trainerExternalId}/{dexSlug}` → `ElectionReportResponse`. The `list` route uses Symfony's `priority: 2` attribute (same mechanism already used in the sister pokenini-back repo for an identical `/election/dex` vs `/election/{dexSlug}/{electionSlug}` collision) so that a literal `.../list` path is matched before the generic `.../{dexSlug}` route could capture `list` as a dex slug.

- [ ] **Step 1: Write the failing tests**

Create `tests/src/Integration/Controller/ElectionReportControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ElectionReportController;
use App\DTO\Response\ElectionEloScoreResponse;
use App\DTO\Response\ElectionMetricsCompletionResponse;
use App\DTO\Response\ElectionViewCountResponse;
use App\DTO\Response\ElectionWinCountResponse;
use App\Factory\DexResponseFactory;
use App\Factory\ElectionEloResponseFactory;
use App\Factory\ElectionMetricsResponseFactory;
use App\Factory\ElectionReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ElectionReportController::class)]
#[CoversClass(ElectionReportResponseFactory::class)]
#[CoversClass(DexResponseFactory::class)]
#[CoversClass(ElectionEloResponseFactory::class)]
#[CoversClass(ElectionMetricsResponseFactory::class)]
#[CoversClass(ElectionMetricsCompletionResponse::class)]
#[CoversClass(ElectionViewCountResponse::class)]
#[CoversClass(ElectionEloScoreResponse::class)]
#[CoversClass(ElectionWinCountResponse::class)]
final class ElectionReportControllerTest extends AbstractTestControllerApi
{
    private const string TRAINER_U12 = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    public function testShowReturnsTopAndMetricsForHomeFavorite(): void
    {
        $this->apiRequest(
            'GET',
            '/election/'.self::TRAINER_U12.'/home',
            [
                'election_slug' => 'favorite',
                'count' => '5',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array{top: array<int, array<string, mixed>>, metrics: array<string, mixed>} $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertArrayHasKey('top', $content);
        $this->assertArrayHasKey('metrics', $content);
        $this->assertCount(5, $content['top']);

        foreach ($content['top'] as $item) {
            $this->assertArrayHasKey('score', $item);
            $this->assertArrayHasKey('pokemon', $item);
            $this->assertArrayHasKey('forms', $item);
            $this->assertArrayHasKey('types', $item);
        }
    }

    public function testShowReturnsMetricsForDemoWithDefaultElectionSlug(): void
    {
        $this->apiRequest(
            'GET',
            '/election/'.self::TRAINER_U12.'/demo',
            [
                'election_slug' => '',
                'count' => '5',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array{top: array<int, array<string, mixed>>, metrics: array<string, array<string, int>|int>} $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(5, $content['top']);
        $this->assertSame(
            [
                'view_count' => ['sum' => 0, 'max' => 0],
                'win_count' => ['sum' => 0, 'max' => 0],
                'completion' => ['at_max_count' => 15, 'under_max_count' => 15],
                'dex_total_count' => 21,
            ],
            $content['metrics'],
        );
    }

    public function testShowReturnsMetricsForAffineeElection(): void
    {
        $this->apiRequest(
            'GET',
            '/election/'.self::TRAINER_U12.'/redgreenblueyellow',
            [
                'election_slug' => 'affinee',
                'count' => '5',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array{top: array<int, array<string, mixed>>, metrics: array<string, array<string, int>|int>} $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(
            [
                'view_count' => ['sum' => 9, 'max' => 3],
                'win_count' => ['sum' => 6, 'max' => 3],
                'completion' => ['at_max_count' => 1, 'under_max_count' => 1],
                'dex_total_count' => 7,
            ],
            $content['metrics'],
        );
    }

    public function testShowDefaultsElectionSlugAndCountWhenOmitted(): void
    {
        $this->apiRequest('GET', '/election/'.self::TRAINER_U12.'/demo');

        $this->assertResponseIsOK();

        /** @var array{top: array<int, array<string, mixed>>, metrics: array<string, mixed>} $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(5, $content['top']);
    }

    public function testShowWithAuth(): void
    {
        $this->apiRequest(
            'GET',
            '/election/'.self::TRAINER_U12.'/home',
            ['election_slug' => 'favorite', 'count' => '5'],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]
        );

        $this->assertResponseIsOK();
    }

    public function testShowWithBadAuth(): void
    {
        $this->apiRequest(
            'GET',
            '/election/'.self::TRAINER_U12.'/home',
            ['election_slug' => 'favorite', 'count' => '5'],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']
        );

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }

    public function testListReturnsOneDexByDefault(): void
    {
        $this->apiRequest('GET', '/election/'.self::TRAINER_U12.'/list');

        $this->assertResponseIsOK();

        /** @var array<int, array{slug: string, report: array{top: array<int, array<string, mixed>>, metrics: array<string, mixed>}}> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(1, $content);
        $this->assertSame('home', $content[0]['slug']);
        $this->assertArrayHasKey('report', $content[0]);
        $this->assertArrayHasKey('top', $content[0]['report']);
        $this->assertArrayHasKey('metrics', $content[0]['report']);
    }

    public function testListReturnsAllEligibleDexWithOptions(): void
    {
        $this->apiRequest('GET', '/election/'.self::TRAINER_U12.'/list', [
            'include_unreleased_dex' => '1',
            'include_premium_dex' => '1',
            'count' => '1',
        ]);

        $this->assertResponseIsOK();

        /** @var array<int, array{slug: string, report: array<string, mixed>}> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(4, $content);
        $this->assertSame(
            ['homepogo', 'home', 'redgreenblueyellow', 'spoon'],
            array_map(static fn (array $item): string => (string) $item['slug'], $content),
        );

        foreach ($content as $item) {
            $this->assertCount(1, $item['report']['top']);
            $this->assertArrayHasKey('dex_total_count', $item['report']['metrics']);
        }
    }

    public function testListIsNotShadowedByTheSingleDexRoute(): void
    {
        $this->apiRequest('GET', '/election/'.self::TRAINER_U12.'/list');

        $this->assertResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertIsArray($content);
        $this->assertArrayNotHasKey('top', $content);
        $this->assertArrayNotHasKey('metrics', $content);
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/ElectionReportControllerTest.php`
Expected: FAIL — 404s, `ElectionReportController` doesn't exist yet.

- [ ] **Step 3: Implement `ElectionReportController`**

Create `src/Controller/ElectionReportController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\DTO\ElectionReportQueryOptions;
use App\DTO\Response\DexResponse;
use App\DTO\Response\ElectionReportResponse;
use App\Factory\DexResponseFactory;
use App\Factory\ElectionReportResponseFactory;
use App\Service\Election\ElectionReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/election')]
final class ElectionReportController extends AbstractController
{
    public function __construct(
        private readonly ElectionReportService $electionReportService,
    ) {}

    /** @return DexResponse[] */
    #[Route(path: '/{trainerExternalId}/list', methods: ['GET'], priority: 2)]
    #[Serialize]
    public function list(
        string $trainerExternalId,
        Request $request,
    ): array {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        $reportQueryOptions = new ElectionReportQueryOptions([
            'election_slug' => $request->query->get('election_slug', ''),
            'count' => $request->query->get('count', 5),
        ]);

        $dexRows = $this->electionReportService->getEligibleDex($dexQueryOptions);

        $dexSlugs = array_map(static fn (array $row): string => (string) $row['slug'], $dexRows);

        $reports = $this->electionReportService->getBatch(
            $trainerExternalId,
            $dexSlugs,
            $reportQueryOptions->electionSlug,
            $reportQueryOptions->count,
        );

        return DexResponseFactory::fromSqlRows($dexRows, $reports);
    }

    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    #[Serialize]
    public function show(
        string $trainerExternalId,
        string $dexSlug,
        Request $request,
    ): ElectionReportResponse {
        $reportQueryOptions = new ElectionReportQueryOptions([
            'election_slug' => $request->query->get('election_slug', ''),
            'count' => $request->query->get('count', 5),
        ]);

        $report = $this->electionReportService->get(
            $trainerExternalId,
            $dexSlug,
            $reportQueryOptions->electionSlug,
            $reportQueryOptions->count,
        );

        return ElectionReportResponseFactory::fromReport($report);
    }
}
```

- [ ] **Step 4: Delete the obsolete controllers, service, and their tests/fixtures**

```bash
rm src/Controller/TrainerPokemonEloController.php
rm src/Controller/DexCanHoldElectionController.php
rm src/Service/DexCanHoldElectionService.php
rm tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php
rm tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php
rm tests/src/Unit/Service/DexCanHoldElectionServiceTest.php
rm tests/resources/fixtures/election_metrics_response.json
rm tests/resources/fixtures/dex_can_hold_election_response.json
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/ElectionReportControllerTest.php`
Expected: PASS (10 tests)

- [ ] **Step 6: Run the full pokenini-api test suite, coverage, and quality gates**

Run: `docker compose exec php php vendor/bin/phpunit` (or `make tests`) then `make coverage`, `make infection`, `make code-quality` from the pokenini-api directory.
Expected: all green, 100% coverage and MSI maintained, PHPStan/Psalm/Deptrac/CS Fixer clean. If PHPStan or Psalm flag a lingering reference to a deleted class (e.g. in `config/services.yaml` autowiring type-hints, which shouldn't need edits since Symfony autowires by constructor type-hint, not explicit service id), fix it before proceeding.

- [ ] **Step 7: Stage the files**

```bash
git add src/Controller/ElectionReportController.php tests/src/Integration/Controller/ElectionReportControllerTest.php
git add -u src/Controller/TrainerPokemonEloController.php src/Controller/DexCanHoldElectionController.php src/Service/DexCanHoldElectionService.php \
  tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php \
  tests/src/Unit/Service/DexCanHoldElectionServiceTest.php \
  tests/resources/fixtures/election_metrics_response.json tests/resources/fixtures/dex_can_hold_election_response.json
```

---

## Part B — pokenini-back (`/home/renaud/projects/pokenini-back`)

### Task 6: `KeyMaker::getElectionDexListKeyForTrainer()` + `ElectionCacheInvalidatorService`

**Files:**
- Modify: `src/Cache/KeyMaker.php`
- Create: `src/Service/CacheInvalidator/ElectionCacheInvalidatorService.php`
- Test: `tests/src/Unit/Cache/KeyMakerTest.php` (add a method)
- Test: `tests/src/Unit/Service/CacheInvalidator/ElectionCacheInvalidatorServiceTest.php`

**Interfaces:**
- Produces: `KeyMaker::getElectionDexListKeyForTrainer(string $trainerId, array $queryParams = []): string` returning `election_dex_list_{trainerId}[_k1v1_k2v2...]`, mirroring the existing `getDexKeyForTrainer(string $trainerId, array $queryParams = []): string` shape exactly (`dex_{trainerId}[...]`).
- Produces: `ElectionCacheInvalidatorService::invalidate(string $trainerId): void` — invalidates the `KeyMaker::getTrainerIdKey($trainerId)` tag (mirrors `AlbumCacheInvalidatorService`, but single-argument since there's no per-dex cache key to delete directly here, only the tag-based trainer-scoped list entry).

- [ ] **Step 1: Write the failing tests**

Add this method to `tests/src/Unit/Cache/KeyMakerTest.php`, right after `testGetElectionDexListKeyWithQueryParams()`:

```php
    public function testGetElectionDexListKeyForTrainer(): void
    {
        $this->assertEquals('election_dex_list_1', KeyMaker::getElectionDexListKeyForTrainer('1'));
        $this->assertEquals('election_dex_list_12', KeyMaker::getElectionDexListKeyForTrainer('12'));
    }

    public function testGetElectionDexListKeyForTrainerWithQueryParams(): void
    {
        $this->assertEquals('election_dex_list_1_1=1', KeyMaker::getElectionDexListKeyForTrainer('1', ['1' => '1']));
        $this->assertEquals(
            'election_dex_list_12_1=1_2=2',
            KeyMaker::getElectionDexListKeyForTrainer('12', ['1' => '1', '2' => '2']),
        );
    }
```

Create `tests/src/Unit/Service/CacheInvalidator/ElectionCacheInvalidatorServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\CacheInvalidator;

use App\Service\CacheInvalidator\ElectionCacheInvalidatorService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

/**
 * @internal
 */
#[CoversClass(ElectionCacheInvalidatorService::class)]
final class ElectionCacheInvalidatorServiceTest extends TestCase
{
    public function testInvalidate(): void
    {
        $cachePool = new ArrayAdapter();
        $cache = new TagAwareAdapter($cachePool, new ArrayAdapter());

        $cache->get('election_dex_list_123', fn ($item) => $item->tag(['trainer#123']) && false ?: 'whatever');
        $cache->get('election_dex_list_456', fn ($item) => $item->tag(['trainer#456']) && false ?: 'whatever');

        $service = new ElectionCacheInvalidatorService($cache);
        $service->invalidate('123');

        $this->assertFalse($cache->hasItem('election_dex_list_123'));
        $this->assertTrue($cache->hasItem('election_dex_list_456'));
    }

    public function testInvalidateMock(): void
    {
        $cache = $this->createMock(TagAwareAdapter::class);
        $cache
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(['trainer#123'])
        ;

        $service = new ElectionCacheInvalidatorService($cache);
        $service->invalidate('123');
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Cache/KeyMakerTest.php tests/src/Unit/Service/CacheInvalidator/ElectionCacheInvalidatorServiceTest.php`
Expected: FAIL — `Call to undefined method App\Cache\KeyMaker::getElectionDexListKeyForTrainer()` / `Class "App\Service\CacheInvalidator\ElectionCacheInvalidatorService" not found`

- [ ] **Step 3: Implement `KeyMaker::getElectionDexListKeyForTrainer()`**

In `src/Cache/KeyMaker.php`, add this method right after `getElectionDexListKey()`:

```php
    /**
     * @param string[] $queryParams
     */
    public static function getElectionDexListKeyForTrainer(string $trainerId, array $queryParams = []): string
    {
        $cacheKeySuffixe = http_build_query($queryParams, '', self::CACHE_KEY_SEPARATOR);

        return self::CACHE_KEY_ELECTION_DEX_LIST.self::CACHE_KEY_SEPARATOR.$trainerId
            .($cacheKeySuffixe ? self::CACHE_KEY_SEPARATOR.$cacheKeySuffixe : '');
    }
```

- [ ] **Step 4: Implement `ElectionCacheInvalidatorService`**

Create `src/Service/CacheInvalidator/ElectionCacheInvalidatorService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Service\CacheInvalidator;

use App\Cache\KeyMaker;

class ElectionCacheInvalidatorService extends AbstractCacheInvalidatorService
{
    public function invalidate(string $trainerId): void
    {
        $this->cache->invalidateTags([
            KeyMaker::getTrainerIdKey($trainerId),
        ]);
    }
}
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Cache/KeyMakerTest.php tests/src/Unit/Service/CacheInvalidator/ElectionCacheInvalidatorServiceTest.php`
Expected: PASS

- [ ] **Step 6: Stage the files**

```bash
git add src/Cache/KeyMaker.php src/Service/CacheInvalidator/ElectionCacheInvalidatorService.php \
  tests/src/Unit/Cache/KeyMakerTest.php tests/src/Unit/Service/CacheInvalidator/ElectionCacheInvalidatorServiceTest.php
```

---

### Task 7: `GetElectionReportApiService` (merges the two api-level election calls)

**Files:**
- Create: `src/Service/Api/GetElectionReportApiService.php`
- Delete: `src/Service/Api/GetElectionTopApiService.php`, `src/Service/Api/GetElectionMetricsApiService.php`
- Delete: `tests/src/Unit/Service/Api/GetElectionTopApiServiceTest.php`, `tests/src/Unit/Service/Api/GetElectionMetricsApiServiceTest.php`
- Test: `tests/src/Unit/Service/Api/GetElectionReportApiServiceTest.php`

**Interfaces:**
- Produces: `GetElectionReportApiService::getReport(string $trainerId, string $dexSlug, string $electionSlug, int $count): array{top: array<int, array<string, mixed>>, metrics: array{view_count: array{sum: int, max: int}, win_count: array{sum: int, max: int}, completion: array{under_max_count: int, at_max_count: int}, dex_total_count: int}}` — calls `GET /election/{trainerId}/{dexSlug}?election_slug=...&count=...` on pokenini-api (the new merged endpoint from Part A Task 5), uncached (matches today's uncached behavior of `/election/top`/`/election/metrics`).

- [ ] **Step 1: Write the failing test**

Create `tests/src/Unit/Service/Api/GetElectionReportApiServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Api;

use App\Service\Api\GetElectionReportApiService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(GetElectionReportApiService::class)]
final class GetElectionReportApiServiceTest extends TestCase
{
    private ArrayAdapter $cachePool;
    private TagAwareAdapter $cache;

    public function testGetReport(): void
    {
        $rawJson = json_encode([
            'top' => array_fill(0, 5, ['pokemon' => ['slug' => 'bulbasaur']]),
            'metrics' => [
                'view_count' => ['sum' => 6, 'max' => 1],
                'win_count' => ['sum' => 2, 'max' => 1],
                'completion' => ['under_max_count' => 1, 'at_max_count' => 5],
                'dex_total_count' => 48,
            ],
        ]);

        $result = $this->getService('4564650', 'home', 'fav', 5, (string) $rawJson)
            ->getReport('4564650', 'home', 'fav', 5)
        ;

        $this->assertCount(5, $result['top']);
        $this->assertSame(
            [
                'view_count' => ['sum' => 6, 'max' => 1],
                'win_count' => ['sum' => 2, 'max' => 1],
                'completion' => ['under_max_count' => 1, 'at_max_count' => 5],
                'dex_total_count' => 48,
            ],
            $result['metrics'],
        );

        $this->assertEmpty($this->cachePool->getValues());
    }

    private function getService(
        string $trainerId,
        string $dexSlug,
        string $electionSlug,
        int $count,
        string $json,
    ): GetElectionReportApiService {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->exactly(2))
            ->method('info')
        ;

        $client = $this->createMock(HttpClientInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json)
        ;

        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.domain/election/{$trainerId}/{$dexSlug}",
                [
                    'query' => [
                        'election_slug' => $electionSlug,
                        'count' => $count,
                    ],
                    'headers' => [
                        'accept' => 'application/json',
                    ],
                    'auth_basic' => [
                        'web',
                        'douze',
                    ],
                    'cafile' => './resources/certificates/cacert.pem',
                ],
            )
            ->willReturn($response)
        ;

        $this->cachePool = new ArrayAdapter();
        $this->cache = new TagAwareAdapter($this->cachePool, new ArrayAdapter());

        return new GetElectionReportApiService(
            $logger,
            $client,
            'https://api.domain',
            './resources/certificates/cacert.pem',
            $this->cache,
            'web',
            'douze',
        );
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/Api/GetElectionReportApiServiceTest.php`
Expected: FAIL — `Class "App\Service\Api\GetElectionReportApiService" not found`

- [ ] **Step 3: Implement `GetElectionReportApiService`**

Create `src/Service/Api/GetElectionReportApiService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Service\Api;

use App\Utils\JsonDecoder;

class GetElectionReportApiService extends AbstractApiService
{
    /**
     * @return array{
     *   top: array<int, array{
     *     pokemon: array{
     *       slug: string,
     *       labels: array{name: string, simplified_name: string, french_name: string, simplified_french_name: string, forms_label: null|string, forms_french_label: null|string},
     *       national_dex_number: int,
     *       regional_dex_number: null|int,
     *       icon: string,
     *       family_order: int,
     *       family_lead: array{slug: string},
     *       original_game_bundle: array{slug: string},
     *       order_number: string,
     *       game_bundles: array{normal: array<int, array{slug: string}>, shiny: array<int, array{slug: string}>},
     *     },
     *     forms: null|array{variant?: array{slug: string, name: string, french_name: string}, regional?: array{slug: string, name: string, french_name: string}, special?: array{slug: string, name: string, french_name: string}},
     *     types: array{primary: array{slug: string, name: string, french_name: string, color: string}, secondary: null|array{slug: string, name: string, french_name: string, color: string}},
     *     score: array{elo: int, significance: bool},
     *   }>,
     *   metrics: array{
     *     view_count: array{sum: int, max: int},
     *     win_count: array{sum: int, max: int},
     *     completion: array{under_max_count: int, at_max_count: int},
     *     dex_total_count: int,
     *   },
     * }
     */
    public function getReport(
        string $trainerId,
        string $dexSlug,
        string $electionSlug,
        int $count,
    ): array {
        $json = $this->requestContent(
            'GET',
            "/election/{$trainerId}/{$dexSlug}",
            [
                'query' => [
                    'election_slug' => $electionSlug,
                    'count' => $count,
                ],
            ],
        );

        /** @var array{top: array<int, array{pokemon: array{slug: string, labels: array{name: string, simplified_name: string, french_name: string, simplified_french_name: string, forms_label: null|string, forms_french_label: null|string}, national_dex_number: int, regional_dex_number: null|int, icon: string, family_order: int, family_lead: array{slug: string}, original_game_bundle: array{slug: string}, order_number: string, game_bundles: array{normal: array<int, array{slug: string}>, shiny: array<int, array{slug: string}>}}, forms: null|array{variant?: array{slug: string, name: string, french_name: string}, regional?: array{slug: string, name: string, french_name: string}, special?: array{slug: string, name: string, french_name: string}}, types: array{primary: array{slug: string, name: string, french_name: string, color: string}, secondary: null|array{slug: string, name: string, french_name: string, color: string}}, score: array{elo: int, significance: bool}}>, metrics: array{view_count: array{sum: int, max: int}, win_count: array{sum: int, max: int}, completion: array{under_max_count: int, at_max_count: int}, dex_total_count: int}} */
        return JsonDecoder::decode($json);
    }
}
```

- [ ] **Step 4: Delete the two obsolete api services and their tests**

```bash
rm src/Service/Api/GetElectionTopApiService.php
rm src/Service/Api/GetElectionMetricsApiService.php
rm tests/src/Unit/Service/Api/GetElectionTopApiServiceTest.php
rm tests/src/Unit/Service/Api/GetElectionMetricsApiServiceTest.php
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/Api/GetElectionReportApiServiceTest.php`
Expected: PASS. `GetElectionTopService`/`GetElectionMetricsService` still reference the deleted classes — expected, fixed in Task 8; do not run the full suite yet.

- [ ] **Step 6: Stage the files**

```bash
git add src/Service/Api/GetElectionReportApiService.php tests/src/Unit/Service/Api/GetElectionReportApiServiceTest.php
git add -u src/Service/Api/GetElectionTopApiService.php src/Service/Api/GetElectionMetricsApiService.php \
  tests/src/Unit/Service/Api/GetElectionTopApiServiceTest.php tests/src/Unit/Service/Api/GetElectionMetricsApiServiceTest.php
```

---

### Task 8: `GetElectionReportService` (merges orchestration) + `ElectionIndexController`

**Files:**
- Create: `src/Service/GetElectionReportService.php`
- Modify: `src/Controller/Election/ElectionIndexController.php`
- Delete: `src/Service/GetElectionTopService.php`, `src/Service/GetElectionMetricsService.php`
- Delete: `tests/src/Unit/Service/GetElectionTopServiceTest.php`, `tests/src/Unit/Service/GetElectionMetricsServiceTest.php`
- Test: `tests/src/Unit/Service/GetElectionReportServiceTest.php`

**Interfaces:**
- Consumes: `GetElectionReportApiService::getReport()` (Task 7), `UserTokenService::getLoggedUserToken(): string` (existing, unchanged), `App\DTO\ElectionMetrics` (existing, unchanged — still constructed the same way, `new ElectionMetrics(array $rawMetrics, int $perViewCount)`).
- Produces: `GetElectionReportService::getReport(string $dexSlug, string $electionSlug): array{top: array<int, array<string, mixed>>, metrics: ElectionMetrics}`. Config bindings `int $topCount` / `int $electionCandidateCount` are already wired in `config/services.yaml` by parameter name (`%env(ELECTION_TOP_COUNT)%` / `%env(ELECTION_CANDIDATE_COUNT)%`) — reuse those exact parameter names in the new service's constructor so no `services.yaml` change is needed.

- [ ] **Step 1: Write the failing test**

Create `tests/src/Unit/Service/GetElectionReportServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Security\UserTokenService;
use App\Service\Api\GetElectionReportApiService;
use App\Service\GetElectionReportService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GetElectionReportService::class)]
final class GetElectionReportServiceTest extends TestCase
{
    public function testGetReport(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService
            ->expects($this->once())
            ->method('getLoggedUserToken')
            ->willReturn('8800088')
        ;

        $apiTop = [
            [
                'pokemon' => ['slug' => 'bulbasaur'],
                'forms' => null,
                'types' => ['primary' => ['slug' => 'grass', 'name' => 'Grass', 'french_name' => 'Plante', 'color' => '#78C850'], 'secondary' => null],
                'score' => ['elo' => 1040, 'significance' => true],
            ],
        ];
        $rawMetrics = [
            'view_count' => ['sum' => 12, 'max' => 4],
            'win_count' => ['sum' => 5, 'max' => 14],
            'completion' => ['under_max_count' => 24, 'at_max_count' => 5],
            'dex_total_count' => 48,
        ];

        $apiService = $this->createMock(GetElectionReportApiService::class);
        $apiService
            ->expects($this->once())
            ->method('getReport')
            ->with('8800088', 'demo', 'whatever', 12)
            ->willReturn(['top' => $apiTop, 'metrics' => $rawMetrics])
        ;

        $service = new GetElectionReportService($userTokenService, $apiService, 12, 12);

        $result = $service->getReport('demo', 'whatever');

        $this->assertSame($apiTop, $result['top']);
        $this->assertSame(24, $result['metrics']->underMaxViewCount);
        $this->assertSame(5, $result['metrics']->maxViewCount);
        $this->assertSame($rawMetrics, $result['metrics']->raw);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/GetElectionReportServiceTest.php`
Expected: FAIL — `Class "App\Service\GetElectionReportService" not found`

- [ ] **Step 3: Implement `GetElectionReportService`**

Create `src/Service/GetElectionReportService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ElectionMetrics;
use App\Security\UserTokenService;
use App\Service\Api\GetElectionReportApiService;

class GetElectionReportService
{
    public function __construct(
        private readonly UserTokenService $userTokenService,
        private readonly GetElectionReportApiService $apiService,
        private readonly int $topCount,
        private readonly int $electionCandidateCount,
    ) {}

    /**
     * @return array{top: array<int, array<string, mixed>>, metrics: ElectionMetrics}
     */
    public function getReport(string $dexSlug, string $electionSlug): array
    {
        $trainerId = $this->userTokenService->getLoggedUserToken();

        $data = $this->apiService->getReport($trainerId, $dexSlug, $electionSlug, $this->topCount);

        return [
            'top' => $data['top'],
            'metrics' => new ElectionMetrics($data['metrics'], $this->electionCandidateCount),
        ];
    }
}
```

- [ ] **Step 4: Update `ElectionIndexController`**

In `src/Controller/Election/ElectionIndexController.php`, replace the two `use` statements:

```php
use App\Service\GetElectionMetricsService;
use App\Service\GetElectionTopService;
```

with:

```php
use App\Service\GetElectionReportService;
```

Replace the `index()` signature's two constructor-injected services:

```php
    public function index(
        GetPokemonsListService $getPokemonsListService,
        GetElectionTopService $electionTopService,
        GetElectionMetricsService $metricsService,
        GetTrainerPokedexService $getTrainerPokedexService,
        Request $request,
        SerializerInterface $serializer,
        string $dexSlug,
        string $electionSlug = '',
    ): JsonResponse {
        $filters = FromRequest::get($request);

        $electionTop = $electionTopService->getTop($dexSlug, $electionSlug);

        $list = $getPokemonsListService->get($dexSlug, $electionSlug, $filters);
        $metrics = $metricsService->getMetrics($dexSlug, $electionSlug);
```

with:

```php
    public function index(
        GetPokemonsListService $getPokemonsListService,
        GetElectionReportService $electionReportService,
        GetTrainerPokedexService $getTrainerPokedexService,
        Request $request,
        SerializerInterface $serializer,
        string $dexSlug,
        string $electionSlug = '',
    ): JsonResponse {
        $filters = FromRequest::get($request);

        $report = $electionReportService->getReport($dexSlug, $electionSlug);
        $electionTop = $report['top'];

        $list = $getPokemonsListService->get($dexSlug, $electionSlug, $filters);
        $metrics = $report['metrics'];
```

The rest of the method (building `$detachedCount`, `$isTheLastPage`, `$isTheLastOne`, and the final `JsonResponse`) is unchanged.

- [ ] **Step 5: Delete the two obsolete orchestration services and their tests**

```bash
rm src/Service/GetElectionTopService.php
rm src/Service/GetElectionMetricsService.php
rm tests/src/Unit/Service/GetElectionTopServiceTest.php
rm tests/src/Unit/Service/GetElectionMetricsServiceTest.php
```

- [ ] **Step 6: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/GetElectionReportServiceTest.php`
Expected: PASS. The integration test `tests/src/Integration/Election/ElectionIndexTest.php` will still fail at this point (its moco mocks target the old separate `/election/top`+`/election/metrics` calls) — that's expected, fixed in Task 11.

- [ ] **Step 7: Stage the files**

```bash
git add src/Service/GetElectionReportService.php src/Controller/Election/ElectionIndexController.php \
  tests/src/Unit/Service/GetElectionReportServiceTest.php
git add -u src/Service/GetElectionTopService.php src/Service/GetElectionMetricsService.php \
  tests/src/Unit/Service/GetElectionTopServiceTest.php tests/src/Unit/Service/GetElectionMetricsServiceTest.php
```

---

### Task 9: `GetElectionDexListApiService` becomes trainer-scoped and cached-per-trainer + `ElectionDexListController`

**Files:**
- Modify: `src/Service/Api/GetElectionDexListApiService.php`
- Modify: `src/Controller/Election/ElectionDexListController.php`
- Test: `tests/src/Unit/Service/Api/GetElectionDexListApiServiceTest.php` (rewrite)

**Interfaces:**
- Consumes: `KeyMaker::getElectionDexListKeyForTrainer(string $trainerId, array $queryParams = []): string` (Task 6), `UserTokenService::getLoggedUserToken(): string` (existing, unchanged).
- Produces: `GetElectionDexListApiService::get(string $trainerId): array`, `::getWithPremium(string $trainerId): array`, `::getWithUnreleasedAndPremium(string $trainerId): array` — each now calls `GET /election/{trainerId}/list?count=0[&include_premium_dex=1][&include_unreleased_dex=1]` (pokenini-api's new endpoint from Part A Task 5). `count=0` is passed explicitly because this call only needs dex identity + metrics for the badge, not the (heavy, nested-Pokemon) `top` array. Cache key now includes the trainer id; cache tags gain `KeyMaker::getTrainerIdKey($trainerId)` alongside the existing `dex`/`election_dex_list` tags.

- [ ] **Step 1: Rewrite the failing test**

Replace the full contents of `tests/src/Unit/Service/Api/GetElectionDexListApiServiceTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Api;

use App\Service\Api\GetElectionDexListApiService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(GetElectionDexListApiService::class)]
final class GetElectionDexListApiServiceTest extends TestCase
{
    private ArrayAdapter $cachePool;
    private TagAwareAdapter $cache;

    public function testGet(): void
    {
        $expectedSlugs = ['homeshiny'];

        $this->assertEquals(
            $expectedSlugs,
            self::extractSlugs($this->getService('8800088', 'election/8800088/list?count=0')->get('8800088')),
        );

        $cacheItem = $this->cache->getItem('election_dex_list_8800088');

        /** @var string $value */
        $value = $cacheItem->get();

        /** @var string[][] */
        $jsonData = json_decode($value, true);

        $this->assertEquals($expectedSlugs, self::extractSlugs($jsonData));

        $this->assertSame(
            [
                'dex' => 'dex',
                'election_dex_list' => 'election_dex_list',
                'trainer#8800088' => 'trainer#8800088',
            ],
            $cacheItem->getMetadata()['tags'],
        );
    }

    public function testGetWithPremium(): void
    {
        $expectedSlugs = ['home', 'redgreenblueyellow'];

        $this->assertEquals(
            $expectedSlugs,
            self::extractSlugs(
                $this->getService('8800088', 'election/8800088/list?count=0&include_premium_dex=1')->getWithPremium('8800088'),
            ),
        );

        $cacheItem = $this->cache->getItem('election_dex_list_8800088_include_premium_dex=1');

        $this->assertSame(
            [
                'dex' => 'dex',
                'election_dex_list' => 'election_dex_list',
                'trainer#8800088' => 'trainer#8800088',
            ],
            $cacheItem->getMetadata()['tags'],
        );
    }

    public function testGetWithUnreleasedAndPremium(): void
    {
        $expectedSlugs = ['home', 'homeshiny', 'redgreenblueyellow', 'redgreenblueyellowshiny'];

        $this->assertEquals(
            $expectedSlugs,
            self::extractSlugs(
                $this
                    ->getService('8800088', 'election/8800088/list?count=0&include_unreleased_dex=1&include_premium_dex=1')
                    ->getWithUnreleasedAndPremium('8800088'),
            ),
        );

        $cacheItem = $this->cache->getItem('election_dex_list_8800088_include_unreleased_dex=1_include_premium_dex=1');

        $this->assertSame(
            [
                'dex' => 'dex',
                'election_dex_list' => 'election_dex_list',
                'trainer#8800088' => 'trainer#8800088',
            ],
            $cacheItem->getMetadata()['tags'],
        );
    }

    public function testGetIsScopedPerTrainer(): void
    {
        $expectedSlugs = ['homeshiny'];

        $service = $this->getService('123', 'election/123/list?count=0');
        $this->assertEquals($expectedSlugs, self::extractSlugs($service->get('123')));

        $this->assertTrue($this->cache->hasItem('election_dex_list_123'));
        $this->assertFalse($this->cache->hasItem('election_dex_list_456'));
    }

    private function getService(string $trainerId, string $endpoint): GetElectionDexListApiService
    {
        $json = (string) file_get_contents(
            '/app/tests/resources/unit/service/api/election_dex_list.json'
        );

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->exactly(2))
            ->method('info')
        ;

        $client = $this->createMock(HttpClientInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json)
        ;

        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.domain/{$endpoint}",
                [
                    'headers' => [
                        'accept' => 'application/json',
                    ],
                    'auth_basic' => [
                        'web',
                        'douze',
                    ],
                    'cafile' => './resources/certificates/cacert.pem',
                ],
            )
            ->willReturn($response)
        ;

        $this->cachePool = new ArrayAdapter();
        $this->cache = new TagAwareAdapter($this->cachePool, new ArrayAdapter());

        return new GetElectionDexListApiService(
            $logger,
            $client,
            'https://api.domain',
            './resources/certificates/cacert.pem',
            $this->cache,
            'web',
            'douze',
        );
    }

    /**
     * @param string[][] $items
     *
     * @return string[]
     */
    private static function extractSlugs(array $items): array
    {
        $slugs = [];

        foreach ($items as $item) {
            $slugs[] = $item['slug'];
        }

        return $slugs;
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/Api/GetElectionDexListApiServiceTest.php`
Expected: FAIL — `Too few arguments to function App\Service\Api\GetElectionDexListApiService::get()`

- [ ] **Step 3: Update `GetElectionDexListApiService`**

Replace the full contents of `src/Service/Api/GetElectionDexListApiService.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Service\Api;

use App\Cache\KeyMaker;
use App\Utils\JsonDecoder;
use Symfony\Contracts\Cache\ItemInterface;

class GetElectionDexListApiService extends AbstractApiService
{
    /**
     * @return string[][]
     */
    public function get(string $trainerId): array
    {
        return $this->getDexWithParam($trainerId, []);
    }

    /**
     * @return string[][]
     */
    public function getWithPremium(string $trainerId): array
    {
        return $this->getDexWithParam($trainerId, [
            'include_premium_dex' => '1',
        ]);
    }

    /**
     * @return string[][]
     */
    public function getWithUnreleasedAndPremium(string $trainerId): array
    {
        return $this->getDexWithParam($trainerId, [
            'include_unreleased_dex' => '1',
            'include_premium_dex' => '1',
        ]);
    }

    /**
     * @param string[] $queryParams
     *
     * @return string[][]
     */
    private function getDexWithParam(string $trainerId, array $queryParams = []): array
    {
        $key = KeyMaker::getElectionDexListKeyForTrainer($trainerId, $queryParams);

        $urlQueryParams = http_build_query(array_merge(['count' => '0'], $queryParams));

        $json = $this->cache->get($key, function (ItemInterface $item) use ($trainerId, $urlQueryParams) {
            $item->tag([
                KeyMaker::getDexKey(),
                KeyMaker::getElectionDexListKey(),
                KeyMaker::getTrainerIdKey($trainerId),
            ]);

            return $this->requestContent(
                'GET',
                "/election/{$trainerId}/list?{$urlQueryParams}",
            );
        });

        /** @var string[][] */
        return JsonDecoder::decode($json);
    }
}
```

- [ ] **Step 4: Update `ElectionDexListController`**

Replace the full contents of `src/Controller/Election/ElectionDexListController.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Controller\Election;

use App\Security\UserTokenService;
use App\Service\Api\GetElectionDexListApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/election')]
final class ElectionDexListController extends AbstractController
{
    #[Route(
        '/dex',
        methods: ['GET'],
        priority: 2,
    )]
    public function index(
        GetElectionDexListApiService $getDexListService,
        UserTokenService $userTokenService,
    ): JsonResponse {
        $trainerId = $userTokenService->getLoggedUserToken();

        switch (true) {
            case $this->isGranted('ROLE_ADMIN'):
                $dex = $getDexListService->getWithUnreleasedAndPremium($trainerId);

                break;

            case $this->isGranted('ROLE_COLLECTOR'):
                $dex = $getDexListService->getWithPremium($trainerId);

                break;

            default:
                $dex = $getDexListService->get($trainerId);

                break;
        }

        return new JsonResponse($dex, Response::HTTP_OK);
    }
}
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/Api/GetElectionDexListApiServiceTest.php`
Expected: PASS (4 tests). The integration test `tests/src/Integration/Election/ElectionDexListTest.php` will still fail (moco mocks target the old unauthenticated-shape `/dex/can_hold_election` call) — expected, fixed in Task 11.

- [ ] **Step 6: Stage the files**

```bash
git add src/Service/Api/GetElectionDexListApiService.php src/Controller/Election/ElectionDexListController.php \
  tests/src/Unit/Service/Api/GetElectionDexListApiServiceTest.php
```

---

### Task 10: `ModifyElectionVoteService` invalidates the trainer-scoped election dex-list cache on vote

**Files:**
- Modify: `src/Service/ModifyElectionVoteService.php`
- Test: `tests/src/Unit/Service/ModifyElectionVoteServiceTest.php`

**Interfaces:**
- Consumes: `ElectionCacheInvalidatorService::invalidate(string $trainerId): void` (Task 6).

- [ ] **Step 1: Update the failing test**

In `tests/src/Unit/Service/ModifyElectionVoteServiceTest.php`, add the import:

```php
use App\Service\CacheInvalidator\ElectionCacheInvalidatorService;
```

Update every one of the four test methods (`testVote`, `testVoteWinnerAsLoser`, `testVoteAllLosers`, `testVoteAllWinners`) the same way: add an `ElectionCacheInvalidatorService` mock expecting exactly one `invalidate('8800088')` call, and pass it as the third constructor argument. For example, `testVote()` becomes:

```php
    public function testVote(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService
            ->expects($this->once())
            ->method('getLoggedUserToken')
            ->willReturn('8800088')
        ;

        $electionVote = $this->makeVote('demo', 'whatever', ['pichu'], ['pikachu', 'raichu']);

        $apiService = $this->createMock(ModifyElectionVoteApiService::class);
        $apiService
            ->expects($this->once())
            ->method('vote')
            ->with(
                '8800088',
                $electionVote,
            )
        ;

        $electionCacheInvalidatorService = $this->createMock(ElectionCacheInvalidatorService::class);
        $electionCacheInvalidatorService
            ->expects($this->once())
            ->method('invalidate')
            ->with('8800088')
        ;

        $service = new ModifyElectionVoteService($userTokenService, $apiService, $electionCacheInvalidatorService);
        $service->vote($electionVote);
    }
```

Apply the identical `$electionCacheInvalidatorService` mock + constructor argument change to `testVoteWinnerAsLoser`, `testVoteAllLosers`, and `testVoteAllWinners` (only the `makeVote(...)` arguments differ between these four methods; everything else about the invalidator mock and constructor call is the same).

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/ModifyElectionVoteServiceTest.php`
Expected: FAIL — `Too few arguments to function App\Service\ModifyElectionVoteService::__construct()`

- [ ] **Step 3: Update `ModifyElectionVoteService`**

Replace the full contents of `src/Service/ModifyElectionVoteService.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ElectionVote;
use App\Security\UserTokenService;
use App\Service\Api\ModifyElectionVoteApiService;
use App\Service\CacheInvalidator\ElectionCacheInvalidatorService;

class ModifyElectionVoteService
{
    public function __construct(
        private readonly UserTokenService $userTokenService,
        private readonly ModifyElectionVoteApiService $apiService,
        private readonly ElectionCacheInvalidatorService $electionCacheInvalidatorService,
    ) {}

    public function vote(ElectionVote $electionVote): void
    {
        $trainerId = $this->userTokenService->getLoggedUserToken();

        $this->apiService->vote($trainerId, $electionVote);

        $this->electionCacheInvalidatorService->invalidate($trainerId);
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/ModifyElectionVoteServiceTest.php`
Expected: PASS (4 tests)

- [ ] **Step 5: Stage the files**

```bash
git add src/Service/ModifyElectionVoteService.php tests/src/Unit/Service/ModifyElectionVoteServiceTest.php
```

---

### Task 11: Update Moco fixtures and integration tests for the merged/trainer-scoped endpoints

**Files:**
- Modify: `tests/resources/moco/Api/moco.json`
- Modify/rename fixture files under `tests/resources/moco/Api/responses/election/`
- Modify: `tests/src/Integration/Election/ElectionIndexTest.php`
- Modify: `tests/src/Integration/Election/ElectionDexListTest.php`

**Interfaces:**
- No new production interfaces — this task only updates test fixtures/mocks so the integration tests exercise the new pokenini-api contract (`GET /election/{trainerId}/{dexSlug}` merged response, `GET /election/{trainerId}/list` trainer-scoped response) instead of the retired one.

- [ ] **Step 1: Replace the moco.json entries for `/election/top` + `/election/metrics`**

Open `tests/resources/moco/Api/moco.json`. Find the five `/election/metrics` entries and the one `/election/top` entry (identified during research: lines ~906, 926, 943, 960, 977, 994). Replace all six with entries matching the new merged path. For each existing `dex_slug` variant (`demolite`-prefixed match, `demolitelastpage`, `demolitelastone`, `demolitenotlastpage`, `demolitenotlastone`), create one merged entry, e.g. replacing the old `/election/top` (regex-matched `dex_slug`) + catch-all `/election/metrics` pair with:

```json
{
  "request": {
    "uri": {
      "match": "^/election/[^/]+/demolite.*$"
    },
    "method": "get",
    "headers": {
      "accept": "application/json",
      "authorization": "Basic d2ViOmRvdXpl"
    }
  },
  "response": {
    "status": "200",
    "file": "/var/moco/responses/election/demolite_report_5.json"
  }
}
```

And for each of the four specific `dex_slug` overrides that previously had their own `/election/metrics` entry (`demolitelastpage`, `demolitelastone`, `demolitenotlastpage`, `demolitenotlastone`), add a more specific entry above the catch-all (moco matches top-down, most specific first):

```json
{
  "request": {
    "uri": {
      "match": "^/election/[^/]+/demolitelastpage$"
    },
    "method": "get",
    "headers": {
      "accept": "application/json",
      "authorization": "Basic d2ViOmRvdXpl"
    }
  },
  "response": {
    "status": "200",
    "file": "/var/moco/responses/election/demolitelastpage_report.json"
  }
}
```

(repeat for `demolitelastone`, `demolitenotlastpage`, `demolitenotlastone`, each pointing at its own `_report.json` file).

- [ ] **Step 2: Build the new merged response fixtures**

For each fixture file you just referenced (`demolite_report_5.json`, `demolitelastpage_report.json`, `demolitelastone_report.json`, `demolitenotlastpage_report.json`, `demolitenotlastone_report.json`), create it under `tests/resources/moco/Api/responses/election/` by combining the **existing** `demolite_top_5.json` (or that scenario's top fixture) and the corresponding `_metrics.json` file into the new `{top, metrics}` shape:

```json
{
  "top": <paste the array contents of the old demolite_top_5.json here>,
  "metrics": <paste the object contents of the old demolite<x>_metrics.json here>
}
```

Do this for all five files (reusing the existing `demolite_top_5.json` array for all five `top` fields, since the old `/election/top` moco entry was itself a single catch-all regex covering every `demolite*` dex slug — only `metrics` varies per scenario). Delete the five old standalone files (`demolite_top_5.json`, `demolitelastpage_metrics.json`, `demolitelastone_metrics.json`, `demolitenotlastpage_metrics.json`, `demolitenotlastone_metrics.json`, `demolite_metrics.json`) once every merged file is built and verified against the assertions in `ElectionIndexTest`'s data provider scenarios (`demolite`, `demolitelastpage`, `demolitelastone`, `demolitenotlastpage`, `demolitenotlastone`).

- [ ] **Step 3: Replace the three `/dex/can_hold_election` moco entries with trainer-scoped `/election/{trainerId}/list` entries**

Find the trainer id used by `ElectionDexListTest` (test user `trainer`, from `GetUserToken::getFakeUserToken`) and the admin-flavored one (`789465465489`) referenced by the moco config's existing `election_dex_list_admin.json` mapping. Replace the three `/dex/can_hold_election` entries with:

```json
{
  "request": {
    "uri": {
      "match": "^/election/[^/]+/list\\?count=0$"
    },
    "method": "get",
    "headers": {
      "accept": "application/json",
      "authorization": "Basic d2ViOmRvdXpl"
    }
  },
  "response": {
    "status": "200",
    "file": "/var/moco/responses/election_dex_list.json"
  }
}
```

```json
{
  "request": {
    "uri": {
      "match": "^/election/[^/]+/list\\?count=0&include_premium_dex=1$"
    },
    "method": "get",
    "headers": {
      "accept": "application/json",
      "authorization": "Basic d2ViOmRvdXpl"
    }
  },
  "response": {
    "status": "200",
    "file": "/var/moco/responses/election_dex_list_include_premium_dex1.json"
  }
}
```

```json
{
  "request": {
    "uri": {
      "match": "^/election/[^/]+/list\\?count=0&include_unreleased_dex=1&include_premium_dex=1$"
    },
    "method": "get",
    "headers": {
      "accept": "application/json",
      "authorization": "Basic d2ViOmRvdXpl"
    }
  },
  "response": {
    "status": "200",
    "file": "/var/moco/responses/election_dex_list_include_premium_dex1_include_unreleased_dex1.json"
  }
}
```

Since these entries no longer distinguish by trainer id (they match any trainer segment via `[^/]+`), the existing response bodies (`election_dex_list.json` etc., which only had `slug`/`original_slug`/`name`/... — no `report` key) keep working unchanged for `GetElectionDexListApiServiceTest`'s moco-level coverage. Each per-trainer response body would need a `report` key too if pokenini-back started asserting on it — it does not (`ElectionDexListController` only re-serves the raw JSON array), so no fixture content change is needed here beyond the URI match update.

- [ ] **Step 4: Run the affected integration tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Election/ElectionIndexTest.php tests/src/Integration/Election/ElectionDexListTest.php`
Expected: PASS. If any `demolite*` scenario fails on an exact metrics value, open the corresponding old `*_metrics.json` file (before deleting it) and double check it was merged verbatim into the new combined fixture — do not invent numbers.

- [ ] **Step 5: Run the full pokenini-back test suite and coverage**

Run: `docker compose exec php php vendor/bin/phpunit` (or `make tests`) then `make coverage` from the pokenini-back directory.
Expected: all green, 100% coverage maintained, no other test references the deleted classes/fixtures (`grep -rn "GetElectionTopApiService\|GetElectionMetricsApiService\|GetElectionTopService\|GetElectionMetricsService" src tests` should return nothing).

- [ ] **Step 6: Stage the files**

```bash
git add tests/resources/moco/Api/moco.json tests/resources/moco/Api/responses/election/ \
  tests/src/Integration/Election/ElectionIndexTest.php tests/src/Integration/Election/ElectionDexListTest.php
```

---

## Part C — pokenini-web (`/home/renaud/projects/pokenini-web`)

### Task 12: `ElectionDexListItem.report` (new response objects mirroring Album's `Report`)

**Files:**
- Create: `src/ResponseObject/Election/ElectionReport.php`, `src/ResponseObject/Election/ElectionReportMetrics.php`, `src/ResponseObject/Election/ElectionReportCompletion.php`
- Modify: `src/ResponseObject/Election/ElectionDexListItem.php`
- Test: `tests/src/Unit/ResponseObject/Election/ElectionReportTest.php`, `tests/src/Unit/ResponseObject/Election/ElectionReportMetricsTest.php`, `tests/src/Unit/ResponseObject/Election/ElectionReportCompletionTest.php`
- Test: `tests/src/Unit/ResponseObject/Election/ElectionDexListItemTest.php` (modify)

**Interfaces:**
- Produces: `ElectionDexListItem::getReport(): ?ElectionReport` (nullable, defaults to `null` — same defensive-deserialization rationale as Album's `DexListItem::getReport()`: a mid-rollout cached response might briefly lack the `report` key). `ElectionReport::getMetrics(): ElectionReportMetrics`. `ElectionReportMetrics::getCompletion(): ElectionReportCompletion`, `::getDexTotalCount(): int`. `ElectionReportCompletion::getAtMaxCount(): int`, `::getUnderMaxCount(): int`. These three new classes deliberately do **not** model the API's `top` field at all (Symfony's serializer silently ignores JSON keys with no matching constructor parameter) — the dex-list badge only ever needs `metrics.completion`/`metrics.dex_total_count`.

- [ ] **Step 1: Write the failing tests**

Create `tests/src/Unit/ResponseObject/Election/ElectionReportCompletionTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\ResponseObject\Election;

use App\ResponseObject\Election\ElectionReportCompletion;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionReportCompletion::class)]
final class ElectionReportCompletionTest extends TestCase
{
    public function testGetters(): void
    {
        $completion = new ElectionReportCompletion(atMaxCount: 5, underMaxCount: 1);

        $this->assertSame(5, $completion->getAtMaxCount());
        $this->assertSame(1, $completion->getUnderMaxCount());
    }
}
```

Create `tests/src/Unit/ResponseObject/Election/ElectionReportMetricsTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\ResponseObject\Election;

use App\ResponseObject\Election\ElectionReportCompletion;
use App\ResponseObject\Election\ElectionReportMetrics;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionReportMetrics::class)]
final class ElectionReportMetricsTest extends TestCase
{
    public function testGetters(): void
    {
        $completion = new ElectionReportCompletion(atMaxCount: 5, underMaxCount: 1);
        $metrics = new ElectionReportMetrics(completion: $completion, dexTotalCount: 48);

        $this->assertSame($completion, $metrics->getCompletion());
        $this->assertSame(48, $metrics->getDexTotalCount());
    }
}
```

Create `tests/src/Unit/ResponseObject/Election/ElectionReportTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\ResponseObject\Election;

use App\ResponseObject\Election\ElectionReport;
use App\ResponseObject\Election\ElectionReportCompletion;
use App\ResponseObject\Election\ElectionReportMetrics;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionReport::class)]
final class ElectionReportTest extends TestCase
{
    public function testGetters(): void
    {
        $metrics = new ElectionReportMetrics(
            completion: new ElectionReportCompletion(atMaxCount: 5, underMaxCount: 1),
            dexTotalCount: 48,
        );
        $report = new ElectionReport(metrics: $metrics);

        $this->assertSame($metrics, $report->getMetrics());
    }
}
```

Replace the full contents of `tests/src/Unit/ResponseObject/Election/ElectionDexListItemTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\ResponseObject\Election;

use App\ResponseObject\Album\DexFlags;
use App\ResponseObject\Election\ElectionDexListItem;
use App\ResponseObject\Election\ElectionReport;
use App\ResponseObject\Election\ElectionReportCompletion;
use App\ResponseObject\Election\ElectionReportMetrics;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionDexListItem::class)]
final class ElectionDexListItemTest extends TestCase
{
    public function testGetters(): void
    {
        $flags = new DexFlags(
            isShiny: false,
            isPrivate: false,
            isOnHome: true,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        $item = new ElectionDexListItem(
            slug: 'swordshield',
            originalSlug: 'swordshield',
            name: 'Sword, Shield',
            frenchName: 'Épée, Bouclier',
            flags: $flags,
            displayTemplate: 'box',
            description: 'A description',
            frenchDescription: 'Une description',
            dexTotalCount: 832,
        );

        $this->assertSame('swordshield', $item->getSlug());
        $this->assertSame('swordshield', $item->getOriginalSlug());
        $this->assertSame('Sword, Shield', $item->getName());
        $this->assertSame('Épée, Bouclier', $item->getFrenchName());
        $this->assertSame($flags, $item->getFlags());
        $this->assertSame('box', $item->getDisplayTemplate());
        $this->assertSame('A description', $item->getDescription());
        $this->assertSame('Une description', $item->getFrenchDescription());
        $this->assertSame(832, $item->getDexTotalCount());
        $this->assertNull($item->getReport());
    }

    public function testNullableGetters(): void
    {
        $item = new ElectionDexListItem(
            slug: 'test',
            originalSlug: 'test',
            name: 'Test',
            frenchName: 'Test',
            flags: new DexFlags(
                isShiny: false,
                isPrivate: false,
                isOnHome: false,
                isDisplayForm: false,
                isReleased: false,
                isPremium: false,
                isCustom: false,
            ),
            displayTemplate: null,
            description: null,
            frenchDescription: null,
            dexTotalCount: null,
        );

        $this->assertNull($item->getDisplayTemplate());
        $this->assertNull($item->getDescription());
        $this->assertNull($item->getFrenchDescription());
        $this->assertNull($item->getDexTotalCount());
        $this->assertNull($item->getReport());
    }

    public function testGettersWithReport(): void
    {
        $flags = new DexFlags(
            isShiny: false,
            isPrivate: false,
            isOnHome: true,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );
        $report = new ElectionReport(
            metrics: new ElectionReportMetrics(
                completion: new ElectionReportCompletion(atMaxCount: 5, underMaxCount: 1),
                dexTotalCount: 48,
            ),
        );

        $item = new ElectionDexListItem(
            slug: 'swordshield',
            originalSlug: 'swordshield',
            name: 'Sword, Shield',
            frenchName: 'Épée, Bouclier',
            flags: $flags,
            displayTemplate: 'box',
            description: null,
            frenchDescription: null,
            dexTotalCount: null,
            report: $report,
        );

        $this->assertSame($report, $item->getReport());
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/ResponseObject/Election/`
Expected: FAIL — the three new classes don't exist yet, and `ElectionDexListItem`'s constructor doesn't accept a `report` argument.

- [ ] **Step 3: Implement the three new response objects**

Create `src/ResponseObject/Election/ElectionReportCompletion.php`:

```php
<?php

declare(strict_types=1);

namespace App\ResponseObject\Election;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionReportCompletion
{
    public function __construct(
        #[SerializedName('at_max_count')]
        private readonly int $atMaxCount,
        #[SerializedName('under_max_count')]
        private readonly int $underMaxCount,
    ) {}

    public function getAtMaxCount(): int
    {
        return $this->atMaxCount;
    }

    public function getUnderMaxCount(): int
    {
        return $this->underMaxCount;
    }
}
```

Create `src/ResponseObject/Election/ElectionReportMetrics.php`:

```php
<?php

declare(strict_types=1);

namespace App\ResponseObject\Election;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionReportMetrics
{
    public function __construct(
        #[SerializedName('completion')]
        private readonly ElectionReportCompletion $completion,
        #[SerializedName('dex_total_count')]
        private readonly int $dexTotalCount,
    ) {}

    public function getCompletion(): ElectionReportCompletion
    {
        return $this->completion;
    }

    public function getDexTotalCount(): int
    {
        return $this->dexTotalCount;
    }
}
```

Create `src/ResponseObject/Election/ElectionReport.php`:

```php
<?php

declare(strict_types=1);

namespace App\ResponseObject\Election;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionReport
{
    public function __construct(
        #[SerializedName('metrics')]
        private readonly ElectionReportMetrics $metrics,
    ) {}

    public function getMetrics(): ElectionReportMetrics
    {
        return $this->metrics;
    }
}
```

- [ ] **Step 4: Update `ElectionDexListItem`**

In `src/ResponseObject/Election/ElectionDexListItem.php`, add the constructor parameter (after `dexTotalCount`) and getter:

```php
        #[SerializedName('dex_total_count')]
        private readonly ?int $dexTotalCount,
        #[SerializedName('report')]
        private readonly ?ElectionReport $report = null,
    ) {}
```

and add, after `getDexTotalCount()`:

```php
    public function getReport(): ?ElectionReport
    {
        return $this->report;
    }
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/ResponseObject/Election/`
Expected: PASS (7 tests)

- [ ] **Step 6: Stage the files**

```bash
git add src/ResponseObject/Election/ElectionReport.php src/ResponseObject/Election/ElectionReportMetrics.php \
  src/ResponseObject/Election/ElectionReportCompletion.php src/ResponseObject/Election/ElectionDexListItem.php \
  tests/src/Unit/ResponseObject/Election/
```

---

### Task 13: Completion badge on the election dex-list card + translations + Moco fixtures

**Files:**
- Modify: `templates/AlbumDexList/_macro.html.twig`
- Modify: `translations/messages+intl-icu.en.yaml`, `translations/messages+intl-icu.fr.yaml`
- Modify: `tests/resources/moco/Back/responses/election/election_dex_list.json`, `tests/resources/moco/Back/responses/election/election_dex_list_admin.json`
- Modify: `tests/src/Integration/Controller/Election/ElectionDexListTest.php`

**Interfaces:**
- Consumes: `ElectionDexListItem::getReport(): ?ElectionReport` (Task 12).

- [ ] **Step 1: Add translation keys**

In `translations/messages+intl-icu.en.yaml`, right after the existing `total_count_suffixe: Pokémons` line inside `election_dex.dex:` (found via `grep -n "total_count_suffixe" translations/messages+intl-icu.en.yaml`), add:

```yaml
    report_badge_suffixe: rated
```

so the block reads:

```yaml
election_dex:
  title: "Choose the dex you want to vote for"
  subtitle: "Depending on the dex, there are more or fewer Pokémon, and more or fewer forms. It's up to you."
  dex:
    total_count_suffixe: Pokémons
    report_badge_suffixe: rated
```

In `translations/messages+intl-icu.fr.yaml`, same insertion:

```yaml
election_dex:
  title: "Choisir le dex pour lequel tu veux voter"
  subtitle: "Selon le dex, il y'a plus ou moins de pokémons, plus ou moins de formes. C'est à toi de voir"
  dex:
    total_count_suffixe: Pokémons
    report_badge_suffixe: notées
```

- [ ] **Step 2: Add the badge to the `itemElection()` macro**

In `templates/AlbumDexList/_macro.html.twig`, the `itemElection()` macro currently has this block:

```twig
      {% if dex.dexTotalCount is not null %}
      <span class="badge rounded-pill bg-primary mb-3">
        {{ dex.dexTotalCount|number_format(0, '.', ' ') }}
        {{ 'election_dex.dex.total_count_suffixe'|trans }}
      </span>
      {% endif %}

      {% if dex.description is not null %}
```

Replace it with:

```twig
      {% if dex.dexTotalCount is not null %}
      <span class="badge rounded-pill bg-primary mb-3">
        {{ dex.dexTotalCount|number_format(0, '.', ' ') }}
        {{ 'election_dex.dex.total_count_suffixe'|trans }}
      </span>
      {% endif %}

      {% if dex.report is not null %}
      <span class="badge rounded-pill bg-primary mb-3">
        {{ dex.report.metrics.completion.atMaxCount|number_format(0, '.', ' ') }} / {{ dex.report.metrics.dexTotalCount|number_format(0, '.', ' ') }}
        {{ 'election_dex.dex.report_badge_suffixe'|trans }}
      </span>
      {% endif %}

      {% if dex.description is not null %}
```

- [ ] **Step 3: Add `report` to the Moco fixtures**

In `tests/resources/moco/Back/responses/election/election_dex_list.json`, add a `"report"` key to each of the four entries (`home`, `homeshiny`, `homepogo`, `mega`), e.g. for `home`:

```json
{
  "slug": "home",
  "original_slug": "home",
  "name": "Home",
  "french_name": "Home",
  "flags": { "is_shiny": false, "is_private": false, "is_on_home": true, "is_display_form": true, "is_released": true, "is_premium": false, "is_custom": false },
  "display_template": "box",
  "description": "All Pokémons that can be transferred to Pokémon Home.\r\n\r\nIncluding males/female, different shapes and transformations (some are not kept during the transfer, but I wanted to have the Pokémons anyway, in case it changes)",
  "french_description": "Tous les pokémons pouvant être transférés sur Pokémon Home.  les mâles/femelles, les formes différentes et les transformations (certains ne sont pas conservés lors du transfert, mais je voulais avoir les pokémons quand même, au cas où ça change)",
  "dex_total_count": 61,
  "report": {
    "top": [],
    "metrics": {
      "view_count": { "sum": 0, "max": 0 },
      "win_count": { "sum": 0, "max": 0 },
      "completion": { "under_max_count": 61, "at_max_count": 0 },
      "dex_total_count": 61
    }
  }
}
```

Use the entry's own `dex_total_count` value for `report.metrics.dex_total_count` and `report.metrics.completion.under_max_count` (i.e. "nothing rated yet" — `at_max_count: 0`) for all four entries in this file, and do the same for every entry in `election_dex_list_admin.json` (21 entries — same shape, `at_max_count: 0`, `under_max_count` = that entry's own `dex_total_count`).

- [ ] **Step 4: Update the badge-count assertion in `ElectionDexListTest`**

`testIndex()` currently asserts `$this->assertCountFilter($crawler, 24, '.dex-item .badge');` (one `dex_total_count` badge per of the 21 dex, plus the 3 flag badges — `is_premium`/`not_is_released`/`is_custom` — seen elsewhere in the fixture). Since every one of the 21 dex now also renders a completion badge, update the count:

```php
        $this->assertCountFilter($crawler, 45, '.dex-item .badge');
```

(21 pre-existing `dex_total_count` badges + 21 new completion badges + the same 3 flag badges as before = 45.)

- [ ] **Step 5: Run the test suite**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/Election/ElectionDexListTest.php`
Expected: PASS.

Then run: `docker compose exec php php vendor/bin/phpunit` (or `make tests`) for the full pokenini-web suite.
Expected: all green — no other test asserts on the `AlbumDexList/_macro.html.twig` election badge markup or on `election_dex_list*.json` fixture shape besides `ElectionDexListTest`.

- [ ] **Step 6: Stage the files**

```bash
git add templates/AlbumDexList/_macro.html.twig translations/messages+intl-icu.en.yaml translations/messages+intl-icu.fr.yaml \
  tests/resources/moco/Back/responses/election/election_dex_list.json tests/resources/moco/Back/responses/election/election_dex_list_admin.json \
  tests/src/Integration/Controller/Election/ElectionDexListTest.php
```

---

## Notes for whoever executes this plan

- Work through the three parts **in order** (pokenini-api → pokenini-back → pokenini-web) — each part's tasks assume the previous part's contract already exists. Within pokenini-api, Tasks 1–5 are strictly sequential (each depends on the previous task's new class). Within pokenini-back, Tasks 6–11 are sequential for the same reason. Task 12 and Task 13 in pokenini-web are sequential.
- Every task's final step stages files with `git add` — per standing project convention, do not run `git commit` at any point in this plan; the user commits when ready.
- Task 5 (Step 6), Task 11 (Step 5), and Task 13 (Step 5) are the three "full suite + coverage/quality gate" checkpoints — one per repo. Do not skip them even under time pressure; they're what catches a missed call site the per-task steps didn't cover.
