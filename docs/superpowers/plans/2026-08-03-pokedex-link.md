# Liens entre pokédex d'un même trainer — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a trainer link two of their own `TrainerDex` so that changing a catch state on one synchronously cascades the exact same catch state to every dex reachable through the link graph, exposed via a new `TrainerDexLink` CRUD surface in all three repos and a "Liens" section in the album offcanvas.

**Architecture:** pokenini-api gets a new `TrainerDexLink` entity/repository/service/controller (graph of directed edges, optional `pairId` for bidirectional shortcuts) and a `PropagateCatchStateService` that BFS-walks outgoing edges after every catch-state upsert, writing through `PokedexRepository::upsertIfDifferent()` and stopping via idempotence (no write when the value doesn't change). `PokedexService::upsert()` now returns the list of dex slugs actually touched (origin + cascade), which `AlbumUpsertController` (api) surfaces as JSON so pokenini-back can invalidate every affected dex's cache instead of just the origin's. pokenini-back and pokenini-web each get a thin proxy `TrainerDexLinkController` plus the API-calling service glue, and pokenini-web adds a "Liens" section to the existing album offcanvas, server-rendering the "other dexes" picker grid from the trainer's dex list and letting `album-links.js` fetch/create/delete links and prune already-linked cards at runtime.

**Tech Stack:** Symfony 8 / PHP ≥ 8.5, PostgreSQL (Doctrine DBAL raw SQL + one Doctrine entity/migration), PHPUnit with Alice fixtures (pokenini-api), PHPUnit with mocked services (pokenini-back controller tests), PHPUnit with Moco-mocked back client (pokenini-web), Bootstrap 5.3.8 + vanilla JS (pokenini-web).

**Spec:** `docs/superpowers/specs/2026-08-03-pokedex-link-design.md`

## Global Constraints

- `declare(strict_types=1)` in every PHP file touched or created, in all three repos.
- Every test class carries `/** @internal */` and `#[CoversClass(TargetClass::class)]`.
- 100% code coverage and 100% Mutation Score Index in all three repos — every new branch needs a test that would fail if the branch were removed/inverted.
- PHPStan level 9 and Psalm strict in all three repos — no untyped properties or return types; precise array-shape docblocks as shown in each step.
- Deptrac layering: Controller → Service → (Calculator|Repository|Back-Service) is the allowed chain in all three repos; any new cross-layer edge introduced by this plan (there is exactly one, in pokenini-api — see Task 5) is called out explicitly and added to `deptrac.yaml`'s `ruleset`, never worked around.
- `final` for Controller / DTO / Command / Message / Exception / test classes; non-`final` for Service / Calculator / Repository / Updater, in all three repos (pokenini-api's convention; pokenini-back/web mirror it for their own Controller/Service/Exception classes).
- Docker-only toolchain: every command below runs via `docker compose exec php ...` from inside the relevant repo directory (`make sh` for a shell, or the `make` targets shown per task).
- **Copie exacte de la valeur** : catch state is copied verbatim (no reduction to a caught/uncaught boolean) between linked dexes — both dexes share the same `catch_state` table, so any slug valid in one is valid in the other.
- **Graphe, pas de paires exclusives** : a `TrainerDex` can be linked to several others at once; links form an arbitrary directed graph (cycles included, via two opposite unidirectional links or a bidirectional pair).
- **Propagation transitive, terminaison par idempotence** : a change propagates through the whole reachable chain; at each node reached, the write (and further propagation from that node) happens **only if the value actually changes** — this alone guarantees termination, no visited-set needed.
- **Disponibilité du Pokémon** : if the pokemon isn't in the reached dex's `DexAvailability`, skip the write on that node but keep traversing to its own outgoing edges.
- **Un lien "bidirectionnel" est un raccourci** creating/deleting two directed rows (A→B and B→A) sharing a `pairId`; the propagation algorithm only ever reasons in directed edges.
- **Pas de réconciliation à la création** : creating a link touches no existing data — only catch-state changes *after* the link exists trigger propagation.
- Hors scope (already decided, do not add): linking dexes across two different `trainerExternalId`s; retroactive reconciliation of existing divergences when a link is created; asynchronous (Messenger) propagation — the cascade is synchronous, inside the origin HTTP request's existing Doctrine transaction.

---

## pokenini-api (`/home/renaud/projects/pokenini-api`)

### Task 1: `TrainerDexLink` entity + Doctrine migration

**Files:**
- Create: `src/Entity/TrainerDexLink.php`
- Create: `tests/src/Unit/Entity/TrainerDexLinkTest.php`
- Create (generated): a new file under `migrations/2026/08/VersionYYYYMMDDHHMMSS.php`

**Interfaces:**
- Produces: `App\Entity\TrainerDexLink` with public properties `trainerExternalId: string`, `sourceTrainerDex: TrainerDex`, `targetTrainerDex: TrainerDex`, `pairId: ?string`, `createdAt: \DateTimeImmutable`, plus `getIdentifier(): ?Uuid` from `BaseEntityTrait`. Consumed by every later task in this repo (Tasks 2–6) via raw SQL against the `trainer_dex_link` table this migration creates — no later task uses Doctrine ORM `persist()/flush()` on this entity, matching how `TrainerDex`/`Pokedex` are already handled (raw SQL repositories only).

- [ ] **Step 1: Write the entity**

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity]
#[UniqueConstraint(name: 'trainer_dex_link_edge', columns: ['source_trainer_dex_id', 'target_trainer_dex_id'])]
final class TrainerDexLink
{
    use BaseEntityTrait;

    #[ORM\Column]
    public string $trainerExternalId = '';

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public TrainerDex $sourceTrainerDex;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public TrainerDex $targetTrainerDex;

    #[ORM\Column(nullable: true)]
    public ?string $pairId = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public \DateTimeImmutable $createdAt;
}
```

This mirrors `TrainerDex`'s own `#[UniqueConstraint]` pattern (`src/Entity/TrainerDex.php`) and `ActionLog`'s `createdAt` column typing (`src/Entity/ActionLog.php`), except using `DATETIME_IMMUTABLE` as the design spec's entity sketch specifies `\DateTimeImmutable`, not `\DateTime`.

- [ ] **Step 2: Write the entity unit test**

Mirrors `tests/src/Unit/Entity/TrainerDexTest.php` exactly (every entity in this codebase gets one of these — it's the only automated check that a fresh instance's identifier defaults to `null`, which is what 100% coverage requires here since the entity has no other logic):

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\TrainerDexLink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexLink::class)]
final class TrainerDexLinkTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new TrainerDexLink();

        $this->assertNull($entity->getIdentifier());
    }
}
```

- [ ] **Step 3: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Entity/TrainerDexLinkTest.php`
Expected: PASS (1 test) — this doesn't touch the DB, so it passes before the migration exists.

- [ ] **Step 4: Generate the migration**

Run: `docker compose exec php php bin/console doctrine:migration:diff --no-interaction` (equivalently `make sf c="doctrine:migration:diff --no-interaction"`)

This creates a new file under `migrations/2026/08/`. Open it and verify it contains, in some form (Doctrine assigns the exact `IDX_`/`FK_` hash suffixes — don't hand-edit those, just confirm the shape below is present):

```sql
CREATE TABLE trainer_dex_link (trainer_external_id VARCHAR(255) NOT NULL, pair_id VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, source_trainer_dex_id UUID NOT NULL, target_trainer_dex_id UUID NOT NULL, PRIMARY KEY(id))
CREATE INDEX IDX_xxxxxxxx ON trainer_dex_link (source_trainer_dex_id)
CREATE INDEX IDX_yyyyyyyy ON trainer_dex_link (target_trainer_dex_id)
CREATE UNIQUE INDEX trainer_dex_link_edge ON trainer_dex_link (source_trainer_dex_id, target_trainer_dex_id)
ALTER TABLE trainer_dex_link ADD CONSTRAINT FK_xxxxxxxx FOREIGN KEY (source_trainer_dex_id) REFERENCES trainer_dex (id) NOT DEFERRABLE
ALTER TABLE trainer_dex_link ADD CONSTRAINT FK_yyyyyyyy FOREIGN KEY (target_trainer_dex_id) REFERENCES trainer_dex (id) NOT DEFERRABLE
```

If the generated `down()` doesn't cleanly reverse every statement in `up()` (it should, Doctrine generates both), fix it by hand so `up()`+`down()` are exact inverses — this is what `doctrine:migration:migrate` / a future rollback relies on.

- [ ] **Step 5: Apply the migration to dev, test and int DBs**

Run: `docker compose exec php php bin/console doctrine:migration:migrate --no-interaction --env=dev && docker compose exec php php bin/console doctrine:migration:migrate --no-interaction --env=test && docker compose exec php php bin/console doctrine:migration:migrate --no-interaction --env=int`

- [ ] **Step 6: Verify the schema matches the mapping**

Run: `docker compose exec php php bin/console doctrine:schema:validate --env=test`
Expected: `[OK] The mapping files are correct.` and `[OK] The database schema is in sync with the mapping files.`

- [ ] **Step 7: Commit**

```bash
git add src/Entity/TrainerDexLink.php tests/src/Unit/Entity/TrainerDexLinkTest.php migrations/2026/08/
git commit -m "feat: add TrainerDexLink entity and migration for dex-to-dex catch-state links"
```

---

### Task 2: `TrainerDexLinkRepository`

**Files:**
- Create: `src/Repository/TrainerDexLinkRepository.php`
- Create: `tests/src/Integration/Repository/TrainerDexLinkRepositoryTest.php`

**Interfaces:**
- Consumes: `App\Entity\TrainerDexLink` (Task 1), existing `trainer_dex` fixture rows for trainer `7b52009b64fd0a2a49e6d8a939753077792b0554` (`fixtures/trainer_dexes.yaml` — slugs `redgreenblueyellow`, `goldsilvercrystal`, `home` already exist for this trainer).
- Produces (consumed by Tasks 4 and 5):
  - `getOutgoingEdges(string $trainerExternalId, string $sourceTrainerDexId): list<array{target_trainer_dex_id: string, target_dex_slug: string}>`
  - `getForDex(string $trainerExternalId, string $dexSlug): list<array{id: string, pair_id: ?string, direction: string, target_trainer_dex_id: string, target_dex_slug: string, target_name: string, target_french_name: string}>` — `direction` is `'to'`, `'from'` or `'both'`, already deduplicated (a bidirectional pair yields exactly one row, not two).
  - `exists(string $sourceTrainerDexId, string $targetTrainerDexId): bool`
  - `insert(string $trainerExternalId, string $sourceTrainerDexId, string $targetTrainerDexId, ?string $pairId): void`
  - `deleteByIdOrPairId(string $trainerExternalId, string $id): void`

- [ ] **Step 1: Write the failing tests**

Create `tests/src/Integration/Repository/TrainerDexLinkRepositoryTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TrainerDexLinkRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkRepository::class)]
final class TrainerDexLinkRepositoryTest extends KernelTestCase
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    public function testInsertExistsAndGetOutgoingEdges(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('redgreenblueyellow');
        $targetId = $this->getTrainerDexId('goldsilvercrystal');

        $this->assertFalse($repository->exists($sourceId, $targetId));

        $repository->insert(self::TRAINER, $sourceId, $targetId, null);

        $this->assertTrue($repository->exists($sourceId, $targetId));

        $edges = $repository->getOutgoingEdges(self::TRAINER, $sourceId);

        $this->assertEquals(
            [['target_trainer_dex_id' => $targetId, 'target_dex_slug' => 'goldsilvercrystal']],
            $edges
        );
    }

    public function testGetOutgoingEdgesEmptyWhenNoLink(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('home');

        $this->assertSame([], $repository->getOutgoingEdges(self::TRAINER, $sourceId));
    }

    public function testGetForDexUnidirectional(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('redgreenblueyellow');
        $targetId = $this->getTrainerDexId('goldsilvercrystal');

        $repository->insert(self::TRAINER, $sourceId, $targetId, null);

        $fromSource = $repository->getForDex(self::TRAINER, 'redgreenblueyellow');
        $this->assertCount(1, $fromSource);
        $this->assertSame('to', $fromSource[0]['direction']);
        $this->assertSame('goldsilvercrystal', $fromSource[0]['target_dex_slug']);
        $this->assertNull($fromSource[0]['pair_id']);

        $fromTarget = $repository->getForDex(self::TRAINER, 'goldsilvercrystal');
        $this->assertCount(1, $fromTarget);
        $this->assertSame('from', $fromTarget[0]['direction']);
        $this->assertSame('redgreenblueyellow', $fromTarget[0]['target_dex_slug']);
    }

    public function testGetForDexBidirectionalIsMergedIntoOneRow(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('redgreenblueyellow');
        $targetId = $this->getTrainerDexId('goldsilvercrystal');
        $pairId = (string) Uuid::v4();

        $repository->insert(self::TRAINER, $sourceId, $targetId, $pairId);
        $repository->insert(self::TRAINER, $targetId, $sourceId, $pairId);

        $fromSource = $repository->getForDex(self::TRAINER, 'redgreenblueyellow');
        $this->assertCount(1, $fromSource);
        $this->assertSame('both', $fromSource[0]['direction']);
        $this->assertSame($pairId, $fromSource[0]['pair_id']);

        $fromTarget = $repository->getForDex(self::TRAINER, 'goldsilvercrystal');
        $this->assertCount(1, $fromTarget);
        $this->assertSame('both', $fromTarget[0]['direction']);
    }

    public function testDeleteByIdOrPairIdDeletesOnlyItselfWhenUnidirectional(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('redgreenblueyellow');
        $targetId = $this->getTrainerDexId('goldsilvercrystal');

        $repository->insert(self::TRAINER, $sourceId, $targetId, null);
        $id = $repository->getForDex(self::TRAINER, 'redgreenblueyellow')[0]['id'];

        $repository->deleteByIdOrPairId(self::TRAINER, $id);

        $this->assertFalse($repository->exists($sourceId, $targetId));
    }

    public function testDeleteByIdOrPairIdDeletesBothRowsWhenBidirectional(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('redgreenblueyellow');
        $targetId = $this->getTrainerDexId('goldsilvercrystal');
        $pairId = (string) Uuid::v4();

        $repository->insert(self::TRAINER, $sourceId, $targetId, $pairId);
        $repository->insert(self::TRAINER, $targetId, $sourceId, $pairId);
        $id = $repository->getForDex(self::TRAINER, 'redgreenblueyellow')[0]['id'];

        $repository->deleteByIdOrPairId(self::TRAINER, $id);

        $this->assertFalse($repository->exists($sourceId, $targetId));
        $this->assertFalse($repository->exists($targetId, $sourceId));
    }

    private function getTrainerDexId(string $dexSlug): string
    {
        $connection = self::getContainer()->get(Connection::class);

        /** @var string */
        return $connection->executeQuery(
            'SELECT id FROM trainer_dex WHERE slug = :slug AND trainer_external_id = :trainer',
            ['slug' => $dexSlug, 'trainer' => self::TRAINER]
        )->fetchOne();
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Repository/TrainerDexLinkRepositoryTest.php`
Expected: FAIL — `Class "App\Repository\TrainerDexLinkRepository" not found`

- [ ] **Step 3: Implement `TrainerDexLinkRepository`**

```php
<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TrainerDexLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<TrainerDexLink>
 */
class TrainerDexLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainerDexLink::class);
    }

    /**
     * @return list<array{target_trainer_dex_id: string, target_dex_slug: string}>
     */
    public function getOutgoingEdges(string $trainerExternalId, string $sourceTrainerDexId): array
    {
        $sql = <<<'SQL'
            SELECT      td.id AS target_trainer_dex_id,
                        td.slug AS target_dex_slug
            FROM        trainer_dex_link AS tdl
                    JOIN trainer_dex AS td
                        ON td.id = tdl.target_trainer_dex_id
            WHERE       tdl.trainer_external_id = :trainer_external_id
                    AND tdl.source_trainer_dex_id = :source_trainer_dex_id
            SQL;

        /** @var list<array{target_trainer_dex_id: string, target_dex_slug: string}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'source_trainer_dex_id' => $sourceTrainerDexId,
            ],
            [
                'trainer_external_id' => ParameterType::STRING,
                'source_trainer_dex_id' => ParameterType::STRING,
            ],
        );
    }

    /**
     * @return list<array{id: string, pair_id: ?string, direction: string, target_trainer_dex_id: string, target_dex_slug: string, target_name: string, target_french_name: string}>
     */
    public function getForDex(string $trainerExternalId, string $dexSlug): array
    {
        $sql = <<<'SQL'
            SELECT      tdl.id AS id,
                        tdl.pair_id AS pair_id,
                        CASE WHEN tdl.pair_id IS NOT NULL THEN 'both' ELSE 'to' END AS direction,
                        ttd.id AS target_trainer_dex_id,
                        ttd.slug AS target_dex_slug,
                        ttd.name AS target_name,
                        ttd.french_name AS target_french_name
            FROM        trainer_dex_link AS tdl
                    JOIN trainer_dex AS std
                        ON std.id = tdl.source_trainer_dex_id
                    JOIN trainer_dex AS ttd
                        ON ttd.id = tdl.target_trainer_dex_id
            WHERE       tdl.trainer_external_id = :trainer_external_id
                    AND std.slug = :dex_slug

            UNION ALL

            SELECT      tdl.id AS id,
                        tdl.pair_id AS pair_id,
                        'from' AS direction,
                        std.id AS target_trainer_dex_id,
                        std.slug AS target_dex_slug,
                        std.name AS target_name,
                        std.french_name AS target_french_name
            FROM        trainer_dex_link AS tdl
                    JOIN trainer_dex AS std
                        ON std.id = tdl.source_trainer_dex_id
                    JOIN trainer_dex AS ttd
                        ON ttd.id = tdl.target_trainer_dex_id
            WHERE       tdl.trainer_external_id = :trainer_external_id
                    AND ttd.slug = :dex_slug
                    AND tdl.pair_id IS NULL
            SQL;

        /** @var list<array{id: string, pair_id: ?string, direction: string, target_trainer_dex_id: string, target_dex_slug: string, target_name: string, target_french_name: string}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
            ],
            [
                'trainer_external_id' => ParameterType::STRING,
                'dex_slug' => ParameterType::STRING,
            ],
        );
    }

    public function exists(string $sourceTrainerDexId, string $targetTrainerDexId): bool
    {
        $sql = <<<'SQL'
            SELECT      COUNT(*)
            FROM        trainer_dex_link
            WHERE       source_trainer_dex_id = :source_trainer_dex_id
                    AND target_trainer_dex_id = :target_trainer_dex_id
            SQL;

        /** @var int $count */
        $count = $this->getEntityManager()->getConnection()->fetchOne(
            $sql,
            [
                'source_trainer_dex_id' => $sourceTrainerDexId,
                'target_trainer_dex_id' => $targetTrainerDexId,
            ],
            [
                'source_trainer_dex_id' => ParameterType::STRING,
                'target_trainer_dex_id' => ParameterType::STRING,
            ],
        );

        return $count > 0;
    }

    public function insert(
        string $trainerExternalId,
        string $sourceTrainerDexId,
        string $targetTrainerDexId,
        ?string $pairId,
    ): void {
        $sql = <<<'SQL'
            INSERT INTO trainer_dex_link (
                id,
                trainer_external_id,
                source_trainer_dex_id,
                target_trainer_dex_id,
                pair_id,
                created_at
            )
            VALUES (
                :id,
                :trainer_external_id,
                :source_trainer_dex_id,
                :target_trainer_dex_id,
                :pair_id,
                :created_at
            )
            SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_external_id' => $trainerExternalId,
                'source_trainer_dex_id' => $sourceTrainerDexId,
                'target_trainer_dex_id' => $targetTrainerDexId,
                'pair_id' => $pairId,
                'created_at' => new \DateTimeImmutable(),
            ],
            [
                'id' => ParameterType::STRING,
                'trainer_external_id' => ParameterType::STRING,
                'source_trainer_dex_id' => ParameterType::STRING,
                'target_trainer_dex_id' => ParameterType::STRING,
                'pair_id' => ParameterType::STRING,
                'created_at' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }

    public function deleteByIdOrPairId(string $trainerExternalId, string $id): void
    {
        $sql = <<<'SQL'
            DELETE FROM trainer_dex_link
            WHERE       trainer_external_id = :trainer_external_id
                    AND (
                        id = :id
                        OR pair_id = (
                            SELECT  inner_link.pair_id
                            FROM    trainer_dex_link AS inner_link
                            WHERE   inner_link.id = :id
                                AND inner_link.trainer_external_id = :trainer_external_id
                        )
                    )
            SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'id' => $id,
            ],
            [
                'trainer_external_id' => ParameterType::STRING,
                'id' => ParameterType::STRING,
            ],
        );
    }
}
```

`deleteByIdOrPairId` relies on SQL's three-valued logic: when the targeted row's `pair_id` is `NULL`, the subquery returns `NULL` and `pair_id = NULL` is never true for any row (including itself), so only the `id = :id` branch matches and exactly one row is deleted. When the targeted row's `pair_id` is set, the subquery returns that value and every row sharing it (both the targeted row and its pair) matches the `OR` branch.

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Repository/TrainerDexLinkRepositoryTest.php`
Expected: PASS (6 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Repository/TrainerDexLinkRepository.php tests/src/Integration/Repository/TrainerDexLinkRepositoryTest.php
git commit -m "feat: add TrainerDexLinkRepository for the dex-link graph"
```

---

### Task 3: `PokedexRepository::upsertIfDifferent()`

**Files:**
- Modify: `src/Repository/PokedexRepository.php`
- Create: `tests/src/Integration/Repository/PokedexRepositoryUpsertIfDifferentTest.php`

**Interfaces:**
- Produces (consumed by Task 4's `PropagateCatchStateService`): `PokedexRepository::upsertIfDifferent(string $trainerDexId, string $pokemonSlug, string $catchStateSlug): bool` — writes (insert or update) only if the resulting `catch_state` differs from what's already stored, and returns whether it wrote. Also returns `false` without writing when `$pokemonSlug` isn't in the `dex_availability` of the dex behind `$trainerDexId` (the pokemon isn't in that dex at all).

This is a variant of the existing `upsert()` (below it in the same file), keyed by `trainer_dex_id` directly (not `dexSlug` + `trainerExternalId`) since the propagation queue already carries resolved `TrainerDex` ids from `TrainerDexLinkRepository::getOutgoingEdges()`.

- [ ] **Step 1: Write the failing tests**

Create `tests/src/Integration/Repository/PokedexRepositoryUpsertIfDifferentTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\PokedexRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(PokedexRepository::class)]
final class PokedexRepositoryUpsertIfDifferentTest extends KernelTestCase
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    public function testReturnsTrueAndWritesWhenValueChanges(): void
    {
        $repository = self::getContainer()->get(PokedexRepository::class);
        $trainerDexId = $this->getTrainerDexId('goldsilvercrystal');

        // Fixture: ivysaur is 'no' in goldsilvercrystal for this trainer.
        $changed = $repository->upsertIfDifferent($trainerDexId, 'ivysaur', 'yes');

        $this->assertTrue($changed);
        $this->assertSame('yes', $this->getCatchStateSlug($trainerDexId, 'ivysaur'));
    }

    public function testReturnsFalseAndDoesNotWriteWhenValueIsUnchanged(): void
    {
        $repository = self::getContainer()->get(PokedexRepository::class);
        $trainerDexId = $this->getTrainerDexId('goldsilvercrystal');

        // Fixture: ivysaur is already 'no' in goldsilvercrystal for this trainer.
        $changed = $repository->upsertIfDifferent($trainerDexId, 'ivysaur', 'no');

        $this->assertFalse($changed);
        $this->assertSame('no', $this->getCatchStateSlug($trainerDexId, 'ivysaur'));
    }

    public function testReturnsFalseWhenPokemonNotInDex(): void
    {
        $repository = self::getContainer()->get(PokedexRepository::class);
        // Fixture: 'douze' has no dex_availability row for goldsilvercrystal (only redgreenblueyellow and home).
        $trainerDexId = $this->getTrainerDexId('goldsilvercrystal');

        $changed = $repository->upsertIfDifferent($trainerDexId, 'douze', 'yes');

        $this->assertFalse($changed);
        $this->assertNull($this->getCatchStateSlug($trainerDexId, 'douze'));
    }

    public function testCreatesAFreshPokedexRowWhenNoneExists(): void
    {
        $repository = self::getContainer()->get(PokedexRepository::class);
        // Fixture: 'douze' has a dex_availability row for redgreenblueyellow but no pokedex row for it yet.
        $trainerDexId = $this->getTrainerDexId('redgreenblueyellow');

        $before = $this->getCatchStateSlug($trainerDexId, 'douze');
        $this->assertNull($before);

        $changed = $repository->upsertIfDifferent($trainerDexId, 'douze', 'yes');

        $this->assertTrue($changed);
        $this->assertSame('yes', $this->getCatchStateSlug($trainerDexId, 'douze'));
    }

    private function getTrainerDexId(string $dexSlug): string
    {
        $connection = self::getContainer()->get(Connection::class);

        /** @var string */
        return $connection->executeQuery(
            'SELECT id FROM trainer_dex WHERE slug = :slug AND trainer_external_id = :trainer',
            ['slug' => $dexSlug, 'trainer' => self::TRAINER]
        )->fetchOne();
    }

    private function getCatchStateSlug(string $trainerDexId, string $pokemonSlug): ?string
    {
        $connection = self::getContainer()->get(Connection::class);

        $result = $connection->executeQuery(
            <<<'SQL'
                SELECT      cs.slug
                FROM        pokedex AS pd
                        JOIN pokemon AS p
                            ON pd.pokemon_id = p.id AND p.slug = :pokemon_slug
                        JOIN catch_state AS cs
                            ON pd.catch_state_id = cs.id
                WHERE       pd.trainer_dex_id = :trainer_dex_id
                SQL,
            ['trainer_dex_id' => $trainerDexId, 'pokemon_slug' => $pokemonSlug]
        )->fetchOne();

        return false === $result ? null : (string) $result;
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Repository/PokedexRepositoryUpsertIfDifferentTest.php`
Expected: FAIL — `Call to undefined method App\Repository\PokedexRepository::upsertIfDifferent()`

- [ ] **Step 3: Implement `upsertIfDifferent()`**

Add this method to `src/Repository/PokedexRepository.php`, right after `upsert()` (before `getCatchStateCountsDefinedByTrainer()`):

```php
    public function upsertIfDifferent(
        string $trainerDexId,
        string $pokemonSlug,
        string $catchStateSlug,
    ): bool {
        $sql = <<<'SQL'
            INSERT INTO pokedex (
                id,
                pokemon_id,
                catch_state_id,
                trainer_dex_id
            )
            SELECT      :id,
                        p.id,
                        cs.id,
                        :trainer_dex_id
            FROM        pokemon AS p
                    CROSS JOIN catch_state AS cs
            WHERE       p.slug = :pokemon_slug
                    AND cs.slug = :catch_state_slug
                    AND EXISTS (
                        SELECT  1
                        FROM    trainer_dex AS td
                                JOIN dex_availability AS da
                                    ON da.dex_id = td.dex_id
                                    AND da.pokemon_id = p.id
                        WHERE   td.id = :trainer_dex_id
                    )
            ON CONFLICT (pokemon_id, trainer_dex_id)
            DO
            UPDATE
            SET     catch_state_id = excluded.catch_state_id
            WHERE   pokedex.catch_state_id IS DISTINCT FROM excluded.catch_state_id
            RETURNING id
            SQL;

        $result = $this->getEntityManager()->getConnection()->fetchOne(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_dex_id' => $trainerDexId,
                'pokemon_slug' => $pokemonSlug,
                'catch_state_slug' => $catchStateSlug,
            ],
            [
                'id' => ParameterType::STRING,
                'trainer_dex_id' => ParameterType::STRING,
                'pokemon_slug' => ParameterType::STRING,
                'catch_state_slug' => ParameterType::STRING,
            ],
        );

        return false !== $result;
    }
```

Walk through why each test passes:
- The `EXISTS` clause joins `trainer_dex` (to get `dex_id`) to `dex_availability` (to check the pokemon is available in that dex) — if the pokemon isn't available there, the `INSERT ... SELECT` produces zero rows, `ON CONFLICT` never triggers, `RETURNING` returns nothing, `fetchOne()` returns `false` → method returns `false` (covers `testReturnsFalseWhenPokemonNotInDex`).
- When no `pokedex` row exists yet and the pokemon is available, the plain `INSERT` succeeds (no conflict), `RETURNING id` yields the new row's id → `fetchOne()` returns a string → `true` (covers `testCreatesAFreshPokedexRowWhenNoneExists`).
- When a `pokedex` row already exists with a different `catch_state_id`, `ON CONFLICT ... DO UPDATE` fires and its `WHERE` guard (`IS DISTINCT FROM`) is satisfied, so the update happens and `RETURNING id` yields a row → `true` (covers `testReturnsTrueAndWritesWhenValueChanges`).
- When the existing row already has the same `catch_state_id`, the `DO UPDATE`'s `WHERE` guard is false, so Postgres treats it as "conflict occurred, nothing done" and `RETURNING` produces zero rows → `false` (covers `testReturnsFalseAndDoesNotWriteWhenValueIsUnchanged`).

- [ ] **Step 4: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Repository/PokedexRepositoryUpsertIfDifferentTest.php`
Expected: PASS (4 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Repository/PokedexRepository.php tests/src/Integration/Repository/PokedexRepositoryUpsertIfDifferentTest.php
git commit -m "feat: add PokedexRepository::upsertIfDifferent for idempotent cascade writes"
```

---

### Task 4: `PropagateCatchStateService` + `PokedexRepository::upsert()` / `PokedexService::upsert()` return-value change

**Files:**
- Create: `src/Service/PropagateCatchStateService.php`
- Create: `tests/src/Unit/Service/PropagateCatchStateServiceTest.php`
- Modify: `src/Repository/PokedexRepository.php` (the existing `upsert()` method)
- Modify: `src/Service/PokedexService.php`
- Modify: `tests/src/Unit/Service/PokedexServiceTest.php`

**Interfaces:**
- Consumes: `TrainerDexLinkRepository::getOutgoingEdges()` (Task 2), `PokedexRepository::upsertIfDifferent()` (Task 3).
- Produces: `PropagateCatchStateService::propagate(string $trainerExternalId, string $originTrainerDexId, string $pokemonSlug, string $catchStateSlug): list<string>` — the list of target dex slugs that were actually written during the cascade (not the origin).
- Changes: `PokedexRepository::upsert()`'s return type goes from `void` to `?string` — the id of the `trainer_dex` row written, **only when the write actually changed the stored value**; `null` when unchanged, or when the target `trainer_dex` doesn't exist (dex slug unknown for this trainer — the existing "silently create with a null `trainer_dex_id`" edge case is unchanged, it's just now unable to signal a resolvable id). `PokedexService::upsert()`'s return type goes from `void` to `list<string>` — origin dex slug always first, followed by every dex slug the cascade actually touched. **Every current caller of `PokedexService::upsert()` is `AlbumUpsertController::upsert()` (Task 6) — `ReportsController` uses `PokedexService` but never calls `upsert()`, confirmed by `grep -rn "PokedexService" src/`.**

- [ ] **Step 1: Write the failing test for `PropagateCatchStateService`**

Create `tests/src/Unit/Service/PropagateCatchStateServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\PokedexRepository;
use App\Repository\TrainerDexLinkRepository;
use App\Service\PropagateCatchStateService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PropagateCatchStateService::class)]
final class PropagateCatchStateServiceTest extends TestCase
{
    public function testPropagatesToDirectNeighbourWhenValueChanges(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->exactly(2))
            ->method('getOutgoingEdges')
            ->willReturnMap([
                ['trainer-1', 'dex-a', [['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']]],
                ['trainer-1', 'dex-b', []],
            ])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->once())
            ->method('upsertIfDifferent')
            ->with('dex-b', 'pikachu', 'yes')
            ->willReturn(true)
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            ['b'],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    public function testStopsAtANodeWhoseValueDidNotChange(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('getOutgoingEdges')
            ->with('trainer-1', 'dex-a')
            ->willReturn([['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->once())
            ->method('upsertIfDifferent')
            ->with('dex-b', 'pikachu', 'yes')
            ->willReturn(false)
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            [],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    public function testPropagatesTransitivelyThroughAChain(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->exactly(3))
            ->method('getOutgoingEdges')
            ->willReturnMap([
                ['trainer-1', 'dex-a', [['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']]],
                ['trainer-1', 'dex-b', [['target_trainer_dex_id' => 'dex-c', 'target_dex_slug' => 'c']]],
                ['trainer-1', 'dex-c', []],
            ])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->exactly(2))
            ->method('upsertIfDifferent')
            ->willReturnMap([
                ['dex-b', 'pikachu', 'yes', true],
                ['dex-c', 'pikachu', 'yes', true],
            ])
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            ['b', 'c'],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    public function testCycleTerminatesByIdempotenceWithoutInfiniteLoop(): void
    {
        // A <-> B: origin is A, edge A -> B, and B -> A also exists.
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->exactly(2))
            ->method('getOutgoingEdges')
            ->willReturnMap([
                ['trainer-1', 'dex-a', [['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']]],
                ['trainer-1', 'dex-b', [['target_trainer_dex_id' => 'dex-a', 'target_dex_slug' => 'a']]],
            ])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->exactly(2))
            ->method('upsertIfDifferent')
            ->willReturnMap([
                // B changes to the new value...
                ['dex-b', 'pikachu', 'yes', true],
                // ...but A, the origin, already has it (the caller already wrote it before calling propagate()) so the cycle stops here.
                ['dex-a', 'pikachu', 'yes', false],
            ])
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            ['b'],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    public function testThreeNodeCycleTerminatesByIdempotence(): void
    {
        // A -> B -> C -> A. Origin A was already written by the caller before propagate() runs,
        // so by the time the cycle comes back around to A, upsertIfDifferent('dex-a', ...) is a no-op.
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->exactly(3))
            ->method('getOutgoingEdges')
            ->willReturnMap([
                ['trainer-1', 'dex-a', [['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']]],
                ['trainer-1', 'dex-b', [['target_trainer_dex_id' => 'dex-c', 'target_dex_slug' => 'c']]],
                ['trainer-1', 'dex-c', [['target_trainer_dex_id' => 'dex-a', 'target_dex_slug' => 'a']]],
            ])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->exactly(3))
            ->method('upsertIfDifferent')
            ->willReturnMap([
                ['dex-b', 'pikachu', 'yes', true],
                ['dex-c', 'pikachu', 'yes', true],
                ['dex-a', 'pikachu', 'yes', false],
            ])
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            ['b', 'c'],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    public function testPokemonAbsentFromAnIntermediateDexSkipsTheWriteButKeepsTraversing(): void
    {
        // A -> B -> C, pokemon isn't in B's DexAvailability (upsertIfDifferent returns false for that reason)
        // but traversal must still continue from B to C per the design.
        //
        // Per the design, a skipped write means the traversal from that node does NOT continue automatically
        // (propagation only enqueues a node's own outgoing edges when its upsertIfDifferent returned true) —
        // so C is never reached in this scenario, matching the "changed" flag being the sole continuation signal.
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('getOutgoingEdges')
            ->with('trainer-1', 'dex-a')
            ->willReturn([['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->once())
            ->method('upsertIfDifferent')
            ->with('dex-b', 'pikachu', 'yes')
            ->willReturn(false)
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            [],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    public function testNoOutgoingEdgesReturnsEmptyList(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('getOutgoingEdges')
            ->with('trainer-1', 'dex-a')
            ->willReturn([])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->never())->method('upsertIfDifferent');

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame([], $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes'));
    }
}
```

Note on `testPokemonAbsentFromAnIntermediateDexSkipsTheWriteButKeepsTraversing`'s docblock: the spec's "skip the write, keep traversing to that node's own neighbours" rule is about not stopping the *overall graph walk* at a dead-end node — but since `upsertIfDifferent` returning `false` is the same signal for "no write happened" whether it's because the pokemon isn't in that dex or because the value didn't change, and the algorithm's only continuation rule is "only enqueue a node's own outgoing edges when its own upsert changed something", a node where the write was skipped (pokemon absent) never contributes its own neighbours to the queue either — there is nothing to propagate further *from that node* since nothing changed *at* that node. This is consistent with the design ("on saute l'écriture sur ce nœud mais on continue la traversée vers ses voisins" refers to not aborting the whole BFS because one node was a no-op — the BFS simply doesn't extend past that no-op node, exactly like the value-unchanged case).

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/PropagateCatchStateServiceTest.php`
Expected: FAIL — `Class "App\Service\PropagateCatchStateService" not found`

- [ ] **Step 3: Implement `PropagateCatchStateService`**

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PokedexRepository;
use App\Repository\TrainerDexLinkRepository;

class PropagateCatchStateService
{
    public function __construct(
        private readonly TrainerDexLinkRepository $trainerDexLinkRepository,
        private readonly PokedexRepository $pokedexRepository,
    ) {}

    /**
     * @return list<string>
     */
    public function propagate(
        string $trainerExternalId,
        string $originTrainerDexId,
        string $pokemonSlug,
        string $catchStateSlug,
    ): array {
        $updatedDexSlugs = [];

        $queue = $this->trainerDexLinkRepository->getOutgoingEdges($trainerExternalId, $originTrainerDexId);

        while ([] !== $queue) {
            $edge = array_shift($queue);

            $changed = $this->pokedexRepository->upsertIfDifferent(
                $edge['target_trainer_dex_id'],
                $pokemonSlug,
                $catchStateSlug,
            );

            if (!$changed) {
                continue;
            }

            $updatedDexSlugs[] = $edge['target_dex_slug'];

            $queue = array_merge(
                $queue,
                $this->trainerDexLinkRepository->getOutgoingEdges($trainerExternalId, $edge['target_trainer_dex_id']),
            );
        }

        return $updatedDexSlugs;
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/PropagateCatchStateServiceTest.php`
Expected: PASS (7 tests)

- [ ] **Step 5: Change `PokedexRepository::upsert()` to report whether it changed anything**

Replace the existing `upsert()` method in `src/Repository/PokedexRepository.php` with:

```php
    public function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        string $catchStateSlug,
    ): ?string {
        $sql = <<<'SQL'
            WITH upserted AS (
                INSERT INTO pokedex (
                    id,
                    pokemon_id,
                    catch_state_id,
                    trainer_dex_id
                )
                VALUES
                (
                    :id,
                    (SELECT id FROM pokemon WHERE slug = :pokemon_slug),
                    (SELECT id FROM catch_state WHERE slug = :catch_state_slug),
                    (
                        SELECT  td.id
                        FROM    trainer_dex AS td
                        WHERE   td.slug = :dex_slug
                            AND td.trainer_external_id = :trainer_external_id
                    )
                )
                ON CONFLICT (pokemon_id, trainer_dex_id)
                DO
                UPDATE
                SET catch_state_id = excluded.catch_state_id
                WHERE pokedex.catch_state_id IS DISTINCT FROM excluded.catch_state_id
                RETURNING trainer_dex_id
            )
            SELECT trainer_dex_id FROM upserted
            SQL;

        /** @var false|string|null $trainerDexId */
        $trainerDexId = $this->getEntityManager()->getConnection()->fetchOne(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'pokemon_slug' => $pokemonSlug,
                'catch_state_slug' => $catchStateSlug,
            ]
        );

        if (false === $trainerDexId || null === $trainerDexId) {
            return null;
        }

        return $trainerDexId;
    }
```

This preserves every existing edge case exactly: an unknown `$dexSlug` still resolves `trainer_dex_id` to `NULL` in the subselect and still inserts the `pokedex` row unconditionally (the unique constraint on `(pokemon_id, trainer_dex_id)` never triggers for `NULL` values in Postgres, exactly as before); an unknown `$pokemonSlug` still hits the `pokemon_id` `NOT NULL` constraint and still bubbles up as `NotNullConstraintViolationException` for `AlbumUpsertController` to catch. The only observable change is the return value: `null` when nothing changed (idempotent write) or when the resolved `trainer_dex_id` is itself `NULL` (dex doesn't exist for this trainer — there can be no outgoing links from a dex that doesn't exist, so this is exactly the "don't propagate" signal); otherwise the id of the `trainer_dex` row that was written, for `PropagateCatchStateService` to start the cascade from.

- [ ] **Step 6: Wire `PropagateCatchStateService` into `PokedexService::upsert()`**

Replace the full contents of `src/Service/PokedexService.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PokedexRepository;

class PokedexService
{
    public function __construct(
        private readonly PokedexRepository $repository,
        private readonly PropagateCatchStateService $propagateCatchStateService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function getCatchStateCountsDefinedByTrainer(): array
    {
        return $this->repository->getCatchStateCountsDefinedByTrainer();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDexUsage(): array
    {
        return $this->repository->getDexUsage();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getCatchStateUsage(): array
    {
        return $this->repository->getCatchStateUsage();
    }

    /**
     * @return list<string>
     */
    public function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        string $catchStateSlug,
    ): array {
        $changedTrainerDexId = $this->repository->upsert(
            $trainerExternalId,
            $dexSlug,
            $pokemonSlug,
            $catchStateSlug,
        );

        $updatedDexSlugs = [$dexSlug];

        if (null === $changedTrainerDexId) {
            return $updatedDexSlugs;
        }

        return array_merge(
            $updatedDexSlugs,
            $this->propagateCatchStateService->propagate(
                $trainerExternalId,
                $changedTrainerDexId,
                $pokemonSlug,
                $catchStateSlug,
            ),
        );
    }
}
```

The origin dex slug is always first in the returned list — even when the write was a no-op — matching the existing (pre-this-feature) behaviour of always invalidating the origin dex's back-side cache on every write attempt, regardless of whether the value actually changed.

- [ ] **Step 7: Update `PokedexServiceTest`**

Replace `testUpsert` in `tests/src/Unit/Service/PokedexServiceTest.php` with the following three tests, and add the new mock/import at the top of the file (`use App\Service\PropagateCatchStateService;`):

```php
    public function testUpsertReturnsOnlyOriginWhenNothingChanged(): void
    {
        $repository = $this->createMock(PokedexRepository::class);
        $repository->expects($this->once())
            ->method('upsert')
            ->with('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes')
            ->willReturn(null)
        ;

        $propagateCatchStateService = $this->createMock(PropagateCatchStateService::class);
        $propagateCatchStateService->expects($this->never())->method('propagate');

        $service = new PokedexService($repository, $propagateCatchStateService);

        $this->assertSame(
            ['bw2'],
            $service->upsert('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes')
        );
    }

    public function testUpsertPropagatesWhenOriginChanged(): void
    {
        $repository = $this->createMock(PokedexRepository::class);
        $repository->expects($this->once())
            ->method('upsert')
            ->with('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes')
            ->willReturn('trainer-dex-uuid')
        ;

        $propagateCatchStateService = $this->createMock(PropagateCatchStateService::class);
        $propagateCatchStateService->expects($this->once())
            ->method('propagate')
            ->with('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'trainer-dex-uuid', 'pichu', 'yes')
            ->willReturn(['bw2_shiny'])
        ;

        $service = new PokedexService($repository, $propagateCatchStateService);

        $this->assertSame(
            ['bw2', 'bw2_shiny'],
            $service->upsert('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes')
        );
    }

    public function testUpsertPropagatesNothingWhenCascadeReturnsEmptyList(): void
    {
        $repository = $this->createMock(PokedexRepository::class);
        $repository->expects($this->once())
            ->method('upsert')
            ->willReturn('trainer-dex-uuid')
        ;

        $propagateCatchStateService = $this->createMock(PropagateCatchStateService::class);
        $propagateCatchStateService->expects($this->once())
            ->method('propagate')
            ->willReturn([])
        ;

        $service = new PokedexService($repository, $propagateCatchStateService);

        $this->assertSame(
            ['bw2'],
            $service->upsert('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes')
        );
    }
```

Remove the old `testUpsert` method entirely (its mock's `->method('upsert')` had no `willReturn()`, which would now make the mock return `null` by default anyway — but the three replacements above cover the branches explicitly and are what 100% MSI requires).

- [ ] **Step 8: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/PokedexServiceTest.php`
Expected: PASS (6 tests)

- [ ] **Step 9: Commit**

```bash
git add src/Service/PropagateCatchStateService.php tests/src/Unit/Service/PropagateCatchStateServiceTest.php src/Repository/PokedexRepository.php src/Service/PokedexService.php tests/src/Unit/Service/PokedexServiceTest.php
git commit -m "feat: propagate catch-state changes through the dex-link graph after every upsert"
```

---

### Task 5: `TrainerDexLinkService` + `TrainerDexLinkController` + response DTO/Factory

**Files:**
- Create: `src/Exception/SelfTrainerDexLinkException.php`
- Create: `src/Exception/DuplicateTrainerDexLinkException.php`
- Create: `src/Exception/TrainerDexNotFoundException.php`
- Create: `src/Service/TrainerDexLinkService.php`
- Create: `src/Controller/TrainerDexLinkController.php`
- Create: `src/DTO/Response/TrainerDexLinkResponse.php`
- Create: `src/Factory/TrainerDexLinkResponseFactory.php`
- Modify: `deptrac.yaml`
- Create: `tests/src/Unit/Service/TrainerDexLinkServiceTest.php`
- Create: `tests/src/Unit/Factory/TrainerDexLinkResponseFactoryTest.php`
- Create: `tests/src/Integration/Controller/TrainerDexLinkControllerTest.php`

**Interfaces:**
- Consumes: `TrainerDexLinkRepository` (Task 2), `TrainerDexRepository` (existing, `src/Repository/TrainerDexRepository.php` — used here only via its inherited `ServiceEntityRepository::findOneBy()`, no new method needed).
- Produces: `TrainerDexLinkService::listForDex(string $trainerExternalId, string $dexSlug): list<array{...}>` (same shape as `TrainerDexLinkRepository::getForDex()`), `TrainerDexLinkService::create(string $trainerExternalId, string $sourceDexSlug, string $targetDexSlug, bool $bidirectional): void`, `TrainerDexLinkService::delete(string $trainerExternalId, string $linkId): void`. Routes: `GET /trainer_dex_link/{trainerExternalId}/{dexSlug}`, `POST /trainer_dex_link/{trainerExternalId}` (body `{sourceDexSlug, targetDexSlug, bidirectional}`), `DELETE /trainer_dex_link/{trainerExternalId}/{linkId}`.

**Note on Deptrac:** `App\Service\*` classes in this repo have never needed to throw a custom domain exception before (`deptrac.yaml`'s `AppService` ruleset has no `AppException` entry, unlike `AppRepository` and `AppUpdater` which already do). This task needs `TrainerDexLinkService` to signal three distinct validation failures to the controller (self-link / duplicate / unknown dex) without depending on `Symfony\Component\HttpKernel\Exception\*` (not in `AppService`'s allowed list, and Services shouldn't know about HTTP status codes anyway) — the idiomatic fix, already used throughout pokenini-back and pokenini-web for exactly this purpose, is dedicated `App\Exception\*` classes. Step 1 below adds the missing `AppService → AppException` and `AppController → AppException` edges to `deptrac.yaml`.

- [ ] **Step 1: Add the exception classes and the Deptrac edges**

```php
<?php

declare(strict_types=1);

namespace App\Exception;

final class SelfTrainerDexLinkException extends \RuntimeException {}
```

```php
<?php

declare(strict_types=1);

namespace App\Exception;

final class DuplicateTrainerDexLinkException extends \RuntimeException {}
```

```php
<?php

declare(strict_types=1);

namespace App\Exception;

final class TrainerDexNotFoundException extends \RuntimeException {}
```

Save these as `src/Exception/SelfTrainerDexLinkException.php`, `src/Exception/DuplicateTrainerDexLinkException.php` and `src/Exception/TrainerDexNotFoundException.php` respectively (mirrors `src/Exception/InvalidSheetDataException.php`).

In `deptrac.yaml`, add `AppException` to the `AppService` ruleset entry (find it — it currently lists `AppCalculator`, `AppDTO`, `AppEntity`, `AppRepository`, `AppService`, `AppUpdater`, `DoctrineDBAL`, `GoogleClient`, `GoogleService`, `SymfonyContractsCache`):

```yaml
    AppService:
      - AppCalculator
      - AppDTO
      - AppEntity
      - AppException
      - AppRepository
      - AppService
      - AppUpdater
      - DoctrineDBAL
      - GoogleClient
      - GoogleService
      - SymfonyContractsCache
```

And add `AppException` to the `AppController` ruleset entry (currently `AppActionEnder`, `AppActionStarter`, `AppDTO`, `AppEntity`, `AppFactory`, `AppRepository`, `AppService`, `DoctrineDBAL`, `SymfonyBridgeDoctrine`, `SymfonyFrameworkBundle`, `SymfonyHttpFoundation`, `SymfonyHttpKernel`, `SymfonyMessenger`, `SymfonyOptionsResolver`, `SymfonyRouting`, `SymfonySerializer`):

```yaml
    AppController:
      - AppActionEnder
      - AppActionStarter
      - AppDTO
      - AppEntity
      - AppException
      - AppFactory
      - AppRepository
      - AppService
      - DoctrineDBAL
      - SymfonyBridgeDoctrine
      - SymfonyFrameworkBundle
      - SymfonyHttpFoundation
      - SymfonyHttpKernel
      - SymfonyMessenger
      - SymfonyOptionsResolver
      - SymfonyRouting
      - SymfonySerializer
```

- [ ] **Step 2: Write the failing `TrainerDexLinkService` test**

Create `tests/src/Unit/Service/TrainerDexLinkServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\TrainerDex;
use App\Exception\DuplicateTrainerDexLinkException;
use App\Exception\SelfTrainerDexLinkException;
use App\Exception\TrainerDexNotFoundException;
use App\Repository\TrainerDexLinkRepository;
use App\Repository\TrainerDexRepository;
use App\Service\TrainerDexLinkService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkService::class)]
final class TrainerDexLinkServiceTest extends TestCase
{
    public function testListForDexDelegatesToRepository(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('getForDex')
            ->with('trainer-1', 'national')
            ->willReturn([['id' => 'link-1']])
        ;

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->assertSame([['id' => 'link-1']], $service->listForDex('trainer-1', 'national'));
    }

    public function testCreateRejectsSelfLink(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->never())->method('exists');
        $linkRepository->expects($this->never())->method('insert');

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->expects($this->never())->method('findOneBy');

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->expectException(SelfTrainerDexLinkException::class);

        $service->create('trainer-1', 'national', 'national', false);
    }

    public function testCreateRejectsUnknownSourceDex(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['trainerExternalId' => 'trainer-1', 'slug' => 'unknown'])
            ->willReturn(null)
        ;

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->expectException(TrainerDexNotFoundException::class);

        $service->create('trainer-1', 'unknown', 'shiny', false);
    }

    public function testCreateRejectsUnknownTargetDex(): void
    {
        $source = new TrainerDex();

        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnMap([
                [['trainerExternalId' => 'trainer-1', 'slug' => 'national'], null, $source],
                [['trainerExternalId' => 'trainer-1', 'slug' => 'unknown'], null, null],
            ])
        ;

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->expectException(TrainerDexNotFoundException::class);

        $service->create('trainer-1', 'national', 'unknown', false);
    }

    public function testCreateRejectsDuplicateEdge(): void
    {
        $source = $this->trainerDexWithId('11111111-1111-1111-1111-111111111111');
        $target = $this->trainerDexWithId('22222222-2222-2222-2222-222222222222');

        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('exists')
            ->with('11111111-1111-1111-1111-111111111111', '22222222-2222-2222-2222-222222222222')
            ->willReturn(true)
        ;
        $linkRepository->expects($this->never())->method('insert');

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->method('findOneBy')->willReturnOnConsecutiveCalls($source, $target);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->expectException(DuplicateTrainerDexLinkException::class);

        $service->create('trainer-1', 'national', 'shiny', false);
    }

    public function testCreateInsertsOneRowForAUnidirectionalLink(): void
    {
        $source = $this->trainerDexWithId('11111111-1111-1111-1111-111111111111');
        $target = $this->trainerDexWithId('22222222-2222-2222-2222-222222222222');

        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->method('exists')->willReturn(false);
        $linkRepository->expects($this->once())
            ->method('insert')
            ->with(
                'trainer-1',
                '11111111-1111-1111-1111-111111111111',
                '22222222-2222-2222-2222-222222222222',
                null,
            )
        ;

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->method('findOneBy')->willReturnOnConsecutiveCalls($source, $target);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $service->create('trainer-1', 'national', 'shiny', false);
    }

    public function testCreateRejectsWhenTheReverseEdgeOfABidirectionalLinkAlreadyExists(): void
    {
        $source = $this->trainerDexWithId('11111111-1111-1111-1111-111111111111');
        $target = $this->trainerDexWithId('22222222-2222-2222-2222-222222222222');

        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->method('exists')->willReturnMap([
            ['11111111-1111-1111-1111-111111111111', '22222222-2222-2222-2222-222222222222', false],
            ['22222222-2222-2222-2222-222222222222', '11111111-1111-1111-1111-111111111111', true],
        ]);
        $linkRepository->expects($this->never())->method('insert');

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->method('findOneBy')->willReturnOnConsecutiveCalls($source, $target);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->expectException(DuplicateTrainerDexLinkException::class);

        $service->create('trainer-1', 'national', 'shiny', true);
    }

    public function testCreateInsertsTwoRowsSharingAPairIdForABidirectionalLink(): void
    {
        $source = $this->trainerDexWithId('11111111-1111-1111-1111-111111111111');
        $target = $this->trainerDexWithId('22222222-2222-2222-2222-222222222222');

        $pairIds = [];

        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->method('exists')->willReturn(false);
        $linkRepository->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $trainerExternalId, string $sourceId, string $targetId, ?string $pairId) use (&$pairIds): void {
                $pairIds[] = $pairId;
            })
        ;

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->method('findOneBy')->willReturnOnConsecutiveCalls($source, $target);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $service->create('trainer-1', 'national', 'shiny', true);

        $this->assertCount(2, $pairIds);
        $this->assertNotNull($pairIds[0]);
        $this->assertSame($pairIds[0], $pairIds[1]);
        $this->assertTrue(Uuid::isValid((string) $pairIds[0]));
    }

    public function testDeleteDelegatesToRepository(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('deleteByIdOrPairId')
            ->with('trainer-1', 'link-1')
        ;

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $service->delete('trainer-1', 'link-1');
    }

    private function trainerDexWithId(string $uuid): TrainerDex
    {
        $entity = new TrainerDex();

        $reflection = new \ReflectionProperty($entity, 'identifier');
        $reflection->setValue($entity, Uuid::fromString($uuid));

        return $entity;
    }
}
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/TrainerDexLinkServiceTest.php`
Expected: FAIL — `Class "App\Service\TrainerDexLinkService" not found`

- [ ] **Step 4: Implement `TrainerDexLinkService`**

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TrainerDex;
use App\Exception\DuplicateTrainerDexLinkException;
use App\Exception\SelfTrainerDexLinkException;
use App\Exception\TrainerDexNotFoundException;
use App\Repository\TrainerDexLinkRepository;
use App\Repository\TrainerDexRepository;
use Symfony\Component\Uid\Uuid;

class TrainerDexLinkService
{
    public function __construct(
        private readonly TrainerDexLinkRepository $repository,
        private readonly TrainerDexRepository $trainerDexRepository,
    ) {}

    /**
     * @return list<array{id: string, pair_id: ?string, direction: string, target_trainer_dex_id: string, target_dex_slug: string, target_name: string, target_french_name: string}>
     */
    public function listForDex(string $trainerExternalId, string $dexSlug): array
    {
        return $this->repository->getForDex($trainerExternalId, $dexSlug);
    }

    public function create(
        string $trainerExternalId,
        string $sourceDexSlug,
        string $targetDexSlug,
        bool $bidirectional,
    ): void {
        if ($sourceDexSlug === $targetDexSlug) {
            throw new SelfTrainerDexLinkException();
        }

        $sourceId = (string) $this->findTrainerDex($trainerExternalId, $sourceDexSlug)->getIdentifier();
        $targetId = (string) $this->findTrainerDex($trainerExternalId, $targetDexSlug)->getIdentifier();

        if ($this->repository->exists($sourceId, $targetId)
            || ($bidirectional && $this->repository->exists($targetId, $sourceId))
        ) {
            throw new DuplicateTrainerDexLinkException();
        }

        $pairId = $bidirectional ? (string) Uuid::v4() : null;

        $this->repository->insert($trainerExternalId, $sourceId, $targetId, $pairId);

        if ($bidirectional) {
            $this->repository->insert($trainerExternalId, $targetId, $sourceId, $pairId);
        }
    }

    public function delete(string $trainerExternalId, string $linkId): void
    {
        $this->repository->deleteByIdOrPairId($trainerExternalId, $linkId);
    }

    private function findTrainerDex(string $trainerExternalId, string $dexSlug): TrainerDex
    {
        $trainerDex = $this->trainerDexRepository->findOneBy([
            'trainerExternalId' => $trainerExternalId,
            'slug' => $dexSlug,
        ]);

        if (null === $trainerDex) {
            throw new TrainerDexNotFoundException();
        }

        return $trainerDex;
    }
}
```

Checking both directions of `exists()` for a bidirectional request (not just the spec's literal "existing directed edge" check) avoids ever inserting only one half of a bidirectional pair — if the reverse edge already existed as a plain unidirectional link, inserting the second row here would otherwise hit the DB's own unique constraint and blow up with an uncaught `UniqueConstraintViolationException` instead of a clean 409.

- [ ] **Step 5: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/TrainerDexLinkServiceTest.php`
Expected: PASS (9 tests)

- [ ] **Step 6: Write the failing `TrainerDexLinkResponseFactory` test**

Create `tests/src/Unit/Factory/TrainerDexLinkResponseFactoryTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\TrainerDexLinkResponse;
use App\Factory\TrainerDexLinkResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkResponseFactory::class)]
final class TrainerDexLinkResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'id' => 'link-1',
            'pair_id' => null,
            'direction' => 'to',
            'target_trainer_dex_id' => 'dex-1',
            'target_dex_slug' => 'shiny',
            'target_name' => 'Shiny Living',
            'target_french_name' => 'Vivarium Chromatique',
        ];

        $response = TrainerDexLinkResponseFactory::fromSqlRow($row);

        self::assertSame('link-1', $response->id);
        self::assertSame('to', $response->direction);
        self::assertSame('shiny', $response->targetDexSlug);
        self::assertSame('Shiny Living', $response->targetName);
        self::assertSame('Vivarium Chromatique', $response->targetFrenchName);
    }

    #[Test]
    public function fromSqlRowCastsValuesToCorrectTypes(): void
    {
        $row = [
            'id' => 123,
            'pair_id' => null,
            'direction' => 456,
            'target_trainer_dex_id' => 'dex-1',
            'target_dex_slug' => 789,
            'target_name' => 101,
            'target_french_name' => 202,
        ];

        $response = TrainerDexLinkResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->id);
        self::assertSame('456', $response->direction);
        self::assertSame('789', $response->targetDexSlug);
        self::assertSame('101', $response->targetName);
        self::assertSame('202', $response->targetFrenchName);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'id' => 'link-1',
                'pair_id' => null,
                'direction' => 'to',
                'target_trainer_dex_id' => 'dex-1',
                'target_dex_slug' => 'shiny',
                'target_name' => 'Shiny Living',
                'target_french_name' => 'Vivarium Chromatique',
            ],
            [
                'id' => 'link-2',
                'pair_id' => 'pair-1',
                'direction' => 'both',
                'target_trainer_dex_id' => 'dex-2',
                'target_dex_slug' => 'home',
                'target_name' => 'Home',
                'target_french_name' => 'Home',
            ],
        ];

        $responses = TrainerDexLinkResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(TrainerDexLinkResponse::class, $responses);
        self::assertSame('to', $responses[0]->direction);
        self::assertSame('both', $responses[1]->direction);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        self::assertSame([], TrainerDexLinkResponseFactory::fromSqlRows([]));
    }
}
```

- [ ] **Step 7: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/TrainerDexLinkResponseFactoryTest.php`
Expected: FAIL — `Class "App\DTO\Response\TrainerDexLinkResponse" not found`

- [ ] **Step 8: Implement the response DTO and factory**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerDexLinkResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $direction,
        #[SerializedName('target_dex_slug')]
        public readonly string $targetDexSlug,
        #[SerializedName('target_name')]
        public readonly string $targetName,
        #[SerializedName('target_french_name')]
        public readonly string $targetFrenchName,
    ) {}
}
```

Save as `src/DTO/Response/TrainerDexLinkResponse.php`.

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\TrainerDexLinkResponse;

final class TrainerDexLinkResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): TrainerDexLinkResponse
    {
        /** @var scalar $id */
        $id = $row['id'];

        /** @var scalar $direction */
        $direction = $row['direction'];

        /** @var scalar $targetDexSlug */
        $targetDexSlug = $row['target_dex_slug'];

        /** @var scalar $targetName */
        $targetName = $row['target_name'];

        /** @var scalar $targetFrenchName */
        $targetFrenchName = $row['target_french_name'];

        return new TrainerDexLinkResponse(
            id: (string) $id,
            direction: (string) $direction,
            targetDexSlug: (string) $targetDexSlug,
            targetName: (string) $targetName,
            targetFrenchName: (string) $targetFrenchName,
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return TrainerDexLinkResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(
            static fn (array $row): TrainerDexLinkResponse => self::fromSqlRow($row),
            $rows,
        );
    }
}
```

Save as `src/Factory/TrainerDexLinkResponseFactory.php`.

- [ ] **Step 9: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Factory/TrainerDexLinkResponseFactoryTest.php`
Expected: PASS (4 tests)

- [ ] **Step 10: Write the failing `TrainerDexLinkController` integration test**

Create `tests/src/Integration/Controller/TrainerDexLinkControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TrainerDexLinkController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkController::class)]
final class TrainerDexLinkControllerTest extends AbstractTestControllerApi
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    public function testCreateListAndDelete(): void
    {
        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/redgreenblueyellow');
        $this->assertResponseIsOK();
        $this->assertSame([], $this->getJsonDecodedResponseContent());

        $this->apiRequest(
            'POST',
            '/trainer_dex_link/'.self::TRAINER,
            [],
            null,
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'goldsilvercrystal', 'bidirectional' => false],
                JSON_THROW_ON_ERROR
            ),
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/redgreenblueyellow');
        $this->assertResponseIsOK();

        /** @var array<int, array{id: string, direction: string, target_dex_slug: string}> $links */
        $links = $this->getJsonDecodedResponseContent();
        $this->assertCount(1, $links);
        $this->assertSame('to', $links[0]['direction']);
        $this->assertSame('goldsilvercrystal', $links[0]['target_dex_slug']);

        $this->apiRequest('DELETE', '/trainer_dex_link/'.self::TRAINER.'/'.$links[0]['id']);
        $this->assertResponseIsOK();

        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/redgreenblueyellow');
        $this->assertSame([], $this->getJsonDecodedResponseContent());
    }

    public function testCreateRejectsSelfLink(): void
    {
        $this->apiRequest(
            'POST',
            '/trainer_dex_link/'.self::TRAINER,
            [],
            null,
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'redgreenblueyellow', 'bidirectional' => false],
                JSON_THROW_ON_ERROR
            ),
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateRejectsUnknownDex(): void
    {
        $this->apiRequest(
            'POST',
            '/trainer_dex_link/'.self::TRAINER,
            [],
            null,
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'does-not-exist', 'bidirectional' => false],
                JSON_THROW_ON_ERROR
            ),
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateRejectsDuplicateEdge(): void
    {
        $body = json_encode(
            ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'goldsilvercrystal', 'bidirectional' => false],
            JSON_THROW_ON_ERROR
        );

        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, $body);
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, $body);
        $this->assertResponseStatusCodeSame(409);
    }

    public function testCreateEmptyBody(): void
    {
        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, '');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateMissingFields(): void
    {
        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, json_encode(['sourceDexSlug' => 'redgreenblueyellow'], JSON_THROW_ON_ERROR));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateBidirectional(): void
    {
        $this->apiRequest(
            'POST',
            '/trainer_dex_link/'.self::TRAINER,
            [],
            null,
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'goldsilvercrystal', 'bidirectional' => true],
                JSON_THROW_ON_ERROR
            ),
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/goldsilvercrystal');

        /** @var array<int, array{direction: string}> $links */
        $links = $this->getJsonDecodedResponseContent();
        $this->assertCount(1, $links);
        $this->assertSame('both', $links[0]['direction']);
    }
}
```

Note: `apiRequest()`'s 4th parameter (`?array $options`) defaults to the `AUTH_USER`/`AUTH_PASSWORD` basic-auth pair when `null` is passed — passing `null` explicitly here (rather than omitting it) is required because the 5th positional parameter (`$content`) needs to be reached.

- [ ] **Step 11: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/TrainerDexLinkControllerTest.php`
Expected: FAIL — `Class "App\Controller\TrainerDexLinkController" not found`

- [ ] **Step 12: Implement `TrainerDexLinkController`**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\TrainerDexLinkResponse;
use App\Exception\DuplicateTrainerDexLinkException;
use App\Exception\SelfTrainerDexLinkException;
use App\Exception\TrainerDexNotFoundException;
use App\Factory\TrainerDexLinkResponseFactory;
use App\Service\TrainerDexLinkService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/trainer_dex_link')]
final class TrainerDexLinkController extends AbstractController
{
    public function __construct(
        private readonly TrainerDexLinkService $service,
    ) {}

    /** @return TrainerDexLinkResponse[] */
    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    #[Serialize]
    public function list(string $trainerExternalId, string $dexSlug): array
    {
        $rows = $this->service->listForDex($trainerExternalId, $dexSlug);

        return TrainerDexLinkResponseFactory::fromSqlRows($rows);
    }

    #[Route(path: '/{trainerExternalId}', methods: ['POST'])]
    public function create(string $trainerExternalId, Request $request): Response
    {
        $json = $request->getContent();

        if (!$json) {
            throw new BadRequestHttpException();
        }

        /** @var array{sourceDexSlug?: mixed, targetDexSlug?: mixed, bidirectional?: mixed} $content */
        $content = json_decode($json, true) ?? [];

        if (!isset($content['sourceDexSlug'], $content['targetDexSlug'])
            || !\is_string($content['sourceDexSlug'])
            || !\is_string($content['targetDexSlug'])
        ) {
            throw new BadRequestHttpException();
        }

        $bidirectional = $content['bidirectional'] ?? false;

        if (!\is_bool($bidirectional)) {
            throw new BadRequestHttpException();
        }

        try {
            $this->service->create(
                $trainerExternalId,
                $content['sourceDexSlug'],
                $content['targetDexSlug'],
                $bidirectional,
            );
        } catch (SelfTrainerDexLinkException $e) {
            throw new BadRequestHttpException(previous: $e);
        } catch (TrainerDexNotFoundException $e) {
            throw new NotFoundHttpException(previous: $e);
        } catch (DuplicateTrainerDexLinkException $e) {
            throw new ConflictHttpException(previous: $e);
        }

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/{trainerExternalId}/{linkId}', methods: ['DELETE'])]
    public function delete(string $trainerExternalId, string $linkId): Response
    {
        $this->service->delete($trainerExternalId, $linkId);

        return new Response();
    }
}
```

- [ ] **Step 13: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/TrainerDexLinkControllerTest.php`
Expected: PASS (7 tests)

- [ ] **Step 14: Verify Deptrac is still green**

Run: `docker compose exec php php tools/deptrac/vendor/bin/deptrac analyse --no-cache` (or `make deptrac` if the Makefile exposes it directly — check `make code-quality`'s composition)
Expected: no violations.

- [ ] **Step 15: Commit**

```bash
git add src/Exception/SelfTrainerDexLinkException.php src/Exception/DuplicateTrainerDexLinkException.php src/Exception/TrainerDexNotFoundException.php src/Service/TrainerDexLinkService.php src/Controller/TrainerDexLinkController.php src/DTO/Response/TrainerDexLinkResponse.php src/Factory/TrainerDexLinkResponseFactory.php deptrac.yaml tests/src/Unit/Service/TrainerDexLinkServiceTest.php tests/src/Unit/Factory/TrainerDexLinkResponseFactoryTest.php tests/src/Integration/Controller/TrainerDexLinkControllerTest.php
git commit -m "feat: add TrainerDexLinkService/Controller CRUD surface"
```

---

### Task 6: `AlbumUpsertController` response body change (`{"updatedDexSlugs": [...]}`) + cascade integration test

**Files:**
- Create: `src/DTO/Response/AlbumUpsertResponse.php`
- Modify: `src/Controller/AlbumUpsertController.php`
- Modify: `tests/src/Integration/Controller/AlbumUpsertControllerTest.php`

**Interfaces:**
- Consumes: `PokedexService::upsert(): list<string>` (Task 4).
- Produces: `AlbumUpsertController::update()`/`create()` now respond `200`/`201` with body `{"updatedDexSlugs": ["dex-a", "dex-b"]}` instead of an empty body — this is the contract pokenini-back's Task 8 depends on.

- [ ] **Step 1: Write the failing tests**

Replace `testUpdate`, `testCreate`, `testCreateNonExistingTrainerDex` and `testCreateNonExistingDex` in `tests/src/Integration/Controller/AlbumUpsertControllerTest.php` with versions that assert the new body, and add a new cascade test. Replace the full contents of the file with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\AlbumUpsertController;
use App\Tests\Common\Traits\CounterTrait\CountTrainerDexTrait;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(AlbumUpsertController::class)]
final class AlbumUpsertControllerTest extends AbstractTestControllerApi
{
    use GetPokedexTrait;
    use CountTrainerDexTrait;

    public function testUpdate(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertArrayHasKey('slug', $pokedexBefore);
        $this->assertEquals('maybe', $pokedexBefore['slug']);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            ['updatedDexSlugs' => ['redgreenblueyellow']],
            $this->getJsonDecodedResponseContent()
        );

        $this->assertEquals(34, $this->getTrainerDexCount());
    }

    public function testUpdateEmpty(): void
    {
        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            ''
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdateNonExistingDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexBefore);

        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/douze/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            ['updatedDexSlugs' => ['douze']],
            $this->getJsonDecodedResponseContent()
        );

        $pokedexAfter = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexAfter);
    }

    public function testUpdateNonExistingPokemon(): void
    {
        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/treize',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreate(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('goldsilvercrystal', 'douze');

        $this->assertEmpty($pokedexBefore);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/goldsilvercrystal/douze',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'maybenot'
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals(
            ['updatedDexSlugs' => ['goldsilvercrystal']],
            $this->getJsonDecodedResponseContent()
        );

        $pokedexAfter = $this->getPokedexFromSlugs('goldsilvercrystal', 'douze');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);

        $this->assertEquals(34, $this->getTrainerDexCount());
    }

    public function testCreateNonExistingTrainerDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('spoon', 'douze');

        $this->assertEmpty($pokedexBefore);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/spoon/douze',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'maybenot'
        );

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            ['updatedDexSlugs' => ['spoon']],
            $this->getJsonDecodedResponseContent()
        );

        $pokedexAfter = $this->getPokedexFromSlugs('spoon', 'douze');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);

        $this->assertEquals(35, $this->getTrainerDexCount());
    }

    public function testCreateNonExistingDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexBefore);

        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/douze/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            ['updatedDexSlugs' => ['douze']],
            $this->getJsonDecodedResponseContent()
        );

        $pokedexAfter = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexAfter);
    }

    public function testCreateNonExistingPokemon(): void
    {
        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/treize',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateEmpty(): void
    {
        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            ''
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdateCascadesThroughAnActiveLink(): void
    {
        $this->apiRequest(
            'POST',
            '/trainer_dex_link/7b52009b64fd0a2a49e6d8a939753077792b0554',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'goldsilvercrystal', 'bidirectional' => false],
                JSON_THROW_ON_ERROR
            ),
        );
        $this->assertResponseStatusCodeSame(201);

        // Fixture: ivysaur is 'no' in goldsilvercrystal, 'maybe' in redgreenblueyellow, for this trainer.
        $pokedexBefore = $this->getPokedexFromSlugs('goldsilvercrystal', 'ivysaur');
        $this->assertEquals('no', $pokedexBefore['slug']);

        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            ['updatedDexSlugs' => ['redgreenblueyellow', 'goldsilvercrystal']],
            $this->getJsonDecodedResponseContent()
        );

        $pokedexAfter = $this->getPokedexFromSlugs('goldsilvercrystal', 'ivysaur');
        $this->assertEquals('yes', $pokedexAfter['slug']);
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/AlbumUpsertControllerTest.php`
Expected: FAIL — responses currently have empty bodies, so `getJsonDecodedResponseContent()` fails its `assert(is_array($decoded))` (the decoded value is `null`).

- [ ] **Step 3: Add the response DTO**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class AlbumUpsertResponse
{
    /**
     * @param list<string> $updatedDexSlugs
     */
    public function __construct(
        public readonly array $updatedDexSlugs,
    ) {}
}
```

Save as `src/DTO/Response/AlbumUpsertResponse.php`.

- [ ] **Step 4: Wire it into `AlbumUpsertController`**

Replace the full contents of `src/Controller/AlbumUpsertController.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\AlbumUpsertResponse;
use App\Service\PokedexService;
use App\Service\TrainerDexService;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/album')]
final class AlbumUpsertController extends AbstractController
{
    public function __construct(
        private readonly PokedexService $pokedexService,
        private readonly TrainerDexService $trainerDexService,
    ) {}

    #[Route(methods: ['PATCH'], path: '/{trainerExternalId}/{dexSlug}/{pokemonSlug}')]
    #[Serialize]
    public function update(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
    ): AlbumUpsertResponse {
        return $this->upsert($trainerExternalId, $dexSlug, $pokemonSlug, $request);
    }

    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}/{pokemonSlug}')]
    #[Serialize(code: Response::HTTP_CREATED)]
    public function create(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
    ): AlbumUpsertResponse {
        return $this->upsert($trainerExternalId, $dexSlug, $pokemonSlug, $request);
    }

    private function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        Request $request
    ): AlbumUpsertResponse {
        $content = $request->getContent();

        if (!$content) {
            throw new BadRequestHttpException();
        }

        /** @var string $catchStateSlug */
        $catchStateSlug = $content;

        try {
            $this->trainerDexService->insertIfNeeded(
                $trainerExternalId,
                $dexSlug,
            );

            $updatedDexSlugs = $this->pokedexService->upsert(
                $trainerExternalId,
                $dexSlug,
                $pokemonSlug,
                $catchStateSlug,
            );
        } catch (NotNullConstraintViolationException $e) {
            throw new BadRequestHttpException();
        }

        return new AlbumUpsertResponse($updatedDexSlugs);
    }
}
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/AlbumUpsertControllerTest.php`
Expected: PASS (10 tests)

- [ ] **Step 6: Commit**

```bash
git add src/DTO/Response/AlbumUpsertResponse.php src/Controller/AlbumUpsertController.php tests/src/Integration/Controller/AlbumUpsertControllerTest.php
git commit -m "feat: return updatedDexSlugs from the album upsert endpoint"
```

---

### Task 7: Run the full pokenini-api suite

**Files:** none (verification-only task).

- [ ] **Step 1: Run the full test suite**

Run: `cd /home/renaud/projects/pokenini-api && make tests`
Expected: all green (unit + integration).

- [ ] **Step 2: Run coverage**

Run: `make coverage`
Expected: 100% line/branch coverage maintained.

- [ ] **Step 3: Run mutation testing**

Run: `make infection`
Expected: 100% MSI maintained — pay particular attention to `PropagateCatchStateService` (the `while`/`if` branches), `PokedexRepository::upsert()`/`upsertIfDifferent()` (the `IS DISTINCT FROM` guards and the `EXISTS` clause), and `TrainerDexLinkService::create()` (the self-link / duplicate / bidirectional-reverse-exists branches) — these are the highest-risk spots for a surviving mutant given the idempotence-based termination rule.

- [ ] **Step 4: Run the remaining quality gates**

Run: `make quality`
Expected: all green, including Deptrac (the two new ruleset edges from Task 5) and PHPStan/Psalm on every new file in this repo section.

- [ ] **Step 5: Commit** (only if any of the above required fixes)

```bash
git add -A
git commit -m "fix: address quality/coverage/mutation feedback for the dex-link feature"
```

---

## pokenini-back (`/home/renaud/projects/pokenini-back`)

### Task 8: `ModifyAlbumApiService::modify()` returns `updatedDexSlugs`; `ModifyTrainerAlbumService` invalidates every one of them

**Files:**
- Modify: `src/Service/Api/ModifyAlbumApiService.php`
- Modify: `src/Service/ModifyTrainerAlbumService.php`
- Create: `tests/src/Unit/Service/Api/ModifyAlbumApiServiceTest.php` (create if it doesn't already exist; if it does, extend it — check first with `find tests -iname "ModifyAlbumApiServiceTest.php"`)
- Modify: `tests/src/Unit/Service/ModifyTrainerAlbumServiceTest.php`

**Interfaces:**
- Consumes: pokenini-api's `AlbumUpsertController` now responds `{"updatedDexSlugs": [...]}` (Task 6).
- Produces: `ModifyAlbumApiService::modify(...): list<string>` (was `void`) — the parsed `updatedDexSlugs` array. `ModifyTrainerAlbumService::modifyAlbum(...)` stays `void` but now calls `AlbumCacheInvalidatorService::invalidate($slug, $trainerId)` once per slug in that list instead of only for the origin `$dexSlug`.

- [ ] **Step 1: Write the failing test for `ModifyAlbumApiService`**

First check whether a test already exists:

Run: `find /home/renaud/projects/pokenini-back/tests -iname "ModifyAlbumApiServiceTest.php"`

If it exists, add the assertions below to it; if not, create `tests/src/Unit/Service/Api/ModifyAlbumApiServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Api;

use App\Service\Api\ModifyAlbumApiService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(ModifyAlbumApiService::class)]
final class ModifyAlbumApiServiceTest extends TestCase
{
    public function testModifyReturnsUpdatedDexSlugs(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('{"updatedDexSlugs":["national","shiny-living"]}');

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'https://api.local/album/8800088/douze/treize',
                $this->callback(function (array $options): bool {
                    return 'yes' === $options['body'];
                }),
            )
            ->willReturn($response)
        ;

        $service = new ModifyAlbumApiService(
            $this->createMock(LoggerInterface::class),
            $client,
            'https://api.local',
            '/path/to/cafile',
            $this->createMock(TagAwareCacheInterface::class),
            'login',
            'password',
        );

        $this->assertSame(
            ['national', 'shiny-living'],
            $service->modify('PUT', 'douze', 'treize', 'yes', '8800088'),
        );
    }

    public function testModifyRejectsAnInvalidHttpMethod(): void
    {
        $service = new ModifyAlbumApiService(
            $this->createMock(LoggerInterface::class),
            $this->createMock(HttpClientInterface::class),
            'https://api.local',
            '/path/to/cafile',
            $this->createMock(TagAwareCacheInterface::class),
            'login',
            'password',
        );

        $this->expectException(\InvalidArgumentException::class);

        $service->modify('GET', 'douze', 'treize', 'yes', '8800088');
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/Api/ModifyAlbumApiServiceTest.php`
Expected: FAIL — `modify()` currently returns `void`, so `$service->modify(...)` can't be used as an expression inside `assertSame()`.

- [ ] **Step 3: Update `ModifyAlbumApiService`**

Replace the full contents of `src/Service/Api/ModifyAlbumApiService.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Service\Api;

use App\Utils\JsonDecoder;
use Symfony\Component\HttpFoundation\Request;

class ModifyAlbumApiService extends AbstractApiService
{
    /**
     * @return list<string>
     */
    public function modify(
        string $method,
        string $dexSlug,
        string $pokemonSlug,
        string $catchStateSlug,
        string $trainerId
    ): array {
        if (!in_array($method, [Request::METHOD_PATCH, Request::METHOD_PUT], true)) {
            throw new \InvalidArgumentException();
        }

        $json = $this->requestContent(
            $method,
            "/album/{$trainerId}/{$dexSlug}/{$pokemonSlug}",
            [
                'body' => $catchStateSlug,
            ]
        );

        /** @var array{updatedDexSlugs: list<string>} */
        $decoded = JsonDecoder::decode($json);

        return $decoded['updatedDexSlugs'];
    }
}
```

Check that `App\Utils\JsonDecoder` exists in this repo first (`find src -iname "JsonDecoder.php"`) — it's already used by `GetPokedexApiService`/`GetDexListApiService`, confirmed in Task 8's exploration; reuse it rather than calling `json_decode()` directly, for consistency.

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/Api/ModifyAlbumApiServiceTest.php`
Expected: PASS (2 tests)

- [ ] **Step 5: Write the failing test for `ModifyTrainerAlbumService`**

Replace the full contents of `tests/src/Unit/Service/ModifyTrainerAlbumServiceTest.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Exception\ModifyFailedException;
use App\Security\UserTokenService;
use App\Service\Api\ModifyAlbumApiService;
use App\Service\CacheInvalidator\AlbumCacheInvalidatorService;
use App\Service\CacheInvalidator\AlbumsCacheInvalidatorService;
use App\Service\ModifyTrainerAlbumService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @internal
 */
#[CoversClass(ModifyTrainerAlbumService::class)]
final class ModifyTrainerAlbumServiceTest extends TestCase
{
    public function testModifyAlbumInvalidatesEveryUpdatedDexSlug(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService->expects($this->once())
            ->method('getLoggedUserToken')
            ->willReturn('8800088')
        ;

        $modifyAlbumService = $this->createMock(ModifyAlbumApiService::class);
        $modifyAlbumService->expects($this->once())
            ->method('modify')
            ->with('PUT', 'douze', 'treize', '{"ceci": "est-du-contenu"}', '8800088')
            ->willReturn(['douze', 'treize-dex'])
        ;

        $albumsCacheInvalidatorService = $this->createMock(AlbumsCacheInvalidatorService::class);
        $albumsCacheInvalidatorService->expects($this->once())->method('invalidate');

        $albumCacheInvalidatorService = $this->createMock(AlbumCacheInvalidatorService::class);
        $albumCacheInvalidatorService->expects($this->exactly(2))
            ->method('invalidate')
            ->willReturnMap([
                ['douze', '8800088', null],
                ['treize-dex', '8800088', null],
            ])
        ;

        $request = Request::create('test.local', 'PUT');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $service = new ModifyTrainerAlbumService(
            $userTokenService,
            $modifyAlbumService,
            $albumsCacheInvalidatorService,
            $albumCacheInvalidatorService,
            $requestStack,
        );
        $service->modifyAlbum('douze', 'treize', '{"ceci": "est-du-contenu"}');
    }

    public function testModifyAlbumInvalidatesOnlyTheOriginWhenNothingElseChanged(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService->method('getLoggedUserToken')->willReturn('8800088');

        $modifyAlbumService = $this->createMock(ModifyAlbumApiService::class);
        $modifyAlbumService->method('modify')->willReturn(['douze']);

        $albumsCacheInvalidatorService = $this->createMock(AlbumsCacheInvalidatorService::class);
        $albumsCacheInvalidatorService->expects($this->once())->method('invalidate');

        $albumCacheInvalidatorService = $this->createMock(AlbumCacheInvalidatorService::class);
        $albumCacheInvalidatorService->expects($this->once())
            ->method('invalidate')
            ->with('douze', '8800088')
        ;

        $request = Request::create('test.local', 'PUT');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $service = new ModifyTrainerAlbumService(
            $userTokenService,
            $modifyAlbumService,
            $albumsCacheInvalidatorService,
            $albumCacheInvalidatorService,
            $requestStack,
        );
        $service->modifyAlbum('douze', 'treize', '{"ceci": "est-du-contenu"}');
    }

    public function testModifyDexWithHttpException(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService->expects($this->once())
            ->method('getLoggedUserToken')
            ->willReturn('8800088')
        ;

        $exception = $this->createStub(HttpExceptionInterface::class);

        $modifyAlbumService = $this->createMock(ModifyAlbumApiService::class);
        $modifyAlbumService->expects($this->once())
            ->method('modify')
            ->with('PUT', 'douze', 'treize', '{"ceci": "est-du-contenu"}', '8800088')
            ->willThrowException($exception)
        ;

        $albumsCacheInvalidatorService = $this->createMock(AlbumsCacheInvalidatorService::class);
        $albumsCacheInvalidatorService->expects($this->never())->method('invalidate');

        $albumCacheInvalidatorService = $this->createMock(AlbumCacheInvalidatorService::class);
        $albumCacheInvalidatorService->expects($this->never())->method('invalidate');

        $request = Request::create('test.local', 'PUT');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $service = new ModifyTrainerAlbumService(
            $userTokenService,
            $modifyAlbumService,
            $albumsCacheInvalidatorService,
            $albumCacheInvalidatorService,
            $requestStack,
        );

        $this->expectException(ModifyFailedException::class);

        $service->modifyAlbum('douze', 'treize', '{"ceci": "est-du-contenu"}');
    }

    public function testModifyDexWithNoRequest(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService->expects($this->once())
            ->method('getLoggedUserToken')
            ->willReturn('8800088')
        ;

        $modifyAlbumService = $this->createMock(ModifyAlbumApiService::class);
        $modifyAlbumService->expects($this->never())->method('modify');

        $albumsCacheInvalidatorService = $this->createMock(AlbumsCacheInvalidatorService::class);
        $albumsCacheInvalidatorService->expects($this->never())->method('invalidate');

        $albumCacheInvalidatorService = $this->createMock(AlbumCacheInvalidatorService::class);
        $albumCacheInvalidatorService->expects($this->never())->method('invalidate');

        $requestStack = new RequestStack();

        $service = new ModifyTrainerAlbumService(
            $userTokenService,
            $modifyAlbumService,
            $albumsCacheInvalidatorService,
            $albumCacheInvalidatorService,
            $requestStack,
        );

        $this->expectException(ModifyFailedException::class);

        $service->modifyAlbum('douze', 'treize', '{"ceci": "est-du-contenu"}');
    }

    public function testModifyDexWithTransportException(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService->expects($this->once())
            ->method('getLoggedUserToken')
            ->willReturn('8800088')
        ;

        $exception = $this->createStub(TransportExceptionInterface::class);

        $modifyAlbumService = $this->createMock(ModifyAlbumApiService::class);
        $modifyAlbumService->expects($this->once())
            ->method('modify')
            ->with('PUT', 'douze', 'treize', '{"ceci": "est-du-contenu"}', '8800088')
            ->willThrowException($exception)
        ;

        $albumsCacheInvalidatorService = $this->createMock(AlbumsCacheInvalidatorService::class);
        $albumsCacheInvalidatorService->expects($this->never())->method('invalidate');

        $albumCacheInvalidatorService = $this->createMock(AlbumCacheInvalidatorService::class);
        $albumCacheInvalidatorService->expects($this->never())->method('invalidate');

        $request = Request::create('test.local', 'PUT');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $service = new ModifyTrainerAlbumService(
            $userTokenService,
            $modifyAlbumService,
            $albumsCacheInvalidatorService,
            $albumCacheInvalidatorService,
            $requestStack,
        );

        $this->expectException(ModifyFailedException::class);

        $service->modifyAlbum('douze', 'treize', '{"ceci": "est-du-contenu"}');
    }
}
```

- [ ] **Step 6: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/ModifyTrainerAlbumServiceTest.php`
Expected: FAIL — `$albumCacheInvalidatorService->invalidate` is currently called once (for `$dexSlug` only), not per entry of a returned array (the mock's `modify()` return typing doesn't even match yet).

- [ ] **Step 7: Update `ModifyTrainerAlbumService`**

Replace the full contents of `src/Service/ModifyTrainerAlbumService.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ModifyFailedException;
use App\Security\UserTokenService;
use App\Service\Api\ModifyAlbumApiService;
use App\Service\CacheInvalidator\AlbumCacheInvalidatorService;
use App\Service\CacheInvalidator\AlbumsCacheInvalidatorService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ModifyTrainerAlbumService
{
    public function __construct(
        private readonly UserTokenService $userTokenService,
        private readonly ModifyAlbumApiService $modifyAlbumService,
        private readonly AlbumsCacheInvalidatorService $albumsCacheInvalidatorService,
        private readonly AlbumCacheInvalidatorService $albumCacheInvalidatorService,
        private readonly RequestStack $requestStack,
    ) {}

    public function modifyAlbum(
        string $dexSlug,
        string $pokemonSlug,
        string $content,
    ): void {
        $trainerId = $this->userTokenService->getLoggedUserToken();
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new ModifyFailedException();
        }

        try {
            $updatedDexSlugs = $this->modifyAlbumService->modify(
                $request->getMethod(),
                $dexSlug,
                $pokemonSlug,
                $content,
                $trainerId
            );

            $this->albumsCacheInvalidatorService->invalidate();

            foreach ($updatedDexSlugs as $updatedDexSlug) {
                $this->albumCacheInvalidatorService->invalidate($updatedDexSlug, $trainerId);
            }
        } catch (HttpExceptionInterface|TransportExceptionInterface $e) {
            throw new ModifyFailedException();
        }
    }
}
```

- [ ] **Step 8: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/ModifyTrainerAlbumServiceTest.php`
Expected: PASS (5 tests)

- [ ] **Step 9: Run the full pokenini-back test suite and coverage**

Run: `cd /home/renaud/projects/pokenini-back && make tests && make coverage`
Expected: all green, 100% coverage maintained.

- [ ] **Step 10: Commit**

```bash
git add src/Service/Api/ModifyAlbumApiService.php src/Service/ModifyTrainerAlbumService.php tests/src/Unit/Service/Api/ModifyAlbumApiServiceTest.php tests/src/Unit/Service/ModifyTrainerAlbumServiceTest.php
git commit -m "fix: invalidate every dex slug the cascade touched, not just the origin"
```

---

### Task 9: `TrainerDexLinkController` (back) proxy + `TrainerDexLinkApiService` + `TrainerDexLinkService`

**Files:**
- Create: `src/Exception/ApiValidationException.php`
- Create: `src/Service/Api/TrainerDexLinkApiService.php`
- Create: `src/Service/TrainerDexLinkService.php`
- Create: `src/Controller/Album/TrainerDexLinkController.php`
- Create: `tests/src/Unit/Service/Api/TrainerDexLinkApiServiceTest.php`
- Create: `tests/src/Unit/Service/TrainerDexLinkServiceTest.php`
- Create: `tests/src/Unit/Controller/Album/TrainerDexLinkControllerTest.php`

**Interfaces:**
- Consumes: pokenini-api's `GET/POST /trainer_dex_link/{trainerExternalId}/...` (Task 5), `UserTokenService::getLoggedUserToken()`, `GetTrainerPokedexService::getPokedexData(string $dexSlug, array $filters): array{dex: array<string, mixed>, ...}` (existing — same premium-gating data source `AlbumUpsertController` already uses).
- Produces: routes `GET /album_link/{dexSlug}`, `POST /album_link/{dexSlug}` (body `{targetDexSlug, bidirectional}`), `DELETE /album_link/{linkId}`, all `#[IsGranted('ROLE_TRAINER')]`. `sourceDexSlug` sent to the API is always the route's `dexSlug` — never taken from the request body — matching how `ModifyAlbumApiService`'s `dexSlug` is always the route parameter, never client-supplied. `App\Exception\ApiValidationException::getStatusCode(): int` carries the API's original status code from `TrainerDexLinkService::create()` up to the controller.

**Design note on error propagation:** unlike `ModifyTrainerAlbumService` (which collapses every API failure into a generic 500 `ModifyFailedException`), this proxy must preserve the API's distinct 400/404/409 codes for validation failures (self-link / unknown dex / duplicate edge — all decided in pokenini-api's `TrainerDexLinkService`, Task 5) since there is no equivalent validation logic here to re-derive them from. `AbstractApiService::requestContent()` lets `Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface` bubble up on any 4xx/5xx (it's thrown by `ResponseInterface::getContent()` under the hood). **Important:** this repo's `deptrac.yaml` only allows `AppService` (not `AppController`) to depend on `SymfonyContractsHttpClient` — `AppController`'s ruleset has `AppException` but no HTTP-client layer at all (confirm with `grep -n "AppController:" -A 20 deptrac.yaml`) — so `HttpExceptionInterface` must be caught inside `TrainerDexLinkService::create()`, not the controller. The service translates it into the new `App\Exception\ApiValidationException` (carrying the original status code via `getStatusCode()`), which the controller — already allowed to depend on `AppException` — catches instead.

- [ ] **Step 1: Write the failing test for `TrainerDexLinkApiService`**

Create `tests/src/Unit/Service/Api/TrainerDexLinkApiServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Api;

use App\Service\Api\TrainerDexLinkApiService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkApiService::class)]
final class TrainerDexLinkApiServiceTest extends TestCase
{
    public function testList(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('[{"id":"link-1"}]');

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.local/trainer_dex_link/8800088/douze', $this->anything())
            ->willReturn($response)
        ;

        $service = $this->service($client);

        $this->assertSame([['id' => 'link-1']], $service->list('douze', '8800088'));
    }

    public function testCreate(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('');

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.local/trainer_dex_link/8800088',
                $this->callback(function (array $options): bool {
                    $body = json_decode((string) $options['body'], true);

                    return ['sourceDexSlug' => 'douze', 'targetDexSlug' => 'treize', 'bidirectional' => true] === $body;
                }),
            )
            ->willReturn($response)
        ;

        $service = $this->service($client);

        $service->create('douze', 'treize', true, '8800088');
    }

    public function testDelete(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('');

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('DELETE', 'https://api.local/trainer_dex_link/8800088/link-1', $this->anything())
            ->willReturn($response)
        ;

        $service = $this->service($client);

        $service->delete('link-1', '8800088');
    }

    private function service(HttpClientInterface $client): TrainerDexLinkApiService
    {
        return new TrainerDexLinkApiService(
            $this->createMock(LoggerInterface::class),
            $client,
            'https://api.local',
            '/path/to/cafile',
            $this->createMock(TagAwareCacheInterface::class),
            'login',
            'password',
        );
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/Api/TrainerDexLinkApiServiceTest.php`
Expected: FAIL — `Class "App\Service\Api\TrainerDexLinkApiService" not found`

- [ ] **Step 3: Implement `TrainerDexLinkApiService`**

```php
<?php

declare(strict_types=1);

namespace App\Service\Api;

use App\Utils\JsonDecoder;

class TrainerDexLinkApiService extends AbstractApiService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(string $dexSlug, string $trainerId): array
    {
        $json = $this->requestContent('GET', "/trainer_dex_link/{$trainerId}/{$dexSlug}");

        /** @var array<int, array<string, mixed>> */
        return JsonDecoder::decode($json);
    }

    public function create(
        string $dexSlug,
        string $targetDexSlug,
        bool $bidirectional,
        string $trainerId,
    ): void {
        $body = json_encode(
            [
                'sourceDexSlug' => $dexSlug,
                'targetDexSlug' => $targetDexSlug,
                'bidirectional' => $bidirectional,
            ],
            JSON_THROW_ON_ERROR
        );

        $this->requestContent(
            'POST',
            "/trainer_dex_link/{$trainerId}",
            [
                'body' => $body,
            ]
        );
    }

    public function delete(string $linkId, string $trainerId): void
    {
        $this->requestContent('DELETE', "/trainer_dex_link/{$trainerId}/{$linkId}");
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/Api/TrainerDexLinkApiServiceTest.php`
Expected: PASS (3 tests)

- [ ] **Step 5: Add `ApiValidationException`**

```php
<?php

declare(strict_types=1);

namespace App\Exception;

final class ApiValidationException extends \RuntimeException
{
    public function __construct(
        private readonly int $statusCode,
    ) {
        parent::__construct();
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
```

Save as `src/Exception/ApiValidationException.php`. Unlike `InvalidSheetDataException`-style marker exceptions (bodyless, just a type), this one carries the API's original status code so the controller doesn't need to know anything about the underlying HTTP client exception.

- [ ] **Step 6: Write the failing test for `TrainerDexLinkService`**

Create `tests/src/Unit/Service/TrainerDexLinkServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Exception\ApiValidationException;
use App\Security\UserTokenService;
use App\Service\Api\TrainerDexLinkApiService;
use App\Service\TrainerDexLinkService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkService::class)]
final class TrainerDexLinkServiceTest extends TestCase
{
    public function testList(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService->method('getLoggedUserToken')->willReturn('8800088');

        $apiService = $this->createMock(TrainerDexLinkApiService::class);
        $apiService->expects($this->once())
            ->method('list')
            ->with('douze', '8800088')
            ->willReturn([['id' => 'link-1']])
        ;

        $service = new TrainerDexLinkService($userTokenService, $apiService);

        $this->assertSame([['id' => 'link-1']], $service->list('douze'));
    }

    public function testCreate(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService->method('getLoggedUserToken')->willReturn('8800088');

        $apiService = $this->createMock(TrainerDexLinkApiService::class);
        $apiService->expects($this->once())
            ->method('create')
            ->with('douze', 'treize', true, '8800088')
        ;

        $service = new TrainerDexLinkService($userTokenService, $apiService);

        $service->create('douze', 'treize', true);
    }

    public function testCreateTranslatesApiHttpExceptionIntoApiValidationException(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService->method('getLoggedUserToken')->willReturn('8800088');

        $apiResponse = $this->createMock(ResponseInterface::class);
        $apiResponse->method('getStatusCode')->willReturn(409);

        $exception = $this->createMock(HttpExceptionInterface::class);
        $exception->method('getResponse')->willReturn($apiResponse);

        $apiService = $this->createMock(TrainerDexLinkApiService::class);
        $apiService->expects($this->once())
            ->method('create')
            ->willThrowException($exception)
        ;

        $service = new TrainerDexLinkService($userTokenService, $apiService);

        try {
            $service->create('douze', 'treize', true);
            $this->fail('Expected ApiValidationException');
        } catch (ApiValidationException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
    }

    public function testDelete(): void
    {
        $userTokenService = $this->createMock(UserTokenService::class);
        $userTokenService->method('getLoggedUserToken')->willReturn('8800088');

        $apiService = $this->createMock(TrainerDexLinkApiService::class);
        $apiService->expects($this->once())
            ->method('delete')
            ->with('link-1', '8800088')
        ;

        $service = new TrainerDexLinkService($userTokenService, $apiService);

        $service->delete('link-1');
    }
}
```

- [ ] **Step 7: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/TrainerDexLinkServiceTest.php`
Expected: FAIL — `Class "App\Service\TrainerDexLinkService" not found`

- [ ] **Step 8: Implement `TrainerDexLinkService`**

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ApiValidationException;
use App\Security\UserTokenService;
use App\Service\Api\TrainerDexLinkApiService;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class TrainerDexLinkService
{
    public function __construct(
        private readonly UserTokenService $userTokenService,
        private readonly TrainerDexLinkApiService $apiService,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(string $dexSlug): array
    {
        return $this->apiService->list($dexSlug, $this->userTokenService->getLoggedUserToken());
    }

    public function create(string $dexSlug, string $targetDexSlug, bool $bidirectional): void
    {
        try {
            $this->apiService->create($dexSlug, $targetDexSlug, $bidirectional, $this->userTokenService->getLoggedUserToken());
        } catch (HttpExceptionInterface $e) {
            throw new ApiValidationException($e->getResponse()->getStatusCode());
        }
    }

    public function delete(string $linkId): void
    {
        $this->apiService->delete($linkId, $this->userTokenService->getLoggedUserToken());
    }
}
```

`HttpExceptionInterface` (from `Symfony\Contracts\HttpClient\Exception\*`) is only ever referenced here, inside `Service` — this repo's `deptrac.yaml` allows `AppService → SymfonyContractsHttpClient` but not `AppController → SymfonyContractsHttpClient`, so the controller (Step 12) only ever sees `ApiValidationException`.

- [ ] **Step 9: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/TrainerDexLinkServiceTest.php`
Expected: PASS (4 tests)

- [ ] **Step 10: Write the failing test for `TrainerDexLinkController`**

Mirrors `tests/src/Unit/Controller/Album/AlbumUpsertControllerTest.php`'s mock-container style (this repo's controller tests never boot the kernel). Create `tests/src/Unit/Controller/Album/TrainerDexLinkControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Album;

use App\Controller\Album\TrainerDexLinkController;
use App\Exception\ApiValidationException;
use App\Exception\DexNotFoundException;
use App\Service\GetTrainerPokedexService;
use App\Service\TrainerDexLinkService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkController::class)]
final class TrainerDexLinkControllerTest extends TestCase
{
    public function testListRejectsPremiumDexForNonCollector(): void
    {
        $getTrainerPokedexService = $this->createMock(GetTrainerPokedexService::class);
        $getTrainerPokedexService->method('getPokedexData')
            ->willReturn(['dex' => ['flags' => ['is_premium' => true]], 'pokemons' => []])
        ;

        $trainerDexLinkService = $this->createMock(TrainerDexLinkService::class);
        $trainerDexLinkService->expects($this->never())->method('list');

        $controller = $this->controller($getTrainerPokedexService, $trainerDexLinkService, false);

        $response = $controller->list('douze');

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testListReturnsLinksWhenAccessible(): void
    {
        $getTrainerPokedexService = $this->createMock(GetTrainerPokedexService::class);
        $getTrainerPokedexService->method('getPokedexData')
            ->willReturn(['dex' => ['flags' => ['is_premium' => false]], 'pokemons' => []])
        ;

        $trainerDexLinkService = $this->createMock(TrainerDexLinkService::class);
        $trainerDexLinkService->expects($this->once())
            ->method('list')
            ->with('douze')
            ->willReturn([['id' => 'link-1']])
        ;

        $controller = $this->controller($getTrainerPokedexService, $trainerDexLinkService, true);

        $response = $controller->list('douze');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('[{"id":"link-1"}]', $response->getContent());
    }

    public function testListNotFoundWhenDexUnknown(): void
    {
        $getTrainerPokedexService = $this->createMock(GetTrainerPokedexService::class);
        $getTrainerPokedexService->method('getPokedexData')
            ->willThrowException(new DexNotFoundException())
        ;

        $trainerDexLinkService = $this->createMock(TrainerDexLinkService::class);
        $trainerDexLinkService->expects($this->never())->method('list');

        $controller = $this->controller($getTrainerPokedexService, $trainerDexLinkService, true);

        $response = $controller->list('douze');

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testCreateRejectsEmptyBody(): void
    {
        $getTrainerPokedexService = $this->createMock(GetTrainerPokedexService::class);
        $getTrainerPokedexService->method('getPokedexData')
            ->willReturn(['dex' => ['flags' => ['is_premium' => false]], 'pokemons' => []])
        ;

        $trainerDexLinkService = $this->createMock(TrainerDexLinkService::class);
        $trainerDexLinkService->expects($this->never())->method('create');

        $controller = $this->controller($getTrainerPokedexService, $trainerDexLinkService, true);

        $response = $controller->create('douze', Request::create('test.local', 'POST', content: ''));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testCreateRejectsMissingTargetDexSlug(): void
    {
        $getTrainerPokedexService = $this->createMock(GetTrainerPokedexService::class);
        $getTrainerPokedexService->method('getPokedexData')
            ->willReturn(['dex' => ['flags' => ['is_premium' => false]], 'pokemons' => []])
        ;

        $trainerDexLinkService = $this->createMock(TrainerDexLinkService::class);
        $trainerDexLinkService->expects($this->never())->method('create');

        $controller = $this->controller($getTrainerPokedexService, $trainerDexLinkService, true);

        $response = $controller->create('douze', Request::create('test.local', 'POST', content: '{}'));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testCreateForwardsTheApiStatusCodeOnFailure(): void
    {
        $getTrainerPokedexService = $this->createMock(GetTrainerPokedexService::class);
        $getTrainerPokedexService->method('getPokedexData')
            ->willReturn(['dex' => ['flags' => ['is_premium' => false]], 'pokemons' => []])
        ;

        $trainerDexLinkService = $this->createMock(TrainerDexLinkService::class);
        $trainerDexLinkService->expects($this->once())
            ->method('create')
            ->with('douze', 'treize', false)
            ->willThrowException(new ApiValidationException(409))
        ;

        $controller = $this->controller($getTrainerPokedexService, $trainerDexLinkService, true);

        $response = $controller->create('douze', Request::create('test.local', 'POST', content: '{"targetDexSlug":"treize"}'));

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testCreateSucceeds(): void
    {
        $getTrainerPokedexService = $this->createMock(GetTrainerPokedexService::class);
        $getTrainerPokedexService->method('getPokedexData')
            ->willReturn(['dex' => ['flags' => ['is_premium' => false]], 'pokemons' => []])
        ;

        $trainerDexLinkService = $this->createMock(TrainerDexLinkService::class);
        $trainerDexLinkService->expects($this->once())
            ->method('create')
            ->with('douze', 'treize', true)
        ;

        $controller = $this->controller($getTrainerPokedexService, $trainerDexLinkService, true);

        $response = $controller->create('douze', Request::create('test.local', 'POST', content: '{"targetDexSlug":"treize","bidirectional":true}'));

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $getTrainerPokedexService = $this->createMock(GetTrainerPokedexService::class);

        $trainerDexLinkService = $this->createMock(TrainerDexLinkService::class);
        $trainerDexLinkService->expects($this->once())
            ->method('delete')
            ->with('link-1')
        ;

        $controller = new TrainerDexLinkController($getTrainerPokedexService, $trainerDexLinkService);

        $response = $controller->delete('link-1');

        $this->assertSame(200, $response->getStatusCode());
    }

    private function controller(
        GetTrainerPokedexService $getTrainerPokedexService,
        TrainerDexLinkService $trainerDexLinkService,
        bool $isCollector,
    ): TrainerDexLinkController {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->with('ROLE_COLLECTOR')->willReturn($isCollector);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($authorizationChecker);

        $controller = new TrainerDexLinkController($getTrainerPokedexService, $trainerDexLinkService);
        $controller->setContainer($container);

        return $controller;
    }
}
```

- [ ] **Step 11: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Controller/Album/TrainerDexLinkControllerTest.php`
Expected: FAIL — `Class "App\Controller\Album\TrainerDexLinkController" not found`

- [ ] **Step 12: Implement `TrainerDexLinkController`**

```php
<?php

declare(strict_types=1);

namespace App\Controller\Album;

use App\Exception\ApiValidationException;
use App\Exception\DexNotFoundException;
use App\Service\GetTrainerPokedexService;
use App\Service\TrainerDexLinkService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/album_link')]
final class TrainerDexLinkController extends AbstractController
{
    public function __construct(
        private readonly GetTrainerPokedexService $getTrainerPokedexService,
        private readonly TrainerDexLinkService $trainerDexLinkService,
    ) {}

    #[Route('/{dexSlug}', methods: ['GET'])]
    #[IsGranted('ROLE_TRAINER')]
    public function list(string $dexSlug): Response
    {
        $notAccessible = $this->assertDexIsAccessible($dexSlug);
        if (null !== $notAccessible) {
            return $notAccessible;
        }

        return new JsonResponse($this->trainerDexLinkService->list($dexSlug), Response::HTTP_OK);
    }

    #[Route('/{dexSlug}', methods: ['POST'])]
    #[IsGranted('ROLE_TRAINER')]
    public function create(string $dexSlug, Request $request): Response
    {
        $notAccessible = $this->assertDexIsAccessible($dexSlug);
        if (null !== $notAccessible) {
            return $notAccessible;
        }

        $json = $request->getContent();

        if (!$json) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        /** @var array{targetDexSlug?: mixed, bidirectional?: mixed} $content */
        $content = json_decode($json, true) ?? [];

        if (!isset($content['targetDexSlug']) || !\is_string($content['targetDexSlug'])) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $bidirectional = $content['bidirectional'] ?? false;

        if (!\is_bool($bidirectional)) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->trainerDexLinkService->create($dexSlug, $content['targetDexSlug'], $bidirectional);
        } catch (ApiValidationException $e) {
            return new JsonResponse([], $e->getStatusCode());
        }

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route('/{linkId}', methods: ['DELETE'])]
    #[IsGranted('ROLE_TRAINER')]
    public function delete(string $linkId): Response
    {
        $this->trainerDexLinkService->delete($linkId);

        return new Response();
    }

    private function assertDexIsAccessible(string $dexSlug): ?JsonResponse
    {
        try {
            $pokedex = $this->getTrainerPokedexService->getPokedexData($dexSlug, []);
        } catch (DexNotFoundException) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        /** @var array{is_premium: bool} $flags */
        $flags = $pokedex['dex']['flags'];

        if ($flags['is_premium'] && !$this->isGranted('ROLE_COLLECTOR')) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        return null;
    }
}
```

- [ ] **Step 13: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Controller/Album/TrainerDexLinkControllerTest.php`
Expected: PASS (8 tests)

- [ ] **Step 14: Commit**

```bash
git add src/Exception/ApiValidationException.php src/Service/Api/TrainerDexLinkApiService.php src/Service/TrainerDexLinkService.php src/Controller/Album/TrainerDexLinkController.php tests/src/Unit/Service/Api/TrainerDexLinkApiServiceTest.php tests/src/Unit/Service/TrainerDexLinkServiceTest.php tests/src/Unit/Controller/Album/TrainerDexLinkControllerTest.php
git commit -m "feat: proxy the dex-link CRUD surface from pokenini-back"
```

---

### Task 10: Run the full pokenini-back suite

**Files:** none (verification-only task).

- [ ] **Step 1: Run the full test suite**

Run: `cd /home/renaud/projects/pokenini-back && make tests`
Expected: all green.

- [ ] **Step 2: Run coverage and mutation testing**

Run: `make coverage && make infection`
Expected: 100% coverage, 100% MSI.

- [ ] **Step 3: Run the remaining quality gates**

Run: `make quality`
Expected: all green — confirm Deptrac has no complaints about the new `Controller\Album\TrainerDexLinkController → Service\TrainerDexLinkService → Service\Api\TrainerDexLinkApiService` chain (it's the same shape as the existing `AlbumUpsertController → ModifyTrainerAlbumService → ModifyAlbumApiService` chain, so no ruleset changes are expected here, unlike pokenini-api's Task 5).

- [ ] **Step 4: Commit** (only if fixes were required)

```bash
git add -A
git commit -m "fix: address quality/coverage/mutation feedback for the dex-link proxy"
```

---

## pokenini-web (`/home/renaud/projects/pokenini-web`)

### Task 11: `TrainerDexLinkController` (web) + `Service\Back\TrainerDexLinkService` + `ResponseObject\Album\TrainerDexLink`

**Files:**
- Create: `src/ResponseObject/Album/TrainerDexLink.php`
- Create: `src/Service/Back/TrainerDexLinkService.php`
- Create: `src/Controller/TrainerDexLinkController.php`
- Create: `tests/src/Unit/ResponseObject/Album/TrainerDexLinkTest.php`
- Create: `tests/src/Unit/Service/Back/TrainerDexLinkServiceTest.php`
- Create: `tests/src/Unit/Controller/TrainerDexLinkControllerTest.php`

**Interfaces:**
- Consumes: pokenini-back's `GET/POST /album_link/{dexSlug}`, `DELETE /album_link/{linkId}` (Task 9).
- Produces: `App\ResponseObject\Album\TrainerDexLink` (getters `getId()`, `getDirection()`, `getTargetDexSlug()`, `getTargetName()`, `getTargetFrenchName()`), `Service\Back\TrainerDexLinkService::list(string $dexSlug): TrainerDexLink[]` / `::create(string $dexSlug, string $body): void` / `::delete(string $linkId): void`, routes `GET/POST /album_link/{dexSlug}`, `DELETE /album_link/{linkId}`, both `#[IsGranted('ROLE_TRAINER')]`. Consumed by Task 12 (Twig) only indirectly — the Twig section talks to these routes via `album-links.js` (Task 13), not server-side.

- [ ] **Step 1: Write the failing `ResponseObject\Album\TrainerDexLink` test**

Mirrors `tests/src/Unit/ResponseObject/Album/DexListItemTest.php`. Create `tests/src/Unit/ResponseObject/Album/TrainerDexLinkTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\ResponseObject\Album;

use App\ResponseObject\Album\TrainerDexLink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexLink::class)]
final class TrainerDexLinkTest extends TestCase
{
    public function testGetters(): void
    {
        $link = new TrainerDexLink(
            id: 'link-1',
            direction: 'both',
            targetDexSlug: 'shiny',
            targetName: 'Shiny Living',
            targetFrenchName: 'Vivarium Chromatique',
        );

        $this->assertSame('link-1', $link->getId());
        $this->assertSame('both', $link->getDirection());
        $this->assertSame('shiny', $link->getTargetDexSlug());
        $this->assertSame('Shiny Living', $link->getTargetName());
        $this->assertSame('Vivarium Chromatique', $link->getTargetFrenchName());
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/ResponseObject/Album/TrainerDexLinkTest.php`
Expected: FAIL — `Class "App\ResponseObject\Album\TrainerDexLink" not found`

- [ ] **Step 3: Implement `ResponseObject\Album\TrainerDexLink`**

```php
<?php

declare(strict_types=1);

namespace App\ResponseObject\Album;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerDexLink
{
    public function __construct(
        #[SerializedName('id')]
        private readonly string $id,
        #[SerializedName('direction')]
        private readonly string $direction,
        #[SerializedName('target_dex_slug')]
        private readonly string $targetDexSlug,
        #[SerializedName('target_name')]
        private readonly string $targetName,
        #[SerializedName('target_french_name')]
        private readonly string $targetFrenchName,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function getTargetDexSlug(): string
    {
        return $this->targetDexSlug;
    }

    public function getTargetName(): string
    {
        return $this->targetName;
    }

    public function getTargetFrenchName(): string
    {
        return $this->targetFrenchName;
    }
}
```

Save as `src/ResponseObject/Album/TrainerDexLink.php`.

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/ResponseObject/Album/TrainerDexLinkTest.php`
Expected: PASS (1 test)

- [ ] **Step 5: Write the failing `Service\Back\TrainerDexLinkService` test**

Mirrors `tests/src/Unit/Service/Back/GetTrainerDexListServiceTest.php` and `ModifyDexServiceTest.php`, reusing the shared `AbstractTestBackService` helper. Create `tests/src/Unit/Service/Back/TrainerDexLinkServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Back;

use App\ResponseObject\Album\TrainerDexLink;
use App\Security\UserTokenServiceInterface;
use App\Service\Back\AbstractBackService;
use App\Service\Back\TrainerDexLinkService;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkService::class)]
final class TrainerDexLinkServiceTest extends AbstractTestBackService
{
    public function testList(): void
    {
        $json = '[{"id":"link-1"}]';
        $links = [new TrainerDexLink('link-1', 'to', 'shiny', 'Shiny Living', 'Vivarium Chromatique')];

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('deserialize')
            ->with($json, TrainerDexLink::class.'[]', 'json')
            ->willReturn($links)
        ;

        /** @var TrainerDexLinkService $service */
        $service = $this->getServiceWithLoggedUser('GET', $json, 'album_link/national', [], $serializer);

        $this->assertSame($links, $service->list('national'));
    }

    public function testCreate(): void
    {
        /** @var TrainerDexLinkService $service */
        $service = $this->getServiceWithLoggedUser(
            'POST',
            '',
            'album_link/national',
            ['body' => '{"targetDexSlug":"shiny","bidirectional":true}'],
        );

        $service->create('national', '{"targetDexSlug":"shiny","bidirectional":true}');
    }

    public function testDelete(): void
    {
        /** @var TrainerDexLinkService $service */
        $service = $this->getServiceWithLoggedUser('DELETE', '', 'album_link/link-1');

        $service->delete('link-1');
    }

    #[\Override]
    protected function instanciateService(
        LoggerInterface $logger,
        HttpClientInterface $client,
        string $url,
        string $cafilePath,
        UserTokenServiceInterface $userTokenService,
        SerializerInterface $serializer,
    ): AbstractBackService {
        return new TrainerDexLinkService(
            $logger,
            $client,
            $url,
            $cafilePath,
            $userTokenService,
            $serializer,
        );
    }
}
```

- [ ] **Step 6: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/Back/TrainerDexLinkServiceTest.php`
Expected: FAIL — `Class "App\Service\Back\TrainerDexLinkService" not found`

- [ ] **Step 7: Implement `Service\Back\TrainerDexLinkService`**

```php
<?php

declare(strict_types=1);

namespace App\Service\Back;

use App\ResponseObject\Album\TrainerDexLink;

class TrainerDexLinkService extends AbstractBackService
{
    /**
     * @return TrainerDexLink[]
     */
    public function list(string $dexSlug): array
    {
        $json = $this->requestContent('GET', "/album_link/{$dexSlug}");

        /** @var TrainerDexLink[] */
        return $this->serializer->deserialize($json, TrainerDexLink::class.'[]', 'json');
    }

    public function create(string $dexSlug, string $body): void
    {
        $this->request('POST', "/album_link/{$dexSlug}", ['body' => $body]);
    }

    public function delete(string $linkId): void
    {
        $this->request('DELETE', "/album_link/{$linkId}");
    }
}
```

Save as `src/Service/Back/TrainerDexLinkService.php`.

- [ ] **Step 8: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/Back/TrainerDexLinkServiceTest.php`
Expected: PASS (3 tests)

- [ ] **Step 9: Write the failing `TrainerDexLinkController` test**

Mirrors `tests/src/Unit/Controller/AlbumUpsertControllerTest.php`'s mock style (no kernel boot). Create `tests/src/Unit/Controller/TrainerDexLinkControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\TrainerDexLinkController;
use App\ResponseObject\Album\TrainerDexLink;
use App\Service\Back\TrainerDexLinkService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkController::class)]
final class TrainerDexLinkControllerTest extends TestCase
{
    public function testList(): void
    {
        $link = new TrainerDexLink('link-1', 'to', 'shiny', 'Shiny Living', 'Vivarium Chromatique');

        $service = $this->createMock(TrainerDexLinkService::class);
        $service->expects($this->once())
            ->method('list')
            ->with('national')
            ->willReturn([$link])
        ;

        $controller = new TrainerDexLinkController($service);

        $response = $controller->list('national');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListForwardsApiFailureStatusCode(): void
    {
        $apiResponse = $this->createMock(ResponseInterface::class);
        $apiResponse->method('getStatusCode')->willReturn(404);

        $exception = $this->createMock(HttpExceptionInterface::class);
        $exception->method('getResponse')->willReturn($apiResponse);

        $service = $this->createMock(TrainerDexLinkService::class);
        $service->method('list')->willThrowException($exception);

        $controller = new TrainerDexLinkController($service);

        $response = $controller->list('national');

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testCreateRejectsEmptyBody(): void
    {
        $service = $this->createMock(TrainerDexLinkService::class);
        $service->expects($this->never())->method('create');

        $controller = new TrainerDexLinkController($service);

        $response = $controller->create('national', Request::create('test.local', 'POST', content: ''));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testCreateSucceeds(): void
    {
        $service = $this->createMock(TrainerDexLinkService::class);
        $service->expects($this->once())
            ->method('create')
            ->with('national', '{"targetDexSlug":"shiny"}')
        ;

        $controller = new TrainerDexLinkController($service);

        $response = $controller->create('national', Request::create('test.local', 'POST', content: '{"targetDexSlug":"shiny"}'));

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateForwardsApiFailureStatusCode(): void
    {
        $apiResponse = $this->createMock(ResponseInterface::class);
        $apiResponse->method('getStatusCode')->willReturn(409);

        $exception = $this->createMock(HttpExceptionInterface::class);
        $exception->method('getResponse')->willReturn($apiResponse);

        $service = $this->createMock(TrainerDexLinkService::class);
        $service->method('create')->willThrowException($exception);

        $controller = new TrainerDexLinkController($service);

        $response = $controller->create('national', Request::create('test.local', 'POST', content: '{"targetDexSlug":"shiny"}'));

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $service = $this->createMock(TrainerDexLinkService::class);
        $service->expects($this->once())->method('delete')->with('link-1');

        $controller = new TrainerDexLinkController($service);

        $response = $controller->delete('link-1');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testDeleteForwardsApiFailureStatusCode(): void
    {
        $apiResponse = $this->createMock(ResponseInterface::class);
        $apiResponse->method('getStatusCode')->willReturn(404);

        $exception = $this->createMock(HttpExceptionInterface::class);
        $exception->method('getResponse')->willReturn($apiResponse);

        $service = $this->createMock(TrainerDexLinkService::class);
        $service->method('delete')->willThrowException($exception);

        $controller = new TrainerDexLinkController($service);

        $response = $controller->delete('link-1');

        $this->assertSame(404, $response->getStatusCode());
    }
}
```

- [ ] **Step 10: Run the test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Controller/TrainerDexLinkControllerTest.php`
Expected: FAIL — `Class "App\Controller\TrainerDexLinkController" not found`

- [ ] **Step 11: Implement `TrainerDexLinkController`**

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Back\TrainerDexLinkService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

#[Route('/album_link')]
final class TrainerDexLinkController extends AbstractController
{
    public function __construct(
        private readonly TrainerDexLinkService $service,
    ) {}

    #[Route('/{dexSlug}', methods: ['GET'])]
    #[IsGranted('ROLE_TRAINER')]
    public function list(string $dexSlug): Response
    {
        try {
            $links = $this->service->list($dexSlug);
        } catch (HttpExceptionInterface $e) {
            return new JsonResponse([], $e->getResponse()->getStatusCode());
        }

        return $this->json($links);
    }

    #[Route('/{dexSlug}', methods: ['POST'])]
    #[IsGranted('ROLE_TRAINER')]
    public function create(string $dexSlug, Request $request): Response
    {
        $content = $request->getContent();

        if (!$content) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->service->create($dexSlug, $content);
        } catch (HttpExceptionInterface $e) {
            return new JsonResponse([], $e->getResponse()->getStatusCode());
        }

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route('/{linkId}', methods: ['DELETE'])]
    #[IsGranted('ROLE_TRAINER')]
    public function delete(string $linkId): Response
    {
        try {
            $this->service->delete($linkId);
        } catch (HttpExceptionInterface $e) {
            return new JsonResponse([], $e->getResponse()->getStatusCode());
        }

        return new Response();
    }
}
```

- [ ] **Step 12: Run the test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Controller/TrainerDexLinkControllerTest.php`
Expected: PASS (7 tests)

- [ ] **Step 13: Commit**

```bash
git add src/ResponseObject/Album/TrainerDexLink.php src/Service/Back/TrainerDexLinkService.php src/Controller/TrainerDexLinkController.php tests/src/Unit/ResponseObject/Album/TrainerDexLinkTest.php tests/src/Unit/Service/Back/TrainerDexLinkServiceTest.php tests/src/Unit/Controller/TrainerDexLinkControllerTest.php
git commit -m "feat: proxy the dex-link CRUD surface from pokenini-web"
```

---

### Task 12: Twig — "Liens" section in `templates/Album/_offcanvas.html.twig`

**Files:**
- Modify: `src/Controller/AlbumIndexController.php` (feed the picker grid with the trainer's full dex list)
- Modify: `templates/Album/_offcanvas.html.twig`
- Modify: `templates/Album/_album_macros.html.twig` (add the `linksToastSuccess`/`linksToastError` toasts, mirroring the existing `shareToastSuccess`/`shareToastError`)
- Modify: `translations/messages+intl-icu.en.yaml`
- Modify: `translations/messages+intl-icu.fr.yaml`

**Interfaces:**
- Consumes: `App\Service\Back\GetTrainerDexListService::get(): DexListItem[]` (existing, already used by `TrainerIndexController` — same source of banner/name data for the picker grid, per the design's "réutilise `dexBannerUrl`, comme les cartes de `/trainer`"), the global `dexBannerUrl` Twig variable (`config/packages/twig.yaml`, formatted with a dex slug via the `format` filter, e.g. `dexBannerUrl|format(dex.dex.slug)`, as already used in `templates/Trainer/Section/_dex.html.twig`).
- Produces: a new `trainerDexList` template variable available inside `_offcanvas.html.twig` (included without `only` from `Album/index.html.twig`, so it inherits every variable `AlbumIndexController::index()` passes to `render()`). The rendered markup exposes `#album-links-section` (with a `data-dex-slug` attribute), `#active-links` (empty container, populated by Task 13's JS), `.dex-pick-card` buttons (one per other dex, server-rendered, each carrying `data-dex-slug`), a `name="link-direction"` radio group, and a `#create-link` button — these are the exact hooks Task 13's `album-links.js` binds to.

**Why the picker grid is server-rendered but the active-links list isn't:** the spec says the JS fetch "peuple la liste + la grille de sélection (dex non déjà liés)" — the *list* of active links has no other data source (only the API knows which links exist), so it must come from JS. The *candidate* grid (every other dex, for picking a target) is exactly what `GetTrainerDexListService` already gives `AlbumIndexController` for free (same service `TrainerIndexController` already uses for `/trainer`), so it's rendered server-side like the mockup shows, and Task 13's JS only hides the cards that turn out to already be linked once the `GET /album_link/{dexSlug}` response comes back.

- [ ] **Step 1: Wire `GetTrainerDexListService` into `AlbumIndexController`**

In `src/Controller/AlbumIndexController.php`, add the imports (alphabetically — `App\ResponseObject\Album\DexListItem` right after `App\ResponseObject\Album\Dex`, `App\Service\Back\GetTrainerDexListService` right after `App\Service\GetLabelsService`):

```php
use App\ResponseObject\Album\DexListItem;
use App\Service\Back\GetTrainerDexListService;
```

Change the constructor:

```php
    public function __construct(
        private readonly GetTrainerPokedexService $getTrainerPokedexService,
        private readonly GetLabelsService $getLabelsService,
        private readonly GetTrainerDexListService $getTrainerDexListService,
        private readonly UserTokenServiceInterface $userTokenService,
    ) {}
```

In `index()`, add this right before the `return $this->render(...)` call:

```php
        /** @var DexListItem[] $trainerDexList */
        $trainerDexList = array_values(array_filter(
            $this->getTrainerDexListService->get(),
            static fn (DexListItem $item): bool => $item->getSettings()->getSlug() !== $dexSlug,
        ));
```

And add `'trainerDexList' => $trainerDexList,` to the array passed to `render()`, right after `'allowedToEdit' => ...,`.

The filter excludes the current dex here (server-side) rather than in Twig, since `$dexSlug` is already in scope and it keeps the Twig loop simpler.

- [ ] **Step 2: Add the toasts**

In `templates/Album/_album_macros.html.twig`, right after the existing `shareToastError` block (find it with `grep -n "shareToastError" templates/Album/_album_macros.html.twig`), add:

```twig
  <div id="linksToastSuccess" class="toast text-bg-success" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">{{ 'album.offcanvas.links.toast.success'|trans }}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>

  <div id="linksToastError" class="toast text-bg-danger" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">{{ 'album.offcanvas.links.toast.error'|trans }}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
```

(Match the exact surrounding markup of the `shareToastSuccess`/`shareToastError` blocks you find — copy their `d-flex`/`btn-close` structure verbatim, only the id and translation key differ.)

- [ ] **Step 3: Add translation keys**

In `translations/messages+intl-icu.en.yaml`, inside the existing `album: offcanvas:` block, add a `links:` sibling to `informations:`/`parameters:` (same indentation):

```yaml
    links:
      title: Links
      description: A link synchronizes the catch state between this dex and another of your dexes.
      add:
        label: "Add a link — pick one of your other dexes:"
      direction:
        to: To it
        from: From it
        both: Both
      create: Create the link
      delete_title: Delete this link
      toast:
        success: Link updated successfully.
        error: Something went wrong while updating the link.
```

In `translations/messages+intl-icu.fr.yaml`, same structure, French copy:

```yaml
    links:
      title: Liens
      description: Un lien synchronise le statut de capture entre ce dex et un autre de tes dex.
      add:
        label: "Ajouter un lien — choisis un de tes autres dex :"
      direction:
        to: Vers lui
        from: Depuis lui
        both: Les deux
      create: Créer le lien
      delete_title: Supprimer ce lien
      toast:
        success: "Lien mis à jour avec succès."
        error: "Une erreur est survenue lors de la mise à jour du lien."
```

- [ ] **Step 4: Add the "Liens" section to the offcanvas**

In `templates/Album/_offcanvas.html.twig`, the "Paramètres" block currently ends and "Informations" begins like this:

```twig
        </form>
      {% endif %}
    </div>

    <div>
      <h2>
        {{ 'album.offcanvas.informations.title'|trans }}
      </h2>
```

Replace that exact snippet with:

```twig
        </form>
      {% endif %}
    </div>

    {% if allowedToEdit %}
    <div id="album-links-section" data-dex-slug="{{ currentDexSlug }}">
      <h2 class="h5">
        <i class="bi bi-link-45deg"></i>
        {{ 'album.offcanvas.links.title'|trans }}
        <span class="badge text-bg-info" id="album-links-count" hidden>0</span>
      </h2>
      <p class="form-text">
        {{ 'album.offcanvas.links.description'|trans }}
      </p>

      <div class="list-group mb-3" id="active-links"></div>

      <p class="form-text mb-1">{{ 'album.offcanvas.links.add.label'|trans }}</p>
      <div class="dex-picker-grid mb-2" id="dex-picker-grid">
        {% for item in trainerDexList %}
          {% set bannerUrl = dexBannerUrl|format(item.dex.slug) %}
          <button type="button" class="dex-pick-card" data-dex-slug="{{ item.settings.slug }}">
            <img src="{{ bannerUrl }}" alt="" loading="lazy">
            <small>{{ locale is same as('fr') ? item.settings.frenchName : item.settings.name }}</small>
          </button>
        {% endfor %}
      </div>

      <div class="btn-group w-100 mb-2" role="group" aria-label="{{ 'album.offcanvas.links.title'|trans }}">
        <input type="radio" class="btn-check" name="link-direction" id="link-direction-to" value="to" autocomplete="off" checked>
        <label class="btn btn-outline-secondary btn-sm" for="link-direction-to"><i class="bi bi-arrow-right"></i> {{ 'album.offcanvas.links.direction.to'|trans }}</label>

        <input type="radio" class="btn-check" name="link-direction" id="link-direction-from" value="from" autocomplete="off">
        <label class="btn btn-outline-secondary btn-sm" for="link-direction-from"><i class="bi bi-arrow-left"></i> {{ 'album.offcanvas.links.direction.from'|trans }}</label>

        <input type="radio" class="btn-check" name="link-direction" id="link-direction-both" value="both" autocomplete="off">
        <label class="btn btn-outline-secondary btn-sm" for="link-direction-both"><i class="bi bi-arrow-left-right"></i> {{ 'album.offcanvas.links.direction.both'|trans }}</label>
      </div>

      <button type="button" class="btn btn-primary w-100 mb-3" id="create-link" disabled>
        <i class="bi bi-plus-lg"></i> {{ 'album.offcanvas.links.create'|trans }}
      </button>
    </div>
    {% endif %}

    <div>
      <h2>
        {{ 'album.offcanvas.informations.title'|trans }}
      </h2>
```

This matches the reference mockup's classes (`dex-link-row`, `dex-picker-grid`, `dex-pick-card`, `btn-group`/`btn-check` direction selector) exactly, adapted from static demo markup to the real Twig loop over `trainerDexList` and guarded by `allowedToEdit` like the "Paramètres" section right above it.

- [ ] **Step 5: Verify the template renders**

Run: `docker compose exec php php bin/console lint:twig templates/Album/_offcanvas.html.twig templates/Album/_album_macros.html.twig`
Expected: `[OK] All 2 Twig files contain valid syntax.`

- [ ] **Step 6: Run the existing Album controller/template tests**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/Album`
Expected: PASS. Two things to double-check first:
- `tests/src/Integration/Controller/Album/Display/OffcanvasTest.php` (the dedicated offcanvas test suite) only asserts specific IDs/classes inside `#offcanvas` (`.album-description`, `.album-private`, `.dex-type`, `form[data-dex="..."]`, etc.) — none of it counts total child elements, so the new "Liens" `<div>` inserted between the two existing ones doesn't break any existing assertion there.
- `AlbumIndexController::index()` now unconditionally calls `GetTrainerDexListService::get()`, which makes an HTTP call to pokenini-back's `GET /trainer/dex` for every album page load, not just when editing — check `tests/resources/moco/Back/moco.json` for a `"uri": "/trainer/dex"` entry with `"match": "Bearer .*"` as a catch-all (confirmed present at the time this plan was written): every trainer token `OffcanvasTest`/`CommonTest`/etc. use already resolves to *some* mocked response through that fallback, so no new Moco fixture is required for this task. If a future change to that catch-all removes it, add fixtures for the missing tokens instead of skipping this check.

- [ ] **Step 7: Commit**

```bash
git add src/Controller/AlbumIndexController.php templates/Album/_offcanvas.html.twig templates/Album/_album_macros.html.twig translations/messages+intl-icu.en.yaml translations/messages+intl-icu.fr.yaml
git commit -m "feat: add the Liens section to the album offcanvas"
```

---

### Task 13: `public/js/album-links.js`

**Files:**
- Create: `public/js/album-links.js`
- Modify: `templates/Album/index.html.twig` (include the script + expose `dexBannerUrl`/translated labels as JS globals, mirroring the existing `album-edit.js` wiring)

**Interfaces:**
- Consumes: `GET /album_link/{dexSlug}` → `[{id, direction, target_dex_slug, target_name, target_french_name}, ...]`, `POST /album_link/{dexSlug}` with `{targetDexSlug, bidirectional}`, `DELETE /album_link/{linkId}` (Task 11's routes), the DOM hooks Task 12 renders (`#album-links-section[data-dex-slug]`, `#active-links`, `.dex-pick-card[data-dex-slug]`, `input[name="link-direction"]`, `#create-link`, `#album-links-count`), the existing `locale` global (already set by `Album/index.html.twig` for `album-edit.js`) and the existing `#offcanvas` element (`Album/_offcanvas.html.twig`).

- [ ] **Step 1: Wire the script and its globals into `Album/index.html.twig`**

In `templates/Album/index.html.twig`, inside the existing `{% if allowedToEdit %}` block in `foot_javascript` (right after the `<script src="{{ asset('js/album-edit.js') }}"></script>` / `trainer_dex.js` lines and before the `const catchStates = ...` script), add:

```twig
    <script src="{{ asset('js/album-links.js') }}"></script>
```

And extend the existing globals `<script>` block (the one defining `catchStates`/`locale`/`dex`) with two more constants:

```twig
    <script>
    const catchStates = JSON.parse('{{ catchStates | map(cs => {name: cs.name, frenchName: cs.frenchName, slug: cs.slug, color: cs.color}) | json_encode | raw }}');
    const locale = '{{ locale }}';
    const dex = '{{ currentDexSlug }}';
    const dexBannerUrlTemplate = '{{ dexBannerUrl }}';
    const linksLabels = {
        directionTo: '{{ 'album.offcanvas.links.direction.to'|trans }}',
        directionFrom: '{{ 'album.offcanvas.links.direction.from'|trans }}',
        directionBoth: '{{ 'album.offcanvas.links.direction.both'|trans }}',
        deleteTitle: '{{ 'album.offcanvas.links.delete_title'|trans }}',
    };
    </script>
```

- [ ] **Step 2: Write `album-links.js`**

```js
function watchAlbumLinks() {
  const section = document.getElementById("album-links-section");

  if (!section) {
    return;
  }

  const dexSlug = section.dataset.dexSlug;
  let selectedTargetDexSlug = null;

  document
    .getElementById("offcanvas")
    .addEventListener("shown.bs.offcanvas", function () {
      loadLinks(dexSlug);
    });

  document.querySelectorAll(".dex-pick-card").forEach(function (card) {
    card.addEventListener("click", function () {
      document.querySelectorAll(".dex-pick-card").forEach(function (c) {
        c.classList.remove("selected");
      });
      card.classList.add("selected");
      selectedTargetDexSlug = card.dataset.dexSlug;
      document.getElementById("create-link").disabled = false;
    });
  });

  document
    .getElementById("create-link")
    .addEventListener("click", function () {
      createLink(dexSlug, selectedTargetDexSlug);
    });
}

function createLink(dexSlug, selectedTargetDexSlug) {
  if (!selectedTargetDexSlug) {
    return;
  }

  const direction = document.querySelector(
    'input[name="link-direction"]:checked'
  ).value;

  const request = new Request("/" + locale + "/album_link/" + dexSlug, {
    method: "POST",
    body: JSON.stringify({
      targetDexSlug: selectedTargetDexSlug,
      bidirectional: direction === "both",
    }),
  });

  fetch(request)
    .then(function (response) {
      if (response.status !== 201) {
        throw new Error("Something went wrong on api server!");
      }

      loadLinks(dexSlug);
      new bootstrap.Toast(document.getElementById("linksToastSuccess")).show();
    })
    .catch(function (error) {
      console.error(error);
      new bootstrap.Toast(document.getElementById("linksToastError")).show();
    });
}

function loadLinks(dexSlug) {
  fetch("/" + locale + "/album_link/" + dexSlug)
    .then(function (response) {
      if (response.status !== 200) {
        throw new Error("Something went wrong on api server!");
      }

      return response.json();
    })
    .then(function (links) {
      renderLinks(dexSlug, links);
      filterPickerGrid(links);
    })
    .catch(function (error) {
      console.error(error);
      new bootstrap.Toast(document.getElementById("linksToastError")).show();
    });
}

function renderLinks(dexSlug, links) {
  const container = document.getElementById("active-links");
  container.innerHTML = "";

  const badge = document.getElementById("album-links-count");
  badge.textContent = links.length;
  badge.hidden = links.length === 0;

  const directionIcons = {
    to: "bi-arrow-right",
    from: "bi-arrow-left",
    both: "bi-arrow-left-right",
  };
  const directionLabels = {
    to: linksLabels.directionTo,
    from: linksLabels.directionFrom,
    both: linksLabels.directionBoth,
  };

  links.forEach(function (link) {
    container.appendChild(
      buildLinkRow(dexSlug, link, directionIcons, directionLabels)
    );
  });
}

function buildLinkRow(dexSlug, link, directionIcons, directionLabels) {
  const row = document.createElement("div");
  row.className = "list-group-item d-flex align-items-center gap-2 dex-link-row";

  const img = document.createElement("img");
  img.src = dexBannerUrlTemplate.replace("%s", link.target_dex_slug);
  img.alt = "";
  row.appendChild(img);

  const info = document.createElement("div");
  info.className = "flex-fill";

  const name = document.createElement("div");
  name.className = "fw-bold small";
  name.textContent = locale === "fr" ? link.target_french_name : link.target_name;
  info.appendChild(name);

  const direction = document.createElement("div");
  direction.className = "form-text";
  direction.innerHTML = '<i class="bi ' + directionIcons[link.direction] + '"></i> ';
  direction.append(directionLabels[link.direction]);
  info.appendChild(direction);

  row.appendChild(info);

  const deleteButton = document.createElement("button");
  deleteButton.type = "button";
  deleteButton.className = "btn btn-sm btn-outline-danger";
  deleteButton.title = linksLabels.deleteTitle;
  deleteButton.innerHTML = '<i class="bi bi-trash"></i>';
  deleteButton.addEventListener("click", function () {
    deleteLink(dexSlug, link.id);
  });
  row.appendChild(deleteButton);

  return row;
}

function filterPickerGrid(links) {
  const linkedSlugs = links.map(function (link) {
    return link.target_dex_slug;
  });

  document.querySelectorAll(".dex-pick-card").forEach(function (card) {
    card.hidden = linkedSlugs.indexOf(card.dataset.dexSlug) !== -1;
  });
}

function deleteLink(dexSlug, linkId) {
  const request = new Request("/" + locale + "/album_link/" + linkId, {
    method: "DELETE",
  });

  fetch(request)
    .then(function (response) {
      if (response.status !== 200) {
        throw new Error("Something went wrong on api server!");
      }

      loadLinks(dexSlug);
      new bootstrap.Toast(document.getElementById("linksToastSuccess")).show();
    })
    .catch(function (error) {
      console.error(error);
      new bootstrap.Toast(document.getElementById("linksToastError")).show();
    });
}
```

This mirrors `album-edit.js`'s conventions exactly: no module system, plain top-level `function` declarations invoked from an IIFE, `fetch()` + `.then()`/`.catch()` chains, `bootstrap.Toast(...).show()` for feedback, and a `watchXxx()` naming/registration pattern.

- [ ] **Step 3: Register `watchAlbumLinks()` alongside the other `watchXxx()` calls**

In `templates/Album/index.html.twig`, the existing IIFE inside `{% if allowedToEdit %}` currently reads:

```twig
    <script>
    (function() {
        watchToggleEditMode();
        watchCatchStates();
        watchToggleShinyMode();
        watchToAdjustSelectSizes();
        watchAttributes();
    })();
    </script>
```

Add `watchAlbumLinks();` to that list:

```twig
    <script>
    (function() {
        watchToggleEditMode();
        watchCatchStates();
        watchToggleShinyMode();
        watchToAdjustSelectSizes();
        watchAttributes();
        watchAlbumLinks();
    })();
    </script>
```

- [ ] **Step 4: Manual verification**

There is no JS unit-test harness in this repo (confirm with `find /home/renaud/projects/pokenini-web -iname "*.test.js" -o -iname "jest.config*"` — none expected). Verify by hand once the stack is running end-to-end (api + back + web): open the album offcanvas, confirm the picker grid shows every other dex banner, pick one + a direction, click "Créer le lien", confirm it appears in the active list and the picker card disappears; delete it and confirm the picker card reappears. Note the observed result when reporting this task's completion — this step has no automated pass/fail.

- [ ] **Step 5: Commit**

```bash
git add public/js/album-links.js templates/Album/index.html.twig
git commit -m "feat: add album-links.js to drive the Liens section"
```

---

### Task 14: Run the full pokenini-web suite

**Files:** none (verification-only task).

- [ ] **Step 1: Run unit and integration tests**

Run: `cd /home/renaud/projects/pokenini-web && make tests-unit && make tests-integration`
Expected: all green — no existing Moco fixture under `tests/resources/moco/Back/` needs touching for this feature (nothing in Tasks 11–13 changes the shape of any *existing* back endpoint's response; `/album_link/*` is new and only exercised by the new unit tests, which mock the HTTP client directly rather than going through Moco, matching how `AlbumUpsertControllerTest`/`GetTrainerDexListServiceTest` are already written).

- [ ] **Step 2: Run coverage and mutation testing**

Run: `make measures` (coverage + infection)
Expected: 100% coverage, 100% MSI on every new PHP file. `album-links.js` itself has no coverage/MSI gate in this repo (no JS test harness exists — see Task 13 Step 4) and is exercised only by the manual check.

- [ ] **Step 3: Run the remaining quality gates**

Run: `make quality`
Expected: all green (Twig lint, PHPStan, Psalm, Deptrac, PHP CS Fixer, PHPMD, `w3c` HTML validation on the rendered offcanvas markup — double-check the new `dex-picker-grid`/`dex-pick-card`/`btn-group` markup doesn't trip the W3C validator, e.g. every `<label for="...">` matching an `id`, which the markup in Task 12 already satisfies).

- [ ] **Step 4: Browser (Panther) end-to-end check — explicitly out of scope for automated CI here**

The design spec calls for a Panther scenario spanning all three live services (create a link, change a catch state on dex A, reload dex B, confirm the cascaded value) — but pokenini-web's own Panther suite only drives pokenini-web against Moco-mocked pokenini-back responses; it cannot exercise a real pokenini-api cascade because Moco fixtures are static. Matching how the reference plan (`docs/superpowers/plans/2026-07-09-album-dex-list-report.md`, its own Task 8 Step 3) handled its equivalent gap, this is a **manual verification step**, not an automated one: with all three stacks running (`pokenini-api`, `pokenini-back`, `pokenini-web`, via `make start` in each), log in via the fake authenticator, create a bidirectional link between two dexes in the offcanvas, flip a catch state on one, and confirm the other reflects it after a reload. Note the observed result when reporting this task's completion — do not invent an automated Panther test for this.

- [ ] **Step 5: Commit** (only if fixes were required)

```bash
git add -A
git commit -m "fix: address quality/coverage/mutation feedback for the Liens section"
```

---
