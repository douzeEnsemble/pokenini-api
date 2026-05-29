# API Response Restructuring (Election/ELO) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `GET /election/top` endpoint from flat JSON with prefixed fields to nested object-oriented structure using DTOs + Factory + Serializer pattern.

**Architecture:** Create immutable response DTOs for Pokemon, Forms, and Types; a Factory to transform flat SQL rows into nested DTOs; update the Controller to apply the transformation before serialization.

**Tech Stack:** Symfony 8, PHP 8.5, Doctrine DBAL, Symfony Serializer

---

## File Structure

**Create:**
- `src/DTO/Response/ElectionEloResponse.php` — top-level response DTO containing nested objects
- `src/DTO/Response/PokemonDataResponse.php` — immutable DTO for Pokemon base data
- `src/DTO/Response/FormsResponse.php` — container for optional form types
- `src/DTO/Response/FormResponse.php` — single form (category, regional, special, variant)
- `src/DTO/Response/TypesResponse.php` — container for primary/secondary types
- `src/Factory/ElectionEloResponseFactory.php` — transforms flat SQL rows → nested DTOs
- `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php` — unit tests for Factory
- `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php` — integration tests for Controller
- `docs/api-migration/election-elo-restructuring.md` — client migration documentation

**Modify:**
- `src/Controller/TrainerPokemonEloController.php` — apply Factory + Serializer to `top()` method
- `tests/resources/moco/ElectionElo/top.json` — update mock response structure

---

## Tasks

### Task 1: Create ElectionEloResponse DTO (top-level)

**Files:**
- Create: `src/DTO/Response/ElectionEloResponse.php`

- [ ] **Step 1: Create the top-level DTO file with nested properties**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionEloResponse
{
    public function __construct(
        public readonly PokemonDataResponse $pokemon,
        public readonly ?FormsResponse $forms,
        public readonly TypesResponse $types,
        public readonly float $elo,
        public readonly bool $significance,
    ) {}
}
```

Save this as `src/DTO/Response/ElectionEloResponse.php`.

- [ ] **Step 2: Verify the file exists and is readable**

Run: `ls -la src/DTO/Response/ElectionEloResponse.php`

Expected: File exists with readable permissions.

---

### Task 2: Create PokemonDataResponse DTO

**Files:**
- Create: `src/DTO/Response/PokemonDataResponse.php`

- [ ] **Step 1: Create the Pokemon data DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

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
```

Save this as `src/DTO/Response/PokemonDataResponse.php`.

- [ ] **Step 2: Verify the file exists and is readable**

Run: `ls -la src/DTO/Response/PokemonDataResponse.php`

Expected: File exists with readable permissions.

---

### Task 3: Create FormsResponse and FormResponse DTOs

**Files:**
- Create: `src/DTO/Response/FormsResponse.php`
- Create: `src/DTO/Response/FormResponse.php`

- [ ] **Step 1: Create FormResponse (single form)**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class FormResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
    ) {}
}
```

Save this as `src/DTO/Response/FormResponse.php`.

- [ ] **Step 2: Create FormsResponse (container)**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class FormsResponse
{
    public function __construct(
        public readonly ?FormResponse $category,
        public readonly ?FormResponse $regional,
        public readonly ?FormResponse $special,
        public readonly ?FormResponse $variant,
    ) {}
}
```

Save this as `src/DTO/Response/FormsResponse.php`.

- [ ] **Step 3: Verify both files exist**

Run: `ls -la src/DTO/Response/FormsResponse.php src/DTO/Response/FormResponse.php`

Expected: Both files exist with readable permissions.

---

### Task 4: Create TypesResponse DTO

**Files:**
- Create: `src/DTO/Response/TypesResponse.php`

- [ ] **Step 1: Create the Types container DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class TypesResponse
{
    public function __construct(
        public readonly ?TypeResponse $primary,
        public readonly ?TypeResponse $secondary,
    ) {}
}
```

Save this as `src/DTO/Response/TypesResponse.php`.

- [ ] **Step 2: Verify the file exists**

Run: `ls -la src/DTO/Response/TypesResponse.php`

Expected: File exists with readable permissions.

---

### Task 5: Create ElectionEloResponseFactory

**Files:**
- Create: `src/Factory/ElectionEloResponseFactory.php`

- [ ] **Step 1: Create the Factory class**

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ElectionEloResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\TypeResponse;
use App\DTO\Response\TypesResponse;

final class ElectionEloResponseFactory
{
    /**
     * Transform a single SQL row into ElectionEloResponse DTO.
     *
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): ElectionEloResponse
    {
        return new ElectionEloResponse(
            pokemon: self::buildPokemonData($row),
            forms: self::buildForms($row),
            types: self::buildTypes($row),
            elo: (float) $row['elo'],
            significance: (bool) $row['significance'],
        );
    }

    /**
     * Transform multiple SQL rows into ElectionEloResponse DTOs.
     *
     * @param array<array<string, mixed>> $rows
     * @return ElectionEloResponse[]
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
            color: '', // Note: color not available in ELO SQL
        );
    }
}
```

Save this as `src/Factory/ElectionEloResponseFactory.php`.

- [ ] **Step 2: Verify the file exists**

Run: `ls -la src/Factory/ElectionEloResponseFactory.php`

Expected: File exists with readable permissions.

---

### Task 6: Write unit tests for ElectionEloResponseFactory

**Files:**
- Create: `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php`

- [ ] **Step 1: Create unit test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ElectionEloResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\TypesResponse;
use App\Factory\ElectionEloResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ElectionEloResponseFactory::class)]
final class ElectionEloResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRow_transformsSingleRowCorrectly(): void
    {
        $row = [
            'elo' => 1200.5,
            'significance' => true,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'pichu',
            'original_game_bundle_slug' => 'red-blue',
            'pokemon_order_number' => '9999-0025-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(ElectionEloResponse::class, $response);
        self::assertSame(1200.5, $response->elo);
        self::assertTrue($response->significance);
        self::assertInstanceOf(PokemonDataResponse::class, $response->pokemon);
        self::assertSame('pikachu', $response->pokemon->slug);
        self::assertSame('Pikachu', $response->pokemon->name);
        self::assertNull($response->forms);
        self::assertInstanceOf(TypesResponse::class, $response->types);
        self::assertNotNull($response->types->primary);
        self::assertSame('electric', $response->types->primary->slug);
        self::assertNull($response->types->secondary);
    }

    #[Test]
    public function fromSqlRow_handlesFormsWhenPresent(): void
    {
        $row = [
            'elo' => 1500.0,
            'significance' => false,
            'pokemon_slug' => 'rotom',
            'pokemon_name' => 'Rotom',
            'pokemon_french_name' => 'Motisma',
            'pokemon_national_dex_number' => 479,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => 'Heat Rotom',
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => 'Motisma Chaleur',
            'pokemon_icon' => 'rotom.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'rotom',
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0479-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'special_form_slug' => 'heat',
            'special_form_name' => 'Heat',
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'secondary_type_slug' => 'fire',
            'secondary_type_name' => 'Fire',
            'secondary_type_french_name' => 'Feu',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertNull($response->forms->category);
        self::assertNull($response->forms->regional);
        self::assertInstanceOf(FormResponse::class, $response->forms->special);
        self::assertSame('heat', $response->forms->special->slug);
        self::assertSame('Heat', $response->forms->special->name);
        self::assertNull($response->forms->variant);
        self::assertNotNull($response->types->secondary);
        self::assertSame('fire', $response->types->secondary->slug);
    }

    #[Test]
    public function fromSqlRow_castsBooleanSignificanceCorrectly(): void
    {
        $row = [
            'elo' => 1200.0,
            'significance' => 0,
            'pokemon_slug' => 'bulbasaur',
            'pokemon_name' => 'Bulbasaur',
            'pokemon_french_name' => 'Bulbizarre',
            'pokemon_national_dex_number' => 1,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'bulbasaur.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'bulbasaur',
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0001-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'primary_type_slug' => 'grass',
            'primary_type_name' => 'Grass',
            'primary_type_french_name' => 'Plante',
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertFalse($response->significance);
    }

    #[Test]
    public function fromSqlRows_transformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'elo' => 1200.0,
                'significance' => true,
                'pokemon_slug' => 'pikachu',
                'pokemon_name' => 'Pikachu',
                'pokemon_french_name' => 'Pikachu',
                'pokemon_national_dex_number' => 25,
                'pokemon_simplified_name' => null,
                'pokemon_forms_label' => null,
                'pokemon_simplified_french_name' => null,
                'pokemon_forms_french_label' => null,
                'pokemon_icon' => 'pikachu.png',
                'pokemon_family_order' => 1,
                'family_lead_slug' => 'pichu',
                'original_game_bundle_slug' => null,
                'pokemon_order_number' => '9999-0025-001',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'primary_type_slug' => 'electric',
                'primary_type_name' => 'Electric',
                'primary_type_french_name' => 'Électrique',
                'secondary_type_slug' => null,
                'secondary_type_name' => null,
                'secondary_type_french_name' => null,
            ],
            [
                'elo' => 1150.0,
                'significance' => false,
                'pokemon_slug' => 'charizard',
                'pokemon_name' => 'Charizard',
                'pokemon_french_name' => 'Dracaufeu',
                'pokemon_national_dex_number' => 6,
                'pokemon_simplified_name' => null,
                'pokemon_forms_label' => null,
                'pokemon_simplified_french_name' => null,
                'pokemon_forms_french_label' => null,
                'pokemon_icon' => 'charizard.png',
                'pokemon_family_order' => 3,
                'family_lead_slug' => 'charmander',
                'original_game_bundle_slug' => null,
                'pokemon_order_number' => '9999-0006-001',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => 'galar',
                'regional_form_name' => 'Galar',
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'primary_type_slug' => 'fire',
                'primary_type_name' => 'Fire',
                'primary_type_french_name' => 'Feu',
                'secondary_type_slug' => 'flying',
                'secondary_type_name' => 'Flying',
                'secondary_type_french_name' => 'Vol',
            ],
        ];

        $responses = ElectionEloResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnly(ElectionEloResponse::class, $responses);
        self::assertSame('pikachu', $responses[0]->pokemon->slug);
        self::assertSame('charizard', $responses[1]->pokemon->slug);
        self::assertSame(1200.0, $responses[0]->elo);
        self::assertSame(1150.0, $responses[1]->elo);
    }

    #[Test]
    public function fromSqlRows_handlesEmptyArray(): void
    {
        $responses = ElectionEloResponseFactory::fromSqlRows([]);

        self::assertIsArray($responses);
        self::assertCount(0, $responses);
    }

    #[Test]
    public function fromSqlRow_castsNumericFieldsCorrectly(): void
    {
        $row = [
            'elo' => '1350.75',
            'significance' => true,
            'pokemon_slug' => 'alakazam',
            'pokemon_name' => 'Alakazam',
            'pokemon_french_name' => 'Alakazam',
            'pokemon_national_dex_number' => '65',
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'alakazam.png',
            'pokemon_family_order' => '4',
            'family_lead_slug' => 'abra',
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0065-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'primary_type_slug' => 'psychic',
            'primary_type_name' => 'Psychic',
            'primary_type_french_name' => 'Psy',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame(1350.75, $response->elo);
        self::assertIsFloat($response->elo);
        self::assertSame(65, $response->pokemon->national_dex_number);
        self::assertIsInt($response->pokemon->national_dex_number);
        self::assertSame(4, $response->pokemon->family_order);
        self::assertIsInt($response->pokemon->family_order);
    }
}
```

Save this as `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php`.

- [ ] **Step 2: Run the unit tests to verify they pass**

Run: `make tests-unit --filter ElectionEloResponseFactoryTest`

Expected: 6 tests pass, 0 failures.

- [ ] **Step 3: Verify 100% code coverage for the Factory**

Run: `make coverage --filter ElectionEloResponseFactoryTest`

Expected: ElectionEloResponseFactory has 100% line and branch coverage.

---

### Task 7: Update TrainerPokemonEloController to use Factory + Serializer

**Files:**
- Modify: `src/Controller/TrainerPokemonEloController.php`

- [ ] **Step 1: Read the current controller**

Current content of `src/Controller/TrainerPokemonEloController.php` (top() method only):

```php
#[Route(path: '/top', methods: ['GET'])]
public function top(
    Request $request,
    TrainerPokemonEloRepository $trainerPokemonEloRepository,
): JsonResponse {
    /** @var array<int|string> $params */
    $params = $request->query->all();
    $queryOptions = new TrainerPokemonEloQueryOptions($params);

    // Better with serializer ?
    return new JsonResponse(
        $trainerPokemonEloRepository->getTopN(
            $queryOptions->trainerExternalId,
            $queryOptions->dexSlug,
            $queryOptions->electionSlug,
            $queryOptions->count,
        )
    );
}
```

- [ ] **Step 2: Update the top() method to use Factory and Serializer**

Replace the `top()` method with:

```php
#[Route(path: '/top', methods: ['GET'])]
public function top(
    Request $request,
    TrainerPokemonEloRepository $trainerPokemonEloRepository,
    SerializerInterface $serializer,
): JsonResponse {
    /** @var array<int|string> $params */
    $params = $request->query->all();
    $queryOptions = new TrainerPokemonEloQueryOptions($params);

    $rows = $trainerPokemonEloRepository->getTopN(
        $queryOptions->trainerExternalId,
        $queryOptions->dexSlug,
        $queryOptions->electionSlug,
        $queryOptions->count,
    );

    $responses = ElectionEloResponseFactory::fromSqlRows($rows);

    return JsonResponse::fromJsonString(
        $serializer->serialize($responses, 'json'),
    );
}
```

Also add the import at the top of the file:

```php
use App\Factory\ElectionEloResponseFactory;
use Symfony\Component\Serializer\SerializerInterface;
```

- [ ] **Step 3: Verify the controller file is syntactically correct**

Run: `make sh -c "php -l src/Controller/TrainerPokemonEloController.php"`

Expected: "No syntax errors detected".

---

### Task 8: Create integration test for TrainerPokemonEloController

**Files:**
- Create: `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`

- [ ] **Step 1: Create integration test file**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(\App\Controller\TrainerPokemonEloController::class)]
final class TrainerPokemonEloControllerTest extends WebTestCase
{
    #[Test]
    public function top_returnsSuccessfulJsonResponse(): void
    {
        $client = static::createClient();
        $response = $client->request(
            'GET',
            '/election/top?trainer_external_id=test_trainer&dex_slug=living_dex&election_slug=default&count=5'
        );

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function top_returnsArrayOfResponses(): void
    {
        $client = static::createClient();
        $response = $client->request(
            'GET',
            '/election/top?trainer_external_id=test_trainer&dex_slug=living_dex&election_slug=default&count=5'
        );

        $data = json_decode($response->getContent(), associative: true);

        self::assertIsArray($data);
        self::assertNotEmpty($data);
    }

    #[Test]
    public function top_eachResponseHasRequiredNestedStructure(): void
    {
        $client = static::createClient();
        $response = $client->request(
            'GET',
            '/election/top?trainer_external_id=test_trainer&dex_slug=living_dex&election_slug=default&count=5'
        );

        $data = json_decode($response->getContent(), associative: true);
        $firstResponse = $data[0];

        // Top-level fields
        self::assertArrayHasKey('elo', $firstResponse);
        self::assertArrayHasKey('significance', $firstResponse);

        // Pokemon nested object
        self::assertArrayHasKey('pokemon', $firstResponse);
        self::assertIsArray($firstResponse['pokemon']);
        self::assertArrayHasKey('slug', $firstResponse['pokemon']);
        self::assertArrayHasKey('name', $firstResponse['pokemon']);
        self::assertArrayHasKey('french_name', $firstResponse['pokemon']);
        self::assertArrayHasKey('national_dex_number', $firstResponse['pokemon']);

        // Types nested object
        self::assertArrayHasKey('types', $firstResponse);
        self::assertIsArray($firstResponse['types']);
        self::assertArrayHasKey('primary', $firstResponse['types']);
        self::assertArrayHasKey('secondary', $firstResponse['types']);
    }

    #[Test]
    public function top_pokemonFieldsAreCorrectTypes(): void
    {
        $client = static::createClient();
        $response = $client->request(
            'GET',
            '/election/top?trainer_external_id=test_trainer&dex_slug=living_dex&election_slug=default&count=5'
        );

        $data = json_decode($response->getContent(), associative: true);
        $firstResponse = $data[0];

        self::assertIsString($firstResponse['pokemon']['slug']);
        self::assertIsString($firstResponse['pokemon']['name']);
        self::assertIsString($firstResponse['pokemon']['french_name']);
        self::assertIsInt($firstResponse['pokemon']['national_dex_number']);
        self::assertIsFloat($firstResponse['elo']);
        self::assertIsBool($firstResponse['significance']);
    }

    #[Test]
    public function top_primaryTypeHasCorrectStructure(): void
    {
        $client = static::createClient();
        $response = $client->request(
            'GET',
            '/election/top?trainer_external_id=test_trainer&dex_slug=living_dex&election_slug=default&count=5'
        );

        $data = json_decode($response->getContent(), associative: true);
        $firstResponse = $data[0];

        if (null !== $firstResponse['types']['primary']) {
            self::assertArrayHasKey('slug', $firstResponse['types']['primary']);
            self::assertArrayHasKey('name', $firstResponse['types']['primary']);
            self::assertArrayHasKey('french_name', $firstResponse['types']['primary']);
            self::assertIsString($firstResponse['types']['primary']['slug']);
            self::assertIsString($firstResponse['types']['primary']['name']);
            self::assertIsString($firstResponse['types']['primary']['french_name']);
        }
    }

    #[Test]
    public function top_formsCanBeNullOrObject(): void
    {
        $client = static::createClient();
        $response = $client->request(
            'GET',
            '/election/top?trainer_external_id=test_trainer&dex_slug=living_dex&election_slug=default&count=5'
        );

        $data = json_decode($response->getContent(), associative: true);

        foreach ($data as $eloResponse) {
            // Forms can be null or an object with optional form types
            if (null !== $eloResponse['forms']) {
                self::assertIsArray($eloResponse['forms']);
                self::assertArrayHasKey('category', $eloResponse['forms']);
                self::assertArrayHasKey('regional', $eloResponse['forms']);
                self::assertArrayHasKey('special', $eloResponse['forms']);
                self::assertArrayHasKey('variant', $eloResponse['forms']);
            }
        }
    }
}
```

Save this as `tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php`.

- [ ] **Step 2: Run integration tests**

Run: `make ti --filter TrainerPokemonEloControllerTest`

Expected: 6 tests pass, 0 failures.

- [ ] **Step 3: Verify integration tests are included in coverage**

Run: `make coverage --filter TrainerPokemonEloControllerTest`

Expected: TrainerPokemonEloController and ElectionEloResponseFactory have 100% line coverage.

---

### Task 9: Update Moco mock fixture for GET /election/top

**Files:**
- Modify: `tests/resources/moco/ElectionElo/top.json`

- [ ] **Step 1: Check if Moco fixture directory exists**

Run: `ls -la tests/resources/moco/ElectionElo/`

If directory doesn't exist, create it:

```bash
mkdir -p tests/resources/moco/ElectionElo/
```

- [ ] **Step 2: Create or update the Moco fixture with nested structure**

Create/update `tests/resources/moco/ElectionElo/top.json` with:

```json
[
  {
    "elo": 1250.5,
    "significance": true,
    "pokemon": {
      "slug": "pikachu",
      "name": "Pikachu",
      "french_name": "Pikachu",
      "national_dex_number": 25,
      "simplified_name": null,
      "forms_label": null,
      "simplified_french_name": null,
      "forms_french_label": null,
      "icon": "pikachu.png",
      "family_order": 1,
      "family_lead_slug": "pichu",
      "original_game_bundle_slug": "red-blue",
      "order_number": "9999-0025-001"
    },
    "forms": null,
    "types": {
      "primary": {
        "slug": "electric",
        "name": "Electric",
        "french_name": "Électrique",
        "color": "#FFCC33"
      },
      "secondary": null
    }
  },
  {
    "elo": 1200.0,
    "significance": false,
    "pokemon": {
      "slug": "charizard",
      "name": "Charizard",
      "french_name": "Dracaufeu",
      "national_dex_number": 6,
      "simplified_name": null,
      "forms_label": null,
      "simplified_french_name": null,
      "forms_french_label": null,
      "icon": "charizard.png",
      "family_order": 3,
      "family_lead_slug": "charmander",
      "original_game_bundle_slug": null,
      "order_number": "9999-0006-001"
    },
    "forms": {
      "category": null,
      "regional": {
        "slug": "galar",
        "name": "Galar"
      },
      "special": null,
      "variant": null
    },
    "types": {
      "primary": {
        "slug": "fire",
        "name": "Fire",
        "french_name": "Feu",
        "color": "#F08030"
      },
      "secondary": {
        "slug": "flying",
        "name": "Flying",
        "french_name": "Vol",
        "color": "#A890F0"
      }
    }
  }
]
```

Save this as `tests/resources/moco/ElectionElo/top.json`.

- [ ] **Step 3: Verify fixture is valid JSON**

Run: `make sh -c "php -r 'json_decode(file_get_contents(\"tests/resources/moco/ElectionElo/top.json\"), true); echo \"JSON valid\";'"`

Expected: "JSON valid".

- [ ] **Step 4: Run integration tests with Moco**

Run: `make integration --filter TrainerPokemonEloControllerTest`

Expected: Integration tests pass with mocked response.

---

### Task 10: Run full quality checks

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

Expected: Code coverage ≥ 100% for new code, MSI ≥ 100%, all checks green.

- [ ] **Step 4: Verify no regressions in existing tests**

Run: `make tests-integration`

Expected: All integration tests pass, including existing ones (not just TrainerPokemonEloController).

---

### Task 11: Create client migration documentation

**Files:**
- Create: `docs/api-migration/election-elo-restructuring.md`

- [ ] **Step 1: Create migration documentation**

```markdown
# Election/ELO API — Response Structure Migration

**Endpoint:** `GET /election/top`  
**Version:** v1 (no versioning needed for this endpoint at this time)  
**Change type:** Breaking change  
**Status:** Live as of [DATE]

## Summary

The `GET /election/top` response structure has been refactored from a flat, prefixed object model to a nested, object-oriented structure. Data is now organized into distinct objects (pokemon, forms, types) rather than flat fields with prefixes (pokemon_slug, pokemon_name, etc.), improving API clarity and maintainability.

## Impact Assessment

### pokenini-back

**Current usage:** Calls `GET /election/top`, passes response through to clients.

**Change required:** None. Response remains a JSON array of ELO objects, fields are now nested.

**Testing:** Verify passthrough still works. Update schema if using schema validation.

### pokenini-web

**Current usage:** Calls `GET /election/top` via pokenini-back, renders in Twig templates.

**Change required:** Update Twig templates to access nested properties instead of flat fields.

**Before:**
```twig
{{ elo.pokemon_slug }}
{{ elo.pokemon_name }}
{{ elo.primary_type_slug }}
```

**After:**
```twig
{{ elo.pokemon.slug }}
{{ elo.pokemon.name }}
{{ elo.types.primary.slug }}
```

**Testing:** Verify template rendering produces correct output.

## Response Comparison

### Before (flat structure with prefixes)

```json
[
  {
    "elo": 1250.5,
    "significance": true,
    "pokemon_slug": "pikachu",
    "pokemon_name": "Pikachu",
    "pokemon_french_name": "Pikachu",
    "pokemon_national_dex_number": 25,
    "pokemon_simplified_name": null,
    "pokemon_forms_label": null,
    "pokemon_simplified_french_name": null,
    "pokemon_forms_french_label": null,
    "pokemon_icon": "pikachu.png",
    "pokemon_family_order": 1,
    "family_lead_slug": "pichu",
    "original_game_bundle_slug": "red-blue",
    "pokemon_order_number": "9999-0025-001",
    "category_form_slug": null,
    "category_form_name": null,
    "regional_form_slug": null,
    "regional_form_name": null,
    "special_form_slug": null,
    "special_form_name": null,
    "variant_form_slug": null,
    "variant_form_name": null,
    "primary_type_slug": "electric",
    "primary_type_name": "Electric",
    "primary_type_french_name": "Électrique",
    "secondary_type_slug": null,
    "secondary_type_name": null,
    "secondary_type_french_name": null
  }
]
```

### After (nested object structure)

```json
[
  {
    "elo": 1250.5,
    "significance": true,
    "pokemon": {
      "slug": "pikachu",
      "name": "Pikachu",
      "french_name": "Pikachu",
      "national_dex_number": 25,
      "simplified_name": null,
      "forms_label": null,
      "simplified_french_name": null,
      "forms_french_label": null,
      "icon": "pikachu.png",
      "family_order": 1,
      "family_lead_slug": "pichu",
      "original_game_bundle_slug": "red-blue",
      "order_number": "9999-0025-001"
    },
    "forms": null,
    "types": {
      "primary": {
        "slug": "electric",
        "name": "Electric",
        "french_name": "Électrique",
        "color": "#FFCC33"
      },
      "secondary": null
    }
  }
]
```

## Migration Steps for Clients

### 1. Update field accessors

Replace all field accesses with nested object notation:

**Old:** `$data['pokemon_slug']`  
**New:** `$data['pokemon']['slug']`

**Old:** `$data['primary_type_name']`  
**New:** `$data['types']['primary']['name']`

### 2. Handle optional nested objects

Forms and secondary type are now `null` when absent:

```twig
{% if elo.forms %}
  {# Forms object is present #}
  {% if elo.forms.regional %}
    Regional form: {{ elo.forms.regional.name }}
  {% endif %}
{% endif %}
```

### 3. Validate schema (if applicable)

If you use JSON schema validation, update schemas to reflect nested structure.

## Timeline

- **API deployed:** [DATE — fill in on release]
- **Client update deadline:** [DATE + 1 week]
- **Support window:** Contact [team contact] if migration issues arise

## Questions?

See the design spec: `docs/superpowers/specs/2026-05-25-api-response-restructuring-design.md`
```

Save this as `docs/api-migration/election-elo-restructuring.md`.

- [ ] **Step 2: Verify the file is readable**

Run: `cat docs/api-migration/election-elo-restructuring.md | head -30`

Expected: File contains migration guidance.

---

### Task 12: Final validation and checklist

**Files:**
- All files from previous tasks

- [ ] **Step 1: Verify all new files exist**

Run: `ls -la src/DTO/Response/ElectionEloResponse.php src/DTO/Response/PokemonDataResponse.php src/DTO/Response/FormsResponse.php src/DTO/Response/FormResponse.php src/DTO/Response/TypesResponse.php src/Factory/ElectionEloResponseFactory.php tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php tests/src/Integration/Controller/TrainerPokemonEloControllerTest.php tests/resources/moco/ElectionElo/top.json docs/api-migration/election-elo-restructuring.md`

Expected: All 10 files exist with readable permissions.

- [ ] **Step 2: Verify modified files have correct syntax**

Run: `make sh -c "php -l src/Controller/TrainerPokemonEloController.php && echo 'Syntax OK'"`

Expected: "Syntax OK".

- [ ] **Step 3: Run complete test suite one final time**

Run: `make tests`

Expected: All tests pass, 0 failures.

- [ ] **Step 4: Run complete quality checks**

Run: `make quality && make measures`

Expected: All quality checks green, 100% coverage, 100% MSI.

- [ ] **Step 5: Verify the endpoint works end-to-end**

Run: `make start` (if not already running), then:

```bash
curl "http://localhost:8000/election/top?trainer_external_id=test&dex_slug=living_dex&election_slug=default&count=5" | jq . | head -50
```

Expected: JSON array of nested ELO objects with pokemon, forms, types structure.

- [ ] **Step 6: Document completion**

Summary of changes:
- ✅ Created ElectionEloResponse DTO (top-level with nested objects)
- ✅ Created PokemonDataResponse DTO (Pokemon data container)
- ✅ Created FormsResponse and FormResponse DTOs (Forms structure)
- ✅ Created TypesResponse DTO (Primary/Secondary types)
- ✅ Created ElectionEloResponseFactory (transforms flat SQL rows → nested DTOs)
- ✅ Updated TrainerPokemonEloController (applies Factory + Serializer)
- ✅ Created unit tests for Factory (6 test cases, 100% coverage)
- ✅ Created integration tests for Controller (6 test cases)
- ✅ Updated Moco fixtures (nested response structure)
- ✅ Created migration documentation
- ✅ All quality gates passing (make quality, make measures)
- ✅ End-to-end validation complete

**Status:** Election/ELO endpoint refactoring complete. Pattern validated at scale for complex nested structures.

---

## Next Steps (not in this plan)

Once this plan is complete and approved:

1. **Refactor other endpoints** — apply same pattern to Dex, Album, Reports
2. **Update pokenini-back and pokenini-web** — adapt to new nested response structures
3. **Validate pattern across teams** — ensure migration is smooth for all clients
4. **Consider versioning** — if future breaking changes occur, may need `/v2/` endpoints

Each future refactoring will follow the same task structure: DTOs → Factory → Controller → Tests → Docs.
