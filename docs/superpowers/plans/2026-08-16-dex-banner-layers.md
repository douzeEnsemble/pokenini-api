# Dex Banner Layers (pokenini-api) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Persist a per-dex `banner_layers` list synced from a new Google Sheet column, and expose it read-only via a new endpoint protected by its own narrowly-scoped credential, for `pokenini-icon`'s banner generator to consume.

**Architecture:** `dex.banner_layers` is a nullable `TEXT` column (Doctrine `Types::SIMPLE_ARRAY` — comma-joined string on disk, hydrates to `string[]|null` through the ORM), written by `DexUpdater`'s existing raw-SQL upsert directly from the sheet's already-comma-separated cell value with zero transformation. A new `icon` HTTP Basic user, restricted by `access_control` to exactly one path, serves `GET /istration/dex-banner-layers`, which returns `{slug: [layers...]}` for every non-deleted dex row with a non-null `banner_layers` (rows without layers are omitted, never emitted as `[]`).

**Tech Stack:** Symfony 8 / PHP ≥ 8.5, Doctrine ORM + DBAL, PostgreSQL, `google/apiclient`, PHPUnit, Moco.

**Spec:** `../pokenini-icon/docs/superpowers/specs/2026-08-16-dex-sheet-banner-layers-design.md` (committed in the sibling `pokenini-icon` repo — read it for full cross-repo context; this plan implements only the `pokenini-api` half). Paired plan: `pokenini-icon`'s `docs/superpowers/plans/2026-08-16-dex-banner-layers-generation.md`.

## Global Constraints

- `declare(strict_types=1)` in every PHP file. Every test class: `/** @internal */` + `#[CoversClass(TargetClass::class)]`.
- 100% coverage, 100% MSI, PHPStan level 9, Psalm strict, Deptrac — no exceptions.
- Endpoint path: `GET /istration/dex-banner-layers` (exact, matches `ImagePipelineRunController`/`BannerPipelineRunController`'s `/istration/*` convention).
- New auth user: username `icon`, role `ROLE_ICON`, env var `ICON_API_PASSWORD`. The endpoint accepts `ROLE_ICON` **or** `ROLE_API` — the existing shared `web`/`ROLE_API` credential must keep working everywhere it already does.
- Response shape: JSON object `{ "<slug>": ["layer1", "layer2"], ... }` — dex rows with no `banner_layers` are omitted entirely, never present as `[]`.
- Sheet column name: `Banner Layers` (distinct from the pre-existing, still-unused `Banner` column — do not touch `Banner`). Cell format: comma-separated layer names (e.g. `shiny,mega`), empty cell → `NULL` in the database, not an empty string.

---

### Task 1: `dex.banner_layers` column

**Files:**
- Modify: `src/Entity/Dex.php`
- Create: `migrations/2026/08/Version<YYYYMMDDHHMMSS>.php` (exact filename/class name generated in Step 1 below — do not hand-pick a timestamp)

**Interfaces:**
- Produces: `Dex::$bannerLayers` (`?array`, ORM-hydrated `string[]|null`) — consumed by nothing yet in this task; Task 2 writes to the underlying column via raw SQL (bypassing the entity), Task 4 reads it back via raw SQL too. This task only needs the column to exist and be ORM-mapped so `doctrine:migration:diff` and future ORM-based reads work.

- [ ] **Step 1: Add the entity property**

In `src/Entity/Dex.php`, add `use Doctrine\DBAL\Types\Types;` is already imported (used by `lastChangedAt`). Add this property after `electionOrderNumber`:

```php
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    public ?array $bannerLayers = null;
```

- [ ] **Step 2: Generate the migration**

Run: `make sf c="doctrine:migration:diff --no-interaction"`
Expected: a new file `migrations/2026/08/Version<timestamp>.php` is created. Open it and confirm it contains exactly one `addSql` call in `up()` of the form `ALTER TABLE dex ADD banner_layers TEXT DEFAULT NULL` (Doctrine's `SIMPLE_ARRAY` type maps to a plain `TEXT` column, not a native Postgres array — this is intentional, see Architecture above) and the matching `ALTER TABLE dex DROP banner_layers` in `down()`. If the generated diff contains anything else (e.g. unrelated schema drift), stop and report it — do not silently include unrelated changes in this migration.

- [ ] **Step 3: Run the migration**

Run: `make sf c="doctrine:migration:migrate --no-interaction"`
Expected: migration applies cleanly, no errors.

- [ ] **Step 4: Verify via a quick manual check**

Run: `make sf c="dbal:run-sql \"SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'dex' AND column_name = 'banner_layers'\""`
Expected: one row, `data_type` = `text`, `is_nullable` = `YES`.

- [ ] **Step 5: Commit**

```bash
git add src/Entity/Dex.php migrations/
git commit -m "feat: add nullable dex.banner_layers column"
```

---

### Task 2: `DexUpdater` persists `Banner Layers` from the sheet

**Files:**
- Modify: `src/Updater/DexUpdater.php`
- Modify: `tests/src/Unit/Updater/DexUpdaterTest.php`
- Modify: `tests/src/Integration/Updater/DexUpdaterTest.php`
- Modify: `tests/resources/moco/Sheets/test.moco.json`
- Modify: `tests/resources/moco/Sheets/int.moco.json`
- Rename + modify: `tests/resources/moco/Sheets/test/values_%27Dex%27%21A1%3AP1.json` → `...A1%3AQ1.json`
- Rename + modify: `tests/resources/moco/Sheets/test/values_%27Dex%27%21A2%3AP.json` → `...A2%3AQ.json`

**Interfaces:**
- Consumes: `Dex::$bannerLayers` column exists (Task 1).
- Produces: `dex.banner_layers` populated on every sync — consumed by Task 4's repository query.

- [ ] **Step 1: Update the Moco Dex-sheet fixtures**

Both `test.moco.json` and `int.moco.json` route the `'Dex'!A1:P1` and `'Dex'!A2:P` request URIs to files under `tests/resources/moco/Sheets/test/` (both environments share the same underlying files — confirmed by reading both route files directly). Rename the two response files and widen their ranges from column P to column Q (17 columns instead of 16) to make room for the new column:

```bash
git mv "tests/resources/moco/Sheets/test/values_%27Dex%27%21A1%3AP1.json" "tests/resources/moco/Sheets/test/values_%27Dex%27%21A1%3AQ1.json"
git mv "tests/resources/moco/Sheets/test/values_%27Dex%27%21A2%3AP.json" "tests/resources/moco/Sheets/test/values_%27Dex%27%21A2%3AQ.json"
```

Edit the renamed header file (`values_%27Dex%27%21A1%3AQ1.json`): change `"range": "Dex!A1:P1"` to `"range": "Dex!A1:Q1"`, and append `"Banner Layers"` as a new 17th element to the `values[0]` array (after `"Can Hold Election"`).

Edit the renamed data file's `"range"` field: change `"Dex!A2:P22"` to `"Dex!A2:Q22"`. Then run this script to append the 17th value to each of the 21 data rows (empty string for every row except `xy`, which gets `"xy,shiny"` so at least one row exercises real parsing):

```bash
python3 - <<'PYEOF'
import json

path = "tests/resources/moco/Sheets/test/values_%27Dex%27%21A2%3AQ.json"
with open(path, encoding="utf-8") as f:
    data = json.load(f)

for row in data["values"]:
    slug = row[1]
    row.append("xy,shiny" if slug == "xy" else "")

with open(path, "w", encoding="utf-8") as f:
    json.dump(data, f, ensure_ascii=True, indent=2)
    f.write("\n")
PYEOF
```

Run: `python3 -c "import json; d = json.load(open('tests/resources/moco/Sheets/test/values_%27Dex%27%21A2%3AQ.json')); print(len(d['values'])); print([len(r) for r in d['values']])"`
Expected: `21` then a list of twenty-one `17`s — confirms every row now has 17 columns and none were accidentally dropped or duplicated.

Now update both route files. In `tests/resources/moco/Sheets/test.moco.json` and `tests/resources/moco/Sheets/int.moco.json`, find the two `'Dex'` route entries and update both the request `uri` and response `file` fields:

```bash
for routeFile in tests/resources/moco/Sheets/test.moco.json tests/resources/moco/Sheets/int.moco.json; do
  sed -i \
    -e "s#values/'Dex'!A1:P1#values/'Dex'!A1:Q1#" \
    -e "s#values/'Dex'!A2:P#values/'Dex'!A2:Q#" \
    -e "s#values_%27Dex%27%21A1%3AP1.json#values_%27Dex%27%21A1%3AQ1.json#" \
    -e "s#values_%27Dex%27%21A2%3AP.json#values_%27Dex%27%21A2%3AQ.json#" \
    "$routeFile"
done
```

Run: `python3 -c "import json; json.load(open('tests/resources/moco/Sheets/test.moco.json')); json.load(open('tests/resources/moco/Sheets/int.moco.json')); print('valid JSON')"`
Expected: `valid JSON` (confirms the `sed` edits didn't corrupt either file).

- [ ] **Step 2: Write the failing unit test**

In `tests/src/Unit/Updater/DexUpdaterTest.php`, add `'Banner Layers',` as a 17th entry to `getHeader()`'s return array (after `'Banner',`), and `'shiny,mega',` as a 17th entry to `getRecord()`'s return array (after the last `''`).

- [ ] **Step 3: Run the unit test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Updater/DexUpdaterTest.php`
Expected: FAIL — `array_combine()`/header-validation error, since `getExpectedHeader()` in the production code doesn't yet include `'Banner Layers'` and the arrays are now mismatched lengths.

- [ ] **Step 4: Update `DexUpdater`**

In `src/Updater/DexUpdater.php`, change:

```php
    protected string $headerCellsRange = 'A1:P1';

    /** @var array<int, string> */
    protected array $recordsCellsRanges = ['A2:P'];
```

to:

```php
    protected string $headerCellsRange = 'A1:Q1';

    /** @var array<int, string> */
    protected array $recordsCellsRanges = ['A2:Q'];
```

Add `'Banner Layers',` to `getExpectedHeader()`'s return array (after `'Banner',`):

```php
    #[\Override]
    protected function getExpectedHeader(): array
    {
        return [
            'Slug',
            'Name',
            'French Name',
            'Order',
            'Election Order',
            'Selection rule',
            'Is Shiny',
            'Is Premium',
            'Is Display Form',
            'Can Hold Election',
            'Is released',
            'Display template',
            '#Region',
            'French description',
            'Description',
            'Banner',
            'Banner Layers',
        ];
    }
```

In `upsertRecord()`, add the parsing right before `$sqlParameters` is built:

```php
        $bannerLayers = '' === $record['Banner Layers'] ? null : $record['Banner Layers'];

        $sqlParameters = [
```

Add `'banner_layers' => $bannerLayers,` to the `$sqlParameters` array (any position — matches by name, not order).

Add `banner_layers` to the `INSERT INTO` column list and `:banner_layers` to the `VALUES` list (both right after `can_hold_election`/`:can_hold_election`), and add `banner_layers = excluded.banner_layers,` to the `ON CONFLICT ... DO UPDATE SET` list (right after `can_hold_election = excluded.can_hold_election,`).

- [ ] **Step 5: Run the unit test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Updater/DexUpdaterTest.php`
Expected: PASS (2/2 tests).

- [ ] **Step 6: Write the failing integration test**

In `tests/src/Integration/Updater/DexUpdaterTest.php`, add (after the `dexIsReleased` test method):

```php
    #[Test]
    public function dexBannerLayers(): void
    {
        $xyBefore = $this->getDexFromSlug('xy');
        $redGreenBlueYellowBefore = $this->getDexFromSlug('redgreenblueyellow');

        $this->assertNull($xyBefore['banner_layers']);
        $this->assertNull($redGreenBlueYellowBefore['banner_layers']);

        $this->getService()->execute('Dex');

        $xyAfter = $this->getDexFromSlug('xy');
        $redGreenBlueYellowAfter = $this->getDexFromSlug('redgreenblueyellow');

        $this->assertSame('xy,shiny', $xyAfter['banner_layers']);
        $this->assertNull($redGreenBlueYellowAfter['banner_layers']);
    }
```

(`getDexFromSlug()` comes from `GetDexTrait`, already `use`d by this class; it runs `SELECT d.*` via raw DBAL, so the new column shows up automatically as the raw stored string, not an ORM-hydrated array — asserting the literal `'xy,shiny'` string is correct here, not `['xy', 'shiny']`.)

- [ ] **Step 7: Run the integration test to verify it fails, then passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Updater/DexUpdaterTest.php`
Expected first (before Step 1's fixture edits would make this meaningful — but Steps 1-6 are already done by this point in the task, so): PASS directly, 3/3 tests (`dexRegion`, `dexIsReleased`, `dexBannerLayers`). If it fails, check: did the Moco fixture edits from Step 1 actually widen both files to column Q and route both `test.moco.json`/`int.moco.json` correctly? Re-run the Step 1 verification commands if unsure.

- [ ] **Step 8: Commit**

```bash
git add src/Updater/DexUpdater.php tests/src/Unit/Updater/DexUpdaterTest.php tests/src/Integration/Updater/DexUpdaterTest.php tests/resources/moco/Sheets/
git commit -m "feat: persist Banner Layers column from the Dex sheet"
```

---

### Task 3: scoped `icon` auth credential

**Files:**
- Modify: `config/packages/security.yaml`
- Modify: `.env.dev`, `.env.test`, `.env.int`, `.env.ci`, `.env.prod`
- Modify: `tests/src/Integration/Controller/AbstractTestControllerApi.php`

**Interfaces:**
- Produces: a second HTTP Basic user `icon` / role `ROLE_ICON`, restricted via `access_control` to `^/istration/dex-banner-layers$` — consumed by Task 4's controller test and by `pokenini-icon`'s CI (external, not part of this repo).

- [ ] **Step 1: Add the `icon` user and scoped access rule**

In `config/packages/security.yaml`, change:

```yaml
  providers:
    users:
      memory:
        users:
          web: { password: '%env(WEB_PASSWORD)%', roles: ["ROLE_API"] }
```

to:

```yaml
  providers:
    users:
      memory:
        users:
          web: { password: '%env(WEB_PASSWORD)%', roles: ["ROLE_API"] }
          icon: { password: '%env(ICON_API_PASSWORD)%', roles: ["ROLE_ICON"] }
```

And change:

```yaml
  access_control:
    - { path: ^/, roles: ROLE_API }
```

to (the new, more specific rule MUST come first — Symfony's `access_control` matches top-to-bottom, first match wins, so putting it after the catch-all would make it dead code):

```yaml
  access_control:
    - { path: ^/istration/dex-banner-layers$, roles: [ROLE_ICON, ROLE_API] }
    - { path: ^/, roles: ROLE_API }
```

- [ ] **Step 2: Add `ICON_API_PASSWORD` to every env file**

In `.env.dev`, `.env.test`, `.env.int`, `.env.ci`, add this line immediately after the existing `WEB_PASSWORD=...` line (same bcrypt hash — this repo already reuses one hash across all four non-prod files for `WEB_PASSWORD`, and the plaintext `douze` is a public test constant in `AbstractTestControllerApi::AUTH_PASSWORD`, so reusing it for `icon` in test-only environments introduces no new secret):

```
ICON_API_PASSWORD='$2y$13$mRUuGx9.c5O8asSS7NrpOOjeNXbaUN0T113lPaBPbP4oLsSTvRM5u'
```

In `.env.prod`, add the same line but with a placeholder that must be rotated to a real value before this reaches production (flag this explicitly in the PR description — same pattern as any other prod secret in this repo):

```
ICON_API_PASSWORD='!ChangeMe!'
```

Check `.env.prod`'s existing `WEB_PASSWORD` line for its exact placeholder convention (e.g. it may already use `!ChangeMe!` or a different bcrypt-hash-of-placeholder convention) and match it exactly rather than assuming — read the file first.

- [ ] **Step 3: Add the `icon` credential constant to the shared test helper**

In `tests/src/Integration/Controller/AbstractTestControllerApi.php`, add alongside the existing constants:

```php
    public const string ICON_AUTH_USER = 'icon';
    public const string ICON_AUTH_PASSWORD = 'douze';
```

- [ ] **Step 4: Run a smoke check**

Run: `make sf c="cache:clear"`
Expected: no errors (confirms `security.yaml`'s YAML is well-formed and the new env var resolves — `ICON_API_PASSWORD` must be set in `.env.dev` for this to succeed, per Step 2).

- [ ] **Step 5: Commit**

```bash
git add config/packages/security.yaml .env.dev .env.test .env.int .env.ci .env.prod tests/src/Integration/Controller/AbstractTestControllerApi.php
git commit -m "feat: add scoped icon/ROLE_ICON credential for the new dex-banner-layers endpoint"
```

---

### Task 4: `GET /istration/dex-banner-layers` endpoint

**Files:**
- Modify: `src/Repository/DexRepository.php`
- Modify: `tests/src/Integration/Repository/DexRepositoryTest.php`
- Create: `src/Service/DexBannerLayersService.php`
- Create: `tests/src/Unit/Service/DexBannerLayersServiceTest.php`
- Create: `src/Controller/DexBannerLayersController.php`
- Create: `tests/src/Integration/Controller/DexBannerLayersControllerTest.php`
- Modify: `fixtures/dexes.yaml`

**Interfaces:**
- Consumes: `dex.banner_layers` column (Task 1, 2), `icon`/`ROLE_ICON` credential (Task 3).
- Produces: `DexRepository::getBannerLayers(): array<array-key, array{slug: string, banner_layers: string}>` — consumed by `DexBannerLayersService::getAll(): array<string, string[]>` — consumed by `DexBannerLayersController::get()`, serialized directly as the JSON response body.

- [ ] **Step 1: Add a `banner_layers`-carrying fixture row**

In `fixtures/dexes.yaml`, add `bannerLayers: ["shiny", "mega"]` to `dex_redgreenblueyellow` (any position among its other keys):

```yaml
  dex_redgreenblueyellow:
    name: "Red / Green / Blue / Yellow"
    slug: "redgreenblueyellow"
    frenchName: "Rouge / Vert / Bleu / Jaune"
    selectionRule: "(p.bankable or p.bankableish) and ba?.redgreenblueyellow"
    displayTemplate: "box"
    orderNumber: 1
    electionOrderNumber: 99
    region: "@region_kanto"
    description: "The list of obtainable Pokémons in Red, Blue, Yellow and even Green games"
    frenchDescription: "La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert."
    lastChangedAt: '<(new DateTime("2023-02-21 08:51:00"))>'
    isPremium: true
    canHoldElection: true
    isReleased: true
    bannerLayers: ["shiny", "mega"]
```

Every other existing dex fixture entry keeps `bannerLayers` unset (defaults to `null` per the entity property default) — this gives the tests below exactly one dex with layers and several without, to prove the filtering behavior.

- [ ] **Step 2: Write the failing repository test**

In `tests/src/Integration/Repository/DexRepositoryTest.php`, add (after the `getData` test method):

```php
    #[Test]
    public function getBannerLayers(): void
    {
        $repo = self::getContainer()->get(DexRepository::class);

        $result = $repo->getBannerLayers();

        $bySlug = [];
        foreach ($result as $row) {
            $bySlug[$row['slug']] = $row['banner_layers'];
        }

        $this->assertArrayHasKey('redgreenblueyellow', $bySlug);
        $this->assertSame('shiny,mega', $bySlug['redgreenblueyellow']);
        $this->assertArrayNotHasKey('goldsilvercrystal', $bySlug);
        $this->assertArrayNotHasKey('home', $bySlug);
    }
```

- [ ] **Step 3: Run the repository test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit --filter getBannerLayers tests/src/Integration/Repository/DexRepositoryTest.php`
Expected: FAIL — `Call to undefined method App\Repository\DexRepository::getBannerLayers()`.

- [ ] **Step 4: Implement `DexRepository::getBannerLayers()`**

In `src/Repository/DexRepository.php`, add (anywhere after `getQueryAll()`):

```php
    /**
     * @return array<array-key, array{slug: string, banner_layers: string}>
     */
    public function getBannerLayers(): array
    {
        $sql = <<<'SQL'
            SELECT      d.slug AS "slug",
                        d.banner_layers AS "banner_layers"
            FROM        dex AS d
            WHERE       d.deleted_at IS NULL
                    AND d.banner_layers IS NOT NULL
            ORDER BY    d.slug ASC
            SQL;

        /** @var array<array-key, array{slug: string, banner_layers: string}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
```

- [ ] **Step 5: Run the repository test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit --filter getBannerLayers tests/src/Integration/Repository/DexRepositoryTest.php`
Expected: PASS.

- [ ] **Step 6: Write the failing service unit test**

Create `tests/src/Unit/Service/DexBannerLayersServiceTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\DexRepository;
use App\Service\DexBannerLayersService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexBannerLayersService::class)]
final class DexBannerLayersServiceTest extends TestCase
{
    #[Test]
    public function getAll(): void
    {
        $repository = $this->createMock(DexRepository::class);
        $repository
            ->expects($this->once())
            ->method('getBannerLayers')
            ->willReturn([
                ['slug' => 'redgreenblueyellow', 'banner_layers' => 'shiny,mega'],
                ['slug' => 'xy', 'banner_layers' => 'xy'],
            ])
        ;

        $service = new DexBannerLayersService($repository);

        $this->assertSame(
            [
                'redgreenblueyellow' => ['shiny', 'mega'],
                'xy' => ['xy'],
            ],
            $service->getAll()
        );
    }

    #[Test]
    public function getAllWithNoBannerLayers(): void
    {
        $repository = $this->createMock(DexRepository::class);
        $repository
            ->expects($this->once())
            ->method('getBannerLayers')
            ->willReturn([])
        ;

        $service = new DexBannerLayersService($repository);

        $this->assertSame([], $service->getAll());
    }
}
```

- [ ] **Step 7: Run the service test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/DexBannerLayersServiceTest.php`
Expected: FAIL — `Class "App\Service\DexBannerLayersService" not found`.

- [ ] **Step 8: Implement `DexBannerLayersService`**

Create `src/Service/DexBannerLayersService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\DexRepository;

class DexBannerLayersService
{
    public function __construct(
        private readonly DexRepository $repository,
    ) {}

    /**
     * @return array<string, string[]>
     */
    public function getAll(): array
    {
        $result = [];

        foreach ($this->repository->getBannerLayers() as $row) {
            $result[$row['slug']] = explode(',', $row['banner_layers']);
        }

        return $result;
    }
}
```

- [ ] **Step 9: Run the service test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Unit/Service/DexBannerLayersServiceTest.php`
Expected: PASS (2/2 tests).

- [ ] **Step 10: Write the failing controller integration test**

Create `tests/src/Integration/Controller/DexBannerLayersControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexBannerLayersController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(DexBannerLayersController::class)]
final class DexBannerLayersControllerTest extends AbstractTestControllerApi
{
    #[Test]
    public function getWithIconCredentials(): void
    {
        $this->apiRequest(
            'GET',
            '/istration/dex-banner-layers',
            [],
            ['PHP_AUTH_USER' => self::ICON_AUTH_USER, 'PHP_AUTH_PW' => self::ICON_AUTH_PASSWORD]
        );

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertArrayHasKey('redgreenblueyellow', $content);
        $this->assertSame(['shiny', 'mega'], $content['redgreenblueyellow']);
        $this->assertArrayNotHasKey('goldsilvercrystal', $content);
        $this->assertArrayNotHasKey('home', $content);
    }

    #[Test]
    public function getWithWebCredentialsAlsoWorks(): void
    {
        $this->apiRequest('GET', '/istration/dex-banner-layers');

        $this->assertJsonResponseIsOK();
    }

    #[Test]
    public function getWithoutCredentialsIsRejected(): void
    {
        $this->apiRequest('GET', '/istration/dex-banner-layers', [], []);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
```

- [ ] **Step 11: Run the controller test to verify it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/DexBannerLayersControllerTest.php`
Expected: FAIL — 404 (route doesn't exist yet).

- [ ] **Step 12: Implement `DexBannerLayersController`**

Create `src/Controller/DexBannerLayersController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DexBannerLayersService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/istration/dex-banner-layers')]
final class DexBannerLayersController extends AbstractController
{
    /**
     * @return array<string, string[]>
     */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(DexBannerLayersService $service): array
    {
        return $service->getAll();
    }
}
```

- [ ] **Step 13: Run the controller test to verify it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/DexBannerLayersControllerTest.php`
Expected: PASS (3/3 tests).

- [ ] **Step 14: Commit**

```bash
git add src/Repository/DexRepository.php src/Service/DexBannerLayersService.php src/Controller/DexBannerLayersController.php tests/src/Integration/Repository/DexRepositoryTest.php tests/src/Unit/Service/DexBannerLayersServiceTest.php tests/src/Integration/Controller/DexBannerLayersControllerTest.php fixtures/dexes.yaml
git commit -m "feat: add GET /istration/dex-banner-layers endpoint"
```

---

### Task 5: Quality gate

**Files:** none new — verification only.

- [ ] **Step 1: Run the full test suite**

Run: `make tests`
Expected: all pass, including every existing `Dex*`/`Type*`-pattern suite untouched by this plan.

- [ ] **Step 2: Run coverage and mutation testing**

Run: `make measures`
Expected: 100% coverage, 100% MSI.

- [ ] **Step 3: Run quality checks**

Run: `make code-quality`
Expected: cs-fixer, phpmd, psalm, phpstan, deptrac, jsonlint all pass with no new baseline entries. Pay attention to Deptrac: `DexBannerLayersController` (Controller layer) → `DexBannerLayersService` (Service layer) → `DexRepository` (Repository layer) must respect the existing `Controller → Service → Calculator → Repository` direction — it does, but confirm rather than assume.

- [ ] **Step 4: Fix any formatting issues found**

```bash
make phpcsfixer-fix
git add -A
git commit -m "style: apply cs-fixer" --allow-empty
```

(Skip the commit if `phpcsfixer-fix` made no changes.)

## Self-Review Notes

- **Spec coverage:** migration/entity ✓ (Task 1), `DexUpdater` persistence + Moco fixtures ✓ (Task 2), scoped `icon` credential ✓ (Task 3), new endpoint (Repository/Service/Controller) ✓ (Task 4). The spec's "existing `Banner` column stays untouched" constraint is respected — no task modifies it.
- **Placeholder scan:** none found. The one spot flagged as needing a live look rather than a hard-coded value is Task 3 Step 2's `.env.prod` placeholder convention — the step explicitly says to read the file first rather than guessing, which is a real instruction, not a TBD.
- **Type/interface consistency:** `DexRepository::getBannerLayers()`'s return shape (`array{slug: string, banner_layers: string}`), `DexBannerLayersService::getAll()`'s return shape (`array<string, string[]>`), and the controller's return type all match across Tasks 4's steps and its own docblocks. `ICON_AUTH_USER`/`ICON_AUTH_PASSWORD` (Task 3) are used with the exact same names in Task 4's controller test.
