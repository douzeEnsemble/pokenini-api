# API Response Restructuring (Election Metrics) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /election/metrics` endpoint from raw PHP array passed directly to `JsonResponse` to a typed response DTO using the Factory + Serializer pattern already applied to `/types`, `/catch_states`, `/game_bundles`, `/forms/*`, `/collections`, `/dex/can_hold_election`, `/dex/{trainerId}/list`, and `/election/top`.

**Architecture:** Create an immutable `ElectionMetricsResponse` DTO with 7 int properties, an `ElectionMetricsResponseFactory` with a `fromArray()` method (not `fromSqlRows()` — `getMetrics()` returns a single typed associative array, not an array of rows), update `TrainerPokemonEloController::metrics()` to inject `SerializerInterface` and apply the Factory, create a fixture JSON file, and update the existing integration test to also cover the new Factory class.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/ElectionMetricsResponse.php` — immutable DTO with 7 `int` properties matching the output of `TrainerPokemonEloRepository::getMetrics()`; all use camelCase PHP names + `#[SerializedName('snake_case')]` following the convention of `PokemonDataResponse`, `DexResponse`, and `FormResponse`
- `src/Factory/ElectionMetricsResponseFactory.php` — single static method `fromArray()` accepting the exact shape type returned by `getMetrics()`; no `fromSqlRows()` since there is only ever one metrics object
- `tests/src/Unit/Factory/ElectionMetricsResponseFactoryTest.php` — unit tests covering normal construction and type safety
- `tests/resources/fixtures/election_metrics_response.json` — expected JSON response for the default test case (`dex_slug=demo`, `election_slug=''`, `trainer_external_id=7b52009b64fd0a2a49e6d8a939753077792b0554`)

**Modify:**
- `src/Controller/TrainerPokemonEloController.php` — add `ElectionMetricsResponseFactory` import, add `SerializerInterface $serializer` to the `metrics()` action signature, apply Factory + Serializer, remove the `// Better with serializer ?` comment
- `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php` — add `use App\Factory\ElectionMetricsResponseFactory` and `#[CoversClass(ElectionMetricsResponseFactory::class)]`, add a fixture assertion test for the default metrics response

---

## Tasks

### Task 1: Create ElectionMetricsResponse DTO

**Files:**
- Create: `src/DTO/Response/ElectionMetricsResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionMetricsResponse
{
    public function __construct(
        #[SerializedName('view_count_sum')]
        public readonly int $viewCountSum,
        #[SerializedName('win_count_sum')]
        public readonly int $winCountSum,
        #[SerializedName('view_count_max')]
        public readonly int $viewCountMax,
        #[SerializedName('win_count_max')]
        public readonly int $winCountMax,
        #[SerializedName('under_max_view_count')]
        public readonly int $underMaxViewCount,
        #[SerializedName('max_view_count')]
        public readonly int $maxViewCount,
        #[SerializedName('dex_total_count')]
        public readonly int $dexTotalCount,
    ) {}
}
```

Save as `src/DTO/Response/ElectionMetricsResponse.php`.

- [ ] **Step 2: Verify the file exists and has no syntax errors**

Run: `docker compose exec php php -l src/DTO/Response/ElectionMetricsResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/ElectionMetricsResponse.php`

---

### Task 2: Create ElectionMetricsResponseFactory

**Files:**
- Create: `src/Factory/ElectionMetricsResponseFactory.php`

- [ ] **Step 1: Create the Factory file**

The factory takes the exact shape returned by `TrainerPokemonEloRepository::getMetrics()`. That method returns a PHP-level `array{view_count_sum: int, ...}` (not raw SQL strings), so no casts are needed — the types are already correct.

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ElectionMetricsResponse;

final class ElectionMetricsResponseFactory
{
    /**
     * Transform the metrics associative array into an ElectionMetricsResponse DTO.
     *
     * @param array{
     *   view_count_sum: int,
     *   win_count_sum: int,
     *   view_count_max: int,
     *   win_count_max: int,
     *   under_max_view_count: int,
     *   max_view_count: int,
     *   dex_total_count: int
     * } $data
     */
    public static function fromArray(array $data): ElectionMetricsResponse
    {
        return new ElectionMetricsResponse(
            viewCountSum: $data['view_count_sum'],
            winCountSum: $data['win_count_sum'],
            viewCountMax: $data['view_count_max'],
            winCountMax: $data['win_count_max'],
            underMaxViewCount: $data['under_max_view_count'],
            maxViewCount: $data['max_view_count'],
            dexTotalCount: $data['dex_total_count'],
        );
    }
}
```

Save as `src/Factory/ElectionMetricsResponseFactory.php`.

- [ ] **Step 2: Verify the file exists and has no syntax errors**

Run: `docker compose exec php php -l src/Factory/ElectionMetricsResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/ElectionMetricsResponseFactory.php`

---

### Task 3: Write unit tests for ElectionMetricsResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/ElectionMetricsResponseFactoryTest.php`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ElectionMetricsResponse;
use App\Factory\ElectionMetricsResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionMetricsResponseFactory::class)]
final class ElectionMetricsResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromArrayTransformsDataCorrectly(): void
    {
        $data = [
            'view_count_sum' => 9,
            'win_count_sum' => 6,
            'view_count_max' => 3,
            'win_count_max' => 3,
            'under_max_view_count' => 1,
            'max_view_count' => 1,
            'dex_total_count' => 7,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertInstanceOf(ElectionMetricsResponse::class, $response);
        self::assertSame(9, $response->viewCountSum);
        self::assertSame(6, $response->winCountSum);
        self::assertSame(3, $response->viewCountMax);
        self::assertSame(3, $response->winCountMax);
        self::assertSame(1, $response->underMaxViewCount);
        self::assertSame(1, $response->maxViewCount);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayHandlesZeroValues(): void
    {
        $data = [
            'view_count_sum' => 0,
            'win_count_sum' => 0,
            'view_count_max' => 0,
            'win_count_max' => 0,
            'under_max_view_count' => 15,
            'max_view_count' => 15,
            'dex_total_count' => 21,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(0, $response->viewCountSum);
        self::assertSame(0, $response->winCountSum);
        self::assertSame(0, $response->viewCountMax);
        self::assertSame(0, $response->winCountMax);
        self::assertSame(15, $response->underMaxViewCount);
        self::assertSame(15, $response->maxViewCount);
        self::assertSame(21, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayHandlesLargeValues(): void
    {
        $data = [
            'view_count_sum' => 100000,
            'win_count_sum' => 75000,
            'view_count_max' => 500,
            'win_count_max' => 499,
            'under_max_view_count' => 3,
            'max_view_count' => 2,
            'dex_total_count' => 1025,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(100000, $response->viewCountSum);
        self::assertSame(75000, $response->winCountSum);
        self::assertSame(500, $response->viewCountMax);
        self::assertSame(499, $response->winCountMax);
        self::assertSame(3, $response->underMaxViewCount);
        self::assertSame(2, $response->maxViewCount);
        self::assertSame(1025, $response->dexTotalCount);
    }
}
```

Save as `tests/src/Unit/Factory/ElectionMetricsResponseFactoryTest.php`.

- [ ] **Step 2: Run the unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/ElectionMetricsResponseFactoryTest.php`

Expected: 3 tests, 0 failures, 0 errors.

---

### Task 4: Update TrainerPokemonEloController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/TrainerPokemonEloController.php`

- [ ] **Step 1: Replace the controller content**

Current file content (`src/Controller/TrainerPokemonEloController.php`):

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TrainerPokemonEloQueryOptions;
use App\Factory\ElectionEloResponseFactory;
use App\Repository\TrainerPokemonEloRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/election')]
final class TrainerPokemonEloController extends AbstractController
{
    #[Route(path: '/top', methods: ['GET'])]
    public function top(
        Request $request,
        TrainerPokemonEloRepository $trainerPokemonEloRepository,
        SerializerInterface $serializer,
    ): JsonResponse {
        /** @var array<int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloQueryOptions($params);

        $rows = $trainerPokemonEloRepository->getTopN(
            $queryOptions->trainerExternalId,
            $queryOptions->dexSlug,
            $queryOptions->electionSlug,
            $queryOptions->count,
        );

        $responses = ElectionEloResponseFactory::fromSqlRows($rows);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }

    #[Route(path: '/metrics', methods: ['GET'])]
    public function metrics(
        Request $request,
        TrainerPokemonEloRepository $trainerPokemonEloRepository,
    ): JsonResponse {
        /** @var array<int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloQueryOptions($params);

        // Better with serializer ?
        return new JsonResponse(
            $trainerPokemonEloRepository->getMetrics(
                $queryOptions->trainerExternalId,
                $queryOptions->dexSlug,
                $queryOptions->electionSlug,
            )
        );
    }
}
```

Replace with:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TrainerPokemonEloQueryOptions;
use App\Factory\ElectionEloResponseFactory;
use App\Factory\ElectionMetricsResponseFactory;
use App\Repository\TrainerPokemonEloRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/election')]
final class TrainerPokemonEloController extends AbstractController
{
    #[Route(path: '/top', methods: ['GET'])]
    public function top(
        Request $request,
        TrainerPokemonEloRepository $trainerPokemonEloRepository,
        SerializerInterface $serializer,
    ): JsonResponse {
        /** @var array<int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloQueryOptions($params);

        $rows = $trainerPokemonEloRepository->getTopN(
            $queryOptions->trainerExternalId,
            $queryOptions->dexSlug,
            $queryOptions->electionSlug,
            $queryOptions->count,
        );

        $responses = ElectionEloResponseFactory::fromSqlRows($rows);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }

    #[Route(path: '/metrics', methods: ['GET'])]
    public function metrics(
        Request $request,
        TrainerPokemonEloRepository $trainerPokemonEloRepository,
        SerializerInterface $serializer,
    ): JsonResponse {
        /** @var array<int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloQueryOptions($params);

        $metrics = $trainerPokemonEloRepository->getMetrics(
            $queryOptions->trainerExternalId,
            $queryOptions->dexSlug,
            $queryOptions->electionSlug,
        );

        $response = ElectionMetricsResponseFactory::fromArray($metrics);

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }
}
```

- [ ] **Step 2: Verify the file has no syntax errors**

Run: `docker compose exec php php -l src/Controller/TrainerPokemonEloController.php`

Expected: `No syntax errors detected in src/Controller/TrainerPokemonEloController.php`

---

### Task 5: Create fixture JSON file for the default metrics response

**Files:**
- Create: `tests/resources/fixtures/election_metrics_response.json`

The fixture represents the response of `GET /election/metrics` for the test case: `trainer_external_id=7b52009b64fd0a2a49e6d8a939753077792b0554`, `dex_slug=demo`, `election_slug=''`. This trainer has no ELO records for the `demo` dex, so all counts are 0 except the dex totals (the `demo` dex has 21 Pokémon, and `under_max_view_count` and `max_view_count` both equal 21 since all are "unseen"). This matches the `testGetMetrics` assertion in the existing `TrainerPokemonEloControllerTest`.

- [ ] **Step 1: Create the fixture file**

```json
{
    "view_count_sum": 0,
    "win_count_sum": 0,
    "view_count_max": 0,
    "win_count_max": 0,
    "under_max_view_count": 15,
    "max_view_count": 15,
    "dex_total_count": 21
}
```

Save as `tests/resources/fixtures/election_metrics_response.json`.

- [ ] **Step 2: Verify the file is valid JSON**

Run: `docker compose exec php php -r "json_decode(file_get_contents('tests/resources/fixtures/election_metrics_response.json'), true, 512, JSON_THROW_ON_ERROR); echo 'Valid JSON'"`

Expected: `Valid JSON`

---

### Task 6: Update integration test to cover ElectionMetricsResponseFactory

**Files:**
- Modify: `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`

The existing tests (`testGetMetrics`, `testGetMetricsBis`, `testGetMetricsNo`) already assert exact response values and will continue to pass unchanged. Two changes are needed: add the `#[CoversClass]` for the new factory so coverage is tracked, and add a fixture assertion test.

- [ ] **Step 1: Replace the file header and add the fixture test**

Current file content (`tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`):

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TrainerPokemonEloController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloController::class)]
final class TrainerPokemonEloControllerTest extends AbstractTestControllerApi
{
```

Replace with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TrainerPokemonEloController;
use App\Factory\ElectionMetricsResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloController::class)]
#[CoversClass(ElectionMetricsResponseFactory::class)]
final class TrainerPokemonEloControllerTest extends AbstractTestControllerApi
{
```

Then, inside the class body, after the last existing test method (`testGetBadAuth`), add the fixture assertion test before the closing `}`:

```php
    public function testGetMetricsMatchesFixture(): void
    {
        $this->apiRequest(
            'GET',
            '/election/metrics',
            [
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'demo',
                'election_slug' => '',
            ]
        );

        $this->assertResponseIsOK();

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/election_metrics_response.json',
            $this->getClientResponseContent(),
        );
    }
```

- [ ] **Step 2: Verify the file has no syntax errors**

Run: `docker compose exec php php -l tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`

Expected: `No syntax errors detected in tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`

---

### Task 7: Run tests and quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run unit tests for the new factory**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/ElectionMetricsResponseFactoryTest.php`

Expected: 3 tests, 0 failures, 0 errors.

- [ ] **Step 2: Run integration tests for the controller**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`

Expected: All tests pass including the 3 existing `testGetMetrics*` tests and the new `testGetMetricsMatchesFixture`.

- [ ] **Step 3: Run all quality checks**

Run: `make quality`

Expected: PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint all pass.

- [ ] **Step 4: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% code coverage for `ElectionMetricsResponse` and `ElectionMetricsResponseFactory`, 100% MSI, all checks green.
