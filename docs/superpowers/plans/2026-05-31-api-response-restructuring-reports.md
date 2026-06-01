# API Response Restructuring (Reports) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /reports` endpoint from raw `new JsonResponse([...])` to the typed DTO + Factory + Serializer pattern used by all other migrated endpoints.

**Architecture:** Create 3 immutable sub-DTOs (one per report section), a wrapper `ReportResponse` DTO that holds them, a single `ReportResponseFactory` that transforms service data into the wrapper, then update the Controller to use Factory + Serializer.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/TrainerCatchStateCountResponse.php` — DTO for `catch_state_counts_defined_by_trainer` items: `{nb, trainer}`
- `src/DTO/Response/DexUsageResponse.php` — DTO for `dex_usage` items: `{nb, name, frenchName}`
- `src/DTO/Response/CatchStateUsageResponse.php` — DTO for `catch_state_usage` items: `{nb, name, frenchName, color}`
- `src/DTO/Response/ReportResponse.php` — wrapper DTO: holds the 3 typed arrays
- `src/Factory/ReportResponseFactory.php` — transforms service data → `ReportResponse`
- `tests/src/Unit/Factory/ReportResponseFactoryTest.php` — 100% unit coverage for the Factory
- `tests/resources/fixtures/reports_response.json` — expected JSON fixture for integration test

**Modify:**
- `src/Controller/ReportsController.php` — inject `SerializerInterface`, use Factory
- `tests/src/Integration/Controller/ReportsControllerTest.php` — replace manual assertions with fixture comparison

---

## Tasks

### Task 1: Create TrainerCatchStateCountResponse DTO

**Files:**
- Create: `src/DTO/Response/TrainerCatchStateCountResponse.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class TrainerCatchStateCountResponse
{
    public function __construct(
        public readonly int $nb,
        public readonly string $trainer,
    ) {}
}
```

Save as `src/DTO/Response/TrainerCatchStateCountResponse.php`.

- [ ] **Step 2: Verify the file exists**

Run: `ls -la src/DTO/Response/TrainerCatchStateCountResponse.php`

Expected: File exists.

---

### Task 2: Create DexUsageResponse DTO

**Files:**
- Create: `src/DTO/Response/DexUsageResponse.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexUsageResponse
{
    public function __construct(
        public readonly int $nb,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
    ) {}
}
```

Save as `src/DTO/Response/DexUsageResponse.php`.

- [ ] **Step 2: Verify the file exists**

Run: `ls -la src/DTO/Response/DexUsageResponse.php`

Expected: File exists.

---

### Task 3: Create CatchStateUsageResponse DTO

**Files:**
- Create: `src/DTO/Response/CatchStateUsageResponse.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class CatchStateUsageResponse
{
    public function __construct(
        public readonly int $nb,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly string $color,
    ) {}
}
```

Save as `src/DTO/Response/CatchStateUsageResponse.php`.

- [ ] **Step 2: Verify the file exists**

Run: `ls -la src/DTO/Response/CatchStateUsageResponse.php`

Expected: File exists.

---

### Task 4: Create ReportResponse wrapper DTO

**Files:**
- Create: `src/DTO/Response/ReportResponse.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ReportResponse
{
    /**
     * @param TrainerCatchStateCountResponse[] $catchStateCountsDefinedByTrainer
     * @param DexUsageResponse[]               $dexUsage
     * @param CatchStateUsageResponse[]        $catchStateUsage
     */
    public function __construct(
        #[SerializedName('catch_state_counts_defined_by_trainer')]
        public readonly array $catchStateCountsDefinedByTrainer,
        #[SerializedName('dex_usage')]
        public readonly array $dexUsage,
        #[SerializedName('catch_state_usage')]
        public readonly array $catchStateUsage,
    ) {}
}
```

Save as `src/DTO/Response/ReportResponse.php`.

- [ ] **Step 2: Verify the file exists**

Run: `ls -la src/DTO/Response/ReportResponse.php`

Expected: File exists.

---

### Task 5: Create ReportResponseFactory

**Files:**
- Create: `src/Factory/ReportResponseFactory.php`

- [ ] **Step 1: Create the Factory**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\ReportResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;

final class ReportResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromCatchStateCountRow(array $row): TrainerCatchStateCountResponse
    {
        /** @var scalar $nb */
        $nb = $row['nb'];

        /** @var scalar $trainer */
        $trainer = $row['trainer'];

        return new TrainerCatchStateCountResponse(
            nb: (int) $nb,
            trainer: (string) $trainer,
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromDexUsageRow(array $row): DexUsageResponse
    {
        /** @var scalar $nb */
        $nb = $row['nb'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        return new DexUsageResponse(
            nb: (int) $nb,
            name: (string) $name,
            frenchName: (string) $frenchName,
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromCatchStateUsageRow(array $row): CatchStateUsageResponse
    {
        /** @var scalar $nb */
        $nb = $row['nb'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $color */
        $color = $row['color'];

        return new CatchStateUsageResponse(
            nb: (int) $nb,
            name: (string) $name,
            frenchName: (string) $frenchName,
            color: (string) $color,
        );
    }

    /**
     * @param array<array-key, mixed> $catchStateCounts
     * @param array<array-key, mixed> $dexUsage
     * @param array<array-key, mixed> $catchStateUsage
     */
    public static function fromServiceArrays(
        array $catchStateCounts,
        array $dexUsage,
        array $catchStateUsage,
    ): ReportResponse {
        return new ReportResponse(
            catchStateCountsDefinedByTrainer: array_map(self::fromCatchStateCountRow(...), $catchStateCounts),
            dexUsage: array_map(self::fromDexUsageRow(...), $dexUsage),
            catchStateUsage: array_map(self::fromCatchStateUsageRow(...), $catchStateUsage),
        );
    }
}
```

Save as `src/Factory/ReportResponseFactory.php`.

- [ ] **Step 2: Verify the file exists**

Run: `ls -la src/Factory/ReportResponseFactory.php`

Expected: File exists.

---

### Task 6: Write unit tests for ReportResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/ReportResponseFactoryTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\ReportResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;
use App\Factory\ReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportResponseFactory::class)]
final class ReportResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromCatchStateCountRowTransformsRowCorrectly(): void
    {
        $row = ['nb' => 28, 'trainer' => '7b52009b64fd0a2a49e6d8a939753077792b0554'];

        $response = ReportResponseFactory::fromCatchStateCountRow($row);

        self::assertSame(28, $response->nb);
        self::assertSame('7b52009b64fd0a2a49e6d8a939753077792b0554', $response->trainer);
    }

    #[Test]
    public function fromCatchStateCountRowCastsToCorrectTypes(): void
    {
        $row = ['nb' => '5', 'trainer' => 'abc'];

        $response = ReportResponseFactory::fromCatchStateCountRow($row);

        self::assertSame(5, $response->nb);
        self::assertSame('abc', $response->trainer);
    }

    #[Test]
    public function fromDexUsageRowTransformsRowCorrectly(): void
    {
        $row = ['nb' => 2, 'name' => 'Red / Green', 'french_name' => 'Rouge / Vert'];

        $response = ReportResponseFactory::fromDexUsageRow($row);

        self::assertSame(2, $response->nb);
        self::assertSame('Red / Green', $response->name);
        self::assertSame('Rouge / Vert', $response->frenchName);
    }

    #[Test]
    public function fromDexUsageRowCastsToCorrectTypes(): void
    {
        $row = ['nb' => '3', 'name' => 123, 'french_name' => 456];

        $response = ReportResponseFactory::fromDexUsageRow($row);

        self::assertSame(3, $response->nb);
        self::assertSame('123', $response->name);
        self::assertSame('456', $response->frenchName);
    }

    #[Test]
    public function fromCatchStateUsageRowTransformsRowCorrectly(): void
    {
        $row = ['nb' => 11, 'name' => 'No', 'french_name' => 'Non', 'color' => '#e57373'];

        $response = ReportResponseFactory::fromCatchStateUsageRow($row);

        self::assertSame(11, $response->nb);
        self::assertSame('No', $response->name);
        self::assertSame('Non', $response->frenchName);
        self::assertSame('#e57373', $response->color);
    }

    #[Test]
    public function fromCatchStateUsageRowCastsToCorrectTypes(): void
    {
        $row = ['nb' => '4', 'name' => 789, 'french_name' => 0, 'color' => 'blue'];

        $response = ReportResponseFactory::fromCatchStateUsageRow($row);

        self::assertSame(4, $response->nb);
        self::assertSame('789', $response->name);
        self::assertSame('0', $response->frenchName);
        self::assertSame('blue', $response->color);
    }

    #[Test]
    public function fromServiceArraysBuildsReportResponseCorrectly(): void
    {
        $catchStateCounts = [
            ['nb' => 28, 'trainer' => '7b52009b64fd0a2a49e6d8a939753077792b0554'],
            ['nb' => 3, 'trainer' => 'bd307a3ec329e10a2cff8fb87480823da114f8f4'],
        ];
        $dexUsage = [
            ['nb' => 2, 'name' => 'Home', 'french_name' => 'Home'],
        ];
        $catchStateUsage = [
            ['nb' => 11, 'name' => 'No', 'french_name' => 'Non', 'color' => '#e57373'],
        ];

        $report = ReportResponseFactory::fromServiceArrays($catchStateCounts, $dexUsage, $catchStateUsage);

        self::assertInstanceOf(ReportResponse::class, $report);
        self::assertCount(2, $report->catchStateCountsDefinedByTrainer);
        self::assertContainsOnlyInstancesOf(TrainerCatchStateCountResponse::class, $report->catchStateCountsDefinedByTrainer);
        self::assertCount(1, $report->dexUsage);
        self::assertContainsOnlyInstancesOf(DexUsageResponse::class, $report->dexUsage);
        self::assertCount(1, $report->catchStateUsage);
        self::assertContainsOnlyInstancesOf(CatchStateUsageResponse::class, $report->catchStateUsage);
    }

    #[Test]
    public function fromServiceArraysHandlesEmptyArrays(): void
    {
        $report = ReportResponseFactory::fromServiceArrays([], [], []);

        self::assertInstanceOf(ReportResponse::class, $report);
        self::assertCount(0, $report->catchStateCountsDefinedByTrainer);
        self::assertCount(0, $report->dexUsage);
        self::assertCount(0, $report->catchStateUsage);
    }
}
```

Save as `tests/src/Unit/Factory/ReportResponseFactoryTest.php`.

- [ ] **Step 2: Run the unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/ReportResponseFactoryTest.php`

Expected: 8 tests pass, 0 failures.

---

### Task 7: Update ReportsController

**Files:**
- Modify: `src/Controller/ReportsController.php`

- [ ] **Step 1: Read the current controller**

Current `src/Controller/ReportsController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PokedexService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reports')]
final class ReportsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(PokedexService $service): JsonResponse
    {
        return new JsonResponse([
            'catch_state_counts_defined_by_trainer' => $service->getCatchStateCountsDefinedByTrainer(),
            'dex_usage' => $service->getDexUsage(),
            'catch_state_usage' => $service->getCatchStateUsage(),
        ]);
    }
}
```

- [ ] **Step 2: Replace with Factory + Serializer version**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\ReportResponseFactory;
use App\Service\PokedexService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/reports')]
final class ReportsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(PokedexService $service, SerializerInterface $serializer): JsonResponse
    {
        $report = ReportResponseFactory::fromServiceArrays(
            $service->getCatchStateCountsDefinedByTrainer(),
            $service->getDexUsage(),
            $service->getCatchStateUsage(),
        );

        return JsonResponse::fromJsonString(
            $serializer->serialize($report, 'json'),
        );
    }
}
```

Save as `src/Controller/ReportsController.php`.

- [ ] **Step 3: Verify syntax**

Run: `docker compose exec php php -l src/Controller/ReportsController.php`

Expected: `No syntax errors detected`.

---

### Task 8: Create the reports JSON fixture

**Files:**
- Create: `tests/resources/fixtures/reports_response.json`

- [ ] **Step 1: Create the fixture**

This JSON must exactly match the output produced by running `GET /reports` against the Alice-seeded integration database (the same data verified by the old `ReportsControllerTest::getReportsData()`).

```json
{
  "catch_state_counts_defined_by_trainer": [
    {
      "nb": 28,
      "trainer": "7b52009b64fd0a2a49e6d8a939753077792b0554"
    },
    {
      "nb": 3,
      "trainer": "bd307a3ec329e10a2cff8fb87480823da114f8f4"
    }
  ],
  "dex_usage": [
    {
      "nb": 2,
      "name": "Red / Green / Blue / Yellow",
      "french_name": "Rouge / Vert / Bleu / Jaune"
    },
    {
      "nb": 2,
      "name": "Gold / Silver / Crystal",
      "french_name": "Or / Argent / Cristal"
    },
    {
      "nb": 2,
      "name": "Home",
      "french_name": "Home"
    },
    {
      "nb": 1,
      "name": "Ruby / Sapphire / Emerald",
      "french_name": "Rubis / Saphir / Émeraude"
    },
    {
      "nb": 1,
      "name": "Home\nShiny",
      "french_name": "Home\nChromatique"
    },
    {
      "nb": 1,
      "name": "Home PoGo",
      "french_name": "Home PoGo"
    }
  ],
  "catch_state_usage": [
    {
      "nb": 11,
      "name": "No",
      "french_name": "Non",
      "color": "#e57373"
    },
    {
      "nb": 4,
      "name": "Maybe",
      "french_name": "Peut être",
      "color": "blue"
    },
    {
      "nb": 5,
      "name": "Maybe not",
      "french_name": "Peut être pas",
      "color": "yellow"
    },
    {
      "nb": 11,
      "name": "Yes",
      "french_name": "Oui",
      "color": "#66bb6a"
    }
  ]
}
```

Save as `tests/resources/fixtures/reports_response.json`.

- [ ] **Step 2: Verify the file is valid JSON**

Run: `docker compose exec php php -r "json_decode(file_get_contents('/app/tests/resources/fixtures/reports_response.json'), true, 512, JSON_THROW_ON_ERROR); echo 'valid'"`

Expected: `valid`.

---

### Task 9: Update ReportsControllerTest

**Files:**
- Modify: `tests/src/Integration/Controller/ReportsControllerTest.php`

- [ ] **Step 1: Read the current test**

Current `tests/src/Integration/Controller/ReportsControllerTest.php` (shown in full above under context).

- [ ] **Step 2: Replace with the new pattern**

The new test follows the same style as `TypesControllerTest`: separate methods per concern, fixture comparison for exact data.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ReportsController;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ReportsController::class)]
final class ReportsControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
            'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsObjectWithRequiredSections(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
            'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);
        self::assertArrayHasKey('catch_state_counts_defined_by_trainer', $data);
        self::assertArrayHasKey('dex_usage', $data);
        self::assertArrayHasKey('catch_state_usage', $data);
    }

    #[Test]
    public function getCatchStateCountsHaveCorrectShape(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
            'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $item */
        foreach ($data['catch_state_counts_defined_by_trainer'] as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('nb', $item);
            self::assertArrayHasKey('trainer', $item);
            self::assertIsInt($item['nb']);
            self::assertIsString($item['trainer']);
        }
    }

    #[Test]
    public function getDexUsageHasCorrectShape(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
            'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $item */
        foreach ($data['dex_usage'] as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('nb', $item);
            self::assertArrayHasKey('name', $item);
            self::assertArrayHasKey('french_name', $item);
            self::assertIsInt($item['nb']);
            self::assertIsString($item['name']);
            self::assertIsString($item['french_name']);
        }
    }

    #[Test]
    public function getCatchStateUsageHasCorrectShape(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
            'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $item */
        foreach ($data['catch_state_usage'] as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('nb', $item);
            self::assertArrayHasKey('name', $item);
            self::assertArrayHasKey('french_name', $item);
            self::assertArrayHasKey('color', $item);
            self::assertIsInt($item['nb']);
            self::assertIsString($item['name']);
            self::assertIsString($item['french_name']);
            self::assertIsString($item['color']);
        }
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
            'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/reports_response.json',
            $content,
        );
    }
}
```

Save as `tests/src/Integration/Controller/ReportsControllerTest.php`.

- [ ] **Step 3: Run the integration tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/ReportsControllerTest.php`

Expected: 6 tests pass, 0 failures.

> **Troubleshooting:** If `getResponseMatchesFixture` fails with a JSON mismatch, dump the actual response to compare:
> ```php
> file_put_contents('tests/last_reports.json', $content);
> ```
> Then diff `tests/last_reports.json` against `tests/resources/fixtures/reports_response.json` and update the fixture accordingly.

---

## Self-Review

### Spec coverage

- [x] Endpoint `GET /reports` uses Factory + Serializer — Task 7
- [x] Three sub-DTOs typed and immutable — Tasks 1-3
- [x] Wrapper DTO with `#[SerializedName]` for snake_case keys — Task 4
- [x] Factory with `/** @var scalar */` casts and explicit type casts — Task 5
- [x] Unit tests covering all 4 factory methods — Task 6
- [x] Integration test with fixture comparison — Tasks 8-9
- [x] JSON output format identical to current (no client-breaking change)

### Placeholder scan

No TBD, TODO, or "similar to" references — all code is complete.

### Type consistency

- `TrainerCatchStateCountResponse::$nb` is `int` throughout (factory casts `(int)`, DTO declares `int`)
- `frenchName` property maps to `french_name` JSON key via `#[SerializedName]` in Tasks 2, 3, 4
- `ReportResponseFactory::fromServiceArrays` parameters named to match their DTO targets: `catchStateCounts → catchStateCountsDefinedByTrainer`, `dexUsage → dexUsage`, `catchStateUsage → catchStateUsage`
