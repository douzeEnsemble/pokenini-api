# API Response Restructuring (Election Metrics — Nested Counts) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /election/metrics` response by grouping the flat prefixed fields `view_count_sum`/`view_count_max` and `win_count_sum`/`win_count_max` into nested `view_count` and `win_count` objects.

**Architecture:** Create two immutable nested response DTOs (`ElectionViewCountResponse`, `ElectionWinCountResponse`), update `ElectionMetricsResponse` to embed them instead of the four flat int fields, update `ElectionMetricsResponseFactory` to build the nested structure, and update all tests and the JSON fixture accordingly.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Response shape change

**Before:**
```json
{
    "view_count_sum": 9,
    "win_count_sum": 6,
    "view_count_max": 3,
    "win_count_max": 3,
    "under_max_view_count": 1,
    "max_view_count": 1,
    "dex_total_count": 7
}
```

**After:**
```json
{
    "view_count": {
        "sum": 9,
        "max": 3
    },
    "win_count": {
        "sum": 6,
        "max": 3
    },
    "under_max_view_count": 1,
    "max_view_count": 1,
    "dex_total_count": 7
}
```

---

## File Structure

**Create:**
- `src/DTO/Response/ElectionViewCountResponse.php` — immutable DTO grouping `sum` and `max` for view counts
- `src/DTO/Response/ElectionWinCountResponse.php` — immutable DTO grouping `sum` and `max` for win counts
- `tests/src/Unit/DTO/Response/ElectionViewCountResponseTest.php` — unit tests for `ElectionViewCountResponse`
- `tests/src/Unit/DTO/Response/ElectionWinCountResponseTest.php` — unit tests for `ElectionWinCountResponse`

**Modify:**
- `src/DTO/Response/ElectionMetricsResponse.php` — replace the four flat int fields with nested `ElectionViewCountResponse $viewCount` and `ElectionWinCountResponse $winCount`
- `src/Factory/ElectionMetricsResponseFactory.php` — build the two nested DTOs from the flat SQL keys
- `tests/src/Unit/DTO/Response/ElectionMetricsResponseTest.php` — update assertions to use nested DTOs
- `tests/src/Unit/Factory/ElectionMetricsResponseFactoryTest.php` — update assertions to traverse nested DTOs
- `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php` — update `assertSame` arrays and `#[CoversClass]` annotations; update fixture assertion
- `tests/resources/fixtures/election_metrics_response.json` — update JSON to nested shape

---

## Tasks

### Task 1: Create ElectionViewCountResponse DTO

**Files:**
- Create: `src/DTO/Response/ElectionViewCountResponse.php`
- Create: `tests/src/Unit/DTO/Response/ElectionViewCountResponseTest.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionViewCountResponse
{
    public function __construct(
        public readonly int $sum,
        public readonly int $max,
    ) {}
}
```

Save as `src/DTO/Response/ElectionViewCountResponse.php`.

- [ ] **Step 2: Write unit tests for ElectionViewCountResponse**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionViewCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionViewCountResponse::class)]
final class ElectionViewCountResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ElectionViewCountResponse(
            sum: 42,
            max: 10,
        );

        self::assertSame(42, $response->sum);
        self::assertSame(10, $response->max);
    }

    #[Test]
    public function constructorHandlesZeroValues(): void
    {
        $response = new ElectionViewCountResponse(
            sum: 0,
            max: 0,
        );

        self::assertSame(0, $response->sum);
        self::assertSame(0, $response->max);
    }
}
```

Save as `tests/src/Unit/DTO/Response/ElectionViewCountResponseTest.php`.

---

### Task 2: Create ElectionWinCountResponse DTO

**Files:**
- Create: `src/DTO/Response/ElectionWinCountResponse.php`
- Create: `tests/src/Unit/DTO/Response/ElectionWinCountResponseTest.php`

- [ ] **Step 1: Create the DTO file**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionWinCountResponse
{
    public function __construct(
        public readonly int $sum,
        public readonly int $max,
    ) {}
}
```

Save as `src/DTO/Response/ElectionWinCountResponse.php`.

- [ ] **Step 2: Write unit tests for ElectionWinCountResponse**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionWinCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionWinCountResponse::class)]
final class ElectionWinCountResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ElectionWinCountResponse(
            sum: 30,
            max: 7,
        );

        self::assertSame(30, $response->sum);
        self::assertSame(7, $response->max);
    }

    #[Test]
    public function constructorHandlesZeroValues(): void
    {
        $response = new ElectionWinCountResponse(
            sum: 0,
            max: 0,
        );

        self::assertSame(0, $response->sum);
        self::assertSame(0, $response->max);
    }
}
```

Save as `tests/src/Unit/DTO/Response/ElectionWinCountResponseTest.php`.

---

### Task 3: Update ElectionMetricsResponse DTO

**Files:**
- Modify: `src/DTO/Response/ElectionMetricsResponse.php`

Current content of `src/DTO/Response/ElectionMetricsResponse.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionMetricsResponse
{
    public function __construct(
        #[SerializedName('view_count_sum')]
        public readonly int $viewCountSum,
        #[SerializedName('win_count_sum')]
        public readonly int $winCountSum,
        #[SerializedName('view_count_max')]
        public readonly int $viewCountMax,
        #[SerializedName('win_count_max')]
        public readonly int $winCountMax,
        #[SerializedName('under_max_view_count')]
        public readonly int $underMaxViewCount,
        #[SerializedName('max_view_count')]
        public readonly int $maxViewCount,
        #[SerializedName('dex_total_count')]
        public readonly int $dexTotalCount,
    ) {}
}
```

- [ ] **Step 1: Replace the file content with the nested version**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionMetricsResponse
{
    public function __construct(
        #[SerializedName('view_count')]
        public readonly ElectionViewCountResponse $viewCount,
        #[SerializedName('win_count')]
        public readonly ElectionWinCountResponse $winCount,
        #[SerializedName('under_max_view_count')]
        public readonly int $underMaxViewCount,
        #[SerializedName('max_view_count')]
        public readonly int $maxViewCount,
        #[SerializedName('dex_total_count')]
        public readonly int $dexTotalCount,
    ) {}
}
```

Save as `src/DTO/Response/ElectionMetricsResponse.php`.

---

### Task 4: Update ElectionMetricsResponseTest

**Files:**
- Modify: `tests/src/Unit/DTO/Response/ElectionMetricsResponseTest.php`

Current content of `tests/src/Unit/DTO/Response/ElectionMetricsResponseTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionMetricsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionMetricsResponse::class)]
final class ElectionMetricsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ElectionMetricsResponse(
            viewCountSum: 9,
            winCountSum: 6,
            viewCountMax: 3,
            winCountMax: 3,
            underMaxViewCount: 1,
            maxViewCount: 1,
            dexTotalCount: 7,
        );

        self::assertSame(9, $response->viewCountSum);
        self::assertSame(6, $response->winCountSum);
        self::assertSame(3, $response->viewCountMax);
        self::assertSame(3, $response->winCountMax);
        self::assertSame(1, $response->underMaxViewCount);
        self::assertSame(1, $response->maxViewCount);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ElectionMetricsResponse(
            viewCountSum: 0,
            winCountSum: 0,
            viewCountMax: 0,
            winCountMax: 0,
            underMaxViewCount: 21,
            maxViewCount: 21,
            dexTotalCount: 21,
        );

        self::assertSame(0, $response->viewCountSum);
        self::assertSame(0, $response->winCountSum);
        self::assertSame(0, $response->viewCountMax);
        self::assertSame(0, $response->winCountMax);
        self::assertSame(21, $response->underMaxViewCount);
        self::assertSame(21, $response->maxViewCount);
        self::assertSame(21, $response->dexTotalCount);
    }
}
```

- [ ] **Step 1: Replace the file content with the nested version**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionMetricsResponse;
use App\DTO\Response\ElectionViewCountResponse;
use App\DTO\Response\ElectionWinCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionMetricsResponse::class)]
final class ElectionMetricsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $viewCount = new ElectionViewCountResponse(sum: 9, max: 3);
        $winCount = new ElectionWinCountResponse(sum: 6, max: 3);

        $response = new ElectionMetricsResponse(
            viewCount: $viewCount,
            winCount: $winCount,
            underMaxViewCount: 1,
            maxViewCount: 1,
            dexTotalCount: 7,
        );

        self::assertSame($viewCount, $response->viewCount);
        self::assertSame($winCount, $response->winCount);
        self::assertSame(1, $response->underMaxViewCount);
        self::assertSame(1, $response->maxViewCount);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function constructorHandlesZeroValues(): void
    {
        $viewCount = new ElectionViewCountResponse(sum: 0, max: 0);
        $winCount = new ElectionWinCountResponse(sum: 0, max: 0);

        $response = new ElectionMetricsResponse(
            viewCount: $viewCount,
            winCount: $winCount,
            underMaxViewCount: 21,
            maxViewCount: 21,
            dexTotalCount: 21,
        );

        self::assertSame($viewCount, $response->viewCount);
        self::assertSame($winCount, $response->winCount);
        self::assertSame(21, $response->underMaxViewCount);
        self::assertSame(21, $response->maxViewCount);
        self::assertSame(21, $response->dexTotalCount);
    }
}
```

Save as `tests/src/Unit/DTO/Response/ElectionMetricsResponseTest.php`.

---

### Task 5: Update ElectionMetricsResponseFactory

**Files:**
- Modify: `src/Factory/ElectionMetricsResponseFactory.php`

Current content of `src/Factory/ElectionMetricsResponseFactory.php`:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ElectionMetricsResponse;

final class ElectionMetricsResponseFactory
{
    /**
     * Transform the metrics associative array into an ElectionMetricsResponse DTO.
     *
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): ElectionMetricsResponse
    {
        /** @var scalar $viewCountSum */
        $viewCountSum = $data['view_count_sum'];

        /** @var scalar $winCountSum */
        $winCountSum = $data['win_count_sum'];

        /** @var scalar $viewCountMax */
        $viewCountMax = $data['view_count_max'];

        /** @var scalar $winCountMax */
        $winCountMax = $data['win_count_max'];

        /** @var scalar $underMaxViewCount */
        $underMaxViewCount = $data['under_max_view_count'];

        /** @var scalar $maxViewCount */
        $maxViewCount = $data['max_view_count'];

        /** @var scalar $dexTotalCount */
        $dexTotalCount = $data['dex_total_count'];

        return new ElectionMetricsResponse(
            viewCountSum: (int) $viewCountSum,
            winCountSum: (int) $winCountSum,
            viewCountMax: (int) $viewCountMax,
            winCountMax: (int) $winCountMax,
            underMaxViewCount: (int) $underMaxViewCount,
            maxViewCount: (int) $maxViewCount,
            dexTotalCount: (int) $dexTotalCount,
        );
    }
}
```

- [ ] **Step 1: Replace the file content to build nested DTOs**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ElectionMetricsResponse;
use App\DTO\Response\ElectionViewCountResponse;
use App\DTO\Response\ElectionWinCountResponse;

final class ElectionMetricsResponseFactory
{
    /**
     * Transform the metrics associative array into an ElectionMetricsResponse DTO.
     *
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): ElectionMetricsResponse
    {
        /** @var scalar $viewCountSum */
        $viewCountSum = $data['view_count_sum'];

        /** @var scalar $winCountSum */
        $winCountSum = $data['win_count_sum'];

        /** @var scalar $viewCountMax */
        $viewCountMax = $data['view_count_max'];

        /** @var scalar $winCountMax */
        $winCountMax = $data['win_count_max'];

        /** @var scalar $underMaxViewCount */
        $underMaxViewCount = $data['under_max_view_count'];

        /** @var scalar $maxViewCount */
        $maxViewCount = $data['max_view_count'];

        /** @var scalar $dexTotalCount */
        $dexTotalCount = $data['dex_total_count'];

        return new ElectionMetricsResponse(
            viewCount: new ElectionViewCountResponse(
                sum: (int) $viewCountSum,
                max: (int) $viewCountMax,
            ),
            winCount: new ElectionWinCountResponse(
                sum: (int) $winCountSum,
                max: (int) $winCountMax,
            ),
            underMaxViewCount: (int) $underMaxViewCount,
            maxViewCount: (int) $maxViewCount,
            dexTotalCount: (int) $dexTotalCount,
        );
    }
}
```

Save as `src/Factory/ElectionMetricsResponseFactory.php`.

---

### Task 6: Update ElectionMetricsResponseFactoryTest

**Files:**
- Modify: `tests/src/Unit/Factory/ElectionMetricsResponseFactoryTest.php`

Current content of `tests/src/Unit/Factory/ElectionMetricsResponseFactoryTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Factory\ElectionMetricsResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionMetricsResponseFactory::class)]
final class ElectionMetricsResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromArrayTransformsDataCorrectly(): void
    {
        $data = [
            'view_count_sum' => 9,
            'win_count_sum' => 6,
            'view_count_max' => 3,
            'win_count_max' => 3,
            'under_max_view_count' => 1,
            'max_view_count' => 1,
            'dex_total_count' => 7,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(9, $response->viewCountSum);
        self::assertSame(6, $response->winCountSum);
        self::assertSame(3, $response->viewCountMax);
        self::assertSame(3, $response->winCountMax);
        self::assertSame(1, $response->underMaxViewCount);
        self::assertSame(1, $response->maxViewCount);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayHandlesZeroValues(): void
    {
        $data = [
            'view_count_sum' => 0,
            'win_count_sum' => 0,
            'view_count_max' => 0,
            'win_count_max' => 0,
            'under_max_view_count' => 15,
            'max_view_count' => 15,
            'dex_total_count' => 21,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(0, $response->viewCountSum);
        self::assertSame(0, $response->winCountSum);
        self::assertSame(0, $response->viewCountMax);
        self::assertSame(0, $response->winCountMax);
        self::assertSame(15, $response->underMaxViewCount);
        self::assertSame(15, $response->maxViewCount);
        self::assertSame(21, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayHandlesLargeValues(): void
    {
        $data = [
            'view_count_sum' => 100000,
            'win_count_sum' => 75000,
            'view_count_max' => 500,
            'win_count_max' => 499,
            'under_max_view_count' => 3,
            'max_view_count' => 2,
            'dex_total_count' => 1025,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(100000, $response->viewCountSum);
        self::assertSame(75000, $response->winCountSum);
        self::assertSame(500, $response->viewCountMax);
        self::assertSame(499, $response->winCountMax);
        self::assertSame(3, $response->underMaxViewCount);
        self::assertSame(2, $response->maxViewCount);
        self::assertSame(1025, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayCastsStringValuesToInt(): void
    {
        $data = [
            'view_count_sum' => '9',
            'win_count_sum' => '6',
            'view_count_max' => '3',
            'win_count_max' => '3',
            'under_max_view_count' => '1',
            'max_view_count' => '1',
            'dex_total_count' => '7',
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(9, $response->viewCountSum);
        self::assertSame(6, $response->winCountSum);
        self::assertSame(3, $response->viewCountMax);
        self::assertSame(3, $response->winCountMax);
        self::assertSame(1, $response->underMaxViewCount);
        self::assertSame(1, $response->maxViewCount);
        self::assertSame(7, $response->dexTotalCount);
    }
}
```

- [ ] **Step 1: Replace the file content to assert nested structure**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ElectionMetricsResponse;
use App\DTO\Response\ElectionViewCountResponse;
use App\DTO\Response\ElectionWinCountResponse;
use App\Factory\ElectionMetricsResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionMetricsResponseFactory::class)]
final class ElectionMetricsResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromArrayTransformsDataCorrectly(): void
    {
        $data = [
            'view_count_sum' => 9,
            'win_count_sum' => 6,
            'view_count_max' => 3,
            'win_count_max' => 3,
            'under_max_view_count' => 1,
            'max_view_count' => 1,
            'dex_total_count' => 7,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertInstanceOf(ElectionMetricsResponse::class, $response);
        self::assertInstanceOf(ElectionViewCountResponse::class, $response->viewCount);
        self::assertInstanceOf(ElectionWinCountResponse::class, $response->winCount);
        self::assertSame(9, $response->viewCount->sum);
        self::assertSame(3, $response->viewCount->max);
        self::assertSame(6, $response->winCount->sum);
        self::assertSame(3, $response->winCount->max);
        self::assertSame(1, $response->underMaxViewCount);
        self::assertSame(1, $response->maxViewCount);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayHandlesZeroValues(): void
    {
        $data = [
            'view_count_sum' => 0,
            'win_count_sum' => 0,
            'view_count_max' => 0,
            'win_count_max' => 0,
            'under_max_view_count' => 15,
            'max_view_count' => 15,
            'dex_total_count' => 21,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(0, $response->viewCount->sum);
        self::assertSame(0, $response->viewCount->max);
        self::assertSame(0, $response->winCount->sum);
        self::assertSame(0, $response->winCount->max);
        self::assertSame(15, $response->underMaxViewCount);
        self::assertSame(15, $response->maxViewCount);
        self::assertSame(21, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayHandlesLargeValues(): void
    {
        $data = [
            'view_count_sum' => 100000,
            'win_count_sum' => 75000,
            'view_count_max' => 500,
            'win_count_max' => 499,
            'under_max_view_count' => 3,
            'max_view_count' => 2,
            'dex_total_count' => 1025,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(100000, $response->viewCount->sum);
        self::assertSame(500, $response->viewCount->max);
        self::assertSame(75000, $response->winCount->sum);
        self::assertSame(499, $response->winCount->max);
        self::assertSame(3, $response->underMaxViewCount);
        self::assertSame(2, $response->maxViewCount);
        self::assertSame(1025, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayCastsStringValuesToInt(): void
    {
        $data = [
            'view_count_sum' => '9',
            'win_count_sum' => '6',
            'view_count_max' => '3',
            'win_count_max' => '3',
            'under_max_view_count' => '1',
            'max_view_count' => '1',
            'dex_total_count' => '7',
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(9, $response->viewCount->sum);
        self::assertSame(3, $response->viewCount->max);
        self::assertSame(6, $response->winCount->sum);
        self::assertSame(3, $response->winCount->max);
        self::assertSame(1, $response->underMaxViewCount);
        self::assertSame(1, $response->maxViewCount);
        self::assertSame(7, $response->dexTotalCount);
    }
}
```

Save as `tests/src/Unit/Factory/ElectionMetricsResponseFactoryTest.php`.

---

### Task 7: Update the JSON fixture

**Files:**
- Modify: `tests/resources/fixtures/election_metrics_response.json`

Current content of `tests/resources/fixtures/election_metrics_response.json`:

```json
{
    "view_count_sum": 0,
    "win_count_sum": 0,
    "view_count_max": 0,
    "win_count_max": 0,
    "under_max_view_count": 15,
    "max_view_count": 15,
    "dex_total_count": 21
}
```

- [ ] **Step 1: Replace the fixture content with the nested shape**

```json
{
    "view_count": {
        "sum": 0,
        "max": 0
    },
    "win_count": {
        "sum": 0,
        "max": 0
    },
    "under_max_view_count": 15,
    "max_view_count": 15,
    "dex_total_count": 21
}
```

Save as `tests/resources/fixtures/election_metrics_response.json`.

---

### Task 8: Update the integration test

**Files:**
- Modify: `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`

- [ ] **Step 1: Add CoversClass annotations for the new DTOs**

In `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`, add two imports and two `#[CoversClass]` attributes.

Current file header (lines 1–16):

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TrainerPokemonEloController;
use App\Factory\ElectionMetricsResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloController::class)]
#[CoversClass(ElectionMetricsResponseFactory::class)]
final class TrainerPokemonEloControllerTest extends AbstractTestControllerApi
{
```

Replace with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TrainerPokemonEloController;
use App\DTO\Response\ElectionViewCountResponse;
use App\DTO\Response\ElectionWinCountResponse;
use App\Factory\ElectionMetricsResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloController::class)]
#[CoversClass(ElectionMetricsResponseFactory::class)]
#[CoversClass(ElectionViewCountResponse::class)]
#[CoversClass(ElectionWinCountResponse::class)]
final class TrainerPokemonEloControllerTest extends AbstractTestControllerApi
{
```

- [ ] **Step 2: Update testGetMetrics to assert the nested structure**

Find the `testGetMetrics` method body and replace its `assertSame` block.

Current `assertSame` block in `testGetMetrics`:

```php
        $this->assertSame(
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
                'under_max_view_count' => 15,
                'max_view_count' => 15,
                'dex_total_count' => 21,
            ],
            $content,
        );
```

Replace with:

```php
        $this->assertSame(
            [
                'view_count' => ['sum' => 0, 'max' => 0],
                'win_count' => ['sum' => 0, 'max' => 0],
                'under_max_view_count' => 15,
                'max_view_count' => 15,
                'dex_total_count' => 21,
            ],
            $content,
        );
```

Also update the `@var` type annotation for `$content` in `testGetMetrics` from:
```php
        /** @var float[]|int[] $content */
```
To:
```php
        /** @var array<string, int|array<string, int>> $content */
```

- [ ] **Step 3: Update testGetMetricsBis to assert the nested structure**

Find the `testGetMetricsBis` method body and replace its `assertSame` block.

Current `assertSame` block in `testGetMetricsBis`:

```php
        $this->assertSame(
            [
                'view_count_sum' => 9,
                'win_count_sum' => 6,
                'view_count_max' => 3,
                'win_count_max' => 3,
                'under_max_view_count' => 1,
                'max_view_count' => 1,
                'dex_total_count' => 7,
            ],
            $content,
        );
```

Replace with:

```php
        $this->assertSame(
            [
                'view_count' => ['sum' => 9, 'max' => 3],
                'win_count' => ['sum' => 6, 'max' => 3],
                'under_max_view_count' => 1,
                'max_view_count' => 1,
                'dex_total_count' => 7,
            ],
            $content,
        );
```

Also update the `@var` type annotation for `$content` in `testGetMetricsBis` from:
```php
        /** @var float[]|int[] $content */
```
To:
```php
        /** @var array<string, int|array<string, int>> $content */
```

- [ ] **Step 4: Update testGetMetricsNo to assert the nested structure**

Find the `testGetMetricsNo` method body and replace its `assertSame` block.

Current `assertSame` block in `testGetMetricsNo`:

```php
        $this->assertSame(
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
                'under_max_view_count' => 7,
                'max_view_count' => 0,
                'dex_total_count' => 7,
            ],
            $content,
        );
```

Replace with:

```php
        $this->assertSame(
            [
                'view_count' => ['sum' => 0, 'max' => 0],
                'win_count' => ['sum' => 0, 'max' => 0],
                'under_max_view_count' => 7,
                'max_view_count' => 0,
                'dex_total_count' => 7,
            ],
            $content,
        );
```

Also update the `@var` type annotation for `$content` in `testGetMetricsNo` from:
```php
        /** @var float[]|int[] $content */
```
To:
```php
        /** @var array<string, int|array<string, int>> $content */
```

---

## Self-Review

### Spec coverage

| Requirement | Covered by |
|---|---|
| Create `ElectionViewCountResponse` DTO | Task 1 |
| Unit tests for `ElectionViewCountResponse` | Task 1 Step 2 |
| Create `ElectionWinCountResponse` DTO | Task 2 |
| Unit tests for `ElectionWinCountResponse` | Task 2 Step 2 |
| Update `ElectionMetricsResponse` to embed nested DTOs | Task 3 |
| Update `ElectionMetricsResponseTest` | Task 4 |
| Update `ElectionMetricsResponseFactory` to build nested DTOs | Task 5 |
| Update `ElectionMetricsResponseFactoryTest` to assert nested | Task 6 |
| Update JSON fixture to nested shape | Task 7 |
| Update integration test assertions and `#[CoversClass]` | Task 8 |

### Type consistency

- `ElectionViewCountResponse` properties: `sum: int`, `max: int` — used consistently in Tasks 1, 4, 5, 6, 8.
- `ElectionWinCountResponse` properties: `sum: int`, `max: int` — used consistently in Tasks 2, 4, 5, 6, 8.
- `ElectionMetricsResponse` updated properties: `viewCount: ElectionViewCountResponse`, `winCount: ElectionWinCountResponse`, `underMaxViewCount: int`, `maxViewCount: int`, `dexTotalCount: int` — used consistently in Tasks 3, 4, 5, 6, 8.
- Factory `fromArray()` input shape unchanged: same 7 SQL keys read in Task 5 as before.

### Placeholder scan

No TBD, TODO, "similar to", or "handle edge cases" placeholders found. All code blocks are complete.
