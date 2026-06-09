# API Response Restructuring (GET /dex/can_hold_election — Nested Flags) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /dex/can_hold_election` response by grouping the 4 flat `is_*` boolean fields of `DexResponse` into a nested `flags` object, reusing the existing `DexFlagsResponse` DTO (already used by `TrainerDexResponse` and `AlbumDexResponse`). Three missing flag columns (`is_private`, `is_on_home`, `is_custom`) are added to the SQL query as `false` constants since there is no trainer context for this global endpoint.

**Architecture:** Modify `DexResponse` to embed `DexFlagsResponse $flags` instead of 4 flat booleans, extend `DexRepository.getCanHoldElection()` SQL to select the 3 missing flags as constants, update `DexResponseFactory.fromSqlRow()` to build the nested DTO, and update all tests and fixtures accordingly. `DexFlagsResponse` and its test are untouched (already correct).

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## Response shape change

**Before:**
```json
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
```

**After:**
```json
{
    "slug": "home",
    "original_slug": "home",
    "name": "Home",
    "french_name": "Home",
    "flags": {
        "is_shiny": false,
        "is_private": false,
        "is_on_home": false,
        "is_display_form": true,
        "is_released": true,
        "is_premium": false,
        "is_custom": false
    },
    "description": "",
    "french_description": "",
    "dex_total_count": 22
}
```

---

## File Structure

**Modify:**
- `src/DTO/Response/DexResponse.php` — replace 4 flat booleans (`isShiny`, `isDisplayForm`, `isReleased`, `isPremium`) with `DexFlagsResponse $flags`
- `src/Factory/DexResponseFactory.php` — build `DexFlagsResponse` from 7 row keys (4 real + 3 new constants)
- `src/Repository/DexRepository.php` — add `false AS "is_private"`, `false AS "is_on_home"`, `false AS "is_custom"` to SELECT
- `tests/src/Unit/DTO/Response/DexResponseTest.php` — pass `DexFlagsResponse` instead of flat booleans, assert on `$response->flags`
- `tests/src/Unit/Factory/DexResponseFactoryTest.php` — add the 3 new keys to each row fixture, assert `DexFlagsResponse` instance and all 7 flag values
- `tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php` — replace flat `is_*` assertions with nested `flags` array assertions
- `tests/resources/fixtures/dex_can_hold_election_response.json` — replace flat flags with nested `flags` object

---

## Tasks

### Task 1: Update DexResponseTest and DexResponse DTO

**Files:**
- Modify: `tests/src/Unit/DTO/Response/DexResponseTest.php`
- Modify: `src/DTO/Response/DexResponse.php`

- [ ] **Step 1: Update the unit test to use DexFlagsResponse**

Replace the full content of `tests/src/Unit/DTO/Response/DexResponseTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\DexResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexResponse::class)]
final class DexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        $response = new DexResponse(
            slug: 'home',
            originalSlug: 'home',
            name: 'Home',
            frenchName: 'Home',
            flags: $flags,
            description: 'The National Dex in Home',
            frenchDescription: 'Le Pokédex National dans Home',
            dexTotalCount: 22,
        );

        self::assertSame('home', $response->slug);
        self::assertSame('home', $response->originalSlug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertSame($flags, $response->flags);
        self::assertSame('The National Dex in Home', $response->description);
        self::assertSame('Le Pokédex National dans Home', $response->frenchDescription);
        self::assertSame(22, $response->dexTotalCount);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $flags = new DexFlagsResponse(
            isShiny: true,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: false,
            isReleased: true,
            isPremium: true,
            isCustom: false,
        );

        $response = new DexResponse(
            slug: 'redgreenblueyellow',
            originalSlug: 'redgreenblueyellow',
            name: 'Red / Green / Blue / Yellow',
            frenchName: 'Rouge / Vert / Bleu / Jaune',
            flags: $flags,
            description: '',
            frenchDescription: '',
            dexTotalCount: 7,
        );

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('redgreenblueyellow', $response->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $response->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $response->frenchName);
        self::assertTrue($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertFalse($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertTrue($response->flags->isPremium);
        self::assertSame(7, $response->dexTotalCount);
    }
}
```

- [ ] **Step 2: Update DexResponse DTO**

Replace the full content of `src/DTO/Response/DexResponse.php` with:

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
        public readonly DexFlagsResponse $flags,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        #[SerializedName('dex_total_count')]
        public readonly int $dexTotalCount,
    ) {}
}
```

---

### Task 2: Update DexResponseFactoryTest, DexRepository SQL, and DexResponseFactory

**Files:**
- Modify: `tests/src/Unit/Factory/DexResponseFactoryTest.php`
- Modify: `src/Repository/DexRepository.php`
- Modify: `src/Factory/DexResponseFactory.php`

- [ ] **Step 1: Update the factory unit test**

Replace the full content of `tests/src/Unit/Factory/DexResponseFactoryTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\DexFlagsResponse;
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
            'is_private' => false,
            'is_on_home' => false,
            'is_display_form' => true,
            'is_released' => true,
            'is_premium' => false,
            'is_custom' => false,
            'description' => 'The National Dex in Home',
            'french_description' => 'Le Pokédex National dans Home',
            'dex_total_count' => 22,
        ];

        $response = DexResponseFactory::fromSqlRow($row);

        self::assertSame('home', $response->slug);
        self::assertSame('home', $response->originalSlug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertInstanceOf(DexFlagsResponse::class, $response->flags);
        self::assertFalse($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
        self::assertSame('The National Dex in Home', $response->description);
        self::assertSame('Le Pokédex National dans Home', $response->frenchDescription);
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
            'is_private' => 0,
            'is_on_home' => 0,
            'is_display_form' => 1,
            'is_released' => 1,
            'is_premium' => 0,
            'is_custom' => 0,
            'description' => 202,
            'french_description' => 303,
            'dex_total_count' => '7',
        ];

        $response = DexResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->originalSlug);
        self::assertSame('789', $response->name);
        self::assertSame('101', $response->frenchName);
        self::assertFalse($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
        self::assertSame('202', $response->description);
        self::assertSame('303', $response->frenchDescription);
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
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
                'description' => '',
                'french_description' => '',
                'dex_total_count' => 22,
            ],
            [
                'slug' => 'redgreenblueyellow',
                'original_slug' => 'redgreenblueyellow',
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => false,
                'description' => '',
                'french_description' => '',
                'dex_total_count' => 7,
            ],
        ];

        $responses = DexResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(DexResponse::class, $responses);
        self::assertSame('home', $responses[0]->slug);
        self::assertSame(22, $responses[0]->dexTotalCount);
        self::assertFalse($responses[0]->flags->isPrivate);
        self::assertSame('redgreenblueyellow', $responses[1]->slug);
        self::assertSame(7, $responses[1]->dexTotalCount);
        self::assertTrue($responses[1]->flags->isPremium);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = DexResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
```

- [ ] **Step 2: Add the 3 missing flag columns to the SQL query in DexRepository**

In `src/Repository/DexRepository.php`, find the `getCanHoldElection()` method and update the SQL SELECT clause. The current SELECT is:

```sql
SELECT      d.slug AS "slug",
            d.slug AS "original_slug",
            d.name AS "name",
            d.french_name AS "french_name",
            d.is_shiny AS "is_shiny",
            d.is_display_form AS "is_display_form",
            d.description AS "description",
            d.french_description AS "french_description",
            d.is_released AS "is_released",
            d.is_premium AS "is_premium",
            COUNT(1) AS dex_total_count
```

Replace it with:

```sql
SELECT      d.slug AS "slug",
            d.slug AS "original_slug",
            d.name AS "name",
            d.french_name AS "french_name",
            d.is_shiny AS "is_shiny",
            false AS "is_private",
            false AS "is_on_home",
            d.is_display_form AS "is_display_form",
            d.is_released AS "is_released",
            d.is_premium AS "is_premium",
            false AS "is_custom",
            d.description AS "description",
            d.french_description AS "french_description",
            COUNT(1) AS dex_total_count
```

The GROUP BY clause stays unchanged — the `false` constants do not need to be grouped.

- [ ] **Step 3: Update DexResponseFactory to build DexFlagsResponse**

Replace the full content of `src/Factory/DexResponseFactory.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexFlagsResponse;
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

        /** @var scalar $isPrivate */
        $isPrivate = $row['is_private'];

        /** @var scalar $isOnHome */
        $isOnHome = $row['is_on_home'];

        /** @var scalar $isDisplayForm */
        $isDisplayForm = $row['is_display_form'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $isCustom */
        $isCustom = $row['is_custom'];

        /** @var scalar $description */
        $description = $row['description'];

        /** @var scalar $frenchDescription */
        $frenchDescription = $row['french_description'];

        /** @var scalar $dexTotalCount */
        $dexTotalCount = $row['dex_total_count'];

        return new DexResponse(
            slug: (string) $slug,
            originalSlug: (string) $originalSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            flags: new DexFlagsResponse(
                isShiny: (bool) $isShiny,
                isPrivate: (bool) $isPrivate,
                isOnHome: (bool) $isOnHome,
                isDisplayForm: (bool) $isDisplayForm,
                isReleased: (bool) $isReleased,
                isPremium: (bool) $isPremium,
                isCustom: (bool) $isCustom,
            ),
            description: (string) $description,
            frenchDescription: (string) $frenchDescription,
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

---

### Task 3: Update integration test and fixture

**Files:**
- Modify: `tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php`
- Modify: `tests/resources/fixtures/dex_can_hold_election_response.json`

- [ ] **Step 1: Update the fixture file**

Replace the full content of `tests/resources/fixtures/dex_can_hold_election_response.json` with:

```json
[
    {
        "slug": "home",
        "original_slug": "home",
        "name": "Home",
        "french_name": "Home",
        "flags": {
            "is_shiny": false,
            "is_private": false,
            "is_on_home": false,
            "is_display_form": true,
            "is_released": true,
            "is_premium": false,
            "is_custom": false
        },
        "description": "",
        "french_description": "",
        "dex_total_count": 22
    }
]
```

- [ ] **Step 2: Update the integration test assertions**

Replace the full content of `tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php` with:

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
            'flags' => [
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            'description' => '',
            'french_description' => '',
            'dex_total_count' => 22,
        ], $content[0]);
    }

    #[Test]
    public function listReturnsDexWithAllOptions(): void
    {
        $this->apiRequest('GET', '/dex/can_hold_election', [
            'include_unreleased_dex' => 1,
            'include_premium_dex' => 1,
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
            'flags' => [
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => false,
                'is_released' => false,
                'is_premium' => false,
                'is_custom' => false,
            ],
            'description' => '',
            'french_description' => '',
            'dex_total_count' => 1,
        ], $content[0]);

        $this->assertEquals([
            'slug' => 'home',
            'original_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'flags' => [
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            'description' => '',
            'french_description' => '',
            'dex_total_count' => 22,
        ], $content[1]);

        $this->assertEquals([
            'slug' => 'redgreenblueyellow',
            'original_slug' => 'redgreenblueyellow',
            'name' => 'Red / Green / Blue / Yellow',
            'french_name' => 'Rouge / Vert / Bleu / Jaune',
            'flags' => [
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => false,
            ],
            'description' => 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            'french_description' => 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            'dex_total_count' => 7,
        ], $content[2]);

        $this->assertEquals([
            'slug' => 'spoon',
            'original_slug' => 'spoon',
            'name' => 'Spoon',
            'french_name' => 'Cuillière',
            'flags' => [
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => false,
                'is_premium' => true,
                'is_custom' => false,
            ],
            'description' => '',
            'french_description' => '',
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

---

### Task 4: Run quality checks

**Files:**
- All modified files

- [ ] **Step 1: Run unit tests**

```bash
docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/DexResponseTest.php tests/src/Unit/Factory/DexResponseFactoryTest.php
```

Expected: All tests pass, 0 failures.

- [ ] **Step 2: Run integration test**

```bash
docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/DexCanHoldElectionControllerTest.php
```

Expected: All 5 tests pass, 0 failures.

- [ ] **Step 3: Run full quality suite**

```bash
make quality && make measures
```

Expected: PHPStan/Psalm/CS-Fixer/PHPMD/Deptrac all green, 100% coverage, 100% MSI.
