# API Response Restructuring (Forms Catalog) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /forms/category`, `GET /forms/regional`, `GET /forms/special` and `GET /forms/variant` from raw SQL array responses to the DTO + Factory + Serializer pattern, matching the architecture established by the Types and CatchStates migrations.

**Architecture:** Update the existing `FormResponse` DTO to add `frenchName`, create a shared `FormResponseFactory` for all four endpoints, update the four controllers to apply the transformation before serialization, create service unit tests (to replace coverage lost when rewriting the integration tests), and replace the four integration controller tests with the newer `WebTestCase` pattern.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## Context

`GET /forms/category`, `GET /forms/regional`, `GET /forms/special` and `GET /forms/variant` all return `[{slug, name, french_name}]` — the same SQL shape with no color. The controllers all contain `// Better with serializer ?` and do `new JsonResponse($forms)` directly on raw SQL rows.

A `FormResponse` DTO already exists (`src/DTO/Response/FormResponse.php`) with only `slug` and `name`. It is also used by `ElectionEloResponseFactory::buildForm()` which creates form references inside the ELO response. To add `frenchName` as a required field while keeping `buildForm()` working, that method will pass `frenchName: ''` — consistent with how `ElectionEloResponseFactory::buildType()` passes `color: ''` for `TypeResponse`.

The reference migrations are:
- `src/Controller/TypesController.php` + `src/Factory/TypeResponseFactory.php`
- `src/Controller/CatchStatesController.php` + `src/Factory/CatchStateResponseFactory.php`

Fixture data (ordered by `orderNumber`, `deleted_at IS NULL`):

| Endpoint | Count | Items in order |
|----------|-------|----------------|
| `/forms/category` | 3 | starter, mythical, legendary |
| `/forms/regional` | 3 | alolan, galarian, hisuian |
| `/forms/special` | 4 | mega, gigantamax, alpha, totem |
| `/forms/variant` | 7 | gender, alternate, baby, battle, item, fusion, unobtainable |

---

## File Structure

**Modify:**
- `src/DTO/Response/FormResponse.php` — add `frenchName` property with `#[SerializedName('french_name')]`
- `src/Factory/ElectionEloResponseFactory.php` — update `buildForm()` to pass `frenchName: ''`
- `src/Controller/CategoryFormsController.php` — apply Factory + Serializer
- `src/Controller/RegionalFormsController.php` — apply Factory + Serializer
- `src/Controller/SpecialFormsController.php` — apply Factory + Serializer
- `src/Controller/VariantFormsController.php` — apply Factory + Serializer
- `tests/src/Unit/DTO/Response/FormResponseTest.php` — cover the new `frenchName` property
- `tests/src/Integration/Controller/CategoryFormsControllerTest.php` — replace with `WebTestCase` pattern
- `tests/src/Integration/Controller/RegionalFormsControllerTest.php` — replace with `WebTestCase` pattern
- `tests/src/Integration/Controller/SpecialFormsControllerTest.php` — replace with `WebTestCase` pattern
- `tests/src/Integration/Controller/VariantFormsControllerTest.php` — replace with `WebTestCase` pattern

**Create:**
- `src/Factory/FormResponseFactory.php` — transforms flat SQL rows → `FormResponse` DTOs
- `tests/src/Unit/Factory/FormResponseFactoryTest.php` — unit tests for the factory
- `tests/src/Unit/Service/CategoryFormsServiceTest.php` — unit tests for the service
- `tests/src/Unit/Service/RegionalFormsServiceTest.php` — unit tests for the service
- `tests/src/Unit/Service/SpecialFormsServiceTest.php` — unit tests for the service
- `tests/src/Unit/Service/VariantFormsServiceTest.php` — unit tests for the service
- `tests/resources/fixtures/forms_category_response.json` — expected JSON fixture
- `tests/resources/fixtures/forms_regional_response.json` — expected JSON fixture
- `tests/resources/fixtures/forms_special_response.json` — expected JSON fixture
- `tests/resources/fixtures/forms_variant_response.json` — expected JSON fixture

---

## Tasks

### Task 1: Update FormResponse DTO

**Files:**
- Modify: `src/DTO/Response/FormResponse.php`

`FormResponse` is currently `final class FormResponse { __construct(string $slug, string $name) }`. Adding `frenchName` as a third required property enables the factory to populate it. The `ElectionEloResponseFactory` will pass `frenchName: ''` (Task 2).

- [ ] **Step 1: Update the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class FormResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
    ) {}
}
```

Save as `src/DTO/Response/FormResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/FormResponse.php`

Expected: `No syntax errors detected in src/DTO/Response/FormResponse.php`

---

### Task 2: Update ElectionEloResponseFactory to pass frenchName

**Files:**
- Modify: `src/Factory/ElectionEloResponseFactory.php`

`buildForm()` currently creates `new FormResponse(slug: ..., name: ...)`. After Task 1 adds `frenchName` as required, this must be updated. The ELO SQL query does not return form french names, so pass `frenchName: ''` — consistent with `buildType()` passing `color: ''`.

- [ ] **Step 1: Update the buildForm() method**

Find the `buildForm()` private method and replace the `return new FormResponse(...)` call:

```php
return new FormResponse(
    slug: (string) $slug,
    name: (string) $name,
    frenchName: '',
);
```

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/Factory/ElectionEloResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/ElectionEloResponseFactory.php`

- [ ] **Step 3: Run unit tests to confirm ElectionElo factory still passes**

Run: `make tests-unit`

Expected: All existing tests pass. 0 failures.

---

### Task 3: Update FormResponseTest

**Files:**
- Modify: `tests/src/Unit/DTO/Response/FormResponseTest.php`

The existing test covers `slug` and `name` only. It must be updated to also cover `frenchName`.

- [ ] **Step 1: Replace the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FormResponse::class)]
final class FormResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new FormResponse(
            slug: 'alolan',
            name: 'Alolan Form',
            frenchName: "Forme d'Alola",
        );

        self::assertSame('alolan', $response->slug);
        self::assertSame('Alolan Form', $response->name);
        self::assertSame("Forme d'Alola", $response->frenchName);
    }
}
```

- [ ] **Step 2: Run unit tests to confirm they pass**

Run: `make tests-unit`

Expected: All tests pass. 0 failures.

---

### Task 4: Create FormResponseFactory

**Files:**
- Create: `src/Factory/FormResponseFactory.php`

The factory mirrors `src/Factory/TypeResponseFactory.php`. Each scalar extraction uses a local typed variable with a PHPDoc cast — required by PHPStan level 9 because SQL rows are `array<array-key, mixed>`.

- [ ] **Step 1: Create the factory file**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\FormResponse;

final class FormResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): FormResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        return new FormResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return FormResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
```

Save as `src/Factory/FormResponseFactory.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/Factory/FormResponseFactory.php`

Expected: `No syntax errors detected in src/Factory/FormResponseFactory.php`

---

### Task 5: Write unit tests for FormResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/FormResponseFactoryTest.php`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\FormResponse;
use App\Factory\FormResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FormResponseFactory::class)]
final class FormResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'alolan',
            'name' => 'Alolan',
            'french_name' => "d'Alola",
        ];

        $response = FormResponseFactory::fromSqlRow($row);

        self::assertSame('alolan', $response->slug);
        self::assertSame('Alolan', $response->name);
        self::assertSame("d'Alola", $response->frenchName);
    }

    #[Test]
    public function fromSqlRowCastsValuesToStrings(): void
    {
        $row = [
            'slug' => 123,
            'name' => 456,
            'french_name' => 789,
        ];

        $response = FormResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->frenchName);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'alolan',
                'name' => 'Alolan',
                'french_name' => "d'Alola",
            ],
            [
                'slug' => 'galarian',
                'name' => 'Galarian',
                'french_name' => 'de Galar',
            ],
        ];

        $responses = FormResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(FormResponse::class, $responses);
        self::assertSame('alolan', $responses[0]->slug);
        self::assertSame('galarian', $responses[1]->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = FormResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
```

Save as `tests/src/Unit/Factory/FormResponseFactoryTest.php`.

- [ ] **Step 2: Run unit tests to confirm they pass**

Run: `make tests-unit`

Expected: 4 new tests pass. 0 failures overall.

---

### Task 6: Update the four form controllers

**Files:**
- Modify: `src/Controller/CategoryFormsController.php`
- Modify: `src/Controller/RegionalFormsController.php`
- Modify: `src/Controller/SpecialFormsController.php`
- Modify: `src/Controller/VariantFormsController.php`

All four controllers follow the same change: inject `SerializerInterface`, apply `FormResponseFactory::fromSqlRows()`, return `JsonResponse::fromJsonString(...)`.

- [ ] **Step 1: Update CategoryFormsController**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\FormResponseFactory;
use App\Service\CategoryFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/forms/category')]
final class CategoryFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CategoryFormsService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $forms = $service->getAll();

        $responses = FormResponseFactory::fromSqlRows($forms);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
```

- [ ] **Step 2: Update RegionalFormsController**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\FormResponseFactory;
use App\Service\RegionalFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/forms/regional')]
final class RegionalFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        RegionalFormsService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $forms = $service->getAll();

        $responses = FormResponseFactory::fromSqlRows($forms);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
```

- [ ] **Step 3: Update SpecialFormsController**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\FormResponseFactory;
use App\Service\SpecialFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/forms/special')]
final class SpecialFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        SpecialFormsService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $forms = $service->getAll();

        $responses = FormResponseFactory::fromSqlRows($forms);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
```

- [ ] **Step 4: Update VariantFormsController**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\FormResponseFactory;
use App\Service\VariantFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/forms/variant')]
final class VariantFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        VariantFormsService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $forms = $service->getAll();

        $responses = FormResponseFactory::fromSqlRows($forms);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
```

- [ ] **Step 5: Verify syntax for all four controllers**

Run:
```
docker compose exec php php -l src/Controller/CategoryFormsController.php && \
docker compose exec php php -l src/Controller/RegionalFormsController.php && \
docker compose exec php php -l src/Controller/SpecialFormsController.php && \
docker compose exec php php -l src/Controller/VariantFormsController.php
```

Expected: `No syntax errors detected` for each file.

---

### Task 7: Create unit tests for the four form services

**Files:**
- Create: `tests/src/Unit/Service/CategoryFormsServiceTest.php`
- Create: `tests/src/Unit/Service/RegionalFormsServiceTest.php`
- Create: `tests/src/Unit/Service/SpecialFormsServiceTest.php`
- Create: `tests/src/Unit/Service/VariantFormsServiceTest.php`

These tests replace the service coverage provided by `#[CoversClass(...Service::class)]` annotations in the old integration tests, which are rewritten in Task 8 to only cover the controller. The pattern is identical to `tests/src/Unit/Service/CatchStatesServiceTest.php`.

- [ ] **Step 1: Create CategoryFormsServiceTest**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\CategoryFormsRepository;
use App\Service\CategoryFormsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CategoryFormsService::class)]
final class CategoryFormsServiceTest extends TestCase
{
    private MockObject&CategoryFormsRepository $repository;
    private CategoryFormsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CategoryFormsRepository::class);
        $this->service = new CategoryFormsService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            ['slug' => 'starter', 'name' => 'Starter', 'french_name' => 'de Départ'],
            ['slug' => 'legendary', 'name' => 'Legendary', 'french_name' => 'Légendaire'],
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

- [ ] **Step 2: Create RegionalFormsServiceTest**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\RegionalFormsRepository;
use App\Service\RegionalFormsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RegionalFormsService::class)]
final class RegionalFormsServiceTest extends TestCase
{
    private MockObject&RegionalFormsRepository $repository;
    private RegionalFormsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RegionalFormsRepository::class);
        $this->service = new RegionalFormsService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            ['slug' => 'alolan', 'name' => 'Alolan', 'french_name' => "d'Alola"],
            ['slug' => 'hisuian', 'name' => 'Hisuian', 'french_name' => 'de Hisui'],
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

- [ ] **Step 3: Create SpecialFormsServiceTest**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\SpecialFormsRepository;
use App\Service\SpecialFormsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SpecialFormsService::class)]
final class SpecialFormsServiceTest extends TestCase
{
    private MockObject&SpecialFormsRepository $repository;
    private SpecialFormsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SpecialFormsRepository::class);
        $this->service = new SpecialFormsService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            ['slug' => 'mega', 'name' => 'Mega', 'french_name' => 'Mega'],
            ['slug' => 'gigantamax', 'name' => 'Gigantamax', 'french_name' => 'Gigamax'],
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

- [ ] **Step 4: Create VariantFormsServiceTest**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\VariantFormsRepository;
use App\Service\VariantFormsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(VariantFormsService::class)]
final class VariantFormsServiceTest extends TestCase
{
    private MockObject&VariantFormsRepository $repository;
    private VariantFormsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VariantFormsRepository::class);
        $this->service = new VariantFormsService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            ['slug' => 'gender', 'name' => 'Gender', 'french_name' => 'Sexe'],
            ['slug' => 'unobtainable', 'name' => 'Unobtainable', 'french_name' => 'Non obtenable'],
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

- [ ] **Step 5: Run unit tests**

Run: `make tests-unit`

Expected: 8 new service tests pass. 0 failures overall.

---

### Task 8: Create JSON fixtures and rewrite the four integration controller tests

**Files:**
- Create: `tests/resources/fixtures/forms_category_response.json`
- Create: `tests/resources/fixtures/forms_regional_response.json`
- Create: `tests/resources/fixtures/forms_special_response.json`
- Create: `tests/resources/fixtures/forms_variant_response.json`
- Modify: `tests/src/Integration/Controller/CategoryFormsControllerTest.php`
- Modify: `tests/src/Integration/Controller/RegionalFormsControllerTest.php`
- Modify: `tests/src/Integration/Controller/SpecialFormsControllerTest.php`
- Modify: `tests/src/Integration/Controller/VariantFormsControllerTest.php`

The fixture JSON is derived from the Alice YAML fixtures, ordered by `orderNumber`. The old tests used `AbstractTestControllerApi`; the new tests use `WebTestCase` directly with credentials passed inline — matching `CatchStatesControllerTest` and `TypesControllerTest`. Service coverage is now handled by the unit tests created in Task 7.

- [ ] **Step 1: Create forms_category_response.json**

```json
[
  {
    "slug": "starter",
    "name": "Starter",
    "french_name": "de Départ"
  },
  {
    "slug": "mythical",
    "name": "Mythical",
    "french_name": "Fabuleux"
  },
  {
    "slug": "legendary",
    "name": "Legendary",
    "french_name": "Légendaire"
  }
]
```

Save as `tests/resources/fixtures/forms_category_response.json`.

- [ ] **Step 2: Create forms_regional_response.json**

```json
[
  {
    "slug": "alolan",
    "name": "Alolan",
    "french_name": "d'Alola"
  },
  {
    "slug": "galarian",
    "name": "Galarian",
    "french_name": "de Galar"
  },
  {
    "slug": "hisuian",
    "name": "Hisuian",
    "french_name": "de Hisui"
  }
]
```

Save as `tests/resources/fixtures/forms_regional_response.json`.

- [ ] **Step 3: Create forms_special_response.json**

```json
[
  {
    "slug": "mega",
    "name": "Mega",
    "french_name": "Mega"
  },
  {
    "slug": "gigantamax",
    "name": "Gigantamax",
    "french_name": "Gigamax"
  },
  {
    "slug": "alpha",
    "name": "Alpha",
    "french_name": "Baron"
  },
  {
    "slug": "totem",
    "name": "Totem",
    "french_name": "Dominant"
  }
]
```

Save as `tests/resources/fixtures/forms_special_response.json`.

- [ ] **Step 4: Create forms_variant_response.json**

```json
[
  {
    "slug": "gender",
    "name": "Gender",
    "french_name": "Sexe"
  },
  {
    "slug": "alternate",
    "name": "Alternate",
    "french_name": "Alternatif"
  },
  {
    "slug": "baby",
    "name": "Baby",
    "french_name": "Bébé"
  },
  {
    "slug": "battle",
    "name": "Battle",
    "french_name": "Combat"
  },
  {
    "slug": "item",
    "name": "Item",
    "french_name": "Objet"
  },
  {
    "slug": "fusion",
    "name": "Fusion",
    "french_name": "Fusion"
  },
  {
    "slug": "unobtainable",
    "name": "Unobtainable",
    "french_name": "Non obtenable"
  }
]
```

Save as `tests/resources/fixtures/forms_variant_response.json`.

- [ ] **Step 5: Replace CategoryFormsControllerTest**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\CategoryFormsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(CategoryFormsController::class)]
final class CategoryFormsControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/category', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsArrayOfForms(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/category', [], [], [
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
    public function getEachFormHasRequiredFields(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/category', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $form */
        foreach ($data as $form) {
            self::assertIsArray($form);
            self::assertArrayHasKey('slug', $form);
            self::assertArrayHasKey('name', $form);
            self::assertArrayHasKey('french_name', $form);
        }
    }

    #[Test]
    public function getFieldValuesAreStrings(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/category', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        /** @var mixed $firstForm */
        $firstForm = $data[0] ?? null;

        self::assertIsArray($firstForm);
        self::assertIsString($firstForm['slug']);
        self::assertIsString($firstForm['name']);
        self::assertIsString($firstForm['french_name']);
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/category', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/forms_category_response.json',
            $content,
        );
    }
}
```

- [ ] **Step 6: Replace RegionalFormsControllerTest**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\RegionalFormsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(RegionalFormsController::class)]
final class RegionalFormsControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/regional', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsArrayOfForms(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/regional', [], [], [
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
    public function getEachFormHasRequiredFields(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/regional', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $form */
        foreach ($data as $form) {
            self::assertIsArray($form);
            self::assertArrayHasKey('slug', $form);
            self::assertArrayHasKey('name', $form);
            self::assertArrayHasKey('french_name', $form);
        }
    }

    #[Test]
    public function getFieldValuesAreStrings(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/regional', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        /** @var mixed $firstForm */
        $firstForm = $data[0] ?? null;

        self::assertIsArray($firstForm);
        self::assertIsString($firstForm['slug']);
        self::assertIsString($firstForm['name']);
        self::assertIsString($firstForm['french_name']);
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/regional', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/forms_regional_response.json',
            $content,
        );
    }
}
```

- [ ] **Step 7: Replace SpecialFormsControllerTest**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\SpecialFormsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(SpecialFormsController::class)]
final class SpecialFormsControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/special', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsArrayOfForms(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/special', [], [], [
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
    public function getEachFormHasRequiredFields(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/special', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $form */
        foreach ($data as $form) {
            self::assertIsArray($form);
            self::assertArrayHasKey('slug', $form);
            self::assertArrayHasKey('name', $form);
            self::assertArrayHasKey('french_name', $form);
        }
    }

    #[Test]
    public function getFieldValuesAreStrings(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/special', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        /** @var mixed $firstForm */
        $firstForm = $data[0] ?? null;

        self::assertIsArray($firstForm);
        self::assertIsString($firstForm['slug']);
        self::assertIsString($firstForm['name']);
        self::assertIsString($firstForm['french_name']);
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/special', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/forms_special_response.json',
            $content,
        );
    }
}
```

- [ ] **Step 8: Replace VariantFormsControllerTest**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\VariantFormsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(VariantFormsController::class)]
final class VariantFormsControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/variant', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsArrayOfForms(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/variant', [], [], [
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
    public function getEachFormHasRequiredFields(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/variant', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $form */
        foreach ($data as $form) {
            self::assertIsArray($form);
            self::assertArrayHasKey('slug', $form);
            self::assertArrayHasKey('name', $form);
            self::assertArrayHasKey('french_name', $form);
        }
    }

    #[Test]
    public function getFieldValuesAreStrings(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/variant', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        /** @var mixed $firstForm */
        $firstForm = $data[0] ?? null;

        self::assertIsArray($firstForm);
        self::assertIsString($firstForm['slug']);
        self::assertIsString($firstForm['name']);
        self::assertIsString($firstForm['french_name']);
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/variant', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/forms_variant_response.json',
            $content,
        );
    }
}
```

- [ ] **Step 9: Run integration tests**

Run: `make tests-integration`

Expected: All integration tests pass, including 20 new form controller tests (5 per endpoint). 0 failures.

---

### Task 9: Run full quality checks

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
git add \
  src/DTO/Response/FormResponse.php \
  src/Factory/ElectionEloResponseFactory.php \
  src/Factory/FormResponseFactory.php \
  src/Controller/CategoryFormsController.php \
  src/Controller/RegionalFormsController.php \
  src/Controller/SpecialFormsController.php \
  src/Controller/VariantFormsController.php \
  tests/src/Unit/DTO/Response/FormResponseTest.php \
  tests/src/Unit/Factory/FormResponseFactoryTest.php \
  tests/src/Unit/Service/CategoryFormsServiceTest.php \
  tests/src/Unit/Service/RegionalFormsServiceTest.php \
  tests/src/Unit/Service/SpecialFormsServiceTest.php \
  tests/src/Unit/Service/VariantFormsServiceTest.php \
  tests/src/Integration/Controller/CategoryFormsControllerTest.php \
  tests/src/Integration/Controller/RegionalFormsControllerTest.php \
  tests/src/Integration/Controller/SpecialFormsControllerTest.php \
  tests/src/Integration/Controller/VariantFormsControllerTest.php \
  tests/resources/fixtures/forms_category_response.json \
  tests/resources/fixtures/forms_regional_response.json \
  tests/resources/fixtures/forms_special_response.json \
  tests/resources/fixtures/forms_variant_response.json
git commit -m "Refactoring GET /forms/* return format to use Serializer and Object"
```

---

## Next Steps (not in this plan)

The remaining `// Better with serializer ?` endpoints are:
- `GET /collections` — fields: slug, name, french_name (no color; same shape as forms)
- `GET /dex/{trainerExternalId}/list` — more complex nested shape, higher-effort migration
