# API Response Restructuring (Pokemon Availabilities) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /debogage/pokemon/{slug}/availabilities` endpoint to use a proper Response DTO + Factory pattern instead of serializing a raw array of availability maps.

**Architecture:** Create an immutable `PokemonAvailabilitiesResponse` DTO, a `PokemonAvailabilitiesResponseFactory` that builds it from the four `*Availabilities` DTOs, and update the controller action to inject `SerializerInterface` as a method parameter and use the factory.

**Tech Stack:** Symfony 8, PHP 8.5, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/PokemonAvailabilitiesResponse.php` — immutable DTO wrapping the four availability maps
- `src/Factory/PokemonAvailabilitiesResponseFactory.php` — builds the DTO from the four `*Availabilities` value-objects
- `tests/src/Unit/Factory/PokemonAvailabilitiesResponseFactoryTest.php` — unit tests covering both factory and DTO

**Modify:**
- `src/Controller/Debug/DebugPokemonController.php` — update `pokemonAvailabilities()` to use factory + `JsonResponse::fromJsonString()`

---

## Tasks

### Task 1: Create PokemonAvailabilitiesResponse DTO

**Files:**
- Create: `src/DTO/Response/PokemonAvailabilitiesResponse.php`

- [ ] **Step 1: Create the DTO file with immutable properties**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonAvailabilitiesResponse
{
    /**
     * @param bool[] $gamesAvailabilities
     * @param bool[] $gamesShiniesAvailabilities
     * @param bool[] $gameBundlesAvailabilities
     * @param bool[] $gameBundlesShiniesAvailabilities
     */
    public function __construct(
        public readonly array $gamesAvailabilities,
        public readonly array $gamesShiniesAvailabilities,
        public readonly array $gameBundlesAvailabilities,
        public readonly array $gameBundlesShiniesAvailabilities,
    ) {}
}
```

Save as `src/DTO/Response/PokemonAvailabilitiesResponse.php`.

- [ ] **Step 2: Verify the file is in the correct location**

Run: `ls -la src/DTO/Response/PokemonAvailabilitiesResponse.php`

Expected: File exists with readable permissions.

---

### Task 2: Create PokemonAvailabilitiesResponseFactory

**Files:**
- Create: `src/Factory/PokemonAvailabilitiesResponseFactory.php`

- [ ] **Step 1: Create the Factory with a static method**

The factory receives the four `*Availabilities` value-objects (from `App\DTO\`) and calls `.all()` on each to get the underlying `bool[]` map.

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\DTO\GamesAvailabilities;
use App\DTO\GamesShiniesAvailabilities;
use App\DTO\Response\PokemonAvailabilitiesResponse;

final class PokemonAvailabilitiesResponseFactory
{
    public static function fromAvailabilities(
        GamesAvailabilities $gamesAvailabilities,
        GamesShiniesAvailabilities $gamesShiniesAvailabilities,
        GameBundlesAvailabilities $gameBundlesAvailabilities,
        GameBundlesShiniesAvailabilities $gameBundlesShiniesAvailabilities,
    ): PokemonAvailabilitiesResponse {
        return new PokemonAvailabilitiesResponse(
            gamesAvailabilities: $gamesAvailabilities->all(),
            gamesShiniesAvailabilities: $gamesShiniesAvailabilities->all(),
            gameBundlesAvailabilities: $gameBundlesAvailabilities->all(),
            gameBundlesShiniesAvailabilities: $gameBundlesShiniesAvailabilities->all(),
        );
    }
}
```

Save as `src/Factory/PokemonAvailabilitiesResponseFactory.php`.

- [ ] **Step 2: Verify the file is in the correct location**

Run: `ls -la src/Factory/PokemonAvailabilitiesResponseFactory.php`

Expected: File exists with readable permissions.

---

### Task 3: Write unit tests for PokemonAvailabilitiesResponseFactory (and DTO)

**Files:**
- Create: `tests/src/Unit/Factory/PokemonAvailabilitiesResponseFactoryTest.php`
- Test: `PokemonAvailabilitiesResponseFactory` and `PokemonAvailabilitiesResponse`

Each field in the DTO maps to one specific `->all()` call. Tests use **distinct keys per availability type** so that any swap between fields (a likely mutation) is immediately caught.

- [ ] **Step 1: Create the unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\DTO\GamesAvailabilities;
use App\DTO\GamesShiniesAvailabilities;
use App\DTO\Response\PokemonAvailabilitiesResponse;
use App\Factory\PokemonAvailabilitiesResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonAvailabilitiesResponseFactory::class)]
#[CoversClass(PokemonAvailabilitiesResponse::class)]
final class PokemonAvailabilitiesResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromAvailabilitiesBuildsResponseWithCorrectFieldMapping(): void
    {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            new GamesAvailabilities(['games-key' => true]),
            new GamesShiniesAvailabilities(['shinies-key' => false]),
            new GameBundlesAvailabilities(['bundles-key' => true]),
            new GameBundlesShiniesAvailabilities(['bundlesshinies-key' => false]),
        );

        self::assertInstanceOf(PokemonAvailabilitiesResponse::class, $response);
        self::assertSame(['games-key' => true], $response->gamesAvailabilities);
        self::assertSame(['shinies-key' => false], $response->gamesShiniesAvailabilities);
        self::assertSame(['bundles-key' => true], $response->gameBundlesAvailabilities);
        self::assertSame(['bundlesshinies-key' => false], $response->gameBundlesShiniesAvailabilities);
    }

    #[Test]
    public function fromAvailabilitiesHandlesEmptyAvailabilities(): void
    {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            new GamesAvailabilities([]),
            new GamesShiniesAvailabilities([]),
            new GameBundlesAvailabilities([]),
            new GameBundlesShiniesAvailabilities([]),
        );

        self::assertSame([], $response->gamesAvailabilities);
        self::assertSame([], $response->gamesShiniesAvailabilities);
        self::assertSame([], $response->gameBundlesAvailabilities);
        self::assertSame([], $response->gameBundlesShiniesAvailabilities);
    }
}
```

Save as `tests/src/Unit/Factory/PokemonAvailabilitiesResponseFactoryTest.php`.

- [ ] **Step 2: Verify the file is in the correct location**

Run: `ls -la tests/src/Unit/Factory/PokemonAvailabilitiesResponseFactoryTest.php`

Expected: File exists with readable permissions.

---

### Task 4: Update DebugPokemonController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/Debug/DebugPokemonController.php`

The current `pokemonAvailabilities()` builds a raw array and passes it to `$this->serialize()` (from `AbstractDebugController`). After the migration, it creates a `PokemonAvailabilitiesResponse` via the factory and uses `JsonResponse::fromJsonString()` — matching the pattern of all other migrated controllers.

The JSON output structure is **unchanged**: the four property names (`gamesAvailabilities`, `gamesShiniesAvailabilities`, `gameBundlesAvailabilities`, `gameBundlesShiniesAvailabilities`) match the current array keys, so no client change is required.

`SerializerInterface` is injected as a **method parameter** (Symfony resolves it automatically). The parent class `AbstractDebugController` keeps its constructor injection unchanged — it is still needed by the `dex()` and `pokemon()` methods that serialize raw Doctrine entities.

- [ ] **Step 1: Read the current controller**

Current content of `src/Controller/Debug/DebugPokemonController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\Entity\Pokemon;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/debogage/pokemon')]
final class DebugPokemonController extends AbstractDebugController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    public function pokemon(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): Response {
        return new Response(
            $this->serialize($pokemon),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }

    #[Route(path: '/{slug}/caches', methods: ['DELETE'])]
    public function pokemonCaches(
        GamesAvailabilitiesService $gamesAvailabilitiesService,
        GamesShiniesAvailabilitiesService $gamesShiniesAvailabilitiesService,
        GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
        CollectionsAvailabilitiesService $collectionsAvailabilitiesService,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): Response {
        $gamesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gamesShiniesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gameBundlesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gameBundlesShiniesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $collectionsAvailabilitiesService->cleanCacheFromPokemon($pokemon);

        return new Response();
    }

    #[Route(path: '/{slug}/availabilities', methods: ['GET'])]
    public function pokemonAvailabilities(
        GamesAvailabilitiesService $gamesAvailabilitiesService,
        GamesShiniesAvailabilitiesService $gamesShiniesAvailabilitiesService,
        GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): Response {
        $gamesAvailabilities = $gamesAvailabilitiesService->getFromPokemon($pokemon);
        $gamesShiniesAvailabilities = $gamesShiniesAvailabilitiesService->getFromPokemon($pokemon);
        $gameBundlesAvailabilities = $gameBundlesAvailabilitiesService->getFromPokemon($pokemon);
        $gameBundlesShiniesAvailabilities = $gameBundlesShiniesAvailabilitiesService->getFromPokemon($pokemon);

        return new Response(
            $this->serialize([
                'gamesAvailabilities' => $gamesAvailabilities->all(),
                'gamesShiniesAvailabilities' => $gamesShiniesAvailabilities->all(),
                'gameBundlesAvailabilities' => $gameBundlesAvailabilities->all(),
                'gameBundlesShiniesAvailabilities' => $gameBundlesShiniesAvailabilities->all(),
            ]),
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

use App\Entity\Pokemon;
use App\Factory\PokemonAvailabilitiesResponseFactory;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/debogage/pokemon')]
final class DebugPokemonController extends AbstractDebugController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    public function pokemon(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): Response {
        return new Response(
            $this->serialize($pokemon),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }

    #[Route(path: '/{slug}/caches', methods: ['DELETE'])]
    public function pokemonCaches(
        GamesAvailabilitiesService $gamesAvailabilitiesService,
        GamesShiniesAvailabilitiesService $gamesShiniesAvailabilitiesService,
        GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
        CollectionsAvailabilitiesService $collectionsAvailabilitiesService,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): Response {
        $gamesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gamesShiniesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gameBundlesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gameBundlesShiniesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $collectionsAvailabilitiesService->cleanCacheFromPokemon($pokemon);

        return new Response();
    }

    #[Route(path: '/{slug}/availabilities', methods: ['GET'])]
    public function pokemonAvailabilities(
        GamesAvailabilitiesService $gamesAvailabilitiesService,
        GamesShiniesAvailabilitiesService $gamesShiniesAvailabilitiesService,
        GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
        SerializerInterface $serializer,
    ): JsonResponse {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            $gamesAvailabilitiesService->getFromPokemon($pokemon),
            $gamesShiniesAvailabilitiesService->getFromPokemon($pokemon),
            $gameBundlesAvailabilitiesService->getFromPokemon($pokemon),
            $gameBundlesShiniesAvailabilitiesService->getFromPokemon($pokemon),
        );

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }
}
```

Save this as `src/Controller/Debug/DebugPokemonController.php`.

- [ ] **Step 3: Verify the controller file is syntactically correct**

Run: `docker compose exec php php -l src/Controller/Debug/DebugPokemonController.php`

Expected: "No syntax errors detected".

---

### Task 5: Verify integration tests still pass

**Files:**
- Read: `tests/src/Integration/Controller/Debug/DebugPokemonControllerTest.php`

The JSON structure is unchanged (property names match the old array keys), so no test modification is needed. The existing integration tests cover all four assertions on the JSON shape.

- [ ] **Step 1: Confirm the integration test file needs no changes**

The existing `testPokemonAvailabilities()` checks these keys:
- `gamesAvailabilities` → maps to `PokemonAvailabilitiesResponse::$gamesAvailabilities`
- `gamesShiniesAvailabilities` → maps to `PokemonAvailabilitiesResponse::$gamesShiniesAvailabilities`
- `gameBundlesAvailabilities` → maps to `PokemonAvailabilitiesResponse::$gameBundlesAvailabilities`
- `gameBundlesShiniesAvailabilities` → maps to `PokemonAvailabilitiesResponse::$gameBundlesShiniesAvailabilities`

All four property names are identical to the current array keys, so the JSON output is byte-for-byte compatible with the existing test assertions.

No file modification required.

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
- ✅ `PokemonAvailabilitiesResponse` DTO created (Task 1)
- ✅ `PokemonAvailabilitiesResponseFactory` created (Task 2)
- ✅ Unit tests covering both DTO and Factory (Task 3, two test cases with distinct keys)
- ✅ Controller updated to use factory + `JsonResponse::fromJsonString()` (Task 4)
- ✅ JSON output is byte-compatible — integration tests require no changes (Task 5)
- ✅ Deptrac compliance: Factory only depends on `AppDTO`; Controller can use `AppFactory`

**Placeholder scan:** No TBDs, no incomplete steps.

**Type consistency:**
- `PokemonAvailabilitiesResponseFactory::fromAvailabilities()` returns `PokemonAvailabilitiesResponse` ✓
- `DebugPokemonController::pokemonAvailabilities()` returns `JsonResponse` ✓
- Factory test uses exact same class names as factory file ✓
