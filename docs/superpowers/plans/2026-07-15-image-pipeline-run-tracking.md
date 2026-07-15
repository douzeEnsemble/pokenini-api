# Image Pipeline Run Tracking Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Persist the status of the `update_images` pipeline (Workflow A run, icon PR, Workflow B run, resources PR) so it survives `pokenini-back` restarts/cache eviction, via a new entity `pokenini-back` creates/updates/reads over HTTP.

**Architecture:** A new Doctrine entity `ImagePipelineRun`, deliberately separate from the existing `ActionLog` system (which models one create→done Messenger job, not a multi-stage external process — see the design spec). Plain synchronous Controller → Service → Repository, no Messenger. Follows this repo's Controller→Factory→Response-DTO convention for the read endpoint.

**Tech Stack:** Symfony 8.0 / PHP ≥ 8.5, Doctrine ORM, PostgreSQL.

## Global Constraints

- `declare(strict_types=1)` in every new file.
- `final`: Controller, DTO, Exception. Non-`final`: Service, Repository (so PHPUnit can mock them).
- Entity properties are public, no getters/setters, per this repo's existing convention (`Type`, `CatchState`) — only `BaseEntityTrait::getIdentifier()` is a real method.
- Read endpoint must go through a `Factory` → `final readonly`-style Response DTO with constructor promotion, decorated `#[Serialize]` on the controller method — plain `json_encode`/manual `JsonResponse` is not this repo's convention for reads (see `CatchStatesController`/`CatchStateResponseFactory`/`CatchStateResponse`).
- Every route sits under the existing global `ROLE_API` HTTP Basic firewall — no extra auth config needed.
- 100% line coverage and 100% Mutation Score Index required (`make measures`); `make quality` must be clean.
- Companion spec: `../pokenini-web/docs/superpowers/specs/2026-07-15-image-pipeline-status-tracking-design.md`.

---

### Task 1: `ImagePipelineRun` entity and migration

**Files:**
- Create: `src/Entity/ImagePipelineRun.php`
- Create: migration under `migrations/2026/07/` (generated, not hand-written)

**Interfaces:**
- Produces: the `ImagePipelineRun` entity with public properties `correlationId` (string, unique), `createdAt`/`updatedAt` (`\DateTime`), and 14 nullable stage fields, consumed by the Repository in Task 2.

- [ ] **Step 1: Write the entity**

Create `src/Entity/ImagePipelineRun.php`:

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ImagePipelineRun
{
    use BaseEntityTrait;

    #[ORM\Column(unique: true)]
    public string $correlationId;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public \DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public \DateTime $updatedAt;

    #[ORM\Column(nullable: true)]
    public ?int $workflowARunId = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowAStatus = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowAConclusion = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowAUrl = null;

    #[ORM\Column(nullable: true)]
    public ?int $iconPrNumber = null;

    #[ORM\Column(nullable: true)]
    public ?string $iconPrUrl = null;

    #[ORM\Column(nullable: true)]
    public ?string $iconPrState = null;

    #[ORM\Column(nullable: true)]
    public ?string $iconPrMergeCommitSha = null;

    #[ORM\Column(nullable: true)]
    public ?int $workflowBRunId = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowBStatus = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowBConclusion = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowBUrl = null;

    #[ORM\Column(nullable: true)]
    public ?int $resourcesPrNumber = null;

    #[ORM\Column(nullable: true)]
    public ?string $resourcesPrUrl = null;

    #[ORM\Column(nullable: true)]
    public ?string $resourcesPrState = null;

    public function __construct(string $correlationId)
    {
        $this->correlationId = $correlationId;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
}
```

Note: this entity is NOT `final` — Doctrine proxy generation needs to be able to subclass it (same reason no entity in this repo is `final`; this isn't in the "final" list in CLAUDE.md's convention table).

- [ ] **Step 2: Generate the migration**

Run: `make sf c="doctrine:migration:diff --no-interaction"`
Expected: a new file appears under `migrations/2026/07/Version<timestamp>.php` containing a `CREATE TABLE image_pipeline_run (...)` matching the 17 columns above (id, correlation_id, created_at, updated_at, plus the 14 nullable stage columns), with a unique index on `correlation_id`.

- [ ] **Step 3: Apply the migration to dev and test databases**

Run:
```bash
make sf c="doctrine:migration:migrate --no-interaction"
docker compose exec php php bin/console doctrine:migration:migrate --no-interaction --env=test
```
Expected: both report the new migration applied with no errors.

- [ ] **Step 4: Commit**

```bash
git add src/Entity/ImagePipelineRun.php migrations/2026/07/
git commit -m "Add ImagePipelineRun entity and migration"
```

---

### Task 2: Repository — create, update, find latest

**Files:**
- Create: `src/Exception/ImagePipelineRunNotFoundException.php`
- Create: `src/DTO/ImagePipelineRunPatch.php`
- Create: `src/Repository/ImagePipelineRunRepository.php`
- Test: `tests/src/Integration/Repository/ImagePipelineRunRepositoryTest.php`

**Interfaces:**
- Produces:
  - `ImagePipelineRunRepository::create(string $correlationId): void`
  - `ImagePipelineRunRepository::updateFields(string $correlationId, ImagePipelineRunPatch $patch): void` — throws `ImagePipelineRunNotFoundException` if `$correlationId` doesn't exist.
  - `ImagePipelineRunRepository::findLatest(): ?ImagePipelineRun` — most recent by `createdAt`, `null` if none exist.
  - `ImagePipelineRunPatch` — a plain, all-nullable-fields, `final readonly` input object (constructor promotion) mirroring the entity's 14 stage fields, used so `updateFields()` never has to assign a loosely-typed value into a strictly-typed property (which PHPStan level 9 would reject).

Consumed by the Service in Task 3.

- [ ] **Step 1: Write the exception**

Create `src/Exception/ImagePipelineRunNotFoundException.php`:

```php
<?php

declare(strict_types=1);

namespace App\Exception;

final class ImagePipelineRunNotFoundException extends \RuntimeException
{
    public function __construct(string $correlationId)
    {
        parent::__construct("Image pipeline run not found for correlation id '{$correlationId}'");
    }
}
```

- [ ] **Step 2: Write the patch DTO**

Create `src/DTO/ImagePipelineRunPatch.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO;

final class ImagePipelineRunPatch
{
    public function __construct(
        public readonly ?int $workflowARunId = null,
        public readonly ?string $workflowAStatus = null,
        public readonly ?string $workflowAConclusion = null,
        public readonly ?string $workflowAUrl = null,
        public readonly ?int $iconPrNumber = null,
        public readonly ?string $iconPrUrl = null,
        public readonly ?string $iconPrState = null,
        public readonly ?string $iconPrMergeCommitSha = null,
        public readonly ?int $workflowBRunId = null,
        public readonly ?string $workflowBStatus = null,
        public readonly ?string $workflowBConclusion = null,
        public readonly ?string $workflowBUrl = null,
        public readonly ?int $resourcesPrNumber = null,
        public readonly ?string $resourcesPrUrl = null,
        public readonly ?string $resourcesPrState = null,
    ) {}
}
```

- [ ] **Step 3: Write the failing repository test**

Create `tests/src/Integration/Repository/ImagePipelineRunRepositoryTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\ImagePipelineRunPatch;
use App\Exception\ImagePipelineRunNotFoundException;
use App\Repository\ImagePipelineRunRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(ImagePipelineRunRepository::class)]
final class ImagePipelineRunRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testCreateThenFindLatest(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $repo->create('corr-1');

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame('corr-1', $run->correlationId);
        $this->assertNull($run->workflowARunId);
    }

    public function testFindLatestReturnsNullWhenEmpty(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $this->assertNull($repo->findLatest());
    }

    public function testFindLatestReturnsMostRecent(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $repo->create('corr-older');
        $repo->create('corr-newer');

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame('corr-newer', $run->correlationId);
    }

    public function testUpdateFieldsAppliesOnlyProvidedFields(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $repo->create('corr-2');

        $repo->updateFields('corr-2', new ImagePipelineRunPatch(
            workflowARunId: 42,
            workflowAStatus: 'completed',
            workflowAConclusion: 'success',
            workflowAUrl: 'https://github.com/douzeEnsemble/pokenini-icon/actions/runs/42',
        ));

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame(42, $run->workflowARunId);
        $this->assertSame('completed', $run->workflowAStatus);
        $this->assertSame('success', $run->workflowAConclusion);
        $this->assertNull($run->iconPrNumber);
    }

    public function testUpdateFieldsThrowsWhenCorrelationIdUnknown(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $this->expectException(ImagePipelineRunNotFoundException::class);

        $repo->updateFields('does-not-exist', new ImagePipelineRunPatch(workflowARunId: 1));
    }
}
```

- [ ] **Step 4: Run the test and confirm it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Repository/ImagePipelineRunRepositoryTest.php`
Expected: FAIL — `Class "App\Repository\ImagePipelineRunRepository" not found`.

- [ ] **Step 5: Write the repository**

Create `src/Repository/ImagePipelineRunRepository.php`:

```php
<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\ImagePipelineRunPatch;
use App\Entity\ImagePipelineRun;
use App\Exception\ImagePipelineRunNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ImagePipelineRun>
 */
class ImagePipelineRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImagePipelineRun::class);
    }

    public function create(string $correlationId): void
    {
        $run = new ImagePipelineRun($correlationId);

        $this->getEntityManager()->persist($run);
        $this->getEntityManager()->flush();
    }

    public function updateFields(string $correlationId, ImagePipelineRunPatch $patch): void
    {
        $run = $this->findOneBy(['correlationId' => $correlationId]);

        if (null === $run) {
            throw new ImagePipelineRunNotFoundException($correlationId);
        }

        if (null !== $patch->workflowARunId) {
            $run->workflowARunId = $patch->workflowARunId;
        }

        if (null !== $patch->workflowAStatus) {
            $run->workflowAStatus = $patch->workflowAStatus;
        }

        if (null !== $patch->workflowAConclusion) {
            $run->workflowAConclusion = $patch->workflowAConclusion;
        }

        if (null !== $patch->workflowAUrl) {
            $run->workflowAUrl = $patch->workflowAUrl;
        }

        if (null !== $patch->iconPrNumber) {
            $run->iconPrNumber = $patch->iconPrNumber;
        }

        if (null !== $patch->iconPrUrl) {
            $run->iconPrUrl = $patch->iconPrUrl;
        }

        if (null !== $patch->iconPrState) {
            $run->iconPrState = $patch->iconPrState;
        }

        if (null !== $patch->iconPrMergeCommitSha) {
            $run->iconPrMergeCommitSha = $patch->iconPrMergeCommitSha;
        }

        if (null !== $patch->workflowBRunId) {
            $run->workflowBRunId = $patch->workflowBRunId;
        }

        if (null !== $patch->workflowBStatus) {
            $run->workflowBStatus = $patch->workflowBStatus;
        }

        if (null !== $patch->workflowBConclusion) {
            $run->workflowBConclusion = $patch->workflowBConclusion;
        }

        if (null !== $patch->workflowBUrl) {
            $run->workflowBUrl = $patch->workflowBUrl;
        }

        if (null !== $patch->resourcesPrNumber) {
            $run->resourcesPrNumber = $patch->resourcesPrNumber;
        }

        if (null !== $patch->resourcesPrUrl) {
            $run->resourcesPrUrl = $patch->resourcesPrUrl;
        }

        if (null !== $patch->resourcesPrState) {
            $run->resourcesPrState = $patch->resourcesPrState;
        }

        $run->updatedAt = new \DateTime();

        $this->getEntityManager()->flush();
    }

    public function findLatest(): ?ImagePipelineRun
    {
        /** @var ?ImagePipelineRun */
        return $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
```

- [ ] **Step 6: Run the test and confirm it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Repository/ImagePipelineRunRepositoryTest.php`
Expected: `OK (5 tests, ...)`

- [ ] **Step 7: Commit**

```bash
git add src/Exception/ImagePipelineRunNotFoundException.php src/DTO/ImagePipelineRunPatch.php src/Repository/ImagePipelineRunRepository.php tests/src/Integration/Repository/ImagePipelineRunRepositoryTest.php
git commit -m "Add ImagePipelineRunRepository with create/updateFields/findLatest"
```

---

### Task 3: Service, Response DTO/Factory, and Controller

**Files:**
- Create: `src/Service/ImagePipelineRunService.php`
- Create: `src/DTO/Response/ImagePipelineRunResponse.php`
- Create: `src/Factory/ImagePipelineRunResponseFactory.php`
- Create: `src/Controller/ImagePipelineRunController.php`
- Test: `tests/src/Integration/Controller/ImagePipelineRunControllerTest.php`

**Interfaces:**
- Produces three routes under `#[Route('/istration/image-pipeline-runs')]`:
  - `POST /istration/image-pipeline-runs` — body `{"correlationId": "..."}` → `201 Created`, or `409 Conflict` if the correlation id already exists.
  - `PATCH /istration/image-pipeline-runs/{correlationId}` — body: any subset of the 14 stage fields (camelCase JSON keys matching `ImagePipelineRunPatch`) → `200 OK`, or `404 Not Found` if the correlation id doesn't exist.
  - `GET /istration/image-pipeline-runs/latest` — returns `ImagePipelineRunResponse` JSON, or `404 Not Found` if no run exists yet.
- These 3 endpoints are what `pokenini-back`'s companion plan calls.

- [ ] **Step 1: Write the Response DTO**

Create `src/DTO/Response/ImagePipelineRunResponse.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ImagePipelineRunResponse
{
    public function __construct(
        #[SerializedName('correlation_id')]
        public readonly string $correlationId,
        #[SerializedName('workflow_a_run_id')]
        public readonly ?int $workflowARunId,
        #[SerializedName('workflow_a_status')]
        public readonly ?string $workflowAStatus,
        #[SerializedName('workflow_a_conclusion')]
        public readonly ?string $workflowAConclusion,
        #[SerializedName('workflow_a_url')]
        public readonly ?string $workflowAUrl,
        #[SerializedName('icon_pr_number')]
        public readonly ?int $iconPrNumber,
        #[SerializedName('icon_pr_url')]
        public readonly ?string $iconPrUrl,
        #[SerializedName('icon_pr_state')]
        public readonly ?string $iconPrState,
        #[SerializedName('icon_pr_merge_commit_sha')]
        public readonly ?string $iconPrMergeCommitSha,
        #[SerializedName('workflow_b_run_id')]
        public readonly ?int $workflowBRunId,
        #[SerializedName('workflow_b_status')]
        public readonly ?string $workflowBStatus,
        #[SerializedName('workflow_b_conclusion')]
        public readonly ?string $workflowBConclusion,
        #[SerializedName('workflow_b_url')]
        public readonly ?string $workflowBUrl,
        #[SerializedName('resources_pr_number')]
        public readonly ?int $resourcesPrNumber,
        #[SerializedName('resources_pr_url')]
        public readonly ?string $resourcesPrUrl,
        #[SerializedName('resources_pr_state')]
        public readonly ?string $resourcesPrState,
    ) {}
}
```

- [ ] **Step 2: Write the Factory**

Create `src/Factory/ImagePipelineRunResponseFactory.php`:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ImagePipelineRunResponse;
use App\Entity\ImagePipelineRun;

final class ImagePipelineRunResponseFactory
{
    public static function fromEntity(ImagePipelineRun $run): ImagePipelineRunResponse
    {
        return new ImagePipelineRunResponse(
            correlationId: $run->correlationId,
            workflowARunId: $run->workflowARunId,
            workflowAStatus: $run->workflowAStatus,
            workflowAConclusion: $run->workflowAConclusion,
            workflowAUrl: $run->workflowAUrl,
            iconPrNumber: $run->iconPrNumber,
            iconPrUrl: $run->iconPrUrl,
            iconPrState: $run->iconPrState,
            iconPrMergeCommitSha: $run->iconPrMergeCommitSha,
            workflowBRunId: $run->workflowBRunId,
            workflowBStatus: $run->workflowBStatus,
            workflowBConclusion: $run->workflowBConclusion,
            workflowBUrl: $run->workflowBUrl,
            resourcesPrNumber: $run->resourcesPrNumber,
            resourcesPrUrl: $run->resourcesPrUrl,
            resourcesPrState: $run->resourcesPrState,
        );
    }
}
```

- [ ] **Step 3: Write the Service**

Create `src/Service/ImagePipelineRunService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ImagePipelineRunPatch;
use App\Entity\ImagePipelineRun;
use App\Repository\ImagePipelineRunRepository;

class ImagePipelineRunService
{
    public function __construct(
        private readonly ImagePipelineRunRepository $repository,
    ) {}

    public function create(string $correlationId): void
    {
        $this->repository->create($correlationId);
    }

    public function updateFields(string $correlationId, ImagePipelineRunPatch $patch): void
    {
        $this->repository->updateFields($correlationId, $patch);
    }

    public function findLatest(): ?ImagePipelineRun
    {
        return $this->repository->findLatest();
    }
}
```

- [ ] **Step 4: Write the failing controller test**

Create `tests/src/Integration/Controller/ImagePipelineRunControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ImagePipelineRunController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ImagePipelineRunController::class)]
final class ImagePipelineRunControllerTest extends AbstractTestControllerApi
{
    public function testCreateThenGetLatest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-1'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest('GET', '/istration/image-pipeline-runs/latest');

        $this->assertResponseIsSuccessful();

        $content = $this->getClientResponseContent();
        $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('corr-1', $data['correlation_id']);
        $this->assertNull($data['workflow_a_run_id']);
    }

    public function testCreateWithDuplicateCorrelationIdConflicts(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-dup'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-dup'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(409);
    }

    public function testGetLatestReturns404WhenNoneExist(): void
    {
        $this->apiRequest('GET', '/istration/image-pipeline-runs/latest');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testPatchAppliesFields(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-patch'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest(
            'PATCH',
            '/istration/image-pipeline-runs/corr-patch',
            [],
            null,
            json_encode(['workflowARunId' => 99, 'workflowAStatus' => 'completed'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseIsSuccessful();

        $this->apiRequest('GET', '/istration/image-pipeline-runs/latest');
        $data = json_decode($this->getClientResponseContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(99, $data['workflow_a_run_id']);
        $this->assertSame('completed', $data['workflow_a_status']);
    }

    public function testPatchReturns404WhenCorrelationIdUnknown(): void
    {
        $this->apiRequest(
            'PATCH',
            '/istration/image-pipeline-runs/does-not-exist',
            [],
            null,
            json_encode(['workflowARunId' => 1], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
```

Note: `apiRequest()` uses `AbstractTestControllerApi`'s default HTTP Basic auth
(`self::AUTH_USER`/`self::AUTH_PASSWORD`) when the `$options` argument is
`null`, per that trait's existing signature.

- [ ] **Step 5: Run the test and confirm it fails**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/ImagePipelineRunControllerTest.php`
Expected: FAIL — `Class "App\Controller\ImagePipelineRunController" not found`.

- [ ] **Step 6: Write the controller**

Create `src/Controller/ImagePipelineRunController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ImagePipelineRunPatch;
use App\DTO\Response\ImagePipelineRunResponse;
use App\Factory\ImagePipelineRunResponseFactory;
use App\Service\ImagePipelineRunService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/istration/image-pipeline-runs')]
final class ImagePipelineRunController extends AbstractController
{
    public function __construct(
        private readonly ImagePipelineRunService $service,
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = $this->decodeBody($request);

        if (!isset($data['correlationId']) || !\is_string($data['correlationId'])) {
            throw new BadRequestHttpException();
        }

        try {
            $this->service->create($data['correlationId']);
        } catch (UniqueConstraintViolationException $e) {
            throw new ConflictHttpException(previous: $e);
        }

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route('/{correlationId}', methods: ['PATCH'])]
    public function patch(string $correlationId, Request $request): Response
    {
        $data = $this->decodeBody($request);

        $patch = new ImagePipelineRunPatch(
            workflowARunId: isset($data['workflowARunId']) ? (int) $data['workflowARunId'] : null,
            workflowAStatus: isset($data['workflowAStatus']) ? (string) $data['workflowAStatus'] : null,
            workflowAConclusion: isset($data['workflowAConclusion']) ? (string) $data['workflowAConclusion'] : null,
            workflowAUrl: isset($data['workflowAUrl']) ? (string) $data['workflowAUrl'] : null,
            iconPrNumber: isset($data['iconPrNumber']) ? (int) $data['iconPrNumber'] : null,
            iconPrUrl: isset($data['iconPrUrl']) ? (string) $data['iconPrUrl'] : null,
            iconPrState: isset($data['iconPrState']) ? (string) $data['iconPrState'] : null,
            iconPrMergeCommitSha: isset($data['iconPrMergeCommitSha']) ? (string) $data['iconPrMergeCommitSha'] : null,
            workflowBRunId: isset($data['workflowBRunId']) ? (int) $data['workflowBRunId'] : null,
            workflowBStatus: isset($data['workflowBStatus']) ? (string) $data['workflowBStatus'] : null,
            workflowBConclusion: isset($data['workflowBConclusion']) ? (string) $data['workflowBConclusion'] : null,
            workflowBUrl: isset($data['workflowBUrl']) ? (string) $data['workflowBUrl'] : null,
            resourcesPrNumber: isset($data['resourcesPrNumber']) ? (int) $data['resourcesPrNumber'] : null,
            resourcesPrUrl: isset($data['resourcesPrUrl']) ? (string) $data['resourcesPrUrl'] : null,
            resourcesPrState: isset($data['resourcesPrState']) ? (string) $data['resourcesPrState'] : null,
        );

        try {
            $this->service->updateFields($correlationId, $patch);
        } catch (\App\Exception\ImagePipelineRunNotFoundException $e) {
            throw new NotFoundHttpException(previous: $e);
        }

        return new Response();
    }

    #[Route('/latest', methods: ['GET'])]
    #[Serialize]
    public function getLatest(): ImagePipelineRunResponse
    {
        $run = $this->service->findLatest();

        if (null === $run) {
            throw new NotFoundHttpException();
        }

        return ImagePipelineRunResponseFactory::fromEntity($run);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeBody(Request $request): array
    {
        $content = $request->getContent();

        if (!$content) {
            throw new BadRequestHttpException();
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $data;
    }
}
```

- [ ] **Step 7: Run the test and confirm it passes**

Run: `docker compose exec php php vendor/bin/phpunit tests/src/Integration/Controller/ImagePipelineRunControllerTest.php`
Expected: `OK (5 tests, ...)`

- [ ] **Step 8: Verify routing**

Run: `docker compose exec php php bin/console debug:router | grep image-pipeline-runs`
Expected: 3 lines — `POST /istration/image-pipeline-runs`, `PATCH /istration/image-pipeline-runs/{correlationId}`, `GET /istration/image-pipeline-runs/latest` — and confirm `/latest` doesn't get shadowed by `{correlationId}` (the test suite passing already proves this, but double check the router output looks right).

- [ ] **Step 9: Commit**

```bash
git add src/Service/ImagePipelineRunService.php src/DTO/Response/ImagePipelineRunResponse.php src/Factory/ImagePipelineRunResponseFactory.php src/Controller/ImagePipelineRunController.php tests/src/Integration/Controller/ImagePipelineRunControllerTest.php
git commit -m "Add ImagePipelineRun create/patch/latest endpoints"
```

---

### Task 4: Full quality and measures gate

**Files:** none (verification only).

- [ ] **Step 1: Run the full test suite**

Run: `make tests`
Expected: all tests pass, including the new files from Tasks 1-3.

- [ ] **Step 2: Run quality checks**

Run: `make quality`
Expected: `code-quality` (PHP CS Fixer, PHPMD, Psalm, PHPStan, Deptrac, jsonlint) and `infra-quality` both clean. If PHP CS Fixer reports differences, run `make phpcsfixer-fix` and re-run. If Deptrac complains about a layer dependency (e.g. `Controller → Exception` for `ImagePipelineRunNotFoundException`, or `Factory → Entity`), check `deptrac.yaml`'s existing rules for the `AppController`/`AppFactory` layers before assuming a fix is needed — these dependencies already exist elsewhere in this codebase (e.g. `AlbumUpsertController` catches `NotNullConstraintViolationException`, `CatchStateResponseFactory` reads DTOs) and should already be permitted.

- [ ] **Step 3: Run coverage and mutation testing**

Run: `make measures`
Expected: 100% coverage, 100% MSI. If a mutant survives in `ImagePipelineRunRepository::updateFields()`'s 14 near-identical `if (null !== ...)` blocks, add an assertion to `ImagePipelineRunRepositoryTest::testUpdateFieldsAppliesOnlyProvidedFields` that pins down a field not yet covered (e.g. one from each of the 4 stage groups: Workflow A, icon PR, Workflow B, resources PR) rather than relying on the single 4-field example already there.

- [ ] **Step 4: Commit any fixes**

```bash
git add -A
git commit -m "Fix quality/coverage findings for image pipeline run tracking"
```

(Skip if nothing needed fixing.)
