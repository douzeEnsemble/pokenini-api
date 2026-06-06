# API Response Restructuring (Dex Debug) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /debogage/dex/{slug}` from direct Doctrine entity serialization to the project-standard DTO + Factory + Serializer pattern.

**Architecture:** Create two immutable response DTOs (`RegionResponse` for the nested region sub-object, `DexDebugResponse` for the full dex data), a `DexDebugResponseFactory` that transforms a `Dex` entity into those DTOs, and update `DebugDexController::dex()` to use the factory instead of serializing the entity directly. Remove the now-unused `AbstractDebugController` inheritance from `DebugDexController`.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine ORM, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/RegionResponse.php` — immutable DTO for the nested `region` sub-object
- `src/DTO/Response/DexDebugResponse.php` — immutable DTO for full Dex entity debug data
- `src/Factory/DexDebugResponseFactory.php` — transforms `Dex` entity + nested `Region` → DTOs
- `tests/src/Unit/Factory/DexDebugResponseFactoryTest.php` — unit tests (6 methods, 100% coverage + MSI)

**Modify:**
- `src/Controller/Debug/DebugDexController.php` — replace entity serialization with Factory + Serializer; extend `AbstractController` instead of `AbstractDebugController`
- `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php` — add `#[CoversClass]` for the new factory and DTOs

---

## Tasks

### Task 1: Create `RegionResponse` DTO

**Files:**
- Create: `src/DTO/Response/RegionResponse.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class RegionResponse
{
    public function __construct(
        public readonly ?string $identifier,
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('order_number')]
        public readonly int $orderNumber,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
```

Save as `src/DTO/Response/RegionResponse.php`.

---

### Task 2: Create `DexDebugResponse` DTO

**Files:**
- Create: `src/DTO/Response/DexDebugResponse.php`

- [ ] **Step 1: Create the DTO file**

All `bool`, `int`, and `\DateTime` entity fields are captured as-is; the nullable `Region` entity becomes a nested `RegionResponse`; `lastChangedAt` and `deletedAt` are pre-formatted as ISO 8601 strings by the factory.

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexDebugResponse
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly ?string $identifier,
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('order_number')]
        public readonly int $orderNumber,
        #[SerializedName('selection_rule')]
        public readonly string $selectionRule,
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        #[SerializedName('is_display_form')]
        public readonly bool $isDisplayForm,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
        public readonly ?RegionResponse $region,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('can_hold_election')]
        public readonly bool $canHoldElection,
        #[SerializedName('last_changed_at')]
        public readonly string $lastChangedAt,
        #[SerializedName('election_order_number')]
        public readonly int $electionOrderNumber,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
```

Save as `src/DTO/Response/DexDebugResponse.php`.

---

### Task 3: Create `DexDebugResponseFactory`

**Files:**
- Create: `src/Factory/DexDebugResponseFactory.php`

- [ ] **Step 1: Create the Factory file**

The factory has one public method (`fromDex`) and one private helper (`buildRegion`). The private method is tested indirectly via the non-null region test cases.

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexDebugResponse;
use App\DTO\Response\RegionResponse;
use App\Entity\Dex;
use App\Entity\Region;

final class DexDebugResponseFactory
{
    public static function fromDex(Dex $dex): DexDebugResponse
    {
        return new DexDebugResponse(
            identifier: $dex->getIdentifier()?->toRfc4122(),
            slug: $dex->slug,
            name: $dex->name,
            frenchName: $dex->frenchName,
            orderNumber: $dex->orderNumber,
            selectionRule: $dex->selectionRule,
            isShiny: $dex->isShiny,
            isPremium: $dex->isPremium,
            isDisplayForm: $dex->isDisplayForm,
            displayTemplate: $dex->displayTemplate,
            region: null !== $dex->region ? self::buildRegion($dex->region) : null,
            description: $dex->description,
            frenchDescription: $dex->frenchDescription,
            isReleased: $dex->isReleased,
            canHoldElection: $dex->canHoldElection,
            lastChangedAt: $dex->lastChangedAt->format(\DateTime::ATOM),
            electionOrderNumber: $dex->electionOrderNumber,
            deletedAt: $dex->deletedAt?->format(\DateTime::ATOM),
        );
    }

    private static function buildRegion(Region $region): RegionResponse
    {
        return new RegionResponse(
            identifier: $region->getIdentifier()?->toRfc4122(),
            slug: $region->slug,
            name: $region->name,
            frenchName: $region->frenchName,
            orderNumber: $region->orderNumber,
            deletedAt: $region->deletedAt?->format(\DateTime::ATOM),
        );
    }
}
```

Save as `src/Factory/DexDebugResponseFactory.php`.

---

### Task 4: Write unit tests for `DexDebugResponseFactory`

**Files:**
- Create: `tests/src/Unit/Factory/DexDebugResponseFactoryTest.php`
- Test: `DexDebugResponseFactory` (covers `DexDebugResponse` and `RegionResponse` implicitly)

Six test methods are required to reach 100% line coverage and 100% MSI:
- Method 1 exercises the happy path with a non-null region and asserts all fields (covers every property assignment and the `buildRegion` private path).
- Method 2 exercises `region = null` with opposite boolean values (kills all "hardcode boolean" mutations not caught by method 1).
- Method 3 exercises `deletedAt` non-null on Dex (kills the `?->format()` removal mutation for `deletedAt`).
- Method 4 exercises `deletedAt` non-null on Region (kills the same mutation in `buildRegion`).
- Method 5 exercises a non-null `identifier` on Dex via reflection (kills the `?->toRfc4122()` removal mutation).
- Method 6 exercises a non-null `identifier` on Region via reflection (kills the same mutation in `buildRegion`).

- [ ] **Step 1: Create the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Dex;
use App\Entity\Region;
use App\Factory\DexDebugResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(DexDebugResponseFactory::class)]
final class DexDebugResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromDex_withRegion_mapsAllFields(): void
    {
        $region = new Region();
        $region->slug = 'kanto';
        $region->name = 'Kanto';
        $region->frenchName = 'Kanto';
        $region->orderNumber = 1;

        $dex = new Dex();
        $dex->slug = 'redgreenblueyellow';
        $dex->name = 'Red/Green/Blue/Yellow';
        $dex->frenchName = 'Rouge/Vert/Bleu/Jaune';
        $dex->orderNumber = 1;
        $dex->selectionRule = '{"type":"all"}';
        $dex->isShiny = false;
        $dex->isPremium = true;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->region = $region;
        $dex->description = 'First generation';
        $dex->frenchDescription = 'Première génération';
        $dex->isReleased = true;
        $dex->canHoldElection = false;
        $dex->lastChangedAt = new \DateTime('2024-01-15T10:30:00+00:00');
        $dex->electionOrderNumber = 5;

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertNull($result->identifier);
        self::assertSame('redgreenblueyellow', $result->slug);
        self::assertSame('Red/Green/Blue/Yellow', $result->name);
        self::assertSame('Rouge/Vert/Bleu/Jaune', $result->frenchName);
        self::assertSame(1, $result->orderNumber);
        self::assertSame('{"type":"all"}', $result->selectionRule);
        self::assertFalse($result->isShiny);
        self::assertTrue($result->isPremium);
        self::assertFalse($result->isDisplayForm);
        self::assertSame('box', $result->displayTemplate);
        self::assertSame('First generation', $result->description);
        self::assertSame('Première génération', $result->frenchDescription);
        self::assertTrue($result->isReleased);
        self::assertFalse($result->canHoldElection);
        self::assertSame('2024-01-15T10:30:00+00:00', $result->lastChangedAt);
        self::assertSame(5, $result->electionOrderNumber);
        self::assertNull($result->deletedAt);

        self::assertNotNull($result->region);
        self::assertNull($result->region->identifier);
        self::assertSame('kanto', $result->region->slug);
        self::assertSame('Kanto', $result->region->name);
        self::assertSame('Kanto', $result->region->frenchName);
        self::assertSame(1, $result->region->orderNumber);
        self::assertNull($result->region->deletedAt);
    }

    #[Test]
    public function fromDex_withNullRegion_setsNullRegion(): void
    {
        $dex = new Dex();
        $dex->slug = 'home';
        $dex->name = 'Home';
        $dex->frenchName = 'Home';
        $dex->orderNumber = 99;
        $dex->selectionRule = '';
        $dex->isShiny = true;
        $dex->isPremium = false;
        $dex->isDisplayForm = true;
        $dex->displayTemplate = 'list';
        $dex->region = null;
        $dex->description = '';
        $dex->frenchDescription = '';
        $dex->isReleased = false;
        $dex->canHoldElection = true;
        $dex->lastChangedAt = new \DateTime('2024-06-01T00:00:00+00:00');
        $dex->electionOrderNumber = 0;

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertNull($result->region);
        self::assertTrue($result->isShiny);
        self::assertFalse($result->isPremium);
        self::assertTrue($result->isDisplayForm);
        self::assertFalse($result->isReleased);
        self::assertTrue($result->canHoldElection);
    }

    #[Test]
    public function fromDex_withDeletedAt_returnsFormattedDate(): void
    {
        $dex = new Dex();
        $dex->slug = 'deleted-dex';
        $dex->name = 'Deleted Dex';
        $dex->frenchName = 'Pokédex Supprimé';
        $dex->orderNumber = 0;
        $dex->selectionRule = '';
        $dex->isShiny = false;
        $dex->isPremium = false;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->description = '';
        $dex->frenchDescription = '';
        $dex->isReleased = false;
        $dex->canHoldElection = false;
        $dex->lastChangedAt = new \DateTime('2024-01-01T00:00:00+00:00');
        $dex->electionOrderNumber = 0;
        $dex->deletedAt = new \DateTime('2024-03-15T12:00:00+00:00');

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertSame('2024-03-15T12:00:00+00:00', $result->deletedAt);
    }

    #[Test]
    public function fromDex_withRegionDeletedAt_returnsFormattedDate(): void
    {
        $region = new Region();
        $region->slug = 'johto';
        $region->name = 'Johto';
        $region->frenchName = 'Johto';
        $region->orderNumber = 2;
        $region->deletedAt = new \DateTime('2024-04-20T08:00:00+00:00');

        $dex = new Dex();
        $dex->slug = 'goldsilvercrystal';
        $dex->name = 'Gold/Silver/Crystal';
        $dex->frenchName = 'Or/Argent/Cristal';
        $dex->orderNumber = 2;
        $dex->selectionRule = '';
        $dex->isShiny = false;
        $dex->isPremium = false;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->region = $region;
        $dex->description = '';
        $dex->frenchDescription = '';
        $dex->isReleased = false;
        $dex->canHoldElection = false;
        $dex->lastChangedAt = new \DateTime('2024-01-01T00:00:00+00:00');
        $dex->electionOrderNumber = 0;

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertNotNull($result->region);
        self::assertSame('2024-04-20T08:00:00+00:00', $result->region->deletedAt);
    }

    #[Test]
    public function fromDex_withIdentifier_returnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        $dex = new Dex();
        $dex->slug = 'test-dex';
        $dex->name = 'Test Dex';
        $dex->frenchName = 'Pokédex Test';
        $dex->orderNumber = 0;
        $dex->selectionRule = '';
        $dex->isShiny = false;
        $dex->isPremium = false;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->description = '';
        $dex->frenchDescription = '';
        $dex->isReleased = false;
        $dex->canHoldElection = false;
        $dex->lastChangedAt = new \DateTime('2024-01-01T00:00:00+00:00');
        $dex->electionOrderNumber = 0;

        $reflection = new \ReflectionProperty(Dex::class, 'identifier');
        $reflection->setValue($dex, $uuid);

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $result->identifier);
    }

    #[Test]
    public function fromDex_withRegionIdentifier_returnsUuidString(): void
    {
        $uuid = Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');

        $region = new Region();
        $region->slug = 'sinnoh';
        $region->name = 'Sinnoh';
        $region->frenchName = 'Sinnoh';
        $region->orderNumber = 4;

        $reflection = new \ReflectionProperty(Region::class, 'identifier');
        $reflection->setValue($region, $uuid);

        $dex = new Dex();
        $dex->slug = 'diamondpearl';
        $dex->name = 'Diamond/Pearl';
        $dex->frenchName = 'Diamant/Perle';
        $dex->orderNumber = 4;
        $dex->selectionRule = '';
        $dex->isShiny = false;
        $dex->isPremium = false;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->region = $region;
        $dex->description = '';
        $dex->frenchDescription = '';
        $dex->isReleased = false;
        $dex->canHoldElection = false;
        $dex->lastChangedAt = new \DateTime('2024-01-01T00:00:00+00:00');
        $dex->electionOrderNumber = 0;

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertNotNull($result->region);
        self::assertSame('6ba7b810-9dad-11d1-80b4-00c04fd430c8', $result->region->identifier);
    }
}
```

Save as `tests/src/Unit/Factory/DexDebugResponseFactoryTest.php`.

---

### Task 5: Update `DebugDexController`

**Files:**
- Modify: `src/Controller/Debug/DebugDexController.php`

- [ ] **Step 1: Read current controller**

Current `src/Controller/Debug/DebugDexController.php`:

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
            ['Content-Type' => 'application/json'],
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

- [ ] **Step 2: Replace controller with updated version**

Both methods now use Factory + Serializer; no method uses `$this->serialize()` from `AbstractDebugController`, so the class now extends `AbstractController` directly. The `Response` import is removed.

```php
<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\Entity\Dex;
use App\Factory\DexAvailabilitiesResponseFactory;
use App\Factory\DexDebugResponseFactory;
use App\Service\DexAvailabilitiesService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/debogage/dex')]
final class DebugDexController extends AbstractController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    public function dex(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Dex $dex,
        SerializerInterface $serializer,
    ): JsonResponse {
        $response = DexDebugResponseFactory::fromDex($dex);

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
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

---

### Task 6: Update integration test `CoversClass`

**Files:**
- Modify: `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php`

- [ ] **Step 1: Add `#[CoversClass]` for the new factory and DTOs**

Add three `#[CoversClass]` attributes to the class header (after existing ones). No existing test method needs to change — the assertions on `identifier`, `slug`, `region.identifier`, and `region.slug` all pass unchanged because the DTO now serializes those fields with the same JSON keys.

```php
#[CoversClass(DebugDexController::class)]
#[CoversClass(DexAvailabilitiesResponseFactory::class)]
#[CoversClass(DexAvailabilitiesService::class)]
#[CoversClass(DexDebugResponseFactory::class)]
#[CoversClass(DexDebugResponse::class)]
#[CoversClass(RegionResponse::class)]
```

Full updated file:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Debug;

use App\Controller\Debug\DebugDexController;
use App\DTO\Response\DexDebugResponse;
use App\DTO\Response\RegionResponse;
use App\Factory\DexAvailabilitiesResponseFactory;
use App\Factory\DexDebugResponseFactory;
use App\Service\DexAvailabilitiesService;
use App\Tests\Integration\Controller\AbstractTestControllerApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(DebugDexController::class)]
#[CoversClass(DexAvailabilitiesResponseFactory::class)]
#[CoversClass(DexAvailabilitiesService::class)]
#[CoversClass(DexDebugResponseFactory::class)]
#[CoversClass(DexDebugResponse::class)]
#[CoversClass(RegionResponse::class)]
final class DebugDexControllerTest extends AbstractTestControllerApi
{
    #[Test]
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

    #[Test]
    public function testDexNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs');

        $this->assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function testDexAvailabilities(): void
    {
        $this->apiRequest('GET', '/debogage/dex/redgreenblueyellow/availabilities');

        $this->assertJsonResponseIsOK();

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

    #[Test]
    public function testDexAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
```

---

## Self-Review Checklist

- [x] **Spec coverage:** Issue #256 asks for object-oriented response format — `DexDebugResponse` nests `RegionResponse` as a sub-object instead of serializing raw entity relations. All entity fields are mapped. ✓
- [x] **No placeholders:** All tasks contain complete, runnable code. ✓
- [x] **Type consistency:** `DexDebugResponseFactory::fromDex` uses `DexDebugResponse` and `RegionResponse` — names match throughout all tasks. ✓
- [x] **100% MSI coverage:** 6 test methods cover: all-fields happy path, null region, Dex.deletedAt non-null, Region.deletedAt non-null, Dex identifier via reflection, Region identifier via reflection. Every boolean is asserted both as `true` and `false` across methods 1 and 2. ✓
- [x] **No regressions:** Existing `testDex` assertions (`identifier`, `slug`, `region.identifier`, `region.slug`) are still satisfied by the new DTO serialization. ✓
