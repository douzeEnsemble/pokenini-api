# API Response Restructuring (GET /dex/{trainerExternalId}/list) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /dex/{trainerExternalId}/list` endpoint from flat `new JsonResponse($dex)` to the DTO + Factory + Serializer pattern already used by every other migrated endpoint.

**Architecture:** Create an immutable `TrainerDexResponse` DTO and a `TrainerDexResponseFactory` that casts the 12 raw SQL fields to their proper PHP types. Wire them into `DexController::list` via `iterator_to_array()` → factory → serializer. The JSON field names are unchanged (all snake_case already), so no downstream consumers break.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL (`iterateAssociative`), Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/TrainerDexResponse.php` — immutable DTO for the trainer's dex list entry (12 fields)
- `src/Factory/TrainerDexResponseFactory.php` — static factory: SQL row array → `TrainerDexResponse`
- `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php` — unit tests for the factory

**Modify:**
- `src/Controller/DexController.php` — add `SerializerInterface`, wire factory, drop `new JsonResponse($dex)`
- `tests/src/Integration/Controller/DexControllerTest.php` — add `/** @internal */` + `#[CoversClass]` for factory

---

## Tasks

### Task 1: Create TrainerDexResponse DTO

**Files:**
- Create: `src/DTO/Response/TrainerDexResponse.php`

- [ ] **Step 1: Create the DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerDexResponse
{
    public function __construct(
        #[SerializedName('dex_slug')]
        public readonly string $dexSlug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly string $slug,
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        #[SerializedName('is_private')]
        public readonly bool $isPrivate,
        #[SerializedName('is_on_home')]
        public readonly bool $isOnHome,
        #[SerializedName('is_display_form')]
        public readonly bool $isDisplayForm,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        #[SerializedName('is_custom')]
        public readonly bool $isCustom,
    ) {}
}
```

Save as `src/DTO/Response/TrainerDexResponse.php`.

- [ ] **Step 2: Verify the file exists**

Run: `ls -la src/DTO/Response/TrainerDexResponse.php`

Expected: file exists with readable permissions.

---

### Task 2: Create TrainerDexResponseFactory

**Files:**
- Create: `src/Factory/TrainerDexResponseFactory.php`

- [ ] **Step 1: Create the factory**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\TrainerDexResponse;

final class TrainerDexResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): TrainerDexResponse
    {
        /** @var scalar $dexSlug */
        $dexSlug = $row['dex_slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $isShiny */
        $isShiny = $row['is_shiny'];

        /** @var scalar $isPrivate */
        $isPrivate = $row['is_private'];

        /** @var scalar $isOnHome */
        $isOnHome = $row['is_on_home'];

        /** @var scalar $isDisplayForm */
        $isDisplayForm = $row['is_display_form'];

        /** @var scalar $displayTemplate */
        $displayTemplate = $row['display_template'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $isCustom */
        $isCustom = $row['is_custom'];

        return new TrainerDexResponse(
            dexSlug: (string) $dexSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            slug: (string) $slug,
            isShiny: (bool) $isShiny,
            isPrivate: (bool) $isPrivate,
            isOnHome: (bool) $isOnHome,
            isDisplayForm: (bool) $isDisplayForm,
            displayTemplate: (string) $displayTemplate,
            isReleased: (bool) $isReleased,
            isPremium: (bool) $isPremium,
            isCustom: (bool) $isCustom,
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return TrainerDexResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
```

Save as `src/Factory/TrainerDexResponseFactory.php`.

- [ ] **Step 2: Verify the file exists**

Run: `ls -la src/Factory/TrainerDexResponseFactory.php`

Expected: file exists with readable permissions.

---

### Task 3: Unit tests for TrainerDexResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php`

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\TrainerDexResponse;
use App\Factory\TrainerDexResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexResponseFactory::class)]
final class TrainerDexResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'dex_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'slug' => 'home',
            'is_shiny' => false,
            'is_private' => true,
            'is_on_home' => false,
            'is_display_form' => true,
            'display_template' => 'box',
            'is_released' => true,
            'is_premium' => false,
            'is_custom' => false,
        ];

        $response = TrainerDexResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(TrainerDexResponse::class, $response);
        self::assertSame('home', $response->dexSlug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertSame('home', $response->slug);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('box', $response->displayTemplate);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }

    #[Test]
    public function fromSqlRowCastsValuesToCorrectTypes(): void
    {
        $row = [
            'dex_slug' => 123,
            'name' => 456,
            'french_name' => 789,
            'slug' => 101,
            'is_shiny' => 0,
            'is_private' => 1,
            'is_on_home' => 0,
            'is_display_form' => 1,
            'display_template' => 202,
            'is_released' => 1,
            'is_premium' => 0,
            'is_custom' => 0,
        ];

        $response = TrainerDexResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->dexSlug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->frenchName);
        self::assertSame('101', $response->slug);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('202', $response->displayTemplate);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'dex_slug' => 'home',
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_on_home' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            [
                'dex_slug' => 'homeshiny',
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_on_home' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
        ];

        $responses = TrainerDexResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(TrainerDexResponse::class, $responses);
        self::assertSame('home', $responses[0]->dexSlug);
        self::assertFalse($responses[0]->isShiny);
        self::assertSame('homeshiny', $responses[1]->dexSlug);
        self::assertTrue($responses[1]->isShiny);
        self::assertTrue($responses[1]->isCustom);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = TrainerDexResponseFactory::fromSqlRows([]);

        self::assertIsArray($responses);
        self::assertCount(0, $responses);
    }
}
```

Save as `tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php`.

- [ ] **Step 2: Run unit tests to confirm they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/TrainerDexResponseFactoryTest.php`

Expected: 4 tests, 0 failures.

---

### Task 4: Update DexController::list to use Factory + Serializer

**Files:**
- Modify: `src/Controller/DexController.php`

- [ ] **Step 1: Read the current controller**

Current content of `src/Controller/DexController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\DTO\TrainerDexAttributes;
use App\Service\TrainerDexService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dex')]
final class DexController extends AbstractController
{
    public function __construct(
        private readonly TrainerDexService $trainerDexService
    ) {}

    #[Route(path: '/{trainerExternalId}/list', methods: ['GET'])]
    public function list(
        string $trainerExternalId,
        Request $request,
    ): JsonResponse {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        /** @var bool[][]|string[][] $dex */
        $dex = iterator_to_array(
            $this->trainerDexService->getListQuery($trainerExternalId, $dexQueryOptions)
        );

        // Better with serializer ?
        return new JsonResponse($dex);
    }

    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}')]
    public function put(
        // ...
    ): Response {
        // unchanged
    }
}
```

- [ ] **Step 2: Replace the controller with the updated version**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\DTO\TrainerDexAttributes;
use App\Factory\TrainerDexResponseFactory;
use App\Service\TrainerDexService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/dex')]
final class DexController extends AbstractController
{
    public function __construct(
        private readonly TrainerDexService $trainerDexService
    ) {}

    #[Route(path: '/{trainerExternalId}/list', methods: ['GET'])]
    public function list(
        string $trainerExternalId,
        Request $request,
        SerializerInterface $serializer,
    ): JsonResponse {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        $dex = iterator_to_array(
            $this->trainerDexService->getListQuery($trainerExternalId, $dexQueryOptions)
        );

        $responses = TrainerDexResponseFactory::fromSqlRows($dex);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }

    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}')]
    public function put(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
    ): Response {
        $json = $request->getContent();

        if (!$json) {
            throw new BadRequestHttpException();
        }

        /** @var bool[] */
        $content = json_decode($json, true);

        try {
            $attributes = new TrainerDexAttributes($content);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $this->trainerDexService->set($trainerExternalId, $dexSlug, $attributes);

        return new Response();
    }
}
```

- [ ] **Step 3: Verify syntax**

Run: `docker compose exec php php -l src/Controller/DexController.php`

Expected: `No syntax errors detected`.

---

### Task 5: Update DexControllerTest coverage annotations

**Files:**
- Modify: `tests/src/Integration/Controller/DexControllerTest.php`

- [ ] **Step 1: Add `/** @internal */` and `#[CoversClass]` for the new factory**

The top of the file currently reads:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexController;
use App\Tests\Common\Traits\GetterTrait\GetTrainerDexTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DexController::class)]
final class DexControllerTest extends AbstractTestControllerApi
```

Wait — verify whether `/** @internal */` is already present before editing. If it is, only add the two new lines below the existing `#[CoversClass(DexController::class)]`. If it is absent, add the full block.

Replace the opening of the file with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexController;
use App\Factory\TrainerDexResponseFactory;
use App\Tests\Common\Traits\GetterTrait\GetTrainerDexTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DexController::class)]
#[CoversClass(TrainerDexResponseFactory::class)]
final class DexControllerTest extends AbstractTestControllerApi
```

The rest of the file (all test methods) is unchanged — the JSON field names in the response are identical to before, so `DexControllerTestData` remains correct without modification.

- [ ] **Step 2: Run the integration tests to confirm they still pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/DexControllerTest.php`

Expected: all existing tests pass (testListUser12, testListUser12WithUnReleased, testListUser12WithPremium, testListUser12WithUnreleasedAndPremium, testListUser13, testListUserUnknown, testUpdate, testUpdateTrainerSlug, testCreate, testCreateWithMissingAttribute, testBadArgument, testEmptyData).

---

### Task 6: Run quality and coverage checks

**Files:**
- All files from previous tasks

- [ ] **Step 1: Run all tests**

Run: `make tests`

Expected: all unit + integration tests pass, 0 failures.

- [ ] **Step 2: Run code quality checks**

Run: `make quality`

Expected: PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint all green.

- [ ] **Step 3: Run coverage + mutation**

Run: `make measures`

Expected: 100% line coverage, 100% MSI, all checks green.

- [ ] **Step 4: Final validation — verify no other tests regress**

Run: `make tests-integration`

Expected: all integration tests pass.
