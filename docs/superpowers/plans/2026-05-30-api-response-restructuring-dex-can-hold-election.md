# API Response Restructuring (Dex Can Hold Election) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /dex/can_hold_election` endpoint from raw SQL rows passed directly to `JsonResponse` to typed response DTOs using the Factory + Serializer pattern already applied to `/types`, `/catch_states`, `/game_bundles`, `/forms/*`, and `/collections`.

**Architecture:** Create an immutable `DexResponse` DTO, a `DexResponseFactory` to transform SQL rows into DTOs, update `DexCanHoldElectionController` to inject `SerializerInterface` and apply the Factory, create a fixture JSON file, and update the existing integration test to the new `AbstractTestControllerApi`-based style with fixture assertion.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/DexResponse.php` — immutable DTO with 11 typed properties (strings, booleans, integer) matching the SQL query output of `DexRepository::getCanHoldElection()`
- `src/Factory/DexResponseFactory.php` — transforms SQL rows → `DexResponse` DTOs with explicit type casts
- `tests/src/Unit/Factory/DexResponseFactoryTest.php` — 4 unit tests covering single row, type casting, multiple rows, empty array
- `tests/resources/fixtures/dex_can_hold_election_response.json` — expected JSON response for fixture assertion (default call: no params)

**Modify:**
- `src/Controller/DexCanHoldElectionController.php` — inject `SerializerInterface`, apply Factory, remove the `// Better with serializer ?` comment
- `tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php` — rewrite to extend `AbstractTestControllerApi`, use `#[Test]` attributes, add `#[CoversClass]` for Factory and Service, add fixture assertion test

---

## Tasks

### Task 1: Create DexResponse DTO

**Files:**
- Create: `src/DTO/Response/DexResponse.php`

- [ ] **Step 1: Create the DTO file**

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
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        #[SerializedName('is_display_form')]
        public readonly bool $isDisplayForm,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        #[SerializedName('dex_total_count')]
        public readonly int $dexTotalCount,
    ) {}
}
```

Save as `src/DTO/Response/DexResponse.php`.

- [ ] **Step 2: Verify the file exists and has no syntax errors**

Run: `docker compose exec php php -l src/DTO/Response/DexResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/DexResponse.php`

---

### Task 2: Create DexResponseFactory

**Files:**
- Create: `src/Factory/DexResponseFactory.php`

- [ ] **Step 1: Create the Factory file**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexResponse;

final class DexResponseFactory
{
    /**
     * Transform a single SQL row into DexResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): DexResponse
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

        /** @var scalar $isDisplayForm */
        $isDisplayForm = $row['is_display_form'];

        /** @var scalar $description */
        $description = $row['description'];

        /** @var scalar $frenchDescription */
        $frenchDescription = $row['french_description'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $dexTotalCount */
        $dexTotalCount = $row['dex_total_count'];

        return new DexResponse(
            slug: (string) $slug,
            originalSlug: (string) $originalSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            isShiny: (bool) $isShiny,
            isDisplayForm: (bool) $isDisplayForm,
            description: (string) $description,
            frenchDescription: (string) $frenchDescription,
            isReleased: (bool) $isReleased,
            isPremium: (bool) $isPremium,
            dexTotalCount: (int) $dexTotalCount,
        );
    }

    /**
     * Transform multiple SQL rows into DexResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return DexResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
```

Save as `src/Factory/DexResponseFactory.php`.

- [ ] **Step 2: Verify the file exists and has no syntax errors**

Run: `docker compose exec php php -l src/Factory/DexResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/DexResponseFactory.php`

---

### Task 3: Write unit tests for DexResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/DexResponseFactoryTest.php`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

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
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'home',
            'original_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'is_shiny' => false,
            'is_display_form' => true,
            'description' => 'The National Dex in Home',
            'french_description' => 'Le Pokédex National dans Home',
            'is_released' => true,
            'is_premium' => false,
            'dex_total_count' => 22,
        ];

        $response = DexResponseFactory::fromSqlRow($row);

        self::assertSame('home', $response->slug);
        self::assertSame('home', $response->originalSlug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('The National Dex in Home', $response->description);
        self::assertSame('Le Pokédex National dans Home', $response->frenchDescription);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertSame(22, $response->dexTotalCount);
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
            'is_display_form' => 1,
            'description' => 202,
            'french_description' => 303,
            'is_released' => 1,
            'is_premium' => 0,
            'dex_total_count' => '7',
        ];

        $response = DexResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->originalSlug);
        self::assertSame('789', $response->name);
        self::assertSame('101', $response->frenchName);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('202', $response->description);
        self::assertSame('303', $response->frenchDescription);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
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
                'is_display_form' => true,
                'description' => '',
                'french_description' => '',
                'is_released' => true,
                'is_premium' => false,
                'dex_total_count' => 22,
            ],
            [
                'slug' => 'redgreenblueyellow',
                'original_slug' => 'redgreenblueyellow',
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'is_shiny' => false,
                'is_display_form' => true,
                'description' => '',
                'french_description' => '',
                'is_released' => true,
                'is_premium' => true,
                'dex_total_count' => 7,
            ],
        ];

        $responses = DexResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(DexResponse::class, $responses);
        self::assertSame('home', $responses[0]->slug);
        self::assertSame(22, $responses[0]->dexTotalCount);
        self::assertSame('redgreenblueyellow', $responses[1]->slug);
        self::assertSame(7, $responses[1]->dexTotalCount);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = DexResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
```

Save as `tests/src/Unit/Factory/DexResponseFactoryTest.php`.

- [ ] **Step 2: Run the unit tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/DexResponseFactoryTest.php`

Expected: 4 tests, 0 failures, 0 errors.

---

### Task 4: Update DexCanHoldElectionController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/DexCanHoldElectionController.php`

- [ ] **Step 1: Replace the controller content**

Current file content (`src/Controller/DexCanHoldElectionController.php`):

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\Service\DexCanHoldElectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dex')]
final class DexCanHoldElectionController extends AbstractController
{
    #[Route(path: '/can_hold_election', methods: ['GET'])]
    public function list(
        Request $request,
        DexCanHoldElectionService $service,
    ): JsonResponse {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        $dex = $service->get($dexQueryOptions);

        // Better with serializer ?
        return new JsonResponse($dex);
    }
}
```

Replace with:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\Factory\DexResponseFactory;
use App\Service\DexCanHoldElectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/dex')]
final class DexCanHoldElectionController extends AbstractController
{
    #[Route(path: '/can_hold_election', methods: ['GET'])]
    public function list(
        Request $request,
        DexCanHoldElectionService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        $dex = $service->get($dexQueryOptions);

        $responses = DexResponseFactory::fromSqlRows($dex);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
```

- [ ] **Step 2: Verify the file has no syntax errors**

Run: `docker compose exec php php -l src/Controller/DexCanHoldElectionController.php`

Expected: `No syntax errors detected in src/Controller/DexCanHoldElectionController.php`

---

### Task 5: Create fixture JSON file for the default response

**Files:**
- Create: `tests/resources/fixtures/dex_can_hold_election_response.json`

The fixture represents the response of `GET /dex/can_hold_election` with no query params (defaults: `include_unreleased_dex=false`, `include_premium_dex=false`), which returns only released, non-premium dexes. Based on the Alice test fixtures in the database, this returns a single entry: the `home` dex.

- [ ] **Step 1: Create the fixture file**

```json
[
    {
        "slug": "home",
        "original_slug": "home",
        "name": "Home",
        "french_name": "Home",
        "is_shiny": false,
        "is_display_form": true,
        "description": "",
        "french_description": "",
        "is_released": true,
        "is_premium": false,
        "dex_total_count": 22
    }
]
```

Save as `tests/resources/fixtures/dex_can_hold_election_response.json`.

- [ ] **Step 2: Verify the file is valid JSON**

Run: `docker compose exec php php -r "json_decode(file_get_contents('tests/resources/fixtures/dex_can_hold_election_response.json'), true, 512, JSON_THROW_ON_ERROR); echo 'Valid JSON'"`

Expected: `Valid JSON`

---

### Task 6: Rewrite integration test for DexCanHoldElectionController

**Files:**
- Modify: `tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php`

The current test extends `WebTestCase` directly with `RefreshDatabaseTrait` and uses old-style method names. Replace the entire file with the new `AbstractTestControllerApi`-based style.

- [ ] **Step 1: Replace the integration test file**

Current file content (`tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php`) should be replaced entirely with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexCanHoldElectionController;
use App\Factory\DexResponseFactory;
use App\Service\DexCanHoldElectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(DexCanHoldElectionController::class)]
#[CoversClass(DexResponseFactory::class)]
#[CoversClass(DexCanHoldElectionService::class)]
final class DexCanHoldElectionControllerTest extends AbstractTestControllerApi
{
    #[Test]
    public function listReturnsDexByDefault(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election');

        $this->assertResponseIsOK();

        /** @var array<int, array<string, mixed>> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(1, $content);

        $this->assertEquals([
            'slug' => 'home',
            'original_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'is_shiny' => false,
            'is_display_form' => true,
            'description' => '',
            'french_description' => '',
            'is_released' => true,
            'is_premium' => false,
            'dex_total_count' => 22,
        ], $content[0]);
    }

    #[Test]
    public function listReturnsDexWithAllOptions(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election', [
            'include_unreleased_dex' => true,
            'include_premium_dex' => true,
        ]);

        $this->assertResponseIsOK();

        /** @var array<int, array<string, mixed>> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(4, $content);

        $this->assertEquals([
            'slug' => 'homepogo',
            'original_slug' => 'homepogo',
            'name' => 'Home PoGo',
            'french_name' => 'Home PoGo',
            'is_shiny' => false,
            'is_display_form' => false,
            'description' => '',
            'french_description' => '',
            'is_released' => false,
            'is_premium' => false,
            'dex_total_count' => 1,
        ], $content[0]);

        $this->assertEquals([
            'slug' => 'home',
            'original_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'is_shiny' => false,
            'is_display_form' => true,
            'description' => '',
            'french_description' => '',
            'is_released' => true,
            'is_premium' => false,
            'dex_total_count' => 22,
        ], $content[1]);

        $this->assertEquals([
            'slug' => 'redgreenblueyellow',
            'original_slug' => 'redgreenblueyellow',
            'name' => 'Red / Green / Blue / Yellow',
            'french_name' => 'Rouge / Vert / Bleu / Jaune',
            'is_shiny' => false,
            'is_display_form' => true,
            'description' => 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            'french_description' => 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            'is_released' => true,
            'is_premium' => true,
            'dex_total_count' => 7,
        ], $content[2]);

        $this->assertEquals([
            'slug' => 'spoon',
            'original_slug' => 'spoon',
            'name' => 'Spoon',
            'french_name' => 'Cuillière',
            'is_shiny' => false,
            'is_display_form' => true,
            'description' => '',
            'french_description' => '',
            'is_released' => false,
            'is_premium' => true,
            'dex_total_count' => 1,
        ], $content[3]);
    }

    #[Test]
    public function listResponseMatchesFixture(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/dex_can_hold_election_response.json',
            $content,
        );
    }

    #[Test]
    public function listReturnsOkWithAuth(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]);

        $this->assertResponseIsOK();
    }

    #[Test]
    public function listReturnsBadAuthWith401(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
```

- [ ] **Step 2: Verify the file has no syntax errors**

Run: `docker compose exec php php -l tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php`

Expected: `No syntax errors detected in tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php`

---

### Task 7: Run tests and quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run unit tests**

Run: `make tests-unit`

Expected: All unit tests pass, 0 failures.

- [ ] **Step 2: Run integration tests**

Run: `make tests-integration`

Expected: All integration tests pass including the rewritten `DexCanHoldElectionControllerTest`.

- [ ] **Step 3: Run all quality checks**

Run: `make quality`

Expected: PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint all pass.

- [ ] **Step 4: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% code coverage for new files, 100% MSI, all checks green.
