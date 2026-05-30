# API Response Restructuring (Collections) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /collections` endpoint from raw SQL rows to typed response DTOs using the Factory + Serializer pattern already applied to `/types`, `/catch_states`, `/game_bundles`, and `/forms/*`.

**Architecture:** Create an immutable `CollectionResponse` DTO, a `CollectionResponseFactory` to transform SQL rows into DTOs, update `CollectionsController` to use them, create a fixture JSON file, and update the existing integration test to assert the fixture response.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/CollectionResponse.php` — immutable DTO with `slug`, `name`, `frenchName`
- `src/Factory/CollectionResponseFactory.php` — transforms SQL rows → `CollectionResponse` DTOs
- `tests/src/Unit/Factory/CollectionResponseFactoryTest.php` — 4 unit tests covering single row, casting, multiple rows, empty array
- `tests/resources/fixtures/collections_response.json` — expected JSON response for fixture assertion

**Modify:**
- `src/Controller/CollectionsController.php` — inject `SerializerInterface`, apply Factory
- `tests/src/Integration/Controller/CollectionsControllerTest.php` — update to new assertion pattern + fixture check

---

## Tasks

### Task 1: Create CollectionResponse DTO

**Files:**
- Create: `src/DTO/Response/CollectionResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class CollectionResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
    ) {}
}
```

Save as `src/DTO/Response/CollectionResponse.php`.

- [ ] **Step 2: Verify the file exists**

Run: `docker compose exec php php -l src/DTO/Response/CollectionResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/CollectionResponse.php`

---

### Task 2: Create CollectionResponseFactory

**Files:**
- Create: `src/Factory/CollectionResponseFactory.php`

- [ ] **Step 1: Create the Factory file**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\CollectionResponse;

final class CollectionResponseFactory
{
    /**
     * Transform a single SQL row into CollectionResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): CollectionResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        return new CollectionResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
        );
    }

    /**
     * Transform multiple SQL rows into CollectionResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return CollectionResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
```

Save as `src/Factory/CollectionResponseFactory.php`.

- [ ] **Step 2: Verify the file exists**

Run: `docker compose exec php php -l src/Factory/CollectionResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/CollectionResponseFactory.php`

---

### Task 3: Write unit tests for CollectionResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/CollectionResponseFactoryTest.php`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\CollectionResponse;
use App\Factory\CollectionResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CollectionResponseFactory::class)]
final class CollectionResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'swshdynamaxadventuresbosses',
            'name' => 'Sword, Shield - Dynamax Adventures bosses',
            'french_name' => 'Sword, Shield - Boss des expéditions Dynamax',
        ];

        $response = CollectionResponseFactory::fromSqlRow($row);

        self::assertSame('swshdynamaxadventuresbosses', $response->slug);
        self::assertSame('Sword, Shield - Dynamax Adventures bosses', $response->name);
        self::assertSame('Sword, Shield - Boss des expéditions Dynamax', $response->frenchName);
    }

    #[Test]
    public function fromSqlRowCastsValuesToStrings(): void
    {
        $row = [
            'slug' => 123,
            'name' => 456,
            'french_name' => 789,
        ];

        $response = CollectionResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->frenchName);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'swshdynamaxadventuresbosses',
                'name' => 'Sword, Shield - Dynamax Adventures bosses',
                'french_name' => 'Sword, Shield - Boss des expéditions Dynamax',
            ],
            [
                'slug' => 'pogodynamax',
                'name' => 'Pokemon Go - Dynamax',
                'french_name' => 'Pokemon Go - Dynamax',
            ],
        ];

        $responses = CollectionResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(CollectionResponse::class, $responses);
        self::assertSame('swshdynamaxadventuresbosses', $responses[0]->slug);
        self::assertSame('pogodynamax', $responses[1]->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = CollectionResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
```

Save as `tests/src/Unit/Factory/CollectionResponseFactoryTest.php`.

- [ ] **Step 2: Run the unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/CollectionResponseFactoryTest.php`

Expected: `OK (4 tests, 7 assertions)`

---

### Task 4: Update CollectionsController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/CollectionsController.php`

- [ ] **Step 1: Replace the controller content**

Current file `src/Controller/CollectionsController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CollectionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collections')]
final class CollectionsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CollectionsService $service
    ): JsonResponse {
        $types = $service->getAll();

        // Better with serializer ?
        return new JsonResponse($types);
    }
}
```

Replace with:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\CollectionResponseFactory;
use App\Service\CollectionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/collections')]
final class CollectionsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CollectionsService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $collections = $service->getAll();

        $responses = CollectionResponseFactory::fromSqlRows($collections);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
```

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/Controller/CollectionsController.php`

Expected: `No syntax errors detected in src/Controller/CollectionsController.php`

---

### Task 5: Create fixture JSON file

**Files:**
- Create: `tests/resources/fixtures/collections_response.json`

- [ ] **Step 1: Create the fixture JSON file**

The data matches the Alice fixtures in `fixtures/collections.yaml` (ordered by `orderNumber` ascending):

```json
[
  {
    "slug": "swshdynamaxadventuresbosses",
    "name": "Sword, Shield - Dynamax Adventures bosses",
    "french_name": "Sword, Shield - Boss des expéditions Dynamax"
  },
  {
    "slug": "svmassoutbreakspaldea",
    "name": "Scarlet, Violet - Paldea's outbreaks",
    "french_name": "Scarlet, Violet - Apparitions massives de Paldea"
  },
  {
    "slug": "svmassoutbreakskitakami",
    "name": "Scarlet, Violet - Kitakami's outbreaks",
    "french_name": "Scarlet, Violet - Apparitions massives de Kitakami"
  },
  {
    "slug": "svmassoutbreaksterrarium",
    "name": "Scarlet, Violet - Terrarium's outbreaks",
    "french_name": "Scarlet, Violet - Apparitions massives du Terrarium"
  },
  {
    "slug": "svtransferable",
    "name": "Scarlet, Violet - Transferable",
    "french_name": "Scarlet, Violet - Transférable"
  },
  {
    "slug": "pogoshadow",
    "name": "Pokemon Go - Shadow",
    "french_name": "Pokemon Go - Obscurs"
  },
  {
    "slug": "pogoshadowshiny",
    "name": "Pokemon Go - Shiny Shadow",
    "french_name": "Pokemon Go - Obscurs Chromatique"
  },
  {
    "slug": "pogodynamax",
    "name": "Pokemon Go - Dynamax",
    "french_name": "Pokemon Go - Dynamax"
  }
]
```

Save as `tests/resources/fixtures/collections_response.json`.

- [ ] **Step 2: Verify the file is valid JSON**

Run: `docker compose exec php php -r "json_decode(file_get_contents('tests/resources/fixtures/collections_response.json'), true); echo json_last_error() === JSON_ERROR_NONE ? 'Valid JSON' : 'Invalid JSON';"`

Expected: `Valid JSON`

---

### Task 6: Update CollectionsControllerTest

**Files:**
- Modify: `tests/src/Integration/Controller/CollectionsControllerTest.php`

The existing test uses `AbstractTestControllerApi` (which brings in `RefreshDatabaseTrait` to reload Alice fixtures) and the old assertion style. Update it to add the fixture assertion and use PHPUnit attributes, while keeping `AbstractTestControllerApi` since the test data comes from Alice fixtures.

Note: the existing `testGetAuth` contains a copy-paste error — it tests `/types` instead of `/collections`. Fix it in this update.

- [ ] **Step 1: Replace the test file content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\CollectionsController;
use App\Factory\CollectionResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(CollectionsController::class)]
#[CoversClass(CollectionResponseFactory::class)]
final class CollectionsControllerTest extends AbstractTestControllerApi
{
    #[Test]
    public function getReturnsCollections(): void
    {
        $this->apiRequest('GET', '/collections');

        $this->assertResponseIsOK();

        /** @var array<array-key, mixed> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(8, $content);

        $this->assertEquals([
            'slug' => 'swshdynamaxadventuresbosses',
            'name' => 'Sword, Shield - Dynamax Adventures bosses',
            'french_name' => 'Sword, Shield - Boss des expéditions Dynamax',
        ], $content[0]);

        $this->assertEquals([
            'slug' => 'svmassoutbreaksterrarium',
            'name' => "Scarlet, Violet - Terrarium's outbreaks",
            'french_name' => 'Scarlet, Violet - Apparitions massives du Terrarium',
        ], $content[3]);

        $this->assertEquals([
            'slug' => 'pogodynamax',
            'name' => 'Pokemon Go - Dynamax',
            'french_name' => 'Pokemon Go - Dynamax',
        ], $content[7]);
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $this->apiRequest('GET', '/collections');

        $this->assertResponseIsOK();

        $response = $this->getClientResponse();
        $content = $response->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/collections_response.json',
            $content,
        );
    }

    #[Test]
    public function getReturnsOkWithAuth(): void
    {
        $this->apiRequest('GET', '/collections', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]);

        $this->assertResponseIsOK();

        /** @var array<array-key, mixed> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(8, $content);
    }

    #[Test]
    public function getReturnsBadAuthWith401(): void
    {
        $this->apiRequest('GET', '/collections', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
```

- [ ] **Step 2: Run the integration tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/CollectionsControllerTest.php`

Expected: `OK (4 tests, ...)` — all pass.

If the fixture JSON is slightly wrong (field order, whitespace differences in french strings), run the test once and compare the actual response against the fixture, then correct the fixture.

---

### Task 7: Run full quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all tests**

Run: `make tests`

Expected: All unit and integration tests pass, 0 failures.

- [ ] **Step 2: Run code quality checks**

Run: `make quality`

Expected: All quality checks pass (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint).

- [ ] **Step 3: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% code coverage, 100% MSI — all green.

- [ ] **Step 4: Verify no regressions**

Run: `make tests-integration`

Expected: All integration tests pass including pre-existing ones.
