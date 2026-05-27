# API Response Restructuring (Types) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /types` endpoint from flat JSON to nested object-oriented structure using DTOs + Factory + Serializer pattern.

**Architecture:** Create immutable response DTOs, a Factory to transform flat SQL rows into nested DTOs, update the Controller to apply the transformation before serialization.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/TypeResponse.php` — immutable DTO representing nested type response
- `src/Factory/TypeResponseFactory.php` — transforms flat SQL rows → TypeResponse DTOs
- `tests/src/Unit/Factory/TypeResponseFactoryTest.php` — unit tests for Factory
- `tests/src/Integration/Controller/TypesControllerTest.php` — integration tests for Controller
- `docs/api-migration/types-restructuring.md` — client migration documentation

**Modify:**
- `src/Controller/TypesController.php` — apply Factory + Serializer
- `tests/resources/moco/Types/get.json` — update mock response structure

---

## Tasks

### Task 1: Create TypeResponse DTO

**Files:**
- Create: `src/DTO/Response/TypeResponse.php`

- [ ] **Step 1: Create the DTO file with immutable properties**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class TypeResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $french_name,
        public readonly string $color,
    ) {}
}
```

Save this as `src/DTO/Response/TypeResponse.php`.

- [ ] **Step 2: Verify the file is in the correct location**

Run: `ls -la src/DTO/Response/TypeResponse.php`

Expected: File exists with readable permissions.

---

### Task 2: Create TypeResponseFactory

**Files:**
- Create: `src/Factory/TypeResponseFactory.php`

- [ ] **Step 1: Create the Factory with static methods**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\TypeResponse;

final class TypeResponseFactory
{
    /**
     * Transform a single SQL row into TypeResponse DTO.
     *
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): TypeResponse
    {
        return new TypeResponse(
            slug: (string) $row['slug'],
            name: (string) $row['name'],
            french_name: (string) $row['french_name'],
            color: (string) $row['color'],
        );
    }

    /**
     * Transform multiple SQL rows into TypeResponse DTOs.
     *
     * @param array<array<string, mixed>> $rows
     * @return TypeResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
```

Save this as `src/Factory/TypeResponseFactory.php`.

- [ ] **Step 2: Verify the file is in the correct location**

Run: `ls -la src/Factory/TypeResponseFactory.php`

Expected: File exists with readable permissions.

---

### Task 3: Write unit tests for TypeResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/TypeResponseFactoryTest.php`
- Test: `TypeResponseFactory` class

- [ ] **Step 1: Create unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\TypeResponse;
use App\Factory\TypeResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TypeResponseFactory::class)]
final class TypeResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRow_transformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'electric',
            'name' => 'Electric',
            'french_name' => 'Électrique',
            'color' => '#FFCC33',
        ];

        $response = TypeResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(TypeResponse::class, $response);
        self::assertSame('electric', $response->slug);
        self::assertSame('Electric', $response->name);
        self::assertSame('Électrique', $response->french_name);
        self::assertSame('#FFCC33', $response->color);
    }

    #[Test]
    public function fromSqlRow_castsSlugsAndNamesToStrings(): void
    {
        $row = [
            'slug' => 123,
            'name' => 456,
            'french_name' => 789,
            'color' => '#ABC123',
        ];

        $response = TypeResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->french_name);
    }

    #[Test]
    public function fromSqlRows_transformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'electric',
                'name' => 'Electric',
                'french_name' => 'Électrique',
                'color' => '#FFCC33',
            ],
            [
                'slug' => 'water',
                'name' => 'Water',
                'french_name' => 'Eau',
                'color' => '#3399FF',
            ],
        ];

        $responses = TypeResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnly(TypeResponse::class, $responses);
        self::assertSame('electric', $responses[0]->slug);
        self::assertSame('water', $responses[1]->slug);
    }

    #[Test]
    public function fromSqlRows_handlesEmptyArray(): void
    {
        $responses = TypeResponseFactory::fromSqlRows([]);

        self::assertIsArray($responses);
        self::assertCount(0, $responses);
    }
}
```

Save this as `tests/src/Unit/Factory/TypeResponseFactoryTest.php`.

- [ ] **Step 2: Run the unit tests to verify they all pass**

Run: `make tests-unit --filter TypeResponseFactoryTest`

Expected: 4 tests pass, 0 failures.

- [ ] **Step 3: Verify 100% code coverage for the Factory**

Run: `make coverage --filter TypeResponseFactoryTest`

Expected: TypeResponseFactory has 100% line and branch coverage.

---

### Task 4: Update TypesController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/TypesController.php`

- [ ] **Step 1: Read the current controller**

Current content of `src/Controller/TypesController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\TypesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/types')]
final class TypesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        TypesService $service
    ): JsonResponse {
        $types = $service->getAll();

        // Better with serializer ?
        return new JsonResponse($types);
    }
}
```

- [ ] **Step 2: Update controller to use Factory and Serializer**

Replace the controller with:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\TypeResponseFactory;
use App\Service\TypesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/types')]
final class TypesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        TypesService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $types = $service->getAll();

        $responses = TypeResponseFactory::fromSqlRows($types);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
```

- [ ] **Step 3: Verify the controller file is syntactically correct**

Run: `make sh -c "php -l src/Controller/TypesController.php"`

Expected: "No syntax errors detected".

---

### Task 5: Create integration test for TypesController

**Files:**
- Create: `tests/src/Integration/Controller/TypesControllerTest.php`

- [ ] **Step 1: Create integration test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(\App\Controller\TypesController::class)]
final class TypesControllerTest extends WebTestCase
{
    #[Test]
    public function get_returnsSuccessfulJsonResponse(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/types');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function get_returnsArrayOfTypes(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/types');

        $data = json_decode($response->getContent(), associative: true);

        self::assertIsArray($data);
        self::assertNotEmpty($data);
    }

    #[Test]
    public function get_eachTypeHasRequiredFields(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/types');

        $data = json_decode($response->getContent(), associative: true);

        foreach ($data as $type) {
            self::assertArrayHasKey('slug', $type);
            self::assertArrayHasKey('name', $type);
            self::assertArrayHasKey('french_name', $type);
            self::assertArrayHasKey('color', $type);
        }
    }

    #[Test]
    public function get_fieldValuesAreStrings(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/types');

        $data = json_decode($response->getContent(), associative: true);
        $firstType = $data[0];

        self::assertIsString($firstType['slug']);
        self::assertIsString($firstType['name']);
        self::assertIsString($firstType['french_name']);
        self::assertIsString($firstType['color']);
    }
}
```

Save this as `tests/src/Integration/Controller/TypesControllerTest.php`.

- [ ] **Step 2: Run integration tests**

Run: `make ti --filter TypesControllerTest`

Expected: 4 tests pass, 0 failures.

- [ ] **Step 3: Verify integration tests are included in coverage**

Run: `make coverage --filter TypesControllerTest`

Expected: TypesController and TypeResponseFactory have 100% line coverage.

---

### Task 6: Update Moco mock fixture for GET /types

**Files:**
- Modify: `tests/resources/moco/Types/get.json`

- [ ] **Step 1: Check current Moco fixture**

Run: `cat tests/resources/moco/Types/get.json`

Expected: File contains mock response for GET /types.

- [ ] **Step 2: Verify fixture has correct structure**

The fixture should return an array of type objects matching the new DTO structure:

```json
[
  {
    "slug": "normal",
    "name": "Normal",
    "french_name": "Normal",
    "color": "#A8A878"
  },
  {
    "slug": "fighting",
    "name": "Fighting",
    "french_name": "Combat",
    "color": "#C03028"
  }
]
```

If the fixture exists and has this structure already, no changes needed. If it has a different structure, update it to match the new DTO response format.

Run: `make integration` to verify Moco serves the correct response during integration tests.

Expected: Integration tests pass with mocked response.

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

Expected: Code coverage ≥ 100% for new code, MSI ≥ 100%, all checks green.

- [ ] **Step 4: Verify no regressions in existing tests**

Run: `make tests-integration`

Expected: All integration tests pass, including existing ones (not just TypesController).

---

### Task 8: Create client migration documentation

**Files:**
- Create: `docs/api-migration/types-restructuring.md`

- [ ] **Step 1: Create migration documentation**

```markdown
# Types API — Response Structure Migration

**Endpoint:** `GET /types`  
**Version:** v1 (no versioning needed for this endpoint at this time)  
**Change type:** Breaking change  
**Status:** Live as of [DATE]

## Summary

The `GET /types` response structure has been refactored from a flat object model to a more explicitly nested object model. This improves API consistency and clarity.

## Impact Assessment

### pokenini-back

**Current usage:** Calls `GET /types`, passes response through to clients.

**Change required:** None. Response remains a JSON array of type objects.

**Testing:** Verify passthrough still works. No schema changes needed.

### pokenini-web

**Current usage:** Calls `GET /types` via pokenini-back, renders in Twig templates.

**Change required:** None. Response structure is identical — fields have same names (slug, name, french_name, color).

**Testing:** Verify Twig rendering still produces correct output.

## Response Comparison

### Before (structure is identical, showing for reference)

```json
[
  {
    "slug": "electric",
    "name": "Electric",
    "french_name": "Électrique",
    "color": "#FFCC33"
  }
]
```

### After

```json
[
  {
    "slug": "electric",
    "name": "Electric",
    "french_name": "Électrique",
    "color": "#FFCC33"
  }
]
```

**Note:** For the Types endpoint, the response structure is identical. The refactoring is internal (DTOs + Serializer). For future endpoints (Election/ELO), nested structures will differ significantly — see `docs/api-migration/` for those when released.

## Timeline

- **API deployed:** [DATE — fill in on release]
- **Client update deadline:** [DATE + 1 week]
- **Support window:** Contact [team contact] if migration issues arise

## Questions?

See the design spec: `docs/superpowers/specs/2026-05-25-api-response-restructuring-design.md`
```

Save this as `docs/api-migration/types-restructuring.md`.

- [ ] **Step 2: Verify the file is readable**

Run: `cat docs/api-migration/types-restructuring.md | head -20`

Expected: File contains migration guidance.

---

### Task 9: Final validation and checklist

**Files:**
- All files from previous tasks

- [ ] **Step 1: Verify all new files exist**

Run: `ls -la src/DTO/Response/TypeResponse.php src/Factory/TypeResponseFactory.php tests/src/Unit/Factory/TypeResponseFactoryTest.php tests/src/Integration/Controller/TypesControllerTest.php docs/api-migration/types-restructuring.md`

Expected: All 5 files exist with readable permissions.

- [ ] **Step 2: Verify modified files have correct syntax**

Run: `make sh -c "php -l src/Controller/TypesController.php && echo 'Syntax OK'"`

Expected: "Syntax OK".

- [ ] **Step 3: Run complete test suite one final time**

Run: `make tests`

Expected: All tests pass, 0 failures.

- [ ] **Step 4: Run complete quality checks**

Run: `make quality && make measures`

Expected: All quality checks green, 100% coverage, 100% MSI.

- [ ] **Step 5: Verify the endpoint works end-to-end**

Run: `make start` (if not already running), then:

```bash
curl http://localhost:8000/types | jq . | head -20
```

Expected: JSON array of types with slug, name, french_name, color fields.

- [ ] **Step 6: Document completion**

Summary of changes:
- ✅ Created TypeResponse DTO (immutable, snake_case properties)
- ✅ Created TypeResponseFactory (transforms flat SQL rows → DTOs)
- ✅ Updated TypesController (applies Factory + Serializer)
- ✅ Created unit tests for Factory (100% coverage)
- ✅ Created integration tests for Controller (4 test cases)
- ✅ Updated Moco fixtures (response structure)
- ✅ Created migration documentation
- ✅ All quality gates passing (make quality, make measures)
- ✅ End-to-end validation complete

**Status:** Types endpoint refactoring complete. Pattern ready for generalization to other endpoints.

---

## Next Steps (not in this plan)

Once this plan is complete and approved:

1. **Refactor Election/ELO endpoint** — use same pattern (DTOs + Factory)
2. **Validate pattern scalability** — ensure complex nested structures work
3. **Update other endpoints** — apply pattern to Dex, Album, Reports progressively
4. **Update client codebases** — pokenini-back and pokenini-web adapt to new structures

Each future refactoring will follow the same task structure: DTOs → Factory → Controller → Tests → Docs.
