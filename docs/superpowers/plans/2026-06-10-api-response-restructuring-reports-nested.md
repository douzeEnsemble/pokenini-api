# API Response Restructuring (Reports — nested objects) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor the three lists of `GET /reports` from flat rows (`{nb, name, french_name, ...}`) to nested object shapes (`{count, catch_state: {...}}`, `{count, dex: {...}}`, `{count, trainer: {...}}`), renaming the `nb` key to `count` (issue douzeEnsemble/pokenini-api#256).

**Architecture:** Create three new nested Response DTOs (`ReportTrainerResponse`, `ReportDexResponse`, `ReportCatchStateResponse`); rework the three existing wrapper DTOs (`TrainerCatchStateCountResponse`, `DexUsageResponse`, `CatchStateUsageResponse`) to hold `count` + a nested object; update `ReportResponseFactory` to build the nested objects; update unit tests, the integration test, the response fixture, and `doc/endpoints.md`.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer, PHPUnit

> **Process notes (user instructions for this run):**
> - **No `git add` / `git commit`** — the user handles git himself.
> - **Do not run test or quality commands** (`make tests*`, `make quality`, `make measures`, phpunit…) — the user runs them himself. The "Run:" steps below are kept for reference and for the user's final verification.
> - Every new class except Controller and Repository gets a dedicated unit test; tests must keep 100% line coverage and 100% MSI on the touched classes.

---

## Response shape change

**Before** — `GET /reports`:
```json
{
  "catch_state_counts_defined_by_trainer": [
    { "nb": 28, "trainer": "7b52009b64fd0a2a49e6d8a939753077792b0554" }
  ],
  "dex_usage": [
    { "nb": 2, "name": "Home", "french_name": "Home" }
  ],
  "catch_state_usage": [
    { "nb": 11, "name": "No", "french_name": "Non", "color": "#e57373" }
  ]
}
```

**After** — `GET /reports`:
```json
{
  "catch_state_counts_defined_by_trainer": [
    { "count": 28, "trainer": { "external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554" } }
  ],
  "dex_usage": [
    { "count": 2, "dex": { "name": "Home", "french_name": "Home" } }
  ],
  "catch_state_usage": [
    { "count": 11, "catch_state": { "name": "No", "french_name": "Non", "color": "#e57373" } }
  ]
}
```

Two breaking changes per item: `nb` → `count`, and the descriptive fields move into a nested object (`trainer`, `dex`, `catch_state`). Downstream: pokenini-back (Moco fixtures + passthrough), then pokenini-web.

The SQL queries in `PokedexRepository` (`getCatchStateCountsDefinedByTrainer()`, `getDexUsage()`, `getCatchStateUsage()`) are **not** modified — they keep returning flat rows keyed `nb`, `trainer`, `name`, `french_name`, `color`. Only the Factory/DTO layer changes.

---

## File Structure

**Create:**
- `src/DTO/Response/ReportTrainerResponse.php` — nested trainer object (`external_id`)
- `src/DTO/Response/ReportDexResponse.php` — nested dex object (`name`, `french_name`)
- `src/DTO/Response/ReportCatchStateResponse.php` — nested catch state object (`name`, `french_name`, `color`)
- `tests/src/Unit/DTO/Response/ReportTrainerResponseTest.php`
- `tests/src/Unit/DTO/Response/ReportDexResponseTest.php`
- `tests/src/Unit/DTO/Response/ReportCatchStateResponseTest.php`

**Modify:**
- `src/DTO/Response/TrainerCatchStateCountResponse.php` — `{count, trainer: ReportTrainerResponse}`, drop `#[SerializedName('nb')]`
- `src/DTO/Response/DexUsageResponse.php` — `{count, dex: ReportDexResponse}`
- `src/DTO/Response/CatchStateUsageResponse.php` — `{count, catchState: ReportCatchStateResponse}`
- `src/Factory/ReportResponseFactory.php` — build the nested objects
- `tests/src/Unit/DTO/Response/TrainerCatchStateCountResponseTest.php`
- `tests/src/Unit/DTO/Response/DexUsageResponseTest.php`
- `tests/src/Unit/DTO/Response/CatchStateUsageResponseTest.php`
- `tests/src/Unit/DTO/Response/ReportResponseTest.php` — constructs the new shapes
- `tests/src/Unit/Factory/ReportResponseFactoryTest.php` — assertions navigate nested objects
- `tests/src/Integration/Controller/ReportsControllerTest.php` — nested shape assertions
- `tests/resources/fixtures/reports_response.json` — new response shape
- `doc/endpoints.md` — section "9. GET `/reports`" example

**Untouched (already correct):** `src/Controller/ReportsController.php`, `src/Service/PokedexService.php`, `src/Repository/PokedexRepository.php`, `src/DTO/Response/ReportResponse.php`.

---

## Tasks

### Task 1: Create ReportTrainerResponse DTO

**Files:**
- Create: `src/DTO/Response/ReportTrainerResponse.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ReportTrainerResponse
{
    public function __construct(
        #[SerializedName('external_id')]
        public readonly string $externalId,
    ) {}
}
```

Save this as `src/DTO/Response/ReportTrainerResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/ReportTrainerResponse.php`

Expected: `No syntax errors detected`.

---

### Task 2: Create unit test for ReportTrainerResponse

**Files:**
- Create: `tests/src/Unit/DTO/Response/ReportTrainerResponseTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ReportTrainerResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportTrainerResponse::class)]
final class ReportTrainerResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ReportTrainerResponse(
            externalId: '7b52009b64fd0a2a49e6d8a939753077792b0554',
        );

        self::assertSame('7b52009b64fd0a2a49e6d8a939753077792b0554', $response->externalId);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ReportTrainerResponse(
            externalId: 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
        );

        self::assertSame('bd307a3ec329e10a2cff8fb87480823da114f8f4', $response->externalId);
    }
}
```

Save this as `tests/src/Unit/DTO/Response/ReportTrainerResponseTest.php`.

- [ ] **Step 2: (User) Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/ReportTrainerResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 3: Create ReportDexResponse DTO

**Files:**
- Create: `src/DTO/Response/ReportDexResponse.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ReportDexResponse
{
    public function __construct(
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
    ) {}
}
```

Save this as `src/DTO/Response/ReportDexResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/ReportDexResponse.php`

Expected: `No syntax errors detected`.

---

### Task 4: Create unit test for ReportDexResponse

**Files:**
- Create: `tests/src/Unit/DTO/Response/ReportDexResponseTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ReportDexResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportDexResponse::class)]
final class ReportDexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ReportDexResponse(
            name: 'Home',
            frenchName: 'Home',
        );

        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ReportDexResponse(
            name: 'Ruby / Sapphire / Emerald',
            frenchName: 'Rubis / Saphir / Émeraude',
        );

        self::assertSame('Ruby / Sapphire / Emerald', $response->name);
        self::assertSame('Rubis / Saphir / Émeraude', $response->frenchName);
    }
}
```

Save this as `tests/src/Unit/DTO/Response/ReportDexResponseTest.php`.

- [ ] **Step 2: (User) Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/ReportDexResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 5: Create ReportCatchStateResponse DTO

**Files:**
- Create: `src/DTO/Response/ReportCatchStateResponse.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ReportCatchStateResponse
{
    public function __construct(
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly string $color,
    ) {}
}
```

Save this as `src/DTO/Response/ReportCatchStateResponse.php`.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/ReportCatchStateResponse.php`

Expected: `No syntax errors detected`.

---

### Task 6: Create unit test for ReportCatchStateResponse

**Files:**
- Create: `tests/src/Unit/DTO/Response/ReportCatchStateResponseTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ReportCatchStateResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportCatchStateResponse::class)]
final class ReportCatchStateResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ReportCatchStateResponse(
            name: 'No',
            frenchName: 'Non',
            color: '#e57373',
        );

        self::assertSame('No', $response->name);
        self::assertSame('Non', $response->frenchName);
        self::assertSame('#e57373', $response->color);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ReportCatchStateResponse(
            name: 'Maybe',
            frenchName: 'Peut être',
            color: 'blue',
        );

        self::assertSame('Maybe', $response->name);
        self::assertSame('Peut être', $response->frenchName);
        self::assertSame('blue', $response->color);
    }
}
```

Save this as `tests/src/Unit/DTO/Response/ReportCatchStateResponseTest.php`.

- [ ] **Step 2: (User) Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/ReportCatchStateResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 7: Update TrainerCatchStateCountResponse DTO

**Files:**
- Modify: `src/DTO/Response/TrainerCatchStateCountResponse.php`

- [ ] **Step 1: Replace the full content**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class TrainerCatchStateCountResponse
{
    public function __construct(
        public readonly int $count,
        public readonly ReportTrainerResponse $trainer,
    ) {}
}
```

Note: the `#[SerializedName('nb')]` attribute and its `use` import are removed — the property now serializes as `count`. `ReportTrainerResponse` is in the same namespace, no import needed.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/TrainerCatchStateCountResponse.php`

Expected: `No syntax errors detected`.

---

### Task 8: Update unit test for TrainerCatchStateCountResponse

**Files:**
- Modify: `tests/src/Unit/DTO/Response/TrainerCatchStateCountResponseTest.php`

- [ ] **Step 1: Replace the full content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ReportTrainerResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerCatchStateCountResponse::class)]
final class TrainerCatchStateCountResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $trainer = new ReportTrainerResponse(externalId: '7b52009b64fd0a2a49e6d8a939753077792b0554');
        $response = new TrainerCatchStateCountResponse(
            count: 28,
            trainer: $trainer,
        );

        self::assertSame(28, $response->count);
        self::assertSame($trainer, $response->trainer);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $trainer = new ReportTrainerResponse(externalId: 'bd307a3ec329e10a2cff8fb87480823da114f8f4');
        $response = new TrainerCatchStateCountResponse(
            count: 3,
            trainer: $trainer,
        );

        self::assertSame(3, $response->count);
        self::assertSame($trainer, $response->trainer);
    }
}
```

- [ ] **Step 2: (User) Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/TrainerCatchStateCountResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 9: Update DexUsageResponse DTO

**Files:**
- Modify: `src/DTO/Response/DexUsageResponse.php`

- [ ] **Step 1: Replace the full content**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class DexUsageResponse
{
    public function __construct(
        public readonly int $count,
        public readonly ReportDexResponse $dex,
    ) {}
}
```

Note: `#[SerializedName('nb')]` (on `count`) and `#[SerializedName('french_name')]` (the flat `frenchName` field disappears) are removed along with the `use` import.

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/DexUsageResponse.php`

Expected: `No syntax errors detected`.

---

### Task 10: Update unit test for DexUsageResponse

**Files:**
- Modify: `tests/src/Unit/DTO/Response/DexUsageResponseTest.php`

- [ ] **Step 1: Replace the full content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\ReportDexResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexUsageResponse::class)]
final class DexUsageResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $dex = new ReportDexResponse(name: 'Home', frenchName: 'Home');
        $response = new DexUsageResponse(
            count: 2,
            dex: $dex,
        );

        self::assertSame(2, $response->count);
        self::assertSame($dex, $response->dex);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $dex = new ReportDexResponse(name: 'Ruby / Sapphire / Emerald', frenchName: 'Rubis / Saphir / Émeraude');
        $response = new DexUsageResponse(
            count: 1,
            dex: $dex,
        );

        self::assertSame(1, $response->count);
        self::assertSame($dex, $response->dex);
    }
}
```

- [ ] **Step 2: (User) Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/DexUsageResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 11: Update CatchStateUsageResponse DTO

**Files:**
- Modify: `src/DTO/Response/CatchStateUsageResponse.php`

- [ ] **Step 1: Replace the full content**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class CatchStateUsageResponse
{
    public function __construct(
        public readonly int $count,
        #[SerializedName('catch_state')]
        public readonly ReportCatchStateResponse $catchState,
    ) {}
}
```

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/DTO/Response/CatchStateUsageResponse.php`

Expected: `No syntax errors detected`.

---

### Task 12: Update unit test for CatchStateUsageResponse

**Files:**
- Modify: `tests/src/Unit/DTO/Response/CatchStateUsageResponseTest.php`

- [ ] **Step 1: Replace the full content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\ReportCatchStateResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CatchStateUsageResponse::class)]
final class CatchStateUsageResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $catchState = new ReportCatchStateResponse(name: 'No', frenchName: 'Non', color: '#e57373');
        $response = new CatchStateUsageResponse(
            count: 11,
            catchState: $catchState,
        );

        self::assertSame(11, $response->count);
        self::assertSame($catchState, $response->catchState);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $catchState = new ReportCatchStateResponse(name: 'Maybe', frenchName: 'Peut être', color: 'blue');
        $response = new CatchStateUsageResponse(
            count: 4,
            catchState: $catchState,
        );

        self::assertSame(4, $response->count);
        self::assertSame($catchState, $response->catchState);
    }
}
```

- [ ] **Step 2: (User) Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/CatchStateUsageResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 13: Update unit test for ReportResponse

**Files:**
- Modify: `tests/src/Unit/DTO/Response/ReportResponseTest.php`

The DTO `ReportResponse` itself does not change, but its test constructs the three wrapper DTOs with the old flat constructors. Update the construction.

- [ ] **Step 1: Replace the full content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\ReportCatchStateResponse;
use App\DTO\Response\ReportDexResponse;
use App\DTO\Response\ReportResponse;
use App\DTO\Response\ReportTrainerResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportResponse::class)]
final class ReportResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $counts = [new TrainerCatchStateCountResponse(
            count: 28,
            trainer: new ReportTrainerResponse(externalId: 'abc'),
        )];
        $dexUsage = [new DexUsageResponse(
            count: 2,
            dex: new ReportDexResponse(name: 'Home', frenchName: 'Home'),
        )];
        $catchStateUsage = [new CatchStateUsageResponse(
            count: 11,
            catchState: new ReportCatchStateResponse(name: 'No', frenchName: 'Non', color: '#e57373'),
        )];

        $response = new ReportResponse(
            catchStateCountsDefinedByTrainer: $counts,
            dexUsage: $dexUsage,
            catchStateUsage: $catchStateUsage,
        );

        self::assertSame($counts, $response->catchStateCountsDefinedByTrainer);
        self::assertSame($dexUsage, $response->dexUsage);
        self::assertSame($catchStateUsage, $response->catchStateUsage);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ReportResponse(
            catchStateCountsDefinedByTrainer: [],
            dexUsage: [],
            catchStateUsage: [],
        );

        self::assertSame([], $response->catchStateCountsDefinedByTrainer);
        self::assertSame([], $response->dexUsage);
        self::assertSame([], $response->catchStateUsage);
    }
}
```

- [ ] **Step 2: (User) Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/ReportResponseTest.php`

Expected: 2 tests pass, 0 failures.

---

### Task 14: Update ReportResponseFactory

**Files:**
- Modify: `src/Factory/ReportResponseFactory.php`

- [ ] **Step 1: Replace the full content**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\ReportCatchStateResponse;
use App\DTO\Response\ReportDexResponse;
use App\DTO\Response\ReportResponse;
use App\DTO\Response\ReportTrainerResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;

final class ReportResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromCatchStateCountRow(array $row): TrainerCatchStateCountResponse
    {
        /** @var scalar $count */
        $count = $row['nb'];

        /** @var scalar $trainer */
        $trainer = $row['trainer'];

        return new TrainerCatchStateCountResponse(
            count: (int) $count,
            trainer: new ReportTrainerResponse(
                externalId: (string) $trainer,
            ),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromDexUsageRow(array $row): DexUsageResponse
    {
        /** @var scalar $count */
        $count = $row['nb'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        return new DexUsageResponse(
            count: (int) $count,
            dex: new ReportDexResponse(
                name: (string) $name,
                frenchName: (string) $frenchName,
            ),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromCatchStateUsageRow(array $row): CatchStateUsageResponse
    {
        /** @var scalar $count */
        $count = $row['nb'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $color */
        $color = $row['color'];

        return new CatchStateUsageResponse(
            count: (int) $count,
            catchState: new ReportCatchStateResponse(
                name: (string) $name,
                frenchName: (string) $frenchName,
                color: (string) $color,
            ),
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $catchStateCounts
     * @param array<array-key, array<array-key, mixed>> $dexUsage
     * @param array<array-key, array<array-key, mixed>> $catchStateUsage
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

- [ ] **Step 2: Verify syntax**

Run: `docker compose exec php php -l src/Factory/ReportResponseFactory.php`

Expected: `No syntax errors detected`.

---

### Task 15: Update unit test for ReportResponseFactory

**Files:**
- Modify: `tests/src/Unit/Factory/ReportResponseFactoryTest.php`

Keep the existing 8 test methods (correct mapping + type casting per row type, plus the two `fromServiceArrays` tests), with assertions navigating the nested objects. The casting tests are what kill the `(int)`/`(string)` cast mutants — do not remove them.

- [ ] **Step 1: Replace the full content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\DexUsageResponse;
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

        self::assertSame(28, $response->count);
        self::assertSame('7b52009b64fd0a2a49e6d8a939753077792b0554', $response->trainer->externalId);
    }

    #[Test]
    public function fromCatchStateCountRowCastsToCorrectTypes(): void
    {
        $row = ['nb' => '5', 'trainer' => 'abc'];

        $response = ReportResponseFactory::fromCatchStateCountRow($row);

        self::assertSame(5, $response->count);
        self::assertSame('abc', $response->trainer->externalId);
    }

    #[Test]
    public function fromDexUsageRowTransformsRowCorrectly(): void
    {
        $row = ['nb' => 2, 'name' => 'Red / Green', 'french_name' => 'Rouge / Vert'];

        $response = ReportResponseFactory::fromDexUsageRow($row);

        self::assertSame(2, $response->count);
        self::assertSame('Red / Green', $response->dex->name);
        self::assertSame('Rouge / Vert', $response->dex->frenchName);
    }

    #[Test]
    public function fromDexUsageRowCastsToCorrectTypes(): void
    {
        $row = ['nb' => '3', 'name' => 123, 'french_name' => 456];

        $response = ReportResponseFactory::fromDexUsageRow($row);

        self::assertSame(3, $response->count);
        self::assertSame('123', $response->dex->name);
        self::assertSame('456', $response->dex->frenchName);
    }

    #[Test]
    public function fromCatchStateUsageRowTransformsRowCorrectly(): void
    {
        $row = ['nb' => 11, 'name' => 'No', 'french_name' => 'Non', 'color' => '#e57373'];

        $response = ReportResponseFactory::fromCatchStateUsageRow($row);

        self::assertSame(11, $response->count);
        self::assertSame('No', $response->catchState->name);
        self::assertSame('Non', $response->catchState->frenchName);
        self::assertSame('#e57373', $response->catchState->color);
    }

    #[Test]
    public function fromCatchStateUsageRowCastsToCorrectTypes(): void
    {
        $row = ['nb' => '4', 'name' => 789, 'french_name' => 0, 'color' => 'blue'];

        $response = ReportResponseFactory::fromCatchStateUsageRow($row);

        self::assertSame(4, $response->count);
        self::assertSame('789', $response->catchState->name);
        self::assertSame('0', $response->catchState->frenchName);
        self::assertSame('blue', $response->catchState->color);
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

        self::assertCount(0, $report->catchStateCountsDefinedByTrainer);
        self::assertCount(0, $report->dexUsage);
        self::assertCount(0, $report->catchStateUsage);
    }
}
```

- [ ] **Step 2: (User) Run the test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/ReportResponseFactoryTest.php`

Expected: 8 tests pass, 0 failures.

---

### Task 16: Update the response fixture

**Files:**
- Modify: `tests/resources/fixtures/reports_response.json`

- [ ] **Step 1: Replace the full content**

```json
{
  "catch_state_counts_defined_by_trainer": [
    {
      "count": 28,
      "trainer": {
        "external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554"
      }
    },
    {
      "count": 3,
      "trainer": {
        "external_id": "bd307a3ec329e10a2cff8fb87480823da114f8f4"
      }
    }
  ],
  "dex_usage": [
    {
      "count": 2,
      "dex": {
        "name": "Red / Green / Blue / Yellow",
        "french_name": "Rouge / Vert / Bleu / Jaune"
      }
    },
    {
      "count": 2,
      "dex": {
        "name": "Gold / Silver / Crystal",
        "french_name": "Or / Argent / Cristal"
      }
    },
    {
      "count": 2,
      "dex": {
        "name": "Home",
        "french_name": "Home"
      }
    },
    {
      "count": 1,
      "dex": {
        "name": "Ruby / Sapphire / Emerald",
        "french_name": "Rubis / Saphir / Émeraude"
      }
    },
    {
      "count": 1,
      "dex": {
        "name": "Home\nShiny",
        "french_name": "Home\nChromatique"
      }
    },
    {
      "count": 1,
      "dex": {
        "name": "Home PoGo",
        "french_name": "Home PoGo"
      }
    }
  ],
  "catch_state_usage": [
    {
      "count": 11,
      "catch_state": {
        "name": "No",
        "french_name": "Non",
        "color": "#e57373"
      }
    },
    {
      "count": 4,
      "catch_state": {
        "name": "Maybe",
        "french_name": "Peut être",
        "color": "blue"
      }
    },
    {
      "count": 5,
      "catch_state": {
        "name": "Maybe not",
        "french_name": "Peut être pas",
        "color": "yellow"
      }
    },
    {
      "count": 11,
      "catch_state": {
        "name": "Yes",
        "french_name": "Oui",
        "color": "#66bb6a"
      }
    }
  ]
}
```

**Important:** the `"Home\nShiny"` / `"Home\nChromatique"` values contain a literal `\n` escape inside the JSON string — keep them exactly as in the current fixture.

---

### Task 17: Update integration test for ReportsController

**Files:**
- Modify: `tests/src/Integration/Controller/ReportsControllerTest.php`

- [ ] **Step 1: Replace the full content**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ReportsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ReportsController::class)]
final class ReportsControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsObjectWithRequiredSections(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
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
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var array<int, mixed> $catchStateCounts */
        $catchStateCounts = $data['catch_state_counts_defined_by_trainer'];

        /** @var mixed $item */
        foreach ($catchStateCounts as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('count', $item);
            self::assertIsInt($item['count']);
            self::assertArrayHasKey('trainer', $item);
            self::assertIsArray($item['trainer']);
            self::assertArrayHasKey('external_id', $item['trainer']);
            self::assertIsString($item['trainer']['external_id']);
        }
    }

    #[Test]
    public function getDexUsageHasCorrectShape(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var array<int, mixed> $dexUsage */
        $dexUsage = $data['dex_usage'];

        /** @var mixed $item */
        foreach ($dexUsage as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('count', $item);
            self::assertIsInt($item['count']);
            self::assertArrayHasKey('dex', $item);
            self::assertIsArray($item['dex']);
            self::assertArrayHasKey('name', $item['dex']);
            self::assertArrayHasKey('french_name', $item['dex']);
            self::assertIsString($item['dex']['name']);
            self::assertIsString($item['dex']['french_name']);
        }
    }

    #[Test]
    public function getCatchStateUsageHasCorrectShape(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var array<int, mixed> $catchStateUsage */
        $catchStateUsage = $data['catch_state_usage'];

        /** @var mixed $item */
        foreach ($catchStateUsage as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('count', $item);
            self::assertIsInt($item['count']);
            self::assertArrayHasKey('catch_state', $item);
            self::assertIsArray($item['catch_state']);
            self::assertArrayHasKey('name', $item['catch_state']);
            self::assertArrayHasKey('french_name', $item['catch_state']);
            self::assertArrayHasKey('color', $item['catch_state']);
            self::assertIsString($item['catch_state']['name']);
            self::assertIsString($item['catch_state']['french_name']);
            self::assertIsString($item['catch_state']['color']);
        }
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
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

- [ ] **Step 2: (User) Run the integration test**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/ReportsControllerTest.php`

Expected: 6 tests pass, 0 failures.

---

### Task 18: Update doc/endpoints.md

**Files:**
- Modify: `doc/endpoints.md` (section "### 9. GET `/reports`")

- [ ] **Step 1: Replace the example response**

In `doc/endpoints.md`, in section `### 9. GET `/reports``, replace the JSON example block:

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
      "name": "Home",
      "french_name": "Home"
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
      "nb": 11,
      "name": "Yes",
      "french_name": "Oui",
      "color": "#66bb6a"
    }
  ]
}
```

with:

```json
{
  "catch_state_counts_defined_by_trainer": [
    {
      "count": 28,
      "trainer": {
        "external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554"
      }
    },
    {
      "count": 3,
      "trainer": {
        "external_id": "bd307a3ec329e10a2cff8fb87480823da114f8f4"
      }
    }
  ],
  "dex_usage": [
    {
      "count": 2,
      "dex": {
        "name": "Red / Green / Blue / Yellow",
        "french_name": "Rouge / Vert / Bleu / Jaune"
      }
    },
    {
      "count": 2,
      "dex": {
        "name": "Home",
        "french_name": "Home"
      }
    }
  ],
  "catch_state_usage": [
    {
      "count": 11,
      "catch_state": {
        "name": "No",
        "french_name": "Non",
        "color": "#e57373"
      }
    },
    {
      "count": 11,
      "catch_state": {
        "name": "Yes",
        "french_name": "Oui",
        "color": "#66bb6a"
      }
    }
  ]
}
```

---

### Task 19: Final verification (run by the user)

**Files:**
- All files from previous tasks

- [ ] **Step 1: (User) Run all tests**

Run: `make tests`

Expected: All unit and integration tests pass, 0 failures.

- [ ] **Step 2: (User) Run code quality checks**

Run: `make quality`

Expected: All quality checks green (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint).

- [ ] **Step 3: (User) Run coverage and mutation checks**

Run: `make measures`

Expected: 100% line coverage and 100% MSI on all touched classes.

- [ ] **Step 4: Summary of changes**

- ✅ New nested DTOs: `ReportTrainerResponse`, `ReportDexResponse`, `ReportCatchStateResponse` (+ dedicated unit tests)
- ✅ `TrainerCatchStateCountResponse`, `DexUsageResponse`, `CatchStateUsageResponse` — now `{count, <nested object>}`, `nb` renamed to `count`
- ✅ `ReportResponseFactory` — builds nested objects from flat SQL rows
- ✅ Unit tests updated (DTOs, Factory) with type-cast assertions preserved for MSI
- ✅ Integration test + `reports_response.json` fixture updated to nested shape
- ✅ `doc/endpoints.md` section 9 updated
- ⏳ Git add/commit left to the user

---

## Next Steps (not in this plan)

Breaking change on `GET /reports`. Downstream repos must follow (workspace convention: api → back → web):

1. **pokenini-back** — update any Moco fixture mocking `/reports` and any BFF code reading `nb`, flat `trainer`, `name`, `french_name`, `color`.
2. **pokenini-web** — update Twig templates / code reading the reports response, plus its Moco fixtures.
