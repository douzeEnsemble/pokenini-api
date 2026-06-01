# API Response Restructuring (Action Logs) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /action_logs` endpoint from raw `new JsonResponse($array)` to the Factory + Serializer + DTO pattern used across all other read endpoints.

**Architecture:** Create two immutable response DTOs (`ActionLogEntryResponse`, `ActionLogResponse`), a Factory to transform and group flat SQL rows into nested DTOs (moving the logic currently in `ActionLogsService::getFormattedLastests()`), simplify the service to a pure repository pass-through, then update the controller to apply the transformation before serialization. The external JSON response shape stays identical — the change is entirely internal.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## Context: what exists today

`ActionLogsController` (the only unmigrated read endpoint) currently calls `$service->getFormattedLastests()`, which groups raw SQL rows by `type_action`, parses JSON `details`, and truncates `execution_time` — then passes the result directly to `new JsonResponse()`.

**Current controller:**
```php
public function get(ActionLogsService $service): JsonResponse
{
    return new JsonResponse($service->getFormattedLastests());
}
```

**Current response format (unchanged after migration):**
```json
{
  "update_pokemons": {
    "current": {
      "created_at": "2026-05-25 10:00:00+00",
      "done_at": "2026-05-25 10:01:00+00",
      "execution_time": "60",
      "details": {"key": "value"},
      "error_trace": null
    },
    "last": {
      "created_at": "2026-05-24 09:00:00+00",
      "done_at": null,
      "execution_time": null,
      "details": null,
      "error_trace": null
    }
  }
}
```

**Note:** The current raw response also included `type_action` and `row_number` fields (leaked from the SQL result via `array_merge`). These are redundant (type_action is the top-level key; row_number is an internal cursor) and are intentionally excluded from the new DTOs. The existing integration test does not assert their presence.

---

## File Structure

**Create:**
- `src/DTO/Response/ActionLogEntryResponse.php` — immutable DTO for a single action log entry
- `src/DTO/Response/ActionLogResponse.php` — immutable DTO with optional `current` and `last` entries
- `src/Factory/ActionLogResponseFactory.php` — groups raw SQL rows and transforms them into DTOs
- `tests/src/Unit/Factory/ActionLogResponseFactoryTest.php` — unit tests for the factory
- `tests/src/Unit/Service/ActionLogsServiceTest.php` — unit tests for the simplified service

**Modify:**
- `src/Service/ActionLogsService.php` — remove `getFormattedLastests()`, keep only `getLastests()`
- `src/Controller/ActionLogsController.php` — use `getLastests()` + factory + serializer
- `tests/src/Integration/Controller/ActionLogsControllerTest.php` — update `#[CoversClass]` annotations and add `/** @internal */`

---

## Tasks

### Task 1: Create ActionLogEntryResponse DTO

**Files:**
- Create: `src/DTO/Response/ActionLogEntryResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ActionLogEntryResponse
{
    /**
     * @param array<string, string>|null $details
     */
    public function __construct(
        #[SerializedName('created_at')]
        public readonly string $createdAt,
        #[SerializedName('done_at')]
        public readonly ?string $doneAt,
        #[SerializedName('execution_time')]
        public readonly ?string $executionTime,
        public readonly ?array $details,
        #[SerializedName('error_trace')]
        public readonly ?string $errorTrace,
    ) {}
}
```

Save as `src/DTO/Response/ActionLogEntryResponse.php`.

- [ ] **Step 2: Verify the file syntax**

Run: `docker compose exec php php -l src/DTO/Response/ActionLogEntryResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/ActionLogEntryResponse.php`

---

### Task 2: Create ActionLogResponse DTO

**Files:**
- Create: `src/DTO/Response/ActionLogResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ActionLogResponse
{
    public function __construct(
        public readonly ?ActionLogEntryResponse $current,
        public readonly ?ActionLogEntryResponse $last,
    ) {}
}
```

Save as `src/DTO/Response/ActionLogResponse.php`.

- [ ] **Step 2: Verify the file syntax**

Run: `docker compose exec php php -l src/DTO/Response/ActionLogResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/ActionLogResponse.php`

---

### Task 3: Create ActionLogResponseFactory

**Files:**
- Create: `src/Factory/ActionLogResponseFactory.php`

The factory absorbs the logic that was in `ActionLogsService::getFormattedLastests()`:
- Groups rows by `type_action`
- Assigns each row to `current` (row_number=1) or `last` (row_number=2)
- Parses JSON-encoded `details` into an array
- Truncates `execution_time` to whole seconds (drops the decimal part)
- Excludes `type_action` and `row_number` from the DTO (they are redundant)

- [ ] **Step 1: Create the factory file**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ActionLogEntryResponse;
use App\DTO\Response\ActionLogResponse;

final class ActionLogResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): ActionLogEntryResponse
    {
        /** @var null|scalar $rawDetails */
        $rawDetails = $row['details'];
        $details = null;
        if (null !== $rawDetails) {
            /** @var array<string, string> $details */
            $details = json_decode((string) $rawDetails, true);
        }

        /** @var null|scalar $rawExecutionTime */
        $rawExecutionTime = $row['execution_time'];
        $executionTime = null;
        if (null !== $rawExecutionTime) {
            [$executionTime] = explode('.', (string) $rawExecutionTime);
        }

        /** @var scalar $createdAt */
        $createdAt = $row['created_at'];

        /** @var null|scalar $doneAt */
        $doneAt = $row['done_at'];

        /** @var null|scalar $errorTrace */
        $errorTrace = $row['error_trace'];

        return new ActionLogEntryResponse(
            createdAt: (string) $createdAt,
            doneAt: null !== $doneAt ? (string) $doneAt : null,
            executionTime: $executionTime,
            details: $details,
            errorTrace: null !== $errorTrace ? (string) $errorTrace : null,
        );
    }

    /**
     * @param array<int, array<array-key, mixed>> $rows
     *
     * @return array<string, ActionLogResponse>
     */
    public static function fromSqlRows(array $rows): array
    {
        /** @var array<string, array{current: ?ActionLogEntryResponse, last: ?ActionLogEntryResponse}> $grouped */
        $grouped = [];
        foreach ($rows as $row) {
            /** @var scalar $typeAction */
            $typeAction = $row['type_action'];
            $typeActionStr = (string) $typeAction;

            /** @var scalar $rowNumber */
            $rowNumber = $row['row_number'];
            $period = 1 === (int) $rowNumber ? 'current' : 'last';

            if (!array_key_exists($typeActionStr, $grouped)) {
                $grouped[$typeActionStr] = ['current' => null, 'last' => null];
            }

            $grouped[$typeActionStr][$period] = self::fromSqlRow($row);
        }

        $result = [];
        foreach ($grouped as $typeAction => $entries) {
            $result[$typeAction] = new ActionLogResponse(
                current: $entries['current'],
                last: $entries['last'],
            );
        }

        return $result;
    }
}
```

Save as `src/Factory/ActionLogResponseFactory.php`.

- [ ] **Step 2: Verify the file syntax**

Run: `docker compose exec php php -l src/Factory/ActionLogResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/ActionLogResponseFactory.php`

---

### Task 4: Write unit tests for ActionLogResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/ActionLogResponseFactoryTest.php`
- Tests: `ActionLogResponseFactory`, `ActionLogEntryResponse`, `ActionLogResponse`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ActionLogEntryResponse;
use App\DTO\Response\ActionLogResponse;
use App\Factory\ActionLogResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ActionLogResponseFactory::class)]
#[CoversClass(ActionLogEntryResponse::class)]
#[CoversClass(ActionLogResponse::class)]
final class ActionLogResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowCreatesEntryWithAllFieldsPresent(): void
    {
        $row = [
            'type_action' => 'update_pokemons',
            'row_number' => 1,
            'created_at' => '2026-05-25 10:00:00+00',
            'done_at' => '2026-05-25 10:01:00+00',
            'execution_time' => '60.123456',
            'details' => '{"nb_pokemons":"1008"}',
            'error_trace' => null,
        ];

        $entry = ActionLogResponseFactory::fromSqlRow($row);

        self::assertSame('2026-05-25 10:00:00+00', $entry->createdAt);
        self::assertSame('2026-05-25 10:01:00+00', $entry->doneAt);
        self::assertSame('60', $entry->executionTime);
        self::assertSame(['nb_pokemons' => '1008'], $entry->details);
        self::assertNull($entry->errorTrace);
    }

    #[Test]
    public function fromSqlRowHandlesAllNullOptionals(): void
    {
        $row = [
            'type_action' => 'update_pokemons',
            'row_number' => 2,
            'created_at' => '2026-05-25 09:00:00+00',
            'done_at' => null,
            'execution_time' => null,
            'details' => null,
            'error_trace' => null,
        ];

        $entry = ActionLogResponseFactory::fromSqlRow($row);

        self::assertSame('2026-05-25 09:00:00+00', $entry->createdAt);
        self::assertNull($entry->doneAt);
        self::assertNull($entry->executionTime);
        self::assertNull($entry->details);
        self::assertNull($entry->errorTrace);
    }

    #[Test]
    public function fromSqlRowSetsErrorTrace(): void
    {
        $row = [
            'type_action' => 'update_pokemons',
            'row_number' => 1,
            'created_at' => '2026-05-25 10:00:00+00',
            'done_at' => '2026-05-25 10:01:00+00',
            'execution_time' => '5',
            'details' => null,
            'error_trace' => 'Exception: something went wrong',
        ];

        $entry = ActionLogResponseFactory::fromSqlRow($row);

        self::assertSame('Exception: something went wrong', $entry->errorTrace);
    }

    #[Test]
    public function fromSqlRowTruncatesExecutionTimeAtDecimalPoint(): void
    {
        $row = [
            'type_action' => 'update_pokemons',
            'row_number' => 1,
            'created_at' => '2026-05-25 10:00:00+00',
            'done_at' => '2026-05-25 10:00:01+00',
            'execution_time' => '1.999999',
            'details' => null,
            'error_trace' => null,
        ];

        $entry = ActionLogResponseFactory::fromSqlRow($row);

        self::assertSame('1', $entry->executionTime);
    }

    #[Test]
    public function fromSqlRowsAssignsCurrentForRowNumberOne(): void
    {
        $rows = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 1,
                'created_at' => '2026-05-25 10:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $result = ActionLogResponseFactory::fromSqlRows($rows);

        self::assertArrayHasKey('update_pokemons', $result);
        self::assertNotNull($result['update_pokemons']->current);
        self::assertNull($result['update_pokemons']->last);
        self::assertSame('2026-05-25 10:00:00+00', $result['update_pokemons']->current->createdAt);
    }

    #[Test]
    public function fromSqlRowsAssignsLastForRowNumberTwo(): void
    {
        $rows = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 2,
                'created_at' => '2026-05-24 09:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $result = ActionLogResponseFactory::fromSqlRows($rows);

        self::assertArrayHasKey('update_pokemons', $result);
        self::assertNull($result['update_pokemons']->current);
        self::assertNotNull($result['update_pokemons']->last);
        self::assertSame('2026-05-24 09:00:00+00', $result['update_pokemons']->last->createdAt);
    }

    #[Test]
    public function fromSqlRowsGroupsCurrentAndLastUnderSameTypeAction(): void
    {
        $rows = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 1,
                'created_at' => '2026-05-25 10:00:00+00',
                'done_at' => '2026-05-25 10:01:00+00',
                'execution_time' => '60',
                'details' => null,
                'error_trace' => null,
            ],
            [
                'type_action' => 'update_pokemons',
                'row_number' => 2,
                'created_at' => '2026-05-24 09:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $result = ActionLogResponseFactory::fromSqlRows($rows);

        self::assertCount(1, $result);
        self::assertArrayHasKey('update_pokemons', $result);
        self::assertNotNull($result['update_pokemons']->current);
        self::assertNotNull($result['update_pokemons']->last);
        self::assertSame('2026-05-25 10:00:00+00', $result['update_pokemons']->current->createdAt);
        self::assertSame('2026-05-24 09:00:00+00', $result['update_pokemons']->last->createdAt);
    }

    #[Test]
    public function fromSqlRowsCreatesSeparateEntriesForDifferentTypeActions(): void
    {
        $rows = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 1,
                'created_at' => '2026-05-25 10:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
            [
                'type_action' => 'update_labels',
                'row_number' => 1,
                'created_at' => '2026-05-25 08:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $result = ActionLogResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $result);
        self::assertArrayHasKey('update_pokemons', $result);
        self::assertArrayHasKey('update_labels', $result);
        self::assertInstanceOf(ActionLogResponse::class, $result['update_pokemons']);
        self::assertInstanceOf(ActionLogResponse::class, $result['update_labels']);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $result = ActionLogResponseFactory::fromSqlRows([]);

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }
}
```

Save as `tests/src/Unit/Factory/ActionLogResponseFactoryTest.php`.

- [ ] **Step 2: Run unit tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/ActionLogResponseFactoryTest.php`

Expected: 8 tests, 0 failures, 0 errors.

---

### Task 5: Simplify ActionLogsService

**Files:**
- Modify: `src/Service/ActionLogsService.php`

Remove `getFormattedLastests()`. Its grouping/parsing logic is now in `ActionLogResponseFactory::fromSqlRows()`. The service becomes a pure repository pass-through.

- [ ] **Step 1: Replace the service file contents**

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ActionLogsRepository;

class ActionLogsService
{
    public function __construct(
        private readonly ActionLogsRepository $repository,
    ) {}

    /**
     * @return array<int, array{
     *  type_action: string,
     *  row_number: int,
     *  created_at: string,
     *  done_at: null|string,
     *  execution_time: null|string,
     *  details: null|string,
     *  error_trace: null|string
     * }>
     */
    public function getLastests(): array
    {
        return $this->repository->getLastests();
    }
}
```

- [ ] **Step 2: Verify the file syntax**

Run: `docker compose exec php php -l src/Service/ActionLogsService.php`

Expected: `No syntax errors detected in src/Service/ActionLogsService.php`

---

### Task 6: Write unit test for ActionLogsService

**Files:**
- Create: `tests/src/Unit/Service/ActionLogsServiceTest.php`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\ActionLogsRepository;
use App\Service\ActionLogsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ActionLogsService::class)]
final class ActionLogsServiceTest extends TestCase
{
    private MockObject&ActionLogsRepository $repository;
    private ActionLogsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ActionLogsRepository::class);
        $this->service = new ActionLogsService($this->repository);
    }

    #[Test]
    public function getLastestsReturnsRepositoryData(): void
    {
        $expectedData = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 1,
                'created_at' => '2026-05-25 10:00:00+00',
                'done_at' => '2026-05-25 10:01:00+00',
                'execution_time' => '60',
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $this->repository
            ->expects(self::once())
            ->method('getLastests')
            ->willReturn($expectedData);

        $result = $this->service->getLastests();

        self::assertSame($expectedData, $result);
    }

    #[Test]
    public function getLastestsHandlesEmptyResult(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('getLastests')
            ->willReturn([]);

        $result = $this->service->getLastests();

        self::assertCount(0, $result);
    }
}
```

Save as `tests/src/Unit/Service/ActionLogsServiceTest.php`.

- [ ] **Step 2: Run the unit test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/ActionLogsServiceTest.php`

Expected: 2 tests, 0 failures, 0 errors.

---

### Task 7: Update ActionLogsController

**Files:**
- Modify: `src/Controller/ActionLogsController.php`

- [ ] **Step 1: Replace the controller contents**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\ActionLogResponseFactory;
use App\Service\ActionLogsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/action_logs')]
final class ActionLogsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        ActionLogsService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $rows = $service->getLastests();
        $responses = ActionLogResponseFactory::fromSqlRows($rows);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
```

- [ ] **Step 2: Verify the file syntax**

Run: `docker compose exec php php -l src/Controller/ActionLogsController.php`

Expected: `No syntax errors detected in src/Controller/ActionLogsController.php`

---

### Task 8: Update ActionLogsControllerTest

**Files:**
- Modify: `tests/src/Integration/Controller/ActionLogsControllerTest.php`

The `#[CoversClass]` annotations need updating:
- Remove `ActionLogsService` (now covered by its own unit test)
- Add `ActionLogResponseFactory` (now used by the controller)
- Add `/** @internal */` (required by project conventions, missing from the existing file)

The test assertions themselves **do not change** — the response format is identical.

- [ ] **Step 1: Update only the class-level declarations (top of file)**

Replace the current imports and class declarations:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ActionLogsController;
use App\Factory\ActionLogResponseFactory;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ActionLogsController::class)]
#[CoversClass(ActionLogResponseFactory::class)]
final class ActionLogsControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
```

Keep the rest of the file (the `testActionLogs()` method and all private helper methods) exactly as-is.

- [ ] **Step 2: Verify the file syntax**

Run: `docker compose exec php php -l tests/src/Integration/Controller/ActionLogsControllerTest.php`

Expected: `No syntax errors detected in tests/src/Integration/Controller/ActionLogsControllerTest.php`

---

### Task 9: Run all unit tests

- [ ] **Step 1: Run all unit tests**

Run: `make tests-unit`

Expected: All unit tests pass, 0 failures. Newly added tests (`ActionLogResponseFactoryTest`, `ActionLogsServiceTest`) appear in the output.

---

### Task 10: Run integration tests for the endpoint

- [ ] **Step 1: Run only the ActionLogs integration test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/ActionLogsControllerTest.php`

Expected: 1 test passes, 0 failures. The `testActionLogs` test exercises `current`/`last` assertions for all action types in the DB fixtures.

---

### Task 11: Run all tests and quality checks

- [ ] **Step 1: Run all tests**

Run: `make tests`

Expected: All unit and integration tests pass, 0 failures.

- [ ] **Step 2: Run code quality checks**

Run: `make quality`

Expected: All checks pass — PHP CS Fixer, PHPMD, Psalm, PHPStan (level 9), Deptrac, jsonlint.

- [ ] **Step 3: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% code coverage for all new files, 100% Mutation Score Index, all checks green.

- [ ] **Step 4: Verify no regressions**

Run: `make tests-integration`

Expected: All integration tests pass (not just ActionLogsControllerTest).

---

## Self-review

**Spec coverage:**
- ✅ `ActionLogEntryResponse` DTO created (Task 1)
- ✅ `ActionLogResponse` DTO created (Task 2)
- ✅ `ActionLogResponseFactory` with grouping, JSON parsing, execution_time truncation (Task 3)
- ✅ Unit tests for factory — all branches covered (Task 4)
- ✅ `ActionLogsService` simplified — `getFormattedLastests()` removed (Task 5)
- ✅ Unit test for simplified service (Task 6)
- ✅ Controller updated to use factory + serializer (Task 7)
- ✅ Controller integration test `#[CoversClass]` updated (Task 8)
- ✅ Quality gates (Tasks 9–11)

**Response format:** Identical to current output. `type_action` and `row_number` were redundant fields leaked by the old `array_merge` approach; they are intentionally excluded. The existing integration test does not assert their presence.

**Type consistency:** `ActionLogEntryResponse` properties use camelCase in PHP with `#[SerializedName]` mapping to snake_case in JSON — consistent with `TypeResponse.frenchName` → `french_name` pattern throughout the codebase.
