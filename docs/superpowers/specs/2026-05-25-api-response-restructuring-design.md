# API Response Restructuring — Design Spec

**Issue :** https://github.com/douzeEnsemble/pokenini-api/issues/256  
**Date :** 2026-05-25  
**Status :** Design (awaiting review and approval)

---

## Overview

Refactor API responses from flat, prefixed structures (`pokemon_slug`, `pokemon_name`, etc.) to nested, object-oriented structures using Symfony Serializer and custom DTOs. This improves code clarity, maintainability, and aligns the API with standard REST design principles.

**Scope :** Start with `GET /types` (simplest endpoint), then generalize pattern to Election/ELO and other endpoints progressively.

**Breaking Change :** Yes. Clients (`pokenini-back`, `pokenini-web`) will be updated after API is deployed.

---

## Architecture

### Data Flow

```
SQL Repository (returns flat data)
  ↓
Factory (transforms flat → nested DTOs)
  ↓
Symfony Serializer (DTOs → JSON with snake_case)
  ↓
JsonResponse (returns to client)
```

### Layers and Responsibilities

| Layer | Role | Change |
|-------|------|--------|
| **Repository** | Fetches raw data from SQL | No change — continues returning flat arrays |
| **Service** | Orchestrates business logic | No change — calls Repository as before |
| **Factory** | Transforms flat SQL rows → nested DTOs | **New layer** |
| **DTO (Response)** | Defines nested structure, immutable value object | **New classes** |
| **Controller** | HTTP endpoint | Modified: applies Factory + Serializer |
| **Serializer** | JSON serialization (built-in Symfony) | Configured to use DTOs, snake_case naming |

---

## Implementation Pattern

### Step 1 : Create Nested DTOs

**Example: TypeResponse (for simple `GET /types` endpoint)**

```php
// src/DTO/Response/TypeResponse.php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class TypeResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $french_name,
        public readonly string $color,
    ) {}
}
```

**Example: PokemonResponse (template for future complex endpoints like Election/ELO)**

```php
// src/DTO/Response/PokemonResponse.php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonResponse
{
    public function __construct(
        public readonly PokemonDataResponse $pokemon,
        public readonly ?FormsResponse $forms,
        public readonly TypesResponse $types,
        public readonly float $elo,
        public readonly bool $significance,
    ) {}
}

final class PokemonDataResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $french_name,
        public readonly int $national_dex_number,
        public readonly ?string $simplified_name,
        public readonly ?string $forms_label,
        public readonly ?string $simplified_french_name,
        public readonly ?string $forms_french_label,
        public readonly ?string $icon,
        public readonly int $family_order,
        public readonly ?string $family_lead_slug,
        public readonly ?string $original_game_bundle_slug,
        public readonly string $order_number,
    ) {}
}

final class FormsResponse
{
    public function __construct(
        public readonly ?FormResponse $category,
        public readonly ?FormResponse $regional,
        public readonly ?FormResponse $special,
        public readonly ?FormResponse $variant,
    ) {}
}

final class FormResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
    ) {}
}

final class TypesResponse
{
    public function __construct(
        public readonly ?TypeResponse $primary,
        public readonly ?TypeResponse $secondary,
    ) {}
}
```

**Naming convention :**
- `*Response` suffix for DTOs representing API responses
- Live in `src/DTO/Response/` namespace
- All properties `readonly` (immutable)
- Nested objects instead of flat prefixed names

**JSON Property Naming :**
- All JSON properties use `snake_case` (Symfony Serializer's default)
- DTO properties match JSON names in snake_case (e.g., `$french_name` → JSON: `"french_name"`)

### Step 2 : Create Factory Classes

**Example: TypeResponseFactory**

```php
// src/Factory/TypeResponseFactory.php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\TypeResponse;

final class TypeResponseFactory
{
    /**
     * Transform a single SQL row into TypeResponse DTO.
     *
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): TypeResponse
    {
        return new TypeResponse(
            slug: (string) $row['slug'],
            name: (string) $row['name'],
            french_name: (string) $row['french_name'],
            color: (string) $row['color'],
        );
    }

    /**
     * Transform multiple SQL rows into TypeResponse DTOs.
     *
     * @param array<array<string, mixed>> $rows
     * @return TypeResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
```

**Example: PokemonResponseFactory (for future complex endpoints)**

```php
// src/Factory/PokemonResponseFactory.php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\PokemonResponse;
use App\DTO\Response\TypeResponse;
use App\DTO\Response\TypesResponse;

final class PokemonResponseFactory
{
    /**
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): PokemonResponse
    {
        return new PokemonResponse(
            pokemon: self::buildPokemonData($row),
            forms: self::buildForms($row),
            types: self::buildTypes($row),
            elo: (float) $row['elo'],
            significance: (bool) $row['significance'],
        );
    }

    /**
     * @param array<array<string, mixed>> $rows
     * @return PokemonResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildPokemonData(array $row): PokemonDataResponse
    {
        return new PokemonDataResponse(
            slug: (string) $row['pokemon_slug'],
            name: (string) $row['pokemon_name'],
            french_name: (string) $row['pokemon_french_name'],
            national_dex_number: (int) $row['pokemon_national_dex_number'],
            simplified_name: $row['pokemon_simplified_name'] ? (string) $row['pokemon_simplified_name'] : null,
            forms_label: $row['pokemon_forms_label'] ? (string) $row['pokemon_forms_label'] : null,
            simplified_french_name: $row['pokemon_simplified_french_name'] ? (string) $row['pokemon_simplified_french_name'] : null,
            forms_french_label: $row['pokemon_forms_french_label'] ? (string) $row['pokemon_forms_french_label'] : null,
            icon: $row['pokemon_icon'] ? (string) $row['pokemon_icon'] : null,
            family_order: (int) $row['pokemon_family_order'],
            family_lead_slug: $row['family_lead_slug'] ? (string) $row['family_lead_slug'] : null,
            original_game_bundle_slug: $row['original_game_bundle_slug'] ? (string) $row['original_game_bundle_slug'] : null,
            order_number: (string) $row['pokemon_order_number'],
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForms(array $row): ?FormsResponse
    {
        $hasAnyForm = !empty($row['category_form_slug']) 
            || !empty($row['regional_form_slug'])
            || !empty($row['special_form_slug'])
            || !empty($row['variant_form_slug']);

        if (!$hasAnyForm) {
            return null;
        }

        return new FormsResponse(
            category: self::buildForm('category_form', $row),
            regional: self::buildForm('regional_form', $row),
            special: self::buildForm('special_form', $row),
            variant: self::buildForm('variant_form', $row),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForm(string $prefix, array $row): ?FormResponse
    {
        $slugKey = "{$prefix}_slug";
        $nameKey = "{$prefix}_name";

        if (empty($row[$slugKey])) {
            return null;
        }

        return new FormResponse(
            slug: (string) $row[$slugKey],
            name: (string) $row[$nameKey],
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildTypes(array $row): TypesResponse
    {
        return new TypesResponse(
            primary: self::buildType('primary_type', $row),
            secondary: self::buildType('secondary_type', $row),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildType(string $prefix, array $row): ?TypeResponse
    {
        $slugKey = "{$prefix}_slug";
        $nameKey = "{$prefix}_name";
        $frenchNameKey = "{$prefix}_french_name";

        if (empty($row[$slugKey])) {
            return null;
        }

        return new TypeResponse(
            slug: (string) $row[$slugKey],
            name: (string) $row[$nameKey],
            french_name: (string) $row[$frenchNameKey],
            color: '', // Note: color not available in current ELO SQL, may need adjustment
        );
    }
}
```

**Responsibility :**
- Single purpose: transform flat SQL rows → nested DTOs
- Stateless, static methods
- Type-safe conversions (cast to correct type)
- Handle nullability (optional forms, types)
- Testable in isolation

### Step 3 : Modify Controller

**Before :**
```php
#[Route(path: '', methods: ['GET'])]
public function get(TypesService $service): JsonResponse
{
    $types = $service->getAll();
    return new JsonResponse($types);
}
```

**After :**
```php
#[Route(path: '', methods: ['GET'])]
public function get(
    TypesService $service,
    SerializerInterface $serializer,
): JsonResponse {
    $types = $service->getAll();
    
    $responses = TypeResponseFactory::fromSqlRows($types);
    
    return JsonResponse::fromJsonString(
        $serializer->serialize($responses, 'json'),
    );
}
```

**Pattern :**
1. Get raw data from Service (unchanged)
2. Apply Factory to transform → DTOs
3. Use Serializer to convert → JSON with snake_case
4. Return JsonResponse

---

## Test Strategy

### Unit Tests

**New tests: Factory transformation**

File: `tests/src/Unit/Factory/TypeResponseFactoryTest.php`

Coverage: 100% of Factory methods
- `fromSqlRow()` — single row transformation
- `fromSqlRows()` — array transformation
- Type casting and null handling
- Edge cases (missing optional fields)

Example:
```php
#[CoversClass(TypeResponseFactory::class)]
final class TypeResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRow_transformsCorrectly(): void
    {
        $row = [
            'slug' => 'electric',
            'name' => 'Electric',
            'french_name' => 'Électrique',
            'color' => '#FFCC33',
        ];

        $response = TypeResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(TypeResponse::class, $response);
        self::assertSame('electric', $response->slug);
        self::assertSame('Electric', $response->name);
    }

    #[Test]
    public function fromSqlRows_transformsMultipleRows(): void
    {
        $rows = [/* ... */];
        $responses = TypeResponseFactory::fromSqlRows($rows);

        self::assertCount(count($rows), $responses);
        self::assertContainsOnly(TypeResponse::class, $responses);
    }
}
```

### Integration Tests

**Update existing: Controller response format**

File: `tests/src/Integration/Controller/TypesControllerTest.php` (update if exists, create if not)

Coverage: Verify complete response chain (Controller → Service → Repository → Factory → Serializer → JSON)

Example:
```php
#[CoversClass(TypesController::class)]
final class TypesControllerTest extends TestCase
{
    #[Test]
    public function get_returnsNestedTypeResponses(): void
    {
        $response = static::createClient()->request('GET', '/types');

        self::assertResponseIsSuccessful();
        
        $data = json_decode($response->getContent(), associative: true);
        
        self::assertIsArray($data);
        self::assertNotEmpty($data);
        
        $firstType = $data[0];
        self::assertArrayHasKey('slug', $firstType);
        self::assertArrayHasKey('name', $firstType);
        self::assertArrayHasKey('french_name', $firstType);
        self::assertArrayHasKey('color', $firstType);
    }
}
```

### Mock/Fixture Updates

**Update: `tests/resources/moco/Types/get.json`**

Moco HTTP mock server returns same structure as refactored API response (tests run against mocked endpoints).

---

## Client Migration Guide

### Documentation File

Create: `docs/api-migration/types-restructuring.md`

Content:
```markdown
# Types API — Response Structure Change

## Summary
Endpoint `GET /types` response structure changes from flat to nested objects.

## Impact
- **Clients affected :** pokenini-back, pokenini-web (if consuming directly)
- **Breaking :** Yes
- **Migration effort :** Minimal for pokenini-back (passthrough), minimal for pokenini-web (Twig rendering)

## Before

```json
[
  {
    "slug": "electric",
    "name": "Electric",
    "french_name": "Électrique",
    "color": "#FFCC33"
  }
]
```

## After

```json
[
  {
    "slug": "electric",
    "name": "Electric",
    "french_name": "Électrique",
    "color": "#FFCC33"
  }
]
```

**Note:** For simple endpoints like Types, structure is identical. For complex endpoints (Election/ELO), structure becomes nested with sub-objects.

## Timeline
- **API deployed :** [DATE]
- **Client updates required by :** [DATE + 1 week]
```

### For future complex endpoints (Election/ELO)

Document detailed before/after with full nested structure.

---

## Generalization Path

### Pattern applies to all endpoints:

1. **Identify endpoint** (e.g., Election/ELO)
2. **Design DTOs** following same structure (nested objects, `*Response` suffix)
3. **Create Factory** following same pattern (static methods, type-safe conversions)
4. **Update Controller** (apply Factory + Serializer)
5. **Write tests** (Factory unit + Controller integration)
6. **Document migration** (create `docs/api-migration/` file)
7. **Update Moco fixtures** (reflect new response structure)

### Suggested rollout order:

1. **Types** ← Start here (validates pattern, simplest endpoint)
2. **Election/ELO** (most complex, demonstrates scalability)
3. **Dex, Album, Report** (progressively, one per PR)

Each endpoint can be refactored independently.

---

## Quality Checklist

Before merging each refactoring PR:

- [ ] All DTOs created, immutable (`readonly`), properly namespaced
- [ ] Factory 100% unit test coverage (100% MSI required per project policy)
- [ ] Controller integration tests updated/created
- [ ] Moco fixtures updated to reflect new response structure
- [ ] `make quality` and `make measures` pass
- [ ] Migration documentation written and committed
- [ ] No breaking changes to non-refactored endpoints
- [ ] Snake_case JSON property names verified (Serializer output)

---

## Files to Create/Modify

### Types endpoint (immediate)

**Create:**
- `src/DTO/Response/TypeResponse.php`
- `src/Factory/TypeResponseFactory.php`
- `tests/src/Unit/Factory/TypeResponseFactoryTest.php`
- `tests/src/Integration/Controller/TypesControllerTest.php`
- `docs/api-migration/types-restructuring.md`

**Modify:**
- `src/Controller/TypesController.php`
- `tests/resources/moco/Types/get.json`

### Templates (for future endpoints)

**Create:**
- `src/DTO/Response/PokemonResponse.php`
- `src/DTO/Response/FormsResponse.php`
- `src/DTO/Response/TypesResponse.php`
- `src/Factory/PokemonResponseFactory.php`

(Later refactorings will follow same pattern)

---

## Architecture Notes

### Why DTOs + Factory (not other approaches)?

| Aspect | DTOs + Factory | Entities + Normalizers | Direct Array Transform |
|--------|---|---|---|
| **Type safety** | ✅ Strong | ✅ Strong | ❌ Weak (arrays) |
| **Scalability** | ✅ Excellent | ⚠️ N+1 query risk | ⚠️ Complex for nested |
| **Testing** | ✅ Isolated | ⚠️ Doctrine overhead | ❌ Less isolated |
| **Documentation** | ✅ DTO = spec | ⚠️ Entity ≠ response | ❌ No explicit structure |
| **Reusability** | ✅ Factory ≥ method | ✅ Normalizer ≥ method | ❌ Inline logic |

### Why Serializer?

- Standard in Symfony ecosystem
- Handles snake_case naming automatically (no manual conversion)
- Extensible for future needs (custom normalizers, context options)
- Separates concerns (DTO structure vs JSON rendering)

### Why immutable DTOs?

- Value objects (no side effects)
- Thread-safe (if async ever needed)
- Clear intent (data container, not mutable entity)
- Easier testing (no state mutations to track)

---

## Future Considerations

### Phase 2 (after Types validated)

Refactor Election/ELO using same pattern. This endpoint is complex enough to validate scalability:
- Multiple nested objects (Pokemon, Forms, Types)
- Optional sub-objects
- Numeric fields (elo, significance)
- SQL prefixes map to nested structure

### Phase 3+ (other endpoints)

Apply pattern to Dex, Album, Reports, etc. Each follows same steps.

### Potential enhancements (future)

- Add `@Groups` serialization (if some clients need filtered responses)
- Add versioning if API ever needs `/v2/` endpoints
- Add pagination metadata wrapper if needed
- Add error response DTOs following same pattern

---

## Acceptance Criteria

✅ Types endpoint refactored with nested DTOs  
✅ Factory classes created, 100% unit test coverage  
✅ Controller integration tests pass  
✅ `make quality` and `make measures` green  
✅ Moco fixtures updated  
✅ Migration documentation written  
✅ Pattern ready for generalization to other endpoints
