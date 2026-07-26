# Single Image-Credit Field Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Collapse the per-image credit from two fields (`sourceName` + `sourceUrl`) into a single field everywhere in the stack (DB column, entity, Sheets column, SQL, DTO/API), since the URL will now be embedded as free text inside that single field by whoever fills the spreadsheet.

**Architecture:** This is a mechanical field-merge refactor spanning the whole existing "pokemon image credit" feature (branch `feature/pokemon-image-credit-system`, not yet merged to `main`, already pushed to `origin`). No new behavior is added; every place that currently reads/writes a `(name, url)` pair now reads/writes a single opaque string. The DB column and the internal SQL/entity property are named `source`; the outward-facing DTO property (JSON API surface) is named `credit` (explicit user decision — kept distinct from `source` to avoid confusion with the Sheets column naming).

**Tech Stack:** Symfony 8 / PHP 8.5, Doctrine Migrations, raw SQL (`resources/sql/*.sql` + inline SQL in Repositories/Updater), Google Sheets sync via `PokemonsUpdater`, Moco HTTP mocks for integration tests, Alice/Hautelook fixtures.

## Global Constraints

- **Edit the existing migration in place** (`migrations/2026/07/Version20260717105931.php`) rather than adding a new migration — this feature branch has not been merged, and the project's own history already amended this exact migration in place (commit `d39b7c3`). Do not create a follow-up "drop column" migration.
- **No intermediate commits in the final branch history.** Per-task implementer commits are fine as SDD scaffolding (the review/fix-loop tooling diffs commit ranges), but before this work is considered done, squash every commit made for this plan into a single commit on top of the commit that preceded Task 1. (User's standing preference — the branch must end with one grouped commit for this whole change, not one per task.)
- **100% coverage / 100% MSI / PHPStan level 9 / Psalm strict / Deptrac** must stay green (`make quality`, `make measures`) — every code path touched here must remain fully tested.
- Entity/DB/SQL-internal naming: `source` (single nullable string, VARCHAR(255), same length as before).
- DTO/JSON-API naming: `credit` (single string property on `ImageCreditResponse`).
- `validateHeader()` in `AbstractUpdater` (`src/Updater/AbstractUpdater.php:81-99`) sorts both the actual and expected header arrays before comparing — **column order in `getExpectedHeader()` does not matter**, only the exact set of column names.
- Google Sheets `Sprites url` / `Shiny Sprites url` columns (positions unrelated to the 6 credit columns added by this feature) stay in the header — they are pre-existing columns unrelated to this feature and nothing else reads them for credit purposes after this change; just stop mapping them into the credit fields.

---

### Task 1: Migration + Entity — single `source` column

**Files:**
- Modify: `migrations/2026/07/Version20260717105931.php`
- Modify: `src/Entity/PokemonImageCredit.php`

**Interfaces:**
- Produces: DB table `pokemon_image_credit` with columns `(id, pokemon_id, size, is_shiny, source)` — no more `source_name`/`source_url`.
- Produces: `App\Entity\PokemonImageCredit::$source` (`?string`, nullable, replaces `$sourceName`/`$sourceUrl`).

- [ ] **Step 1: Edit the migration**

In `migrations/2026/07/Version20260717105931.php`, replace the `up()` body's `CREATE TABLE` line and `getDescription()`:

```php
public function getDescription(): string
{
    return 'Add pokemon_image_credit table for per-image source attribution (size, shininess, single source field)';
}

public function up(Schema $schema): void
{
    $this->addSql('CREATE TABLE pokemon_image_credit (size VARCHAR(16) NOT NULL, is_shiny BOOLEAN NOT NULL, source VARCHAR(255) DEFAULT NULL, id UUID NOT NULL, pokemon_id UUID NOT NULL, PRIMARY KEY (id))');
    $this->addSql('CREATE INDEX IDX_F25B4BEF2FE71C3E ON pokemon_image_credit (pokemon_id)');
    $this->addSql('CREATE UNIQUE INDEX uniq_pokemon_image_credit_slot ON pokemon_image_credit (pokemon_id, size, is_shiny)');
    $this->addSql('ALTER TABLE pokemon_image_credit ADD CONSTRAINT FK_F25B4BEF2FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE');
}
```

`down()` is unchanged (it just drops the whole table).

- [ ] **Step 2: Edit the entity**

In `src/Entity/PokemonImageCredit.php`, replace:

```php
    #[ORM\Column(nullable: true)]
    public ?string $sourceName = null;

    #[ORM\Column(nullable: true)]
    public ?string $sourceUrl = null;
```

with:

```php
    #[ORM\Column(nullable: true)]
    public ?string $source = null;
```

- [ ] **Step 3: Reset the DB and verify**

```bash
make sh
php bin/console doctrine:migrations:migrate --no-interaction -e test
php bin/console doctrine:migrations:migrate --no-interaction -e int
```

(These will fail loudly if the migration SQL is malformed — that's the check for this step. Full functional verification happens once fixtures/tests are updated in later tasks.)

---

### Task 2: `/credits` endpoint — Repository, Service, DTO, Factory, Controller test, Alice fixtures

**Files:**
- Modify: `src/Repository/PokemonImageCreditRepository.php`
- Modify: `src/Service/ImageCreditsService.php`
- Modify: `src/DTO/Response/ImageCreditResponse.php`
- Modify: `src/Factory/ImageCreditResponseFactory.php`
- Modify: `fixtures/pokemon_image_credits.yaml`
- Modify: `tests/src/Integration/Repository/PokemonImageCreditRepositoryTest.php`
- Modify: `tests/src/Integration/Controller/ImageCreditsControllerTest.php`
- Modify: `tests/src/Unit/DTO/Response/ImageCreditResponseTest.php`
- Modify: `tests/src/Unit/Factory/ImageCreditResponseFactoryTest.php`
- Modify: `tests/src/Unit/Service/ImageCreditsServiceTest.php`

**Interfaces:**
- Produces: `App\DTO\Response\ImageCreditResponse::__construct(string $credit)` with public readonly `$credit`.
- Produces: `PokemonImageCreditRepository::findAllDistinctSources(): array<array{source: string}>`.
- Produces: `ImageCreditResponseFactory::fromSqlRow(array $row): ImageCreditResponse` reading `$row['source']`.

- [ ] **Step 1: Update the DTO**

`src/DTO/Response/ImageCreditResponse.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ImageCreditResponse
{
    public function __construct(
        public readonly string $credit,
    ) {}
}
```

- [ ] **Step 2: Update the factory**

`src/Factory/ImageCreditResponseFactory.php`:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ImageCreditResponse;

final class ImageCreditResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): ImageCreditResponse
    {
        /** @var scalar $source */
        $source = $row['source'];

        return new ImageCreditResponse(credit: (string) $source);
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return ImageCreditResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
```

- [ ] **Step 3: Update the repository**

`src/Repository/PokemonImageCreditRepository.php`, replace `findAllDistinctSources()`:

```php
    /**
     * @return array<array{source: string}>
     */
    public function findAllDistinctSources(): array
    {
        $sql = <<<'SQL'
            SELECT DISTINCT source
            FROM pokemon_image_credit
            WHERE source IS NOT NULL
            ORDER BY source
            SQL;

        /** @var array<array{source: string}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
```

- [ ] **Step 4: Update the service doc block**

`src/Service/ImageCreditsService.php`, update the `@return` PHPDoc on `getAll()`:

```php
    /**
     * @return array<array{source: string}>
     */
    public function getAll(): array
```

(Method body is unchanged — it just delegates to the repository.)

- [ ] **Step 5: Update the Alice fixtures**

`fixtures/pokemon_image_credits.yaml`, replace every `sourceName`/`sourceUrl` pair with a single `source` field (embed the URL as text, matching how the real spreadsheet source column will look):

```yaml
App\Entity\PokemonImageCredit:
  pokemon_image_credit_bulbasaur_small_regular:
    pokemon: "@pokemon_bulbasaur"
    size: "small"
    isShiny: false
    source: "PokéSprite - https://github.com/msikma/pokesprite"

  pokemon_image_credit_bulbasaur_big_regular:
    pokemon: "@pokemon_bulbasaur"
    size: "big"
    isShiny: false
    source: "PokemonDB - https://pokemondb.net/sprites/bulbasaur"

  pokemon_image_credit_douze_small_shiny_no_source:
    pokemon: "@pokemon_douze"
    size: "small"
    isShiny: true
    source: ~

  pokemon_image_credit_ivysaur_small_regular:
    pokemon: "@pokemon_ivysaur"
    size: "small"
    isShiny: false
    source: "Bulbapedia - https://bulbapedia.bulbagarden.net"

  pokemon_image_credit_venusaur_small_regular:
    pokemon: "@pokemon_venusaur"
    size: "small"
    isShiny: false
    source: "Serebii - https://serebii.net"
```

- [ ] **Step 6: Update `PokemonImageCreditRepositoryTest`**

`tests/src/Integration/Repository/PokemonImageCreditRepositoryTest.php`, replace the assertion body:

```php
        self::assertCount(4, $result);
        self::assertSame(
            [
                ['source' => 'Bulbapedia - https://bulbapedia.bulbagarden.net'],
                ['source' => 'PokemonDB - https://pokemondb.net/sprites/bulbasaur'],
                ['source' => 'PokéSprite - https://github.com/msikma/pokesprite'],
                ['source' => 'Serebii - https://serebii.net'],
            ],
            $result,
        );
```

- [ ] **Step 7: Update `ImageCreditsControllerTest`**

`tests/src/Integration/Controller/ImageCreditsControllerTest.php`, in `getReturnsDeduplicatedCreditsFromFixtures()`:

```php
        self::assertContains(['credit' => 'Bulbapedia - https://bulbapedia.bulbagarden.net'], $data);
        self::assertContains(['credit' => 'PokéSprite - https://github.com/msikma/pokesprite'], $data);
        self::assertContains(['credit' => 'PokemonDB - https://pokemondb.net/sprites/bulbasaur'], $data);
        self::assertContains(['credit' => 'Serebii - https://serebii.net'], $data);
        self::assertCount(4, $data);
```

- [ ] **Step 8: Update `ImageCreditResponseTest`**

`tests/src/Unit/DTO/Response/ImageCreditResponseTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ImageCreditResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageCreditResponse::class)]
final class ImageCreditResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesCredit(): void
    {
        $response = new ImageCreditResponse(credit: 'PokéSprite - https://github.com/msikma/pokesprite');

        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $response->credit);
    }

    #[Test]
    public function propertyIsReadonly(): void
    {
        $response = new ImageCreditResponse(credit: 'PokemonDB - https://pokemondb.net/sprites/bulbasaur');

        self::assertSame('PokemonDB - https://pokemondb.net/sprites/bulbasaur', $response->credit);
    }
}
```

- [ ] **Step 9: Update `ImageCreditResponseFactoryTest`**

`tests/src/Unit/Factory/ImageCreditResponseFactoryTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ImageCreditResponse;
use App\Factory\ImageCreditResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageCreditResponseFactory::class)]
final class ImageCreditResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowBuildsImageCreditResponse(): void
    {
        $response = ImageCreditResponseFactory::fromSqlRow([
            'source' => 'PokemonDB - https://pokemondb.net',
        ]);

        self::assertSame('PokemonDB - https://pokemondb.net', $response->credit);
    }

    #[Test]
    public function fromSqlRowCastsNonStringValuesToString(): void
    {
        $response = ImageCreditResponseFactory::fromSqlRow([
            'source' => 42,
        ]);

        self::assertSame('42', $response->credit);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRows(): void
    {
        $responses = ImageCreditResponseFactory::fromSqlRows([
            ['source' => 'A'],
            ['source' => 'B'],
        ]);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(ImageCreditResponse::class, $responses);
        self::assertSame('A', $responses[0]->credit);
        self::assertSame('B', $responses[1]->credit);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        self::assertCount(0, ImageCreditResponseFactory::fromSqlRows([]));
    }
}
```

- [ ] **Step 10: Update `ImageCreditsServiceTest`**

`tests/src/Unit/Service/ImageCreditsServiceTest.php`, in `getAllReturnsRepositoryData()`:

```php
        $expected = [['source' => 'A']];
```

(rest of the test is unchanged.)

- [ ] **Step 11: Run the tests for this task**

```bash
docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/Response/ImageCreditResponseTest.php tests/src/Unit/Factory/ImageCreditResponseFactoryTest.php tests/src/Unit/Service/ImageCreditsServiceTest.php
docker compose exec php php vendor/bin/phpunit tests/src/Integration/Repository/PokemonImageCreditRepositoryTest.php tests/src/Integration/Controller/ImageCreditsControllerTest.php
```

Expected: all PASS.

---

### Task 3: Per-pokemon credit SQL (PokedexRepository + 3 SQL files) and the 3 factories that build `ImageCreditResponse` from a row prefix

**Files:**
- Modify: `src/Repository/PokedexRepository.php:351-354`
- Modify: `resources/sql/pokemons-get_n_to_vote.sql:57-60`
- Modify: `resources/sql/pokemons-get_n_to_pick.sql:35-38`
- Modify: `resources/sql/trainer_pokemon_elo-get_top_n.sql:86-89`
- Modify: `src/Factory/AlbumPokemonResponseFactory.php` (`buildCredit()` method, ~line 149-165)
- Modify: `src/Factory/ElectionPokemonResponseFactory.php` (`buildCredit()` method, ~line 157-172)
- Modify: `src/Factory/ElectionEloResponseFactory.php` (`buildCredit()` method, ~line 159-174)
- Modify: `tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php`
- Modify: `tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`
- Modify: `tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php`
- Modify: `tests/src/Common/Data/PokemonData.php`
- Modify: `tests/src/Common/Data/AlbumData.php`

**Interfaces:**
- Consumes: `ImageCreditResponse::__construct(string $credit)` from Task 2.
- Produces: SQL row key `{prefix}_credit_source` for `prefix` in `small_regular`, `small_shiny`, `big_regular`, `big_shiny`.

- [ ] **Step 1: Update the 4 SQL sources**

In `src/Repository/PokedexRepository.php` (lines 351-354), `resources/sql/pokemons-get_n_to_vote.sql` (lines 57-60), `resources/sql/pokemons-get_n_to_pick.sql` (lines 35-38), and `resources/sql/trainer_pokemon_elo-get_top_n.sql` (lines 86-89), replace the 4-line credit `SELECT` block:

```sql
pic_sr.source_name AS small_regular_credit_name, pic_sr.source_url AS small_regular_credit_url,
pic_ss.source_name AS small_shiny_credit_name, pic_ss.source_url AS small_shiny_credit_url,
pic_br.source_name AS big_regular_credit_name, pic_br.source_url AS big_regular_credit_url,
pic_bs.source_name AS big_shiny_credit_name, pic_bs.source_url AS big_shiny_credit_url,
```

with:

```sql
pic_sr.source AS small_regular_credit_source,
pic_ss.source AS small_shiny_credit_source,
pic_br.source AS big_regular_credit_source,
pic_bs.source AS big_shiny_credit_source,
```

The `LEFT JOIN pokemon_image_credit AS pic_sr/pic_ss/pic_br/pic_bs ...` lines immediately below are unaffected — leave them as-is.

- [ ] **Step 2: Update the 3 factories' `buildCredit()`**

In each of `AlbumPokemonResponseFactory`, `ElectionPokemonResponseFactory`, `ElectionEloResponseFactory`, replace the private `buildCredit()` method:

```php
    /**
     * @param array<string, mixed> $row
     */
    private static function buildCredit(string $prefix, array $row): ?ImageCreditResponse
    {
        $key = "{$prefix}_credit_source";

        if (empty($row[$key])) {
            return null;
        }

        /** @var scalar $credit */
        $credit = $row[$key];

        return new ImageCreditResponse(credit: (string) $credit);
    }
```

The 4 call sites (`self::buildCredit('small_regular', $row)`, etc.) are unchanged.

- [ ] **Step 3: Update the test data builders**

`tests/src/Common/Data/PokemonData.php`, replace `noCredits()`:

```php
    /**
     * @return null[]
     */
    public static function noCredits(): array
    {
        return [
            'small_regular_credit_source' => null,
            'small_shiny_credit_source' => null,
            'big_regular_credit_source' => null,
            'big_shiny_credit_source' => null,
        ];
    }
```

Then, in every fixture method in `PokemonData.php` that sets literal `{slot}_credit_name`/`{slot}_credit_url` pairs (the report identified three: the Bulbasaur-shaped fixture around line 68-75, the Ivysaur-shaped one around line 124-131, and the Venusaur-shaped one around line 180-187 — search the file for `_credit_name` to find all occurrences), collapse each pair into a single `{slot}_credit_source` key. For example, replace:

```php
            'small_regular_credit_name' => 'PokéSprite',
            'small_regular_credit_url' => 'https://github.com/msikma/pokesprite',
            'small_shiny_credit_name' => null,
            'small_shiny_credit_url' => null,
            'big_regular_credit_name' => 'PokemonDB',
            'big_regular_credit_url' => 'https://pokemondb.net/sprites/bulbasaur',
            'big_shiny_credit_name' => null,
            'big_shiny_credit_url' => null,
```

with:

```php
            'small_regular_credit_source' => 'PokéSprite - https://github.com/msikma/pokesprite',
            'small_shiny_credit_source' => null,
            'big_regular_credit_source' => 'PokemonDB - https://pokemondb.net/sprites/bulbasaur',
            'big_shiny_credit_source' => null,
```

Apply the same collapsing pattern to the other two fixture methods (search for `_credit_name` to locate them precisely — line numbers may have shifted from the ones below since this is from an earlier read of the file):

The Ivysaur-shaped fixture (~line 124-131):

```php
            'small_regular_credit_name' => 'Bulbapedia',
            'small_regular_credit_url' => 'https://bulbapedia.bulbagarden.net',
            'small_shiny_credit_name' => null,
            'small_shiny_credit_url' => null,
            'big_regular_credit_name' => null,
            'big_regular_credit_url' => null,
            'big_shiny_credit_name' => null,
            'big_shiny_credit_url' => null,
```

becomes:

```php
            'small_regular_credit_source' => 'Bulbapedia - https://bulbapedia.bulbagarden.net',
            'small_shiny_credit_source' => null,
            'big_regular_credit_source' => null,
            'big_shiny_credit_source' => null,
```

The Venusaur-shaped fixture (~line 180-187):

```php
            'small_regular_credit_name' => 'Serebii',
            'small_regular_credit_url' => 'https://serebii.net',
            'small_shiny_credit_name' => null,
            'small_shiny_credit_url' => null,
            'big_regular_credit_name' => null,
            'big_regular_credit_url' => null,
            'big_shiny_credit_name' => null,
            'big_shiny_credit_url' => null,
```

becomes:

```php
            'small_regular_credit_source' => 'Serebii - https://serebii.net',
            'small_shiny_credit_source' => null,
            'big_regular_credit_source' => null,
            'big_shiny_credit_source' => null,
```

`tests/src/Common/Data/AlbumData.php`, replace `buildNestedCredit()`:

```php
    /**
     * @param array<string, mixed> $flat
     *
     * @return null|array<string, mixed>
     */
    private static function buildNestedCredit(string $prefix, array $flat): ?array
    {
        $key = "{$prefix}_credit_source";

        if (empty($flat[$key])) {
            return null;
        }

        /** @var scalar $credit */
        $credit = $flat[$key];

        return [
            'credit' => (string) $credit,
        ];
    }
```

The call sites (`self::buildNestedCredit('small_regular', $flat)`, etc., lines ~832-835) and the 10 `...PokemonData::noCredits()` spreads are unchanged (they pick up the new shape automatically via `PokemonData::noCredits()`).

- [ ] **Step 4: Update `AlbumPokemonResponseFactoryTest`**

`tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php` (around lines 417-457): the base row from `getBulbasaurRow()` now includes 4 `_credit_source` keys (defaulted to `null`) instead of 8 `_name`/`_url` keys — update `getBulbasaurRow()` wherever it defines those defaults, following the same collapsing pattern as Step 3. Replace the 3 credit-specific tests:

```php
    #[Test]
    public function fromSqlRowWithCreditColumnsBuildsImageCreditResponse(): void
    {
        $row = array_merge($this->getBulbasaurRow(), [
            'small_regular_credit_source' => 'PokéSprite - https://github.com/msikma/pokesprite',
        ]);

        $response = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertNotNull($response->pokemon->smallRegularCredit);
        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $response->pokemon->smallRegularCredit->credit);
        self::assertNull($response->pokemon->smallShinyCredit);
        self::assertNull($response->pokemon->bigRegularCredit);
        self::assertNull($response->pokemon->bigShinyCredit);
    }

    #[Test]
    public function fromSqlRowWithEmptyCreditSourceReturnsNullCredit(): void
    {
        $row = array_merge($this->getBulbasaurRow(), [
            'big_regular_credit_source' => '',
        ]);

        $response = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->pokemon->bigRegularCredit);
    }
```

(This removes the old `fromSqlRowWithOnlyCreditNameReturnsNullCredit`/`fromSqlRowWithOnlyCreditUrlReturnsNullCredit` pair — that partial-data distinction no longer exists with a single field — and replaces it with one `empty string → null` case.)

- [ ] **Step 5: Update `ElectionPokemonResponseFactoryTest`**

`tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php`: update `buildRow()`'s defaults (lines ~483-529) by collapsing the 8 `_name`/`_url` default keys into 4 `_credit_source` keys (all `null`), and rewrite the 4 credit tests (lines ~417-476):

```php
    #[Test]
    public function fromSqlRowWithNoCreditColumnsReturnsNullCredits(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->pokemon->smallRegularCredit);
        self::assertNull($response->pokemon->smallShinyCredit);
        self::assertNull($response->pokemon->bigRegularCredit);
        self::assertNull($response->pokemon->bigShinyCredit);
    }

    #[Test]
    public function fromSqlRowWithCreditColumnsBuildsImageCreditResponse(): void
    {
        $row = $this->buildRow([
            'small_regular_credit_source' => 'PokéSprite - https://github.com/msikma/pokesprite',
            'big_shiny_credit_source' => 'PokemonDB - https://pokemondb.net/sprites/bulbasaur',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNotNull($response->pokemon->smallRegularCredit);
        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $response->pokemon->smallRegularCredit->credit);
        self::assertNull($response->pokemon->smallShinyCredit);
        self::assertNull($response->pokemon->bigRegularCredit);
        self::assertNotNull($response->pokemon->bigShinyCredit);
        self::assertSame('PokemonDB - https://pokemondb.net/sprites/bulbasaur', $response->pokemon->bigShinyCredit->credit);
    }

    #[Test]
    public function fromSqlRowWithEmptyCreditSourceReturnsNullCredit(): void
    {
        $row = $this->buildRow([
            'big_regular_credit_source' => '',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->pokemon->bigRegularCredit);
    }
```

- [ ] **Step 6: Update `ElectionEloResponseFactoryTest`**

`tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php` (lines ~671-825): each of the 3 credit tests inlines the full row array. Collapse `big_shiny_credit_name`/`big_shiny_credit_url` into `big_shiny_credit_source` in each, and drop the "only name"/"only url" pair down to one "empty string" case, mirroring Steps 4-5:

```php
    #[Test]
    public function fromSqlRowWithCreditColumnsBuildsImageCreditResponse(): void
    {
        $row = [
            // ... same base fields as the existing test (elo, significance, pokemon_*, etc.) ...
            'big_shiny_credit_source' => 'PokemonDB - https://pokemondb.net/sprites/pikachu-shiny',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertNull($response->pokemon->smallRegularCredit);
        self::assertNull($response->pokemon->smallShinyCredit);
        self::assertNull($response->pokemon->bigRegularCredit);
        self::assertNotNull($response->pokemon->bigShinyCredit);
        self::assertSame('PokemonDB - https://pokemondb.net/sprites/pikachu-shiny', $response->pokemon->bigShinyCredit->credit);
    }

    #[Test]
    public function fromSqlRowWithEmptyCreditSourceReturnsNullCredit(): void
    {
        $row = [
            // ... same base fields ...
            'big_shiny_credit_source' => '',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertNull($response->pokemon->bigShinyCredit);
    }
```

Keep the exact same base-field block (`elo`, `significance`, `pokemon_slug`, ... `game_bundle_shiny_slugs`) that's already in the file — only the trailing credit key(s) change.

- [ ] **Step 7: Run the tests for this task**

```bash
docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/AlbumPokemonResponseFactoryTest.php tests/src/Unit/Factory/ElectionPokemonResponseFactoryTest.php tests/src/Unit/Factory/ElectionEloResponseFactoryTest.php
docker compose exec php php vendor/bin/phpunit --filter AlbumIndex tests/src/Unit/
```

Expected: all PASS. (The last invocation catches `AlbumIndexResponseFactoryTest`, which builds full rows via `AlbumData.php`/`PokemonData.php` and must still pass unchanged since it only ever passes `smallRegularCredit: null` etc.)

---

### Task 4: `AlbumIndexFilteredController` integration test

**Files:**
- Modify: `tests/src/Integration/Controller/AlbumIndexFilteredController/ImageCreditTest.php`

**Interfaces:**
- Consumes: `fixtures/pokemon_image_credits.yaml` from Task 2 (Bulbasaur small-regular = `PokéSprite - https://github.com/msikma/pokesprite`, big-regular = `PokemonDB - https://pokemondb.net/sprites/bulbasaur`).

- [ ] **Step 1: Update the assertions**

In `testIndexIncludesBulbasaurImageCredits()`, replace:

```php
        $this->assertArrayHasKey('small_regular_credit', $bulbasaur);
        $this->assertIsArray($bulbasaur['small_regular_credit']);

        /** @var array<string, mixed> $smallRegularCredit */
        $smallRegularCredit = $bulbasaur['small_regular_credit'];
        self::assertSame('PokéSprite', $smallRegularCredit['name']);
        self::assertSame('https://github.com/msikma/pokesprite', $smallRegularCredit['url']);

        $this->assertArrayHasKey('big_regular_credit', $bulbasaur);
        $this->assertIsArray($bulbasaur['big_regular_credit']);

        /** @var array<string, mixed> $bigRegularCredit */
        $bigRegularCredit = $bulbasaur['big_regular_credit'];
        self::assertSame('PokemonDB', $bigRegularCredit['name']);
```

with:

```php
        $this->assertArrayHasKey('small_regular_credit', $bulbasaur);
        $this->assertIsArray($bulbasaur['small_regular_credit']);

        /** @var array<string, mixed> $smallRegularCredit */
        $smallRegularCredit = $bulbasaur['small_regular_credit'];
        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $smallRegularCredit['credit']);

        $this->assertArrayHasKey('big_regular_credit', $bulbasaur);
        $this->assertIsArray($bulbasaur['big_regular_credit']);

        /** @var array<string, mixed> $bigRegularCredit */
        $bigRegularCredit = $bulbasaur['big_regular_credit'];
        self::assertSame('PokemonDB - https://pokemondb.net/sprites/bulbasaur', $bigRegularCredit['credit']);
```

The trailing `assertNull($bulbasaur['small_shiny_credit'])`/`assertNull($bulbasaur['big_shiny_credit'])` lines are unchanged.

- [ ] **Step 2: Run the test**

```bash
docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/AlbumIndexFilteredController/ImageCreditTest.php
```

Expected: PASS.

---

### Task 5: `PokemonsUpdater` — Sheets column merge

**Files:**
- Modify: `src/Updater/PokemonsUpdater.php`
- Modify: `tests/src/Unit/Updater/PokemonsUpdaterTest.php`
- Modify: `tests/src/Integration/Updater/PokemonsUpdaterTest.php` (only the raw-SQL assertion block)

**Interfaces:**
- Produces: `PokemonsUpdater::transformRecord()` output keys `smallRegularCredit`, `smallShinyCredit`, `bigRegularCredit`, `bigShinyCredit` (each a plain string), replacing the previous 8 `*CreditName`/`*CreditUrl` keys.
- Produces: `PokemonsUpdater::upsertImageCredit(string $pokemonSlug, string $size, bool $isShiny, string $source): void` (was 5 args with `$sourceName, $sourceUrl`).
- Consumes: Task 6's moco fixtures must expose exactly the Sheets header this task declares (`A1:AJ1`, 36 columns) — do this task first, Task 6 second.

- [ ] **Step 1: Shrink the expected header and range**

In `src/Updater/PokemonsUpdater.php`, change:

```php
    protected string $headerCellsRange = 'A1:AL1';

    /** @var array<int, string> */
    protected array $recordsCellsRanges = ['A2:AL'];
```

to:

```php
    protected string $headerCellsRange = 'A1:AJ1';

    /** @var array<int, string> */
    protected array $recordsCellsRanges = ['A2:AJ'];
```

And in `getExpectedHeader()`, remove the two `'Icon Source Url'` and `'Shiny Icon Source Url'` entries so the trailing block reads:

```php
            'Icon Source',
            'Shiny Icon Source',
            'Sprites Source',
            'Shiny Sprites Source',
        ];
```

(the rest of the array, everything before `'Icon Source'`, is unchanged — `'Sprites url'`/`'Shiny Sprites url'` stay where they are, higher up in the list; they're no longer read for credits but the physical Sheet column still exists).

- [ ] **Step 2: Update `transformRecord()`**

Replace the 8 credit lines:

```php
            'smallRegularCreditName' => $record['Icon Source'],
            'smallRegularCreditUrl' => $record['Icon Source Url'],
            'smallShinyCreditName' => $record['Shiny Icon Source'],
            'smallShinyCreditUrl' => $record['Shiny Icon Source Url'],
            'bigRegularCreditName' => $record['Sprites Source'],
            'bigRegularCreditUrl' => $record['Sprites url'],
            'bigShinyCreditName' => $record['Shiny Sprites Source'],
            'bigShinyCreditUrl' => $record['Shiny Sprites url'],
```

with:

```php
            'smallRegularCredit' => $record['Icon Source'],
            'smallShinyCredit' => $record['Shiny Icon Source'],
            'bigRegularCredit' => $record['Sprites Source'],
            'bigShinyCredit' => $record['Shiny Sprites Source'],
```

- [ ] **Step 3: Update `upsertRecord()`'s 4 calls**

Replace:

```php
        $this->upsertImageCredit($slug, 'small', false, (string) $newRecord['smallRegularCreditName'], (string) $newRecord['smallRegularCreditUrl']);
        $this->upsertImageCredit($slug, 'small', true, (string) $newRecord['smallShinyCreditName'], (string) $newRecord['smallShinyCreditUrl']);
        $this->upsertImageCredit($slug, 'big', false, (string) $newRecord['bigRegularCreditName'], (string) $newRecord['bigRegularCreditUrl']);
        $this->upsertImageCredit($slug, 'big', true, (string) $newRecord['bigShinyCreditName'], (string) $newRecord['bigShinyCreditUrl']);
```

with:

```php
        $this->upsertImageCredit($slug, 'small', false, (string) $newRecord['smallRegularCredit']);
        $this->upsertImageCredit($slug, 'small', true, (string) $newRecord['smallShinyCredit']);
        $this->upsertImageCredit($slug, 'big', false, (string) $newRecord['bigRegularCredit']);
        $this->upsertImageCredit($slug, 'big', true, (string) $newRecord['bigShinyCredit']);
```

- [ ] **Step 4: Update `upsertImageCredit()`**

Replace the whole method:

```php
    private function upsertImageCredit(string $pokemonSlug, string $size, bool $isShiny, string $source): void
    {
        if ('' === $source) {
            $this->deleteImageCredit($pokemonSlug, $size, $isShiny);

            return;
        }

        $sql = <<<'SQL'
            INSERT INTO pokemon_image_credit (id, pokemon_id, size, is_shiny, source)
            VALUES (
                :id,
                (SELECT id FROM pokemon WHERE slug = :slug),
                :size,
                :isShiny,
                :source
            )
            ON CONFLICT (pokemon_id, size, is_shiny)
            DO
            UPDATE
            SET source = excluded.source
            SQL;

        $this->executeQuery($sql, [
            'id' => (string) Uuid::v4(),
            'slug' => $pokemonSlug,
            'size' => $size,
            'isShiny' => (int) $isShiny,
            'source' => $source,
        ]);
    }
```

`deleteImageCredit()` is unchanged.

- [ ] **Step 5: Update `tests/src/Unit/Updater/PokemonsUpdaterTest.php`**

In `getRecord()`, remove the `'Icon Source Url'` and `'Shiny Icon Source Url'` entries:

```php
            'Icon Source' => 'PokéSprite',
            'Shiny Icon Source' => 'PokéSprite Shiny',
            'Sprites Source' => 'PokemonDB',
            'Shiny Sprites Source' => 'PokemonDB Shiny',
```

In `testTransformRecordMapsFieldsCorrectly()`, replace the 8 credit assertions:

```php
        $this->assertSame('PokéSprite', $result['smallRegularCreditName']);
        $this->assertSame('https://github.com/msikma/pokesprite', $result['smallRegularCreditUrl']);
        $this->assertSame('PokéSprite Shiny', $result['smallShinyCreditName']);
        $this->assertSame('https://github.com/msikma/pokesprite/shiny', $result['smallShinyCreditUrl']);
        $this->assertSame('PokemonDB', $result['bigRegularCreditName']);
        $this->assertSame('https://pokemondb.net/sprites/pikachu', $result['bigRegularCreditUrl']);
        $this->assertSame('PokemonDB Shiny', $result['bigShinyCreditName']);
        $this->assertSame('https://pokemondb.net/sprites/pikachu-shiny', $result['bigShinyCreditUrl']);
```

with:

```php
        $this->assertSame('PokéSprite', $result['smallRegularCredit']);
        $this->assertSame('PokéSprite Shiny', $result['smallShinyCredit']);
        $this->assertSame('PokemonDB', $result['bigRegularCredit']);
        $this->assertSame('PokemonDB Shiny', $result['bigShinyCredit']);
```

(`'Sprites url'`/`'Shiny Sprites url'` stay in `getRecord()` unchanged — they're still legitimate header columns, just no longer read into credit fields.)

- [ ] **Step 6: Update `tests/src/Integration/Updater/PokemonsUpdaterTest.php`**

Replace the raw-SQL block in `testImportNewPokemons()`:

```php
        /** @var false|string[] $megaCharizardXSmallCredit */
        $megaCharizardXSmallCredit = $connection->executeQuery(
            <<<'SQL'
                SELECT pic.source_name, pic.source_url
                FROM pokemon_image_credit pic
                INNER JOIN pokemon p ON p.id = pic.pokemon_id
                WHERE p.slug = :slug
                    AND pic.size = :size
                    AND pic.is_shiny = :isShiny
                SQL,
            [
                'slug' => 'charizard-mega-x',
                'size' => 'small',
                'isShiny' => 0,
            ]
        )->fetchAssociative();

        $this->assertNotFalse($megaCharizardXSmallCredit);
        $this->assertEquals('PokéSprite', $megaCharizardXSmallCredit['source_name']);
        $this->assertEquals('https://github.com/msikma/pokesprite/charizard-mega-x', $megaCharizardXSmallCredit['source_url']);
```

with:

```php
        /** @var false|string[] $megaCharizardXSmallCredit */
        $megaCharizardXSmallCredit = $connection->executeQuery(
            <<<'SQL'
                SELECT pic.source
                FROM pokemon_image_credit pic
                INNER JOIN pokemon p ON p.id = pic.pokemon_id
                WHERE p.slug = :slug
                    AND pic.size = :size
                    AND pic.is_shiny = :isShiny
                SQL,
            [
                'slug' => 'charizard-mega-x',
                'size' => 'small',
                'isShiny' => 0,
            ]
        )->fetchAssociative();

        $this->assertNotFalse($megaCharizardXSmallCredit);
        $this->assertEquals('PokéSprite', $megaCharizardXSmallCredit['source']);
```

This depends on Task 6 leaving the moco `only_new` fixture's first data row with `PokéSprite` as the small-regular credit value (see Task 6 Step 3's worked example) — do Task 6 before running this test.

- [ ] **Step 7: Run the unit test only for now**

```bash
docker compose exec php php vendor/bin/phpunit tests/src/Unit/Updater/PokemonsUpdaterTest.php
```

Expected: PASS. (The integration test needs Task 6's fixtures first — verify it at the end of Task 6.)

---

### Task 6: Moco Sheets fixtures — shrink `AL` range to `AJ`, drop the 2 URL columns

**Files:**
- Rename + modify 18 files under `tests/resources/moco/Sheets/test/` (list below)
- Modify: `tests/resources/moco/Sheets/test.moco.json`
- Modify: `tests/resources/moco/Sheets/int.moco.json`

**Interfaces:**
- Consumes: Task 5's new header (`A1:AJ1` / `A2:AJ`, 36 columns, without `Icon Source Url` / `Shiny Icon Source Url`).

The 18 files to rename (replace `AL1` → `AJ1`, and the bare `AL` at the end of a data-range filename → `AJ`; keep everything else in the filename identical):

| Old filename (under `tests/resources/moco/Sheets/test/`) | New filename |
|---|---|
| `values_%27empty%27%21A1%3AAL1.json` | `values_%27empty%27%21A1%3AAJ1.json` |
| `values_%27Pok%C3%A9mons%27%21A1%3AAL1.json` | `values_%27Pok%C3%A9mons%27%21A1%3AAJ1.json` |
| `values_%27Pok%C3%A9mons%27%21A2%3AAL.json` | `values_%27Pok%C3%A9mons%27%21A2%3AAJ.json` |
| `values_%27pokemon_list%20%2F%20different_columns_order%27%21A1%3AAL1.json` | `values_%27pokemon_list%20%2F%20different_columns_order%27%21A1%3AAJ1.json` |
| `values_%27pokemon_list%20%2F%20different_columns_order%27%21A2%3AAL.json` | `values_%27pokemon_list%20%2F%20different_columns_order%27%21A2%3AAJ.json` |
| `values_%27pokemon_list%20%2F%20family_link%27%21A1%3AAL1.json` | `values_%27pokemon_list%20%2F%20family_link%27%21A1%3AAJ1.json` |
| `values_%27pokemon_list%20%2F%20family_link%27%21A2%3AAL.json` | `values_%27pokemon_list%20%2F%20family_link%27%21A2%3AAJ.json` |
| `values_%27pokemon_list%20%2F%20new_and_existing%27%21A1%3AAL1.json` | `values_%27pokemon_list%20%2F%20new_and_existing%27%21A1%3AAJ1.json` |
| `values_%27pokemon_list%20%2F%20new_and_existing%27%21A2%3AAL.json` | `values_%27pokemon_list%20%2F%20new_and_existing%27%21A2%3AAJ.json` |
| `values_%27pokemon_list%20%2F%20only_existing%27%21A1%3AAL1.json` | `values_%27pokemon_list%20%2F%20only_existing%27%21A1%3AAJ1.json` |
| `values_%27pokemon_list%20%2F%20only_existing%27%21A2%3AAL.json` | `values_%27pokemon_list%20%2F%20only_existing%27%21A2%3AAJ.json` |
| `values_%27pokemon_list%20%2F%20only_new%27%21A1%3AAL1.json` | `values_%27pokemon_list%20%2F%20only_new%27%21A1%3AAJ1.json` |
| `values_%27pokemon_list%20%2F%20only_new%27%21A2%3AAL.json` | `values_%27pokemon_list%20%2F%20only_new%27%21A2%3AAJ.json` |
| `values_%27pokemon_list%20%2F%20update_regional_form%27%21A1%3AAL1.json` | `values_%27pokemon_list%20%2F%20update_regional_form%27%21A1%3AAJ1.json` |
| `values_%27pokemon_list%20%2F%20update_regional_form%27%21A2%3AAL.json` | `values_%27pokemon_list%20%2F%20update_regional_form%27%21A2%3AAJ.json` |
| `values_%27pokemon_list%20%2F%20update_type%27%21A1%3AAL1.json` | `values_%27pokemon_list%20%2F%20update_type%27%21A1%3AAJ1.json` |
| `values_%27pokemon_list%20%2F%20update_type%27%21A2%3AAL.json` | `values_%27pokemon_list%20%2F%20update_type%27%21A2%3AAJ.json` |
| `values_%27wrong_sheet%27%21A1%3AAL1.json` | `values_%27wrong_sheet%27%21A1%3AAJ1.json` |

- [ ] **Step 1: Rename all 18 files with `git mv`**

```bash
cd tests/resources/moco/Sheets/test
git mv "values_%27empty%27%21A1%3AAL1.json" "values_%27empty%27%21A1%3AAJ1.json"
git mv "values_%27Pok%C3%A9mons%27%21A1%3AAL1.json" "values_%27Pok%C3%A9mons%27%21A1%3AAJ1.json"
git mv "values_%27Pok%C3%A9mons%27%21A2%3AAL.json" "values_%27Pok%C3%A9mons%27%21A2%3AAJ.json"
git mv "values_%27pokemon_list%20%2F%20different_columns_order%27%21A1%3AAL1.json" "values_%27pokemon_list%20%2F%20different_columns_order%27%21A1%3AAJ1.json"
git mv "values_%27pokemon_list%20%2F%20different_columns_order%27%21A2%3AAL.json" "values_%27pokemon_list%20%2F%20different_columns_order%27%21A2%3AAJ.json"
git mv "values_%27pokemon_list%20%2F%20family_link%27%21A1%3AAL1.json" "values_%27pokemon_list%20%2F%20family_link%27%21A1%3AAJ1.json"
git mv "values_%27pokemon_list%20%2F%20family_link%27%21A2%3AAL.json" "values_%27pokemon_list%20%2F%20family_link%27%21A2%3AAJ.json"
git mv "values_%27pokemon_list%20%2F%20new_and_existing%27%21A1%3AAL1.json" "values_%27pokemon_list%20%2F%20new_and_existing%27%21A1%3AAJ1.json"
git mv "values_%27pokemon_list%20%2F%20new_and_existing%27%21A2%3AAL.json" "values_%27pokemon_list%20%2F%20new_and_existing%27%21A2%3AAJ.json"
git mv "values_%27pokemon_list%20%2F%20only_existing%27%21A1%3AAL1.json" "values_%27pokemon_list%20%2F%20only_existing%27%21A1%3AAJ1.json"
git mv "values_%27pokemon_list%20%2F%20only_existing%27%21A2%3AAL.json" "values_%27pokemon_list%20%2F%20only_existing%27%21A2%3AAJ.json"
git mv "values_%27pokemon_list%20%2F%20only_new%27%21A1%3AAL1.json" "values_%27pokemon_list%20%2F%20only_new%27%21A1%3AAJ1.json"
git mv "values_%27pokemon_list%20%2F%20only_new%27%21A2%3AAL.json" "values_%27pokemon_list%20%2F%20only_new%27%21A2%3AAJ.json"
git mv "values_%27pokemon_list%20%2F%20update_regional_form%27%21A1%3AAL1.json" "values_%27pokemon_list%20%2F%20update_regional_form%27%21A1%3AAJ1.json"
git mv "values_%27pokemon_list%20%2F%20update_regional_form%27%21A2%3AAL.json" "values_%27pokemon_list%20%2F%20update_regional_form%27%21A2%3AAJ.json"
git mv "values_%27pokemon_list%20%2F%20update_type%27%21A1%3AAL1.json" "values_%27pokemon_list%20%2F%20update_type%27%21A1%3AAJ1.json"
git mv "values_%27pokemon_list%20%2F%20update_type%27%21A2%3AAL.json" "values_%27pokemon_list%20%2F%20update_type%27%21A2%3AAJ.json"
git mv "values_%27wrong_sheet%27%21A1%3AAL1.json" "values_%27wrong_sheet%27%21A1%3AAJ1.json"
```

- [ ] **Step 2: Fix each header file's `range` field and column list**

For each of the 9 renamed `...A1%3AAJ1.json` files: open it, update the `"range"` JSON field by replacing the trailing `AL1` with `AJ1` (e.g. `"'Pokémons'!A1:AL1"` → `"'Pokémons'!A1:AJ1"`). Then, **only if** the file's `"values"` header row contains the literal strings `"Icon Source Url"` and `"Shiny Icon Source Url"` (the real `Pokémons` header and the `pokemon_list / *` scenario headers do; the `empty` and `wrong_sheet` fixtures may not — check each), remove those two entries from the row array. Note their column names' exact position before removing — you'll need the same two index positions to fix the sibling `A2:AJ` data file in Step 3. `different_columns_order` deliberately shuffles column order, so compute its indices independently from the other files; do not assume they match.

Worked example — `values_%27Pok%C3%A9mons%27%21A1%3AAJ1.json` (formerly `...AL1.json`), whose original 38-column header (indices 0-37) was:

```
0 Bankable, 1 Bankable-ish, 2 Breeedable Form, 3 #Origin, 4 #Games First Appears On,
5 #Form variant, 6 #Regional form, 7 #Special form, 8 #Category form, 9 #Family,
10 Family order, 11 Pokémon Nom Complet, 12 Pokémon Nom simplifié, 13 Slug, 14 Forme,
15 Pokémon Nom Complet Fr, 16 Pokémon Nom simplifié Fr, 17 Forme Fr, 18 Dex, 19 Sprites,
20 Shiny Sprites, 21 Icon, 22 Sprites url, 23 Shiny Sprites url, 24 #Type 1, 25 #Type 2,
26 Species number, 27 MBCMechachu sprites index, 28 PokemonDB icon name, 29 PokemonDB icon dex,
30 generic-slug, 31 #Groups, 32 Icon Source, 33 Icon Source Url, 34 Shiny Icon Source,
35 Shiny Icon Source Url, 36 Sprites Source, 37 Shiny Sprites Source
```

Remove index 35 (`Shiny Icon Source Url`) then index 33 (`Icon Source Url`) — removing the higher index first avoids re-indexing headaches. Resulting header (36 entries) ends `..., 30 generic-slug, 31 #Groups, 32 Icon Source, 33 Shiny Icon Source, 34 Sprites Source, 35 Shiny Sprites Source`, and the JSON `"range"` becomes `"'Pokémons'!A1:AJ1"`.

- [ ] **Step 3: Fix each data file's rows using the same two indices as its header sibling**

For each of the 9 renamed `...A2%3AAJ.json` files: update the `"range"` field the same way (trailing `AL` → `AJ`, e.g. `"'Pokémons'!A2:AL"` → `"'Pokémons'!A2:AJ"`). Then, for every row in `"values"`, remove the entries at the same two indices you removed from the sibling header file in Step 2 — but only if the row is long enough to contain them (the Google Sheets mock omits trailing empty cells, so many rows are shorter than 38 entries and need no edit at all). Always remove the higher index first.

Worked example — `values_%27pokemon_list%20%2F%20only_new%27%21A2%3AAJ.json` (formerly `...AL.json`), whose header sibling has the credit columns at the same indices 33/35 as the `Pokémons` example above (verify this against that file's own header before editing — don't assume). The first row (Mega Charizard X) is the only one long enough (38 entries) to be affected:

Before (last 6 of 38 entries, indices 32-37): `"PokéSprite", "https://github.com/msikma/pokesprite/charizard-mega-x", "PokéSprite Shiny", "https://github.com/msikma/pokesprite/charizard-mega-x-shiny", "PokemonDB", "PokemonDB Shiny"`

After removing index 35 then index 33 (last 4 of 36 entries): `"PokéSprite", "PokéSprite Shiny", "PokemonDB", "PokemonDB Shiny"`

The other 7 rows in this file are all shorter than index 33 already (they're truncated before the credit columns) — leave them untouched. This is the row Task 5 Step 6's integration-test assertion depends on (`source` = `'PokéSprite'` for `charizard-mega-x` small-regular).

- [ ] **Step 4: Fix the `empty` and `wrong_sheet` fixtures**

`values_%27empty%27%21A1%3AAJ1.json` and `values_%27wrong_sheet%27%21A1%3AAJ1.json` exist to make header validation fail or exercise an empty-sheet path — check their `"values"` content. If it doesn't literally contain `"Icon Source Url"`/`"Shiny Icon Source Url"`, only the `"range"` field needs the `AL1` → `AJ1` fix (already done as part of renaming if you follow Step 2's instruction generically); if it does mirror the real header, apply the same removal as Step 2.

- [ ] **Step 5: Update `test.moco.json`**

Open `tests/resources/moco/Sheets/test.moco.json`. It has 20 entries (out of ~120 total) whose request URI or response file references one of the 18 renamed files (indices in the original file, per the earlier research: 7, 8, 9, 45-55, 66, 67, 108, 109, 115, 116 — re-locate them by content since edits may shift line numbers). For every one of those 20 entries: replace `AL1` with `AJ1` in the request URI, and replace the response file path with the new filename from the Step 1 table (both the URI and the file path need the same substitution). Do **not** touch any entry whose range is `Regional Dex Number` (e.g. `A1002:L1006`) — those coincidentally contain the substring `AL1` too but are unrelated data (verify each match is genuinely a `:AL1` or `:AL` *Sheets column range* boundary for the `Pokémons` / `pokemon_list / *` / `empty` / `wrong_sheet` sheets before editing it).

- [ ] **Step 6: Update `int.moco.json`**

Same as Step 5, but for `tests/resources/moco/Sheets/int.moco.json`'s 6 matching entries (`'Pokémons'!A1:AL1`, `'Pokémons'!A2:AL`, `'pokemon_list / family_link'!A1:AL1`, `'pokemon_list / family_link'!A2:AL`, `'pokemon_list / update_type'!A1:AL1`, `'pokemon_list / update_type'!A2:AL`). This file's response `file` paths already point at `/var/moco/test/...` (it reuses the `test/` fixtures) — update them to the same new filenames from Step 1.

- [ ] **Step 7: Validate moco routing and run the full Updater test suite**

```bash
make check-moco-refs
docker compose exec php php vendor/bin/phpunit tests/src/Unit/Updater/PokemonsUpdaterTest.php tests/src/Integration/Updater/PokemonsUpdaterTest.php
```

Expected: `make check-moco-refs` reports no dangling/missing references, all PHPUnit tests PASS (this also exercises the Task 5 Step 6 integration assertion end-to-end).

---

### Task 7: Full verification and single commit

**Files:** none (verification only)

- [ ] **Step 1: Run the full test suite**

```bash
make tests
```

Expected: 0 failures.

- [ ] **Step 2: Run coverage and mutation testing**

```bash
make measures
```

Expected: 100% coverage, 100% MSI.

- [ ] **Step 3: Run quality checks**

```bash
make quality
```

Expected: PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint all green. Pay particular attention to jsonlint (moco fixtures) and Deptrac (no new cross-layer dependency was introduced, this task only edits existing method bodies).

- [ ] **Step 4: `make integration` (optional but recommended given the header/range change)**

```bash
make init-db
make integration
```

Expected: Postman/Newman suite green — this is the only check that would catch a live-shaped mismatch between the (now `AJ`-ranged) `PokemonsUpdater` expectations and the fixture data end-to-end via `app:update:pokemons`.

- [ ] **Step 5: Squash into a single commit for the whole branch delta**

Per standing instruction, the branch must end with one grouped commit for this whole change, not one per task. Find the commit that was HEAD before Task 1 started (`PRE_PLAN_HEAD`), then soft-reset onto it and recommit everything as one commit:

```bash
git reset --soft PRE_PLAN_HEAD
git status # sanity-check everything from Tasks 1-6 is staged
git commit -m "$(cat <<'EOF'
Collapse per-image credit name+url into a single source/credit field

The image-credit feature originally stored two columns per image slot
(source name + source url). Per product decision, a single free-text
field is enough — the URL will be embedded as text by whoever fills the
spreadsheet. Collapses pokemon_image_credit.source_name/source_url into
one `source` column (entity, migration, repository, updater), and the
API-facing ImageCreditResponse DTO from {name, url} to a single `credit`
string. Sheets sync drops the 2 now-unneeded "* Source Url" columns,
shrinking the consumed range from A1:AL1/A2:AL to A1:AJ1/A2:AJ.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```
