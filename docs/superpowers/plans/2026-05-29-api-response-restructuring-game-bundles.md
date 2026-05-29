# API Response Restructuring (Game Bundles) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /game_bundles` from flat JSON (`generation_slug`) to a nested object-oriented structure (`generation: { slug }`) using the established DTO + Factory + Serializer pattern.

**Architecture:** Create two immutable Response DTOs (`GameBundleResponse` with a nested `GenerationResponse`), a Factory that transforms flat SQL rows into nested DTOs, and update the Controller to apply the Factory + Serializer before returning. Repository and Service stay unchanged.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

**Issue:** https://github.com/douzeEnsemble/pokenini-api/issues/256
**Reference plans:** `docs/superpowers/plans/2026-05-25-api-response-restructuring-types.md`, `docs/superpowers/plans/2026-05-27-api-response-restructuring-election-elo.md`
**Design spec:** `docs/superpowers/specs/2026-05-25-api-response-restructuring-design.md`

---

## Context: actual codebase conventions (READ FIRST)

This plan follows the conventions of the **already-merged** Types and Election/ELO migrations, which differ from the older design-spec snippets:

- **DTO properties are camelCase**, with `#[SerializedName('snake_case')]` for multi-word JSON keys. Single-word keys (`slug`, `name`) need no attribute. See `src/DTO/Response/TypeResponse.php`.
- **Factories add `/** @var scalar $x */` annotations** before every cast (Psalm strict / PHPStan level 9). PHPDoc is `@param array<array-key, mixed> $row`. See `src/Factory/TypeResponseFactory.php`.
- **Every Response DTO has its own unit test** under `tests/src/Unit/DTO/Response/` with `#[CoversClass(...)]` (strict coverage). See `tests/src/Unit/DTO/Response/TypeResponseTest.php`.
- **Test methods use `#[Test]` + camelCase names**, classes carry `/** @internal */`.
- **`GET /game_bundles` reads from the database** (`game_bundle JOIN game_generation`), NOT from Google Sheets. There is **no Moco fixture** for it and none is needed. Integration tests run against the real DB (`APP_ENV=int`).
- The integration test `tests/src/Integration/Controller/GameBundlesControllerTest.php` **already exists** (extends `AbstractTestControllerApi`). It is **modified**, not created.

### Current shape (before)

`src/Repository/GameBundlesRepository.php::getAll()` returns rows shaped:

```
name, french_name, slug, generation_slug
```

Current JSON (`new JsonResponse($gameBundles)` in the controller):

```json
[
  {
    "name": "Red, Green, Blue, Yellow",
    "french_name": "Rouge, Vert, Bleu, Jaune",
    "slug": "redgreenblueyellow",
    "generation_slug": "1"
  }
]
```

### Target shape (after)

```json
[
  {
    "slug": "redgreenblueyellow",
    "name": "Red, Green, Blue, Yellow",
    "french_name": "Rouge, Vert, Bleu, Jaune",
    "generation": { "slug": "1" }
  }
]
```

(Key order is irrelevant — clients and tests compare by key. The change that matters: `generation_slug` becomes a nested `generation` object.)

---

## File Structure

**Create:**
- `src/DTO/Response/GenerationResponse.php` — immutable DTO for a generation (single `slug`)
- `src/DTO/Response/GameBundleResponse.php` — immutable DTO with nested `generation`
- `tests/src/Unit/DTO/Response/GenerationResponseTest.php` — unit test for the DTO
- `tests/src/Unit/DTO/Response/GameBundleResponseTest.php` — unit test for the DTO
- `src/Factory/GameBundleResponseFactory.php` — transforms flat SQL rows → nested DTOs
- `tests/src/Unit/Factory/GameBundleResponseFactoryTest.php` — unit tests for the Factory
- `docs/api-migration/game-bundles-restructuring.md` — client migration documentation

**Modify:**
- `src/Controller/GameBundlesController.php` — apply Factory + Serializer
- `tests/src/Integration/Controller/GameBundlesControllerTest.php` — assert the nested structure

**Unchanged (do NOT touch):**
- `src/Service/GameBundlesService.php`
- `src/Repository/GameBundlesRepository.php`

---

## Tasks

### Task 1: Create GenerationResponse DTO (TDD)

**Files:**
- Create: `tests/src/Unit/DTO/Response/GenerationResponseTest.php`
- Create: `src/DTO/Response/GenerationResponse.php`

- [ ] **Step 1: Write the failing unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GenerationResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GenerationResponse::class)]
final class GenerationResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new GenerationResponse(
            slug: '1',
        );

        self::assertSame('1', $response->slug);
    }
}
```

Save this as `tests/src/Unit/DTO/Response/GenerationResponseTest.php`.

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit --filter GenerationResponseTest tests/src/Unit/`

Expected: FAIL — `Class "App\DTO\Response\GenerationResponse" not found`.

- [ ] **Step 3: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class GenerationResponse
{
    public function __construct(
        public readonly string $slug,
    ) {}
}
```

Save this as `src/DTO/Response/GenerationResponse.php`.

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit --filter GenerationResponseTest tests/src/Unit/`

Expected: PASS — 1 test, 1 assertion.

- [ ] **Step 5: Commit**

```bash
git add src/DTO/Response/GenerationResponse.php tests/src/Unit/DTO/Response/GenerationResponseTest.php
git commit -m "feat: add GenerationResponse DTO"
```

---

### Task 2: Create GameBundleResponse DTO (TDD)

**Files:**
- Create: `tests/src/Unit/DTO/Response/GameBundleResponseTest.php`
- Create: `src/DTO/Response/GameBundleResponse.php`

- [ ] **Step 1: Write the failing unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundleResponse;
use App\DTO\Response\GenerationResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleResponse::class)]
final class GameBundleResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new GameBundleResponse(
            slug: 'redgreenblueyellow',
            name: 'Red, Green, Blue, Yellow',
            frenchName: 'Rouge, Vert, Bleu, Jaune',
            generation: new GenerationResponse(slug: '1'),
        );

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('Red, Green, Blue, Yellow', $response->name);
        self::assertSame('Rouge, Vert, Bleu, Jaune', $response->frenchName);
        self::assertSame('1', $response->generation->slug);
    }
}
```

Save this as `tests/src/Unit/DTO/Response/GameBundleResponseTest.php`.

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit --filter GameBundleResponseTest tests/src/Unit/`

Expected: FAIL — `Class "App\DTO\Response\GameBundleResponse" not found`.

- [ ] **Step 3: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameBundleResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly GenerationResponse $generation,
    ) {}
}
```

Save this as `src/DTO/Response/GameBundleResponse.php`.

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit --filter GameBundleResponseTest tests/src/Unit/`

Expected: PASS — 1 test, 4 assertions.

- [ ] **Step 5: Commit**

```bash
git add src/DTO/Response/GameBundleResponse.php tests/src/Unit/DTO/Response/GameBundleResponseTest.php
git commit -m "feat: add GameBundleResponse DTO with nested generation"
```

---

### Task 3: Create GameBundleResponseFactory (TDD)

**Files:**
- Create: `tests/src/Unit/Factory/GameBundleResponseFactoryTest.php`
- Create: `src/Factory/GameBundleResponseFactory.php`

- [ ] **Step 1: Write the failing unit test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\GameBundleResponse;
use App\Factory\GameBundleResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleResponseFactory::class)]
final class GameBundleResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'name' => 'Red, Green, Blue, Yellow',
            'french_name' => 'Rouge, Vert, Bleu, Jaune',
            'slug' => 'redgreenblueyellow',
            'generation_slug' => '1',
        ];

        $response = GameBundleResponseFactory::fromSqlRow($row);

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('Red, Green, Blue, Yellow', $response->name);
        self::assertSame('Rouge, Vert, Bleu, Jaune', $response->frenchName);
        self::assertSame('1', $response->generation->slug);
    }

    #[Test]
    public function fromSqlRowCastsValuesToStrings(): void
    {
        $row = [
            'name' => 123,
            'french_name' => 456,
            'slug' => 789,
            'generation_slug' => 1,
        ];

        $response = GameBundleResponseFactory::fromSqlRow($row);

        self::assertSame('789', $response->slug);
        self::assertSame('123', $response->name);
        self::assertSame('456', $response->frenchName);
        self::assertSame('1', $response->generation->slug);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'name' => 'Red, Green, Blue, Yellow',
                'french_name' => 'Rouge, Vert, Bleu, Jaune',
                'slug' => 'redgreenblueyellow',
                'generation_slug' => '1',
            ],
            [
                'name' => 'Ruby, Sapphire, Emerald',
                'french_name' => 'Rubis, Saphir, Émeraude',
                'slug' => 'rubysapphireemerald',
                'generation_slug' => '3',
            ],
        ];

        $responses = GameBundleResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(GameBundleResponse::class, $responses);
        self::assertSame('redgreenblueyellow', $responses[0]->slug);
        self::assertSame('1', $responses[0]->generation->slug);
        self::assertSame('rubysapphireemerald', $responses[1]->slug);
        self::assertSame('3', $responses[1]->generation->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = GameBundleResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
```

Save this as `tests/src/Unit/Factory/GameBundleResponseFactoryTest.php`.

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit --filter GameBundleResponseFactoryTest tests/src/Unit/`

Expected: FAIL — `Class "App\Factory\GameBundleResponseFactory" not found`.

- [ ] **Step 3: Create the Factory**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\GameBundleResponse;
use App\DTO\Response\GenerationResponse;

final class GameBundleResponseFactory
{
    /**
     * Transform a single SQL row into GameBundleResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): GameBundleResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        return new GameBundleResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            generation: self::buildGeneration($row),
        );
    }

    /**
     * Transform multiple SQL rows into GameBundleResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return GameBundleResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function buildGeneration(array $row): GenerationResponse
    {
        /** @var scalar $slug */
        $slug = $row['generation_slug'];

        return new GenerationResponse(
            slug: (string) $slug,
        );
    }
}
```

Save this as `src/Factory/GameBundleResponseFactory.php`.

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit --filter GameBundleResponseFactoryTest tests/src/Unit/`

Expected: PASS — 4 tests, 0 failures.

- [ ] **Step 5: Commit**

```bash
git add src/Factory/GameBundleResponseFactory.php tests/src/Unit/Factory/GameBundleResponseFactoryTest.php
git commit -m "feat: add GameBundleResponseFactory"
```

---

### Task 4: Update GameBundlesController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/GameBundlesController.php`

- [ ] **Step 1: Replace the controller with the Factory + Serializer version**

Current content of `src/Controller/GameBundlesController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GameBundlesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/game_bundles')]
final class GameBundlesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        GameBundlesService $service
    ): JsonResponse {
        $gameBundles = $service->getAll();

        // Better with serializer ?
        return new JsonResponse($gameBundles);
    }
}
```

Replace the **entire file** with:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\GameBundleResponseFactory;
use App\Service\GameBundlesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/game_bundles')]
final class GameBundlesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        GameBundlesService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $gameBundles = $service->getAll();

        $responses = GameBundleResponseFactory::fromSqlRows($gameBundles);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
```

- [ ] **Step 2: Verify the controller is syntactically correct**

Run: `docker compose exec php php -l src/Controller/GameBundlesController.php`

Expected: "No syntax errors detected in src/Controller/GameBundlesController.php".

- [ ] **Step 3: Commit**

```bash
git add src/Controller/GameBundlesController.php
git commit -m "refactor: GET /game_bundles returns nested generation via Factory + Serializer"
```

---

### Task 5: Update the integration test assertions to the nested structure

**Files:**
- Modify: `tests/src/Integration/Controller/GameBundlesControllerTest.php`

The existing test asserts the old flat shape (`'generation_slug' => '1'`). Update the three `assertEquals` blocks in `testGetCollection` to assert the nested `generation` object. Keep `testGetAuth` and `testGetBadAuth` unchanged.

- [ ] **Step 1: Replace the three element assertions in `testGetCollection`**

Replace this block:

```php
        $this->assertEquals([
            'name' => 'Red, Green, Blue, Yellow',
            'french_name' => 'Rouge, Vert, Bleu, Jaune',
            'slug' => 'redgreenblueyellow',
            'generation_slug' => '1',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Ruby, Sapphire, Emerald',
            'french_name' => 'Rubis, Saphir, Émeraude',
            'slug' => 'rubysapphireemerald',
            'generation_slug' => '3',
        ], $content[2]);

        $this->assertEquals([
            'name' => 'Black, White',
            'french_name' => 'Noir, Blanc',
            'slug' => 'blackwhite',
            'generation_slug' => '5',
        ], $content[6]);
```

with:

```php
        $this->assertEquals([
            'slug' => 'redgreenblueyellow',
            'name' => 'Red, Green, Blue, Yellow',
            'french_name' => 'Rouge, Vert, Bleu, Jaune',
            'generation' => ['slug' => '1'],
        ], $content[0]);

        $this->assertEquals([
            'slug' => 'rubysapphireemerald',
            'name' => 'Ruby, Sapphire, Emerald',
            'french_name' => 'Rubis, Saphir, Émeraude',
            'generation' => ['slug' => '3'],
        ], $content[2]);

        $this->assertEquals([
            'slug' => 'blackwhite',
            'name' => 'Black, White',
            'french_name' => 'Noir, Blanc',
            'generation' => ['slug' => '5'],
        ], $content[6]);
```

> Note: `assertEquals` on associative arrays is order-insensitive, so the reordered keys are fine. The `@var string[] $content` docblock on `$content` in the existing test still type-checks because each element is now a nested array; if Psalm/PHPStan complains, change the docblock to `/** @var array<int, array<string, mixed>> $content */`.

- [ ] **Step 2: Run the integration test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit --filter GameBundlesControllerTest tests/src/Integration/`

Expected: PASS — 3 tests (`testGetCollection`, `testGetAuth`, `testGetBadAuth`), 0 failures.

- [ ] **Step 3: Commit**

```bash
git add tests/src/Integration/Controller/GameBundlesControllerTest.php
git commit -m "test: assert nested generation in GET /game_bundles integration test"
```

---

### Task 6: Create client migration documentation

**Files:**
- Create: `docs/api-migration/game-bundles-restructuring.md`

- [ ] **Step 1: Create the migration document**

```markdown
# Game Bundles API — Response Structure Migration

**Endpoint:** `GET /game_bundles`
**Change type:** Breaking change
**Status:** Live as of [DATE — fill in on release]

## Summary

The `GET /game_bundles` response was refactored from a flat structure with a
`generation_slug` field to a nested structure with a `generation` object. This
aligns the endpoint with the object-oriented response format introduced for
`/types` and `/election/top` (issue #256).

## Impact Assessment

### pokenini-back

**Current usage:** Calls `GET /game_bundles`, caches and passes the response through.

**Change required:** Update any schema validation / DTO that maps `generation_slug`.
If the response is forwarded as-is, only mapped accessors need updating. Refresh the
Moco fixture used by pokenini-back's tests (`tests/resources/moco/.../game_bundles`).

### pokenini-web

**Current usage:** Renders game bundles in Twig via pokenini-back.

**Change required:** Replace `generation_slug` accessors with the nested object.

**Before:**
```twig
{{ bundle.generation_slug }}
```

**After:**
```twig
{{ bundle.generation.slug }}
```

## Response Comparison

### Before (flat)

```json
[
  {
    "name": "Red, Green, Blue, Yellow",
    "french_name": "Rouge, Vert, Bleu, Jaune",
    "slug": "redgreenblueyellow",
    "generation_slug": "1"
  }
]
```

### After (nested generation)

```json
[
  {
    "slug": "redgreenblueyellow",
    "name": "Red, Green, Blue, Yellow",
    "french_name": "Rouge, Vert, Bleu, Jaune",
    "generation": { "slug": "1" }
  }
]
```

## Migration Steps for Clients

1. Replace `data['generation_slug']` with `data['generation']['slug']`.
2. Update JSON schemas / fixtures to the nested shape.
3. Verify rendering and caching still work end-to-end.

## Timeline

- **API deployed:** [DATE — fill in on release]
- **Client update deadline:** [DATE + 1 week]

## Questions?

See the design spec: `docs/superpowers/specs/2026-05-25-api-response-restructuring-design.md`
```

Save this as `docs/api-migration/game-bundles-restructuring.md`.

- [ ] **Step 2: Commit**

```bash
git add docs/api-migration/game-bundles-restructuring.md
git commit -m "docs: add game_bundles response migration guide"
```

---

### Task 7: Full quality gates and final validation

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all tests**

Run: `make tests`

Expected: All unit and integration tests pass, 0 failures (including the unchanged endpoints).

- [ ] **Step 2: Run code quality checks**

Run: `make quality`

Expected: All green — PHP CS Fixer, PHPMD, Psalm (strict), PHPStan (level 9), Deptrac, jsonlint. In particular, Deptrac must allow `Controller → Factory` (already allowed: `TypesController` uses `TypeResponseFactory`).

- [ ] **Step 3: Run coverage and mutation checks**

Run: `make measures`

Expected: 100% code coverage and 100% MSI, including the new `GameBundleResponse`, `GenerationResponse`, and `GameBundleResponseFactory`.

- [ ] **Step 4: Verify the new files exist**

Run: `ls -la src/DTO/Response/GenerationResponse.php src/DTO/Response/GameBundleResponse.php src/Factory/GameBundleResponseFactory.php tests/src/Unit/DTO/Response/GenerationResponseTest.php tests/src/Unit/DTO/Response/GameBundleResponseTest.php tests/src/Unit/Factory/GameBundleResponseFactoryTest.php docs/api-migration/game-bundles-restructuring.md`

Expected: All 7 files exist.

- [ ] **Step 5 (optional): End-to-end smoke check**

If the stack is running (`make start`), call the endpoint with the API user:

```bash
curl -s -u web:douze http://localhost:8000/game_bundles | head -c 400
```

Expected: JSON array where each element has `slug`, `name`, `french_name`, and a nested `generation` object with a `slug`.

- [ ] **Step 6: Final commit (if any uncommitted changes remain)**

```bash
git add -A
git commit -m "chore: finalize GET /game_bundles response restructuring"
```

---

## Self-Review checklist (run before handoff)

- [ ] **Spec coverage:** DTOs created (`GameBundleResponse`, `GenerationResponse`), Factory created, Controller updated, integration test updated, migration doc written — matches the design-spec generalization steps.
- [ ] **No placeholders:** every code step contains complete code; only the migration doc has intentional `[DATE]` fields (filled at release).
- [ ] **Type consistency:** DTO property `frenchName` (`#[SerializedName('french_name')]`) is used identically in the DTO, factory (`frenchName:`), DTO test, factory test, and asserted as `french_name` in JSON. `GenerationResponse->slug` is used consistently. Factory methods `fromSqlRow` / `fromSqlRows` match their call sites.
- [ ] **Convention match:** camelCase DTO properties + `SerializedName`, `/** @var scalar */` casts, `#[Test]` + `/** @internal */`, dedicated DTO unit tests — all mirror the merged Types/Election migrations.

---

## Next Steps (not in this plan)

Remaining un-migrated flat endpoints follow the same pattern: `/catch_states`, `/forms/category`, `/forms/regional`, `/forms/special`, `/forms/variant`, `/collections`, `/dex`, `/reports`. Each is an independent PR: DTO(s) → Factory → Controller → tests → migration doc.
