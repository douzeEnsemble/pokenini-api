# API Response Restructuring (Dex Availabilities) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /debogage/dex/{slug}/availabilities` endpoint to use a proper Response DTO + Factory pattern instead of building a raw array and serializing it via `AbstractDebugController::serialize()`.

**Architecture:** Create an immutable `DexAvailabilitiesResponse` DTO wrapping the list of Pokemon slugs, a `DexAvailabilitiesResponseFactory` that builds it from `DexAvailability[]` entities, and update `DebugDexController::dexAvailabilities()` to inject `SerializerInterface` as a method parameter and use `JsonResponse::fromJsonString()`. The JSON structure changes from a flat array `["bulbasaur", ...]` to a wrapped object `{"pokemons": ["bulbasaur", ...]}`.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine ORM entities, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/DexAvailabilitiesResponse.php` — immutable DTO wrapping `string[] $pokemons`
- `src/Factory/DexAvailabilitiesResponseFactory.php` — transforms `DexAvailability[]` into `DexAvailabilitiesResponse`
- `tests/src/Unit/Factory/DexAvailabilitiesResponseFactoryTest.php` — unit tests covering factory and DTO

**Modify:**
- `src/Controller/Debug/DebugDexController.php` — update `dexAvailabilities()` to use factory + `JsonResponse::fromJsonString()`
- `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php` — update `testDexAvailabilities()` for new `{"pokemons": [...]}` response structure

---

## Tasks

### Task 1: Create DexAvailabilitiesResponse DTO

**Files:**
- Create: `src/DTO/Response/DexAvailabilitiesResponse.php`

- [ ] **Step 1: Create the DTO file with immutable properties**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class DexAvailabilitiesResponse
{
    /**
     * @param string[] $pokemons
     */
    public function __construct(
        public readonly array $pokemons,
    ) {}
}
```

Save as `src/DTO/Response/DexAvailabilitiesResponse.php`.

- [ ] **Step 2: Verify the file is in the correct location**

Run: `ls -la src/DTO/Response/DexAvailabilitiesResponse.php`

Expected: File exists with readable permissions.

---

### Task 2: Create DexAvailabilitiesResponseFactory

**Files:**
- Create: `src/Factory/DexAvailabilitiesResponseFactory.php`

- [ ] **Step 1: Create the Factory with a static method**

The factory receives `DexAvailability[]` entities, extracts `pokemon->slug` from each, and builds a `DexAvailabilitiesResponse`.

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexAvailabilitiesResponse;
use App\Entity\DexAvailability;

final class DexAvailabilitiesResponseFactory
{
    /**
     * @param DexAvailability[] $dexAvailabilities
     */
    public static function fromDexAvailabilities(array $dexAvailabilities): DexAvailabilitiesResponse
    {
        $pokemons = array_map(
            static fn(DexAvailability $dexAvailability): string => $dexAvailability->pokemon->slug,
            $dexAvailabilities
        );

        return new DexAvailabilitiesResponse(pokemons: $pokemons);
    }
}
```

Save as `src/Factory/DexAvailabilitiesResponseFactory.php`.

- [ ] **Step 2: Verify the file is in the correct location**

Run: `ls -la src/Factory/DexAvailabilitiesResponseFactory.php`

Expected: File exists with readable permissions.

---

### Task 3: Write unit tests for DexAvailabilitiesResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/DexAvailabilitiesResponseFactoryTest.php`
- Test: `DexAvailabilitiesResponseFactory` class

Tests use **distinct slug values per item** so that any mutation swapping or dropping items is caught immediately by the MSI check.

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Factory\DexAvailabilitiesResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailabilitiesResponseFactory::class)]
final class DexAvailabilitiesResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromDexAvailabilitiesExtractsPokemonSlugsInOrder(): void
    {
        $pokemon1 = new Pokemon();
        $pokemon1->slug = 'bulbasaur';

        $pokemon2 = new Pokemon();
        $pokemon2->slug = 'ivysaur';

        $result = DexAvailabilitiesResponseFactory::fromDexAvailabilities([
            DexAvailability::create($pokemon1, new Dex()),
            DexAvailability::create($pokemon2, new Dex()),
        ]);

        self::assertSame(['bulbasaur', 'ivysaur'], $result->pokemons);
    }

    #[Test]
    public function fromDexAvailabilitiesHandlesEmptyArray(): void
    {
        $result = DexAvailabilitiesResponseFactory::fromDexAvailabilities([]);

        self::assertSame([], $result->pokemons);
    }
}
```

Save as `tests/src/Unit/Factory/DexAvailabilitiesResponseFactoryTest.php`.

- [ ] **Step 2: Verify the file is in the correct location**

Run: `ls -la tests/src/Unit/Factory/DexAvailabilitiesResponseFactoryTest.php`

Expected: File exists with readable permissions.

---

### Task 4: Update DebugDexController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/Debug/DebugDexController.php`

The `dex()` method continues to use `$this->serialize($dex)` from `AbstractDebugController` — no change there. Only `dexAvailabilities()` is migrated: it receives `SerializerInterface` as a method parameter (Symfony autowires it), replaces the manual `foreach` loop and `$this->serialize()` call with the factory + `JsonResponse::fromJsonString()`.

The JSON response structure changes from `["bulbasaur", ...]` to `{"pokemons": ["bulbasaur", ...]}`.

- [ ] **Step 1: Read the current controller**

Current content of `src/Controller/Debug/DebugDexController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\Entity\Dex;
use App\Service\DexAvailabilitiesService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/debogage/dex')]
final class DebugDexController extends AbstractDebugController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    public function dex(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Dex $dex,
    ): Response {
        return new Response(
            $this->serialize($dex),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }

    #[Route(path: '/{slug}/availabilities', methods: ['GET'])]
    public function dexAvailabilities(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Dex $dex,
        DexAvailabilitiesService $dexAvailabilitiesService,
    ): Response {
        $dexAvailabilities = $dexAvailabilitiesService->getByDex($dex);

        $pokemons = [];

        foreach ($dexAvailabilities as $dexAvailability) {
            $pokemons[] = $dexAvailability->pokemon->slug;
        }

        return new Response(
            $this->serialize($pokemons),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }
}
```

- [ ] **Step 2: Replace the controller with the migrated version**

```php
<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\Entity\Dex;
use App\Factory\DexAvailabilitiesResponseFactory;
use App\Service\DexAvailabilitiesService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/debogage/dex')]
final class DebugDexController extends AbstractDebugController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    public function dex(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Dex $dex,
    ): Response {
        return new Response(
            $this->serialize($dex),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }

    #[Route(path: '/{slug}/availabilities', methods: ['GET'])]
    public function dexAvailabilities(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Dex $dex,
        DexAvailabilitiesService $dexAvailabilitiesService,
        SerializerInterface $serializer,
    ): JsonResponse {
        $dexAvailabilities = $dexAvailabilitiesService->getByDex($dex);

        $response = DexAvailabilitiesResponseFactory::fromDexAvailabilities($dexAvailabilities);

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }
}
```

Save as `src/Controller/Debug/DebugDexController.php`.

- [ ] **Step 3: Verify the controller file is syntactically correct**

Run: `docker compose exec php php -l src/Controller/Debug/DebugDexController.php`

Expected: "No syntax errors detected".

---

### Task 5: Update integration test for new response structure

**Files:**
- Modify: `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php`

The JSON response for `testDexAvailabilities()` changes from a flat string array `["bulbasaur", ...]` to a wrapped object `{"pokemons": ["bulbasaur", ...]}`. The `testDex()`, `testDexNotFound()`, and `testDexAvailabilitiesNotFound()` tests are unchanged.

- [ ] **Step 1: Read the current test file**

Current content of `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Debug;

use App\Controller\Debug\DebugDexController;
use App\Service\DexAvailabilitiesService;
use App\Tests\Integration\Controller\AbstractTestControllerApi;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DebugDexController::class)]
#[CoversClass(DexAvailabilitiesService::class)]
final class DebugDexControllerTest extends AbstractTestControllerApi
{
    public function testDex(): void
    {
        $this->apiRequest('GET', '/debogage/dex/redgreenblueyellow');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var null|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('identifier', $data);
        $this->assertEquals('redgreenblueyellow', $data['slug']);

        $this->assertArrayHasKey('region', $data);
        $this->assertArrayHasKey('identifier', $data['region']);
        $this->assertEquals('kanto', $data['region']['slug']);
    }

    public function testDexNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDexAvailabilities(): void
    {
        $this->apiRequest('GET', '/debogage/dex/redgreenblueyellow/availabilities');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var null|string[] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertContains('bulbasaur', $data);
        $this->assertContains('douze', $data);
    }

    public function testDexAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
```

- [ ] **Step 2: Replace `testDexAvailabilities()` with the updated version**

Replace only the `testDexAvailabilities()` method body — all other methods stay identical:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Debug;

use App\Controller\Debug\DebugDexController;
use App\Factory\DexAvailabilitiesResponseFactory;
use App\Service\DexAvailabilitiesService;
use App\Tests\Integration\Controller\AbstractTestControllerApi;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DebugDexController::class)]
#[CoversClass(DexAvailabilitiesResponseFactory::class)]
#[CoversClass(DexAvailabilitiesService::class)]
final class DebugDexControllerTest extends AbstractTestControllerApi
{
    public function testDex(): void
    {
        $this->apiRequest('GET', '/debogage/dex/redgreenblueyellow');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var null|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('identifier', $data);
        $this->assertEquals('redgreenblueyellow', $data['slug']);

        $this->assertArrayHasKey('region', $data);
        $this->assertArrayHasKey('identifier', $data['region']);
        $this->assertEquals('kanto', $data['region']['slug']);
    }

    public function testDexNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDexAvailabilities(): void
    {
        $this->apiRequest('GET', '/debogage/dex/redgreenblueyellow/availabilities');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var null|array{pokemons: string[]} $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('pokemons', $data);
        $this->assertContains('bulbasaur', $data['pokemons']);
        $this->assertContains('douze', $data['pokemons']);
    }

    public function testDexAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
```

Save as `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php`.

- [ ] **Step 3: Verify the test file is syntactically correct**

Run: `docker compose exec php php -l tests/src/Integration/Controller/Debug/DebugDexControllerTest.php`

Expected: "No syntax errors detected".

---

### Task 6: Run full quality checks

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

Expected: 100% code coverage for new classes, 100% MSI, all checks green.

---

## Self-Review

**Spec coverage:**
- ✅ `DexAvailabilitiesResponse` DTO created with `string[] $pokemons` property (Task 1)
- ✅ `DexAvailabilitiesResponseFactory::fromDexAvailabilities()` created transforming `DexAvailability[]` → DTO (Task 2)
- ✅ Unit tests with two cases: non-empty array (catches slug-extraction mutations) + empty array (Task 3)
- ✅ `DebugDexController::dexAvailabilities()` updated: `SerializerInterface` method injection, factory usage, `JsonResponse::fromJsonString()` (Task 4)
- ✅ Integration test updated: `$data['pokemons']` instead of `$data`, `#[CoversClass(DexAvailabilitiesResponseFactory::class)]` added (Task 5)
- ✅ Deptrac: Factory only depends on `App\DTO\Response` and `App\Entity`; Controller uses `App\Factory` — all within allowed dependency directions

**Placeholder scan:** No TBDs, no incomplete steps, no "similar to Task N" references.

**Type consistency:**
- `DexAvailabilitiesResponseFactory::fromDexAvailabilities()` returns `DexAvailabilitiesResponse` ✓
- `DebugDexController::dexAvailabilities()` returns `JsonResponse` ✓
- Factory test instantiates `DexAvailability::create(Pokemon, Dex)` matching entity's static constructor signature ✓
- Integration test `$data['pokemons']` matches DTO property name `DexAvailabilitiesResponse::$pokemons` ✓
