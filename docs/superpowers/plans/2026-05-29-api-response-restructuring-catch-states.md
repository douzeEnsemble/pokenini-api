# API Response Restructuring (CatchStates) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /catch_states` endpoint from raw SQL array response to the DTO + Factory + Serializer pattern, matching the architecture established by the Types migration.

**Architecture:** Create an immutable `CatchStateResponse` DTO, a `CatchStateResponseFactory` to transform flat SQL rows into typed DTOs, update `CatchStatesController` to apply the transformation before serialization, create a `CatchStatesService` unit test, and replace the integration controller test with the newer `WebTestCase` pattern.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## Context

`GET /catch_states` returns `[{slug, name, french_name, color}]` — an identical SQL shape to the already-migrated Types endpoint. The controller currently contains a `// Better with serializer ?` comment and does `new JsonResponse($catchStates)` directly on raw SQL rows. The reference migration is `src/Controller/TypesController.php`.

The Alice fixture `fixtures/catch_states.yaml` defines 5 records; `catchstate_forbidden` has `deletedAt` set so it is excluded by the SQL `WHERE deleted_at IS NULL`. The 4 active states ordered by `orderNumber` are: no, maybe, maybenot, yes.

---

## File Structure

**Create:**
- `src/DTO/Response/CatchStateResponse.php` — immutable DTO for one catch state
- `src/Factory/CatchStateResponseFactory.php` — transforms flat SQL rows → `CatchStateResponse` DTOs
- `tests/src/Unit/Factory/CatchStateResponseFactoryTest.php` — unit tests for the factory
- `tests/src/Unit/Service/CatchStatesServiceTest.php` — unit tests for the service
- `tests/resources/fixtures/catch_states_response.json` — expected JSON response fixture

**Modify:**
- `src/Controller/CatchStatesController.php` — apply Factory + Serializer, remove raw `JsonResponse`
- `tests/src/Integration/Controller/CatchStatesControllerTest.php` — replace with new `WebTestCase` pattern (removes `AbstractTestControllerApi` / `RefreshDatabaseTrait`)

---

## Tasks

### Task 1: Create CatchStateResponse DTO

**Files:**
- Create: `src/DTO/Response/CatchStateResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class CatchStateResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly string $color,
    ) {}
}
```

Save as `src/DTO/Response/CatchStateResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/CatchStateResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/CatchStateResponse.php`

---

### Task 2: Create CatchStateResponseFactory

**Files:**
- Create: `src/Factory/CatchStateResponseFactory.php`

The factory mirrors `src/Factory/TypeResponseFactory.php` exactly. Each scalar extraction uses a local typed variable and a PHPDoc cast — required by PHPStan level 9 because SQL rows are typed `array<array-key, mixed>`.

- [ ] **Step 1: Create the factory file**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\CatchStateResponse;

final class CatchStateResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): CatchStateResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $color */
        $color = $row['color'];

        return new CatchStateResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            color: (string) $color,
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return CatchStateResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
```

Save as `src/Factory/CatchStateResponseFactory.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/Factory/CatchStateResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/CatchStateResponseFactory.php`

---

### Task 3: Write unit tests for CatchStateResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/CatchStateResponseFactoryTest.php`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\CatchStateResponse;
use App\Factory\CatchStateResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CatchStateResponseFactory::class)]
final class CatchStateResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'no',
            'name' => 'No',
            'french_name' => 'Non',
            'color' => '#e57373',
        ];

        $response = CatchStateResponseFactory::fromSqlRow($row);

        self::assertSame('no', $response->slug);
        self::assertSame('No', $response->name);
        self::assertSame('Non', $response->frenchName);
        self::assertSame('#e57373', $response->color);
    }

    #[Test]
    public function fromSqlRowCastsSlugsAndNamesToStrings(): void
    {
        $row = [
            'slug' => 123,
            'name' => 456,
            'french_name' => 789,
            'color' => '#ABC123',
        ];

        $response = CatchStateResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->frenchName);
        self::assertSame('#ABC123', $response->color);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'no',
                'name' => 'No',
                'french_name' => 'Non',
                'color' => '#e57373',
            ],
            [
                'slug' => 'yes',
                'name' => 'Yes',
                'french_name' => 'Oui',
                'color' => '#66bb6a',
            ],
        ];

        $responses = CatchStateResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(CatchStateResponse::class, $responses);
        self::assertSame('no', $responses[0]->slug);
        self::assertSame('yes', $responses[1]->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = CatchStateResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
```

Save as `tests/src/Unit/Factory/CatchStateResponseFactoryTest.php`.

- [ ] **Step 2: Run unit tests to confirm they pass**

Run: `make tests-unit`

Expected: 4 new tests pass. 0 failures overall.

---

### Task 4: Create unit test for CatchStatesService

**Files:**
- Create: `tests/src/Unit/Service/CatchStatesServiceTest.php`

This test replaces the coverage that the old `CatchStatesControllerTest` provided via its `#[CoversClass(CatchStatesService::class)]` annotation. The pattern is identical to `tests/src/Unit/Service/TypesServiceTest.php`.

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\CatchStatesRepository;
use App\Service\CatchStatesService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CatchStatesService::class)]
final class CatchStatesServiceTest extends TestCase
{
    private MockObject&CatchStatesRepository $repository;
    private CatchStatesService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CatchStatesRepository::class);
        $this->service = new CatchStatesService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            [
                'slug' => 'no',
                'name' => 'No',
                'french_name' => 'Non',
                'color' => '#e57373',
            ],
            [
                'slug' => 'yes',
                'name' => 'Yes',
                'french_name' => 'Oui',
                'color' => '#66bb6a',
            ],
        ];

        $this->repository
            ->expects(self::once())
            ->method('getAll')
            ->willReturn($expectedData)
        ;

        $result = $this->service->getAll();

        self::assertSame($expectedData, $result);
    }

    #[Test]
    public function getAllHandlesEmptyResult(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('getAll')
            ->willReturn([])
        ;

        $result = $this->service->getAll();

        self::assertCount(0, $result);
    }
}
```

Save as `tests/src/Unit/Service/CatchStatesServiceTest.php`.

- [ ] **Step 2: Run unit tests to confirm they pass**

Run: `make tests-unit`

Expected: 2 new service tests pass. 0 failures overall.

---

### Task 5: Update CatchStatesController

**Files:**
- Modify: `src/Controller/CatchStatesController.php`

- [ ] **Step 1: Replace the controller content**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\CatchStateResponseFactory;
use App\Service\CatchStatesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/catch_states')]
final class CatchStatesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CatchStatesService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $catchStates = $service->getAll();

        $responses = CatchStateResponseFactory::fromSqlRows($catchStates);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
```

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/Controller/CatchStatesController.php`

Expected: `No syntax errors detected in src/Controller/CatchStatesController.php`

---

### Task 6: Create JSON fixture and update integration controller test

**Files:**
- Create: `tests/resources/fixtures/catch_states_response.json`
- Modify: `tests/src/Integration/Controller/CatchStatesControllerTest.php`

The fixture JSON is derived from `fixtures/catch_states.yaml`. The 4 active states ordered by `orderNumber` are: no (1), maybe (2), maybenot (3), yes (4). The `forbidden` record has `deletedAt` set and is excluded by the SQL `WHERE deleted_at IS NULL`.

- [ ] **Step 1: Create the JSON fixture file**

```json
[
  {
    "slug": "no",
    "name": "No",
    "french_name": "Non",
    "color": "#e57373"
  },
  {
    "slug": "maybe",
    "name": "Maybe",
    "french_name": "Peut être",
    "color": "blue"
  },
  {
    "slug": "maybenot",
    "name": "Maybe not",
    "french_name": "Peut être pas",
    "color": "yellow"
  },
  {
    "slug": "yes",
    "name": "Yes",
    "french_name": "Oui",
    "color": "#66bb6a"
  }
]
```

Save as `tests/resources/fixtures/catch_states_response.json`.

- [ ] **Step 2: Replace the integration controller test**

The old test extended `AbstractTestControllerApi` (with `RefreshDatabaseTrait`) and covered `CatchStatesService`. The new test follows the `TypesControllerTest` pattern: extends `WebTestCase` directly, passes credentials inline, validates response shape and content via the fixture file. `CatchStatesService` coverage is now handled by `CatchStatesServiceTest` (Task 4).

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\CatchStatesController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(CatchStatesController::class)]
final class CatchStatesControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/catch_states', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsArrayOfCatchStates(): void
    {
        $client = self::createClient();
        $client->request('GET', '/catch_states', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);
        self::assertNotEmpty($data);
    }

    #[Test]
    public function getEachCatchStateHasRequiredFields(): void
    {
        $client = self::createClient();
        $client->request('GET', '/catch_states', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $catchState */
        foreach ($data as $catchState) {
            self::assertIsArray($catchState);
            self::assertArrayHasKey('slug', $catchState);
            self::assertArrayHasKey('name', $catchState);
            self::assertArrayHasKey('french_name', $catchState);
            self::assertArrayHasKey('color', $catchState);
        }
    }

    #[Test]
    public function getFieldValuesAreStrings(): void
    {
        $client = self::createClient();
        $client->request('GET', '/catch_states', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        /** @var mixed $firstCatchState */
        $firstCatchState = $data[0] ?? null;

        self::assertIsArray($firstCatchState);
        self::assertIsString($firstCatchState['slug']);
        self::assertIsString($firstCatchState['name']);
        self::assertIsString($firstCatchState['french_name']);
        self::assertIsString($firstCatchState['color']);
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/catch_states', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/catch_states_response.json',
            $content,
        );
    }
}
```

- [ ] **Step 3: Run integration tests**

Run: `make tests-integration`

Expected: All integration tests pass, including the 5 new CatchStates controller tests. 0 failures.

---

### Task 7: Run full quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all tests**

Run: `make tests`

Expected: All unit and integration tests pass. 0 failures.

- [ ] **Step 2: Run code quality checks**

Run: `make quality`

Expected: All checks pass — PHP CS Fixer, PHPMD, Psalm, PHPStan level 9, Deptrac, jsonlint.

- [ ] **Step 3: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% code coverage, 100% MSI, all checks green.

- [ ] **Step 4: Commit**

```bash
git add src/DTO/Response/CatchStateResponse.php \
        src/Factory/CatchStateResponseFactory.php \
        src/Controller/CatchStatesController.php \
        tests/src/Unit/Factory/CatchStateResponseFactoryTest.php \
        tests/src/Unit/Service/CatchStatesServiceTest.php \
        tests/src/Integration/Controller/CatchStatesControllerTest.php \
        tests/resources/fixtures/catch_states_response.json
git commit -m "Refactoring GET /catch_states return format to use Serializer and Object"
```

---

## Next Steps (not in this plan)

The same pattern applies to the remaining `// Better with serializer ?` endpoints:
- `GET /collections` — fields: slug, name, french_name (no color)
- `GET /forms/category`, `GET /forms/regional`, `GET /forms/special`, `GET /forms/variant` — fields: slug, name (already have `FormResponse` DTO but controllers are not yet migrated)
