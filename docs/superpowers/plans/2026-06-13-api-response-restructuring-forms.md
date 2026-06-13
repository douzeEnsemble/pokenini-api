# API Response Restructuring (Forms) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /forms/category`, `GET /forms/regional`, `GET /forms/special`, and `GET /forms/variant` endpoints from flat JSON to nested object-oriented structure using DTOs + Factory + Serializer pattern.

**Architecture:** Four controllers share a single migration: create one immutable `FormResponse` DTO and one `FormResponseFactory` that transforms flat SQL rows into typed DTOs, then update each controller to apply the transformation before serialization. All four endpoints return identical shapes, so one DTO + one Factory covers all four.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/FormResponse.php` — immutable DTO representing a form object (`slug`, `name`, `french_name`)
- `src/Factory/FormResponseFactory.php` — transforms flat SQL rows → `FormResponse` DTOs
- `tests/src/Unit/DTO/Response/FormResponseTest.php` — unit tests for the DTO
- `tests/src/Unit/Factory/FormResponseFactoryTest.php` — unit tests for the Factory
- `tests/src/Integration/Controller/CategoryFormsControllerTest.php` — integration tests for category forms
- `tests/src/Integration/Controller/RegionalFormsControllerTest.php` — integration tests for regional forms
- `tests/src/Integration/Controller/SpecialFormsControllerTest.php` — integration tests for special forms
- `tests/src/Integration/Controller/VariantFormsControllerTest.php` — integration tests for variant forms
- `tests/resources/fixtures/forms_category_response.json` — fixture for category controller test
- `tests/resources/fixtures/forms_regional_response.json` — fixture for regional controller test
- `tests/resources/fixtures/forms_special_response.json` — fixture for special controller test
- `tests/resources/fixtures/forms_variant_response.json` — fixture for variant controller test
- `docs/api-migration/forms-restructuring.md` — client migration documentation

**Modify:**
- `src/Controller/CategoryFormsController.php` — apply Factory + Serializer
- `src/Controller/RegionalFormsController.php` — apply Factory + Serializer
- `src/Controller/SpecialFormsController.php` — apply Factory + Serializer
- `src/Controller/VariantFormsController.php` — apply Factory + Serializer

---

## Tasks

### Task 1: Create FormResponse DTO

**Files:**
- Create: `src/DTO/Response/FormResponse.php`

- [ ] **Step 1: Create the DTO file with immutable properties**

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

Save this as `src/DTO/Response/FormResponse.php`.

- [ ] **Step 2: Verify the file is in the correct location**

Run: `ls -la src/DTO/Response/FormResponse.php`

Expected: File exists with readable permissions.

---

### Task 2: Write unit tests for FormResponse DTO

**Files:**
- Create: `tests/src/Unit/DTO/Response/FormResponseTest.php`
- Test: `FormResponse` class

- [ ] **Step 1: Create unit test file**

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

Save this as `tests/src/Unit/DTO/Response/FormResponseTest.php`.

---

### Task 3: Create FormResponseFactory

**Files:**
- Create: `src/Factory/FormResponseFactory.php`

- [ ] **Step 1: Create the Factory with static methods**

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

Save this as `src/Factory/FormResponseFactory.php`.

- [ ] **Step 2: Verify the file is in the correct location**

Run: `ls -la src/Factory/FormResponseFactory.php`

Expected: File exists with readable permissions.

---

### Task 4: Write unit tests for FormResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/FormResponseFactoryTest.php`
- Test: `FormResponseFactory` class

- [ ] **Step 1: Create unit test file**

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

Save this as `tests/src/Unit/Factory/FormResponseFactoryTest.php`.

---

### Task 5: Update CategoryFormsController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/CategoryFormsController.php`

- [ ] **Step 1: Read the current controller**

Current content of `src/Controller/CategoryFormsController.php` (before migration):

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/forms/category')]
final class CategoryFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CategoryFormsService $service
    ): JsonResponse {
        $forms = $service->getAll();

        return new JsonResponse($forms);
    }
}
```

- [ ] **Step 2: Update controller to use Factory and Serializer**

Replace the controller with:

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

---

### Task 6: Create integration test and fixture for CategoryFormsController

**Files:**
- Create: `tests/src/Integration/Controller/CategoryFormsControllerTest.php`
- Create: `tests/resources/fixtures/forms_category_response.json`

- [ ] **Step 1: Create fixture file**

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

Save this as `tests/resources/fixtures/forms_category_response.json`.

Note: The exact content depends on the test database seed. Run the test first and adjust if the fixture doesn't match.

- [ ] **Step 2: Create integration test file**

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

Save this as `tests/src/Integration/Controller/CategoryFormsControllerTest.php`.

---

### Task 7: Update RegionalFormsController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/RegionalFormsController.php`

- [ ] **Step 1: Update controller to use Factory and Serializer**

Replace the controller with:

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

---

### Task 8: Create integration test and fixture for RegionalFormsController

**Files:**
- Create: `tests/src/Integration/Controller/RegionalFormsControllerTest.php`
- Create: `tests/resources/fixtures/forms_regional_response.json`

- [ ] **Step 1: Create fixture file**

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

Save this as `tests/resources/fixtures/forms_regional_response.json`.

- [ ] **Step 2: Create integration test file**

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

Save this as `tests/src/Integration/Controller/RegionalFormsControllerTest.php`.

---

### Task 9: Update SpecialFormsController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/SpecialFormsController.php`

- [ ] **Step 1: Update controller to use Factory and Serializer**

Replace the controller with:

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

---

### Task 10: Create integration test and fixture for SpecialFormsController

**Files:**
- Create: `tests/src/Integration/Controller/SpecialFormsControllerTest.php`
- Create: `tests/resources/fixtures/forms_special_response.json`

- [ ] **Step 1: Create fixture file**

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

Save this as `tests/resources/fixtures/forms_special_response.json`.

- [ ] **Step 2: Create integration test file**

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

Save this as `tests/src/Integration/Controller/SpecialFormsControllerTest.php`.

---

### Task 11: Update VariantFormsController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/VariantFormsController.php`

- [ ] **Step 1: Update controller to use Factory and Serializer**

Replace the controller with:

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

---

### Task 12: Create integration test and fixture for VariantFormsController

**Files:**
- Create: `tests/src/Integration/Controller/VariantFormsControllerTest.php`
- Create: `tests/resources/fixtures/forms_variant_response.json`

- [ ] **Step 1: Create fixture file**

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

Save this as `tests/resources/fixtures/forms_variant_response.json`.

- [ ] **Step 2: Create integration test file**

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

Save this as `tests/src/Integration/Controller/VariantFormsControllerTest.php`.

---

### Task 13: Run full quality checks

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

---

### Task 14: Create client migration documentation

**Files:**
- Create: `docs/api-migration/forms-restructuring.md`

- [ ] **Step 1: Create migration documentation**

```markdown
# Forms API — Response Structure Migration

**Endpoints:**
- `GET /forms/category`
- `GET /forms/regional`
- `GET /forms/special`
- `GET /forms/variant`

**Change type:** Non-breaking (same field names, same structure, now typed via DTO + Serializer)
**Status:** Live

## Summary

The four `GET /forms/*` endpoints have been refactored to use the DTO + Factory + Serializer
pattern, aligning them with the rest of the API. The response shape is identical to the
previous implementation — no field was added, removed, or renamed.

## Response Shape (unchanged)

```json
[
  { "slug": "alolan", "name": "Alolan", "french_name": "d'Alola" },
  { "slug": "galarian", "name": "Galarian", "french_name": "de Galar" }
]
```

## Impact Assessment

### pokenini-back

**Change required:** None. The response shape is identical.

**Testing:** Run existing integration tests to confirm no regression.

### pokenini-web

**Change required:** None. The response shape is identical.

**Testing:** Run existing tests to confirm no regression.
```

Save this as `docs/api-migration/forms-restructuring.md`.

---

### Task 15: Final validation

- [ ] **Step 1: Verify all new files exist**

Run:
```bash
ls -la \
  src/DTO/Response/FormResponse.php \
  src/Factory/FormResponseFactory.php \
  tests/src/Unit/DTO/Response/FormResponseTest.php \
  tests/src/Unit/Factory/FormResponseFactoryTest.php \
  tests/src/Integration/Controller/CategoryFormsControllerTest.php \
  tests/src/Integration/Controller/RegionalFormsControllerTest.php \
  tests/src/Integration/Controller/SpecialFormsControllerTest.php \
  tests/src/Integration/Controller/VariantFormsControllerTest.php \
  tests/resources/fixtures/forms_category_response.json \
  tests/resources/fixtures/forms_regional_response.json \
  tests/resources/fixtures/forms_special_response.json \
  tests/resources/fixtures/forms_variant_response.json \
  docs/api-migration/forms-restructuring.md
```

Expected: All 13 files exist.

- [ ] **Step 2: Run complete test suite one final time**

Run: `make tests`

Expected: All tests pass, 0 failures.

- [ ] **Step 3: Run complete quality checks**

Run: `make quality && make measures`

Expected: All quality checks green, 100% coverage, 100% MSI.

- [ ] **Step 4: Verify the endpoints work end-to-end**

Run (if the stack is running):

```bash
curl -u web:douze http://localhost:8080/forms/category | jq .
curl -u web:douze http://localhost:8080/forms/regional | jq .
curl -u web:douze http://localhost:8080/forms/special | jq .
curl -u web:douze http://localhost:8080/forms/variant | jq .
```

Expected: JSON arrays of form objects with `slug`, `name`, `french_name` string fields.

- [ ] **Step 5: Document completion**

Summary of changes:
- ✅ Created `FormResponse` DTO (immutable, `slug`, `name`, `french_name`)
- ✅ Created `FormResponseFactory` (transforms flat SQL rows → DTOs)
- ✅ Updated 4 form controllers (apply Factory + Serializer)
- ✅ Created unit test for DTO (1 test case)
- ✅ Created unit tests for Factory (4 test cases)
- ✅ Created integration tests for 4 controllers (5 tests each = 20 total)
- ✅ Created fixture files for each controller
- ✅ Created migration documentation
- ✅ All quality gates passing

**Status:** Forms endpoints refactoring complete. Same pattern as Types, CatchStates, Collections, GameBundles endpoints.
