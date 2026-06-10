# API Response Restructuring (Dex Debug — Nested Flags) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Group the 5 flat `is_*` boolean flags of `DexDebugResponse` into a nested `flags` object (`DexDebugFlagsResponse`), aligning with the pattern already applied to `DexResponse`, `TrainerDexResponse`, and `AlbumDexResponse` (issue #256).

**Architecture:** Create a new immutable `DexDebugFlagsResponse` DTO holding the 5 flags specific to the debug endpoint (`is_shiny`, `is_premium`, `is_display_form`, `is_released`, `can_hold_election`). Update `DexDebugResponse` to embed it instead of the 5 flat booleans. Update `DexDebugResponseFactory` to build the nested DTO via a private `buildFlags()` helper. Update all unit tests and integration test assertions. No changes to the controller, service, repository, or entity.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Response shape change

**Before:**
```json
{
    "identifier": "550e8400-e29b-41d4-a716-446655440000",
    "slug": "redgreenblueyellow",
    "name": "Red/Green/Blue/Yellow",
    "french_name": "Rouge/Vert/Bleu/Jaune",
    "order_number": 1,
    "selection_rule": "{\"type\":\"all\"}",
    "is_shiny": false,
    "is_premium": false,
    "is_display_form": true,
    "display_template": "box",
    "region": {"identifier": "6ba7b810-9dad-11d1-80b4-00c04fd430c8", "slug": "kanto", "name": "Kanto", "french_name": "Kanto", "order_number": 1, "deleted_at": null},
    "description": "First generation",
    "french_description": "Première génération",
    "is_released": true,
    "can_hold_election": false,
    "last_changed_at": "2024-01-15T10:30:00+00:00",
    "election_order_number": 5,
    "deleted_at": null
}
```

**After:**
```json
{
    "identifier": "550e8400-e29b-41d4-a716-446655440000",
    "slug": "redgreenblueyellow",
    "name": "Red/Green/Blue/Yellow",
    "french_name": "Rouge/Vert/Bleu/Jaune",
    "order_number": 1,
    "selection_rule": "{\"type\":\"all\"}",
    "flags": {
        "is_shiny": false,
        "is_premium": false,
        "is_display_form": true,
        "is_released": true,
        "can_hold_election": false
    },
    "display_template": "box",
    "region": {"identifier": "6ba7b810-9dad-11d1-80b4-00c04fd430c8", "slug": "kanto", "name": "Kanto", "french_name": "Kanto", "order_number": 1, "deleted_at": null},
    "description": "First generation",
    "french_description": "Première génération",
    "last_changed_at": "2024-01-15T10:30:00+00:00",
    "election_order_number": 5,
    "deleted_at": null
}
```

---

## File Structure

**Create:**
- `src/DTO/Response/DexDebugFlagsResponse.php` — new immutable DTO for the 5 debug-specific flags
- `tests/src/Unit/DTO/Response/DexDebugFlagsResponseTest.php` — unit tests for `DexDebugFlagsResponse`

**Modify:**
- `src/DTO/Response/DexDebugResponse.php` — replace 5 flat `is_*` booleans with `DexDebugFlagsResponse $flags`
- `src/Factory/DexDebugResponseFactory.php` — add private `buildFlags()` helper; update constructor call
- `tests/src/Unit/DTO/Response/DexDebugResponseTest.php` — pass `DexDebugFlagsResponse` instead of flat booleans; assert on `$response->flags`
- `tests/src/Unit/Factory/DexDebugResponseFactoryTest.php` — assert `DexDebugFlagsResponse` instance and each flag value via `$result->flags`
- `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php` — add `flags` key assertions to `testDex()`

---

## Tasks

### Task 1: Create `DexDebugFlagsResponse` DTO and its unit test

**Files:**
- Create: `src/DTO/Response/DexDebugFlagsResponse.php`
- Create: `tests/src/Unit/DTO/Response/DexDebugFlagsResponseTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/src/Unit/DTO/Response/DexDebugFlagsResponseTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexDebugFlagsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexDebugFlagsResponse::class)]
final class DexDebugFlagsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $flags = new DexDebugFlagsResponse(
            isShiny: true,
            isPremium: false,
            isDisplayForm: true,
            isReleased: false,
            canHoldElection: true,
        );

        self::assertTrue($flags->isShiny);
        self::assertFalse($flags->isPremium);
        self::assertTrue($flags->isDisplayForm);
        self::assertFalse($flags->isReleased);
        self::assertTrue($flags->canHoldElection);
    }

    #[Test]
    public function constructorAcceptsAllFalse(): void
    {
        $flags = new DexDebugFlagsResponse(
            isShiny: false,
            isPremium: false,
            isDisplayForm: false,
            isReleased: false,
            canHoldElection: false,
        );

        self::assertFalse($flags->isShiny);
        self::assertFalse($flags->isPremium);
        self::assertFalse($flags->isDisplayForm);
        self::assertFalse($flags->isReleased);
        self::assertFalse($flags->canHoldElection);
    }

    #[Test]
    public function constructorAcceptsAllTrue(): void
    {
        $flags = new DexDebugFlagsResponse(
            isShiny: true,
            isPremium: true,
            isDisplayForm: true,
            isReleased: true,
            canHoldElection: true,
        );

        self::assertTrue($flags->isShiny);
        self::assertTrue($flags->isPremium);
        self::assertTrue($flags->isDisplayForm);
        self::assertTrue($flags->isReleased);
        self::assertTrue($flags->canHoldElection);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/DexDebugFlagsResponseTest.php`

Expected: FAIL — `App\DTO\Response\DexDebugFlagsResponse not found`

- [ ] **Step 3: Create the DTO**

Create `src/DTO/Response/DexDebugFlagsResponse.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexDebugFlagsResponse
{
    public function __construct(
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        #[SerializedName('is_display_form')]
        public readonly bool $isDisplayForm,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('can_hold_election')]
        public readonly bool $canHoldElection,
    ) {}
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/DexDebugFlagsResponseTest.php`

Expected: 3 tests, 0 failures.

---

### Task 2: Update `DexDebugResponseTest` and `DexDebugResponse` DTO

**Files:**
- Modify: `tests/src/Unit/DTO/Response/DexDebugResponseTest.php`
- Modify: `src/DTO/Response/DexDebugResponse.php`

- [ ] **Step 1: Update the unit test to use `DexDebugFlagsResponse`**

Replace the full content of `tests/src/Unit/DTO/Response/DexDebugResponseTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexDebugFlagsResponse;
use App\DTO\Response\DexDebugResponse;
use App\DTO\Response\RegionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexDebugResponse::class)]
final class DexDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $region = new RegionResponse(
            identifier: '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            slug: 'kanto',
            name: 'Kanto',
            frenchName: 'Kanto',
            orderNumber: 1,
            deletedAt: null,
        );

        $flags = new DexDebugFlagsResponse(
            isShiny: false,
            isPremium: true,
            isDisplayForm: false,
            isReleased: true,
            canHoldElection: false,
        );

        $response = new DexDebugResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440000',
            slug: 'redgreenblueyellow',
            name: 'Red/Green/Blue/Yellow',
            frenchName: 'Rouge/Vert/Bleu/Jaune',
            orderNumber: 1,
            selectionRule: '{"type":"all"}',
            flags: $flags,
            displayTemplate: 'box',
            region: $region,
            description: 'First generation',
            frenchDescription: 'Première génération',
            lastChangedAt: '2024-01-15T10:30:00+00:00',
            electionOrderNumber: 5,
            deletedAt: '2024-03-01T00:00:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $response->identifier);
        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('Red/Green/Blue/Yellow', $response->name);
        self::assertSame('Rouge/Vert/Bleu/Jaune', $response->frenchName);
        self::assertSame(1, $response->orderNumber);
        self::assertSame('{"type":"all"}', $response->selectionRule);
        self::assertSame($flags, $response->flags);
        self::assertFalse($response->flags->isShiny);
        self::assertTrue($response->flags->isPremium);
        self::assertFalse($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->canHoldElection);
        self::assertSame('box', $response->displayTemplate);
        self::assertSame($region, $response->region);
        self::assertSame('First generation', $response->description);
        self::assertSame('Première génération', $response->frenchDescription);
        self::assertSame('2024-01-15T10:30:00+00:00', $response->lastChangedAt);
        self::assertSame(5, $response->electionOrderNumber);
        self::assertSame('2024-03-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $flags = new DexDebugFlagsResponse(
            isShiny: true,
            isPremium: false,
            isDisplayForm: true,
            isReleased: false,
            canHoldElection: true,
        );

        $response = new DexDebugResponse(
            identifier: null,
            slug: 'home',
            name: 'Home',
            frenchName: 'Home',
            orderNumber: 99,
            selectionRule: '',
            flags: $flags,
            displayTemplate: 'list',
            region: null,
            description: '',
            frenchDescription: '',
            lastChangedAt: '2024-06-01T00:00:00+00:00',
            electionOrderNumber: 0,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->region);
        self::assertNull($response->deletedAt);
        self::assertTrue($response->flags->isShiny);
        self::assertFalse($response->flags->isPremium);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertFalse($response->flags->isReleased);
        self::assertTrue($response->flags->canHoldElection);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/DexDebugResponseTest.php`

Expected: FAIL — constructor does not accept `flags:` parameter yet.

- [ ] **Step 3: Update `DexDebugResponse` DTO**

Replace the full content of `src/DTO/Response/DexDebugResponse.php` with:

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
        public readonly DexDebugFlagsResponse $flags,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
        public readonly ?RegionResponse $region,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        #[SerializedName('last_changed_at')]
        public readonly string $lastChangedAt,
        #[SerializedName('election_order_number')]
        public readonly int $electionOrderNumber,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/DexDebugResponseTest.php`

Expected: 2 tests, 0 failures.

---

### Task 3: Update `DexDebugResponseFactoryTest` and `DexDebugResponseFactory`

**Files:**
- Modify: `tests/src/Unit/Factory/DexDebugResponseFactoryTest.php`
- Modify: `src/Factory/DexDebugResponseFactory.php`

- [ ] **Step 1: Update the factory test**

Replace the full content of `tests/src/Unit/Factory/DexDebugResponseFactoryTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\DexDebugFlagsResponse;
use App\DTO\Response\DexDebugResponse;
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
    public function fromDexWithRegionMapsAllFields(): void
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
        self::assertInstanceOf(DexDebugFlagsResponse::class, $result->flags);
        self::assertFalse($result->flags->isShiny);
        self::assertTrue($result->flags->isPremium);
        self::assertFalse($result->flags->isDisplayForm);
        self::assertTrue($result->flags->isReleased);
        self::assertFalse($result->flags->canHoldElection);
        self::assertSame('box', $result->displayTemplate);
        self::assertSame('First generation', $result->description);
        self::assertSame('Première génération', $result->frenchDescription);
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
    public function fromDexWithNullRegionSetsNullRegion(): void
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
        self::assertInstanceOf(DexDebugFlagsResponse::class, $result->flags);
        self::assertTrue($result->flags->isShiny);
        self::assertFalse($result->flags->isPremium);
        self::assertTrue($result->flags->isDisplayForm);
        self::assertFalse($result->flags->isReleased);
        self::assertTrue($result->flags->canHoldElection);
    }

    #[Test]
    public function fromDexWithDeletedAtReturnsFormattedDate(): void
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
    public function fromDexWithRegionDeletedAtReturnsFormattedDate(): void
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
    public function fromDexWithIdentifierReturnsUuidString(): void
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
    public function fromDexWithRegionIdentifierReturnsUuidString(): void
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

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/DexDebugResponseFactoryTest.php`

Expected: FAIL — `fromDex()` still builds flat booleans, so `$result->flags` does not exist.

- [ ] **Step 3: Update `DexDebugResponseFactory` to build the nested `DexDebugFlagsResponse`**

Replace the full content of `src/Factory/DexDebugResponseFactory.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexDebugFlagsResponse;
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
            flags: self::buildFlags($dex),
            displayTemplate: $dex->displayTemplate,
            region: null !== $dex->region ? self::buildRegion($dex->region) : null,
            description: $dex->description,
            frenchDescription: $dex->frenchDescription,
            lastChangedAt: $dex->lastChangedAt->format(\DateTime::ATOM),
            electionOrderNumber: $dex->electionOrderNumber,
            deletedAt: $dex->deletedAt?->format(\DateTime::ATOM),
        );
    }

    private static function buildFlags(Dex $dex): DexDebugFlagsResponse
    {
        return new DexDebugFlagsResponse(
            isShiny: $dex->isShiny,
            isPremium: $dex->isPremium,
            isDisplayForm: $dex->isDisplayForm,
            isReleased: $dex->isReleased,
            canHoldElection: $dex->canHoldElection,
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

- [ ] **Step 4: Run the factory test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/DexDebugResponseFactoryTest.php`

Expected: 6 tests, 0 failures.

---

### Task 4: Update the integration test

**Files:**
- Modify: `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php`

- [ ] **Step 1: Update `testDex()` to assert on the nested `flags` key**

Replace the full content of `tests/src/Integration/Controller/Debug/DebugDexControllerTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Debug;

use App\Controller\Debug\DebugDexController;
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

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('identifier', $data);
        $this->assertEquals('redgreenblueyellow', $data['slug']);

        $this->assertArrayHasKey('region', $data);
        $this->assertArrayHasKey('identifier', $data['region']);
        $this->assertEquals('kanto', $data['region']['slug']);

        $this->assertArrayHasKey('flags', $data);
        $this->assertArrayHasKey('is_shiny', $data['flags']);
        $this->assertArrayHasKey('is_premium', $data['flags']);
        $this->assertArrayHasKey('is_display_form', $data['flags']);
        $this->assertArrayHasKey('is_released', $data['flags']);
        $this->assertArrayHasKey('can_hold_election', $data['flags']);

        $this->assertArrayNotHasKey('is_shiny', $data);
        $this->assertArrayNotHasKey('is_premium', $data);
        $this->assertArrayNotHasKey('is_display_form', $data);
        $this->assertArrayNotHasKey('is_released', $data);
        $this->assertArrayNotHasKey('can_hold_election', $data);
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

        /** @var null|array{pokemons: array{slug: string}[]} $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('pokemons', $data);
        $this->assertNotEmpty($data['pokemons']);

        $slugs = array_column($data['pokemons'], 'slug');
        $this->assertContains('bulbasaur', $slugs);
        $this->assertContains('douze', $slugs);
    }

    #[Test]
    public function testDexAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
```

- [ ] **Step 2: Run the integration test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/Debug/DebugDexControllerTest.php`

Expected: FAIL on `testDex()` — `flags` key not present yet (factory not updated until Task 3 step 3).

Note: Since Task 3 already updated the factory, this test should actually pass. Run it to confirm.

Expected: 4 tests, 0 failures.

---

### Task 5: Run full unit test suite and quality checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all unit tests**

Run: `make tu`

Expected: All unit tests pass, 0 failures.

- [ ] **Step 2: Run all integration tests**

Run: `make ti`

Expected: All integration tests pass, 0 failures.

- [ ] **Step 3: Run code quality checks**

Run: `make code-quality`

Expected: PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac — all green.

- [ ] **Step 4: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% line coverage, 100% MSI — all checks green.

- [ ] **Step 5: Summary of changes**

- ✅ Created `DexDebugFlagsResponse` DTO (5 boolean flags, `SerializedName` annotations)
- ✅ Updated `DexDebugResponse` to embed `DexDebugFlagsResponse $flags` instead of 5 flat booleans
- ✅ Updated `DexDebugResponseFactory` to use private `buildFlags()` helper
- ✅ Created `DexDebugFlagsResponseTest` (3 test cases, 100% coverage)
- ✅ Updated `DexDebugResponseTest` (2 test cases using the new DTO)
- ✅ Updated `DexDebugResponseFactoryTest` (6 test cases asserting on `$result->flags`)
- ✅ Updated `DebugDexControllerTest` (4 test cases, `testDex()` checks `flags` key and absence of flat flags)
- ✅ All quality gates passing (`make quality`, `make measures`)
