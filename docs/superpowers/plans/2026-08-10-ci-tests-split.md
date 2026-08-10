# CI Tests Split + Docker Image Archive Cache Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Split `pokenini-api`'s single-job `ci_tests.yml` into 5 parallel jobs (unit, integration, api-integration/Newman, measures, security) and add a Docker image archive cache to the shared composite action.

**Architecture:** Two independent, additive changes to GitHub Actions YAML — no application code touched. Task 1 replaces the one `allin` job in `ci_tests.yml` with 5 jobs, splitting its steps 1-for-1 across jobs (the Newman/Postman phase becomes its own `api-integration` job, distinct from the PHPUnit `integration-tests` job). Task 2 adds the Docker image archive cache to `./.github/actions/docker-compose/action.yml`, using the design already implemented, reviewed, and bug-fixed in `pokenini-back` (see that repo's `docs/superpowers/plans/2026-08-10-ci-tests-split.md` Task 2 for the precedent this copies) — the save step runs after `docker compose up` (not before, which is what made the images-not-yet-tagged bug in the first `pokenini-back` draft), uses split `actions/cache/restore@v5`/`actions/cache/save@v5` steps, and archives only the 3 built services.

**Tech Stack:** GitHub Actions YAML, Docker Compose, `actions/cache@v5`.

## Global Constraints

- No change to trigger conditions: `push` to `main`, `pull_request: ~`, `workflow_call` with `DOCKERHUB_USERNAME`/`DOCKERHUB_TOKEN` secrets — copied verbatim across all 5 new jobs.
- `PHP_CS_FIXER_IGNORE_ENV: 1` env var stays at workflow level.
- Neither `time-testing` nor `browser-testing` PHPUnit groups exist in `tests/src` today (confirmed via repo-wide grep during design) — keep both `--exclude-group` flags exactly as they are; do not remove them.
- The `php_dev` Docker Compose build target does not `COPY` application source or `composer.lock` — only `.docker/php/conf.d/*.ini` files and a pinned `symfony-cli` binary. `vendor/` is bind-mounted and installed fresh per job; it is untouched by this plan.
- The `api-integration` job's Newman/Postman steps, and the composite action's `test`-database creation and `Doctrine Schema Validator` steps, must be copied byte-for-byte from the current files — no behavior change to seeding logic, migrations, or the Postman collection.
- Pushing the branch and watching the real Actions run is the final verification step, but must not be done without checking with the user first.

---

### Task 1: Split `ci_tests.yml` into 5 parallel jobs

**Files:**
- Modify: `.github/workflows/ci_tests.yml` (full rewrite of the `jobs:` section — `on:` and top-level `env:` blocks are unchanged)

**Interfaces:**
- Consumes: `./.github/actions/docker-compose` composite action (unchanged in this task).
- Produces: 5 job names (`unit-tests`, `integration-tests`, `api-integration`, `measures`, `security`) that Task 2's composite-action change is exercised by.

- [ ] **Step 1: Read the current file to confirm no drift**

Run: `cat .github/workflows/ci_tests.yml`

Confirm it still matches this baseline `jobs:` section (if it doesn't, stop and check with the user before continuing):

```yaml
jobs:
  allin:
    name: All tests
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v6
      - name: Prepare
        uses: ./.github/actions/docker-compose
      - name: PHPUnit / Run
        run: |
          docker compose exec -T php php vendor/bin/phpunit tests/src/ --exclude-group="time-testing"
      - name: Measures / Run Coverage
        run: |
          docker compose exec \
            -e XDEBUG_MODE=coverage \
            -T php php vendor/bin/phpunit \
            --exclude-group="browser-testing" \
            --coverage-clover=coverage.xml \
            --coverage-xml=build/coverage/coverage-xml \
            --log-junit=build/coverage/junit.xml
      - name: Measures / Composer install Infection
        shell: bash
        run: docker compose exec -T php composer install --working-dir=tools/infection --prefer-dist --no-progress --no-interaction
      - name: Measures / Run Infection
        run: |
          docker compose exec -T php php tools/infection/vendor/bin/infection \
            --threads=4 --no-progress \
            --skip-initial-tests --coverage=build/coverage \
            --min-msi=100 --min-covered-msi=100 \
            --filter=src
      - name: Integration / Copy .env file
        shell: bash
        run: cp .env.int .env
      - name: Integration / Initialize
        run: |
          docker compose exec -T php php bin/console doctrine:database:create --if-not-exists
          docker compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction
          docker compose exec -T php php bin/console app:update:labels
          docker compose exec -T php php bin/console app:update:games_collections_and_dex
          docker compose exec -T php php bin/console app:update:pokemons
          docker compose exec -T php php bin/console app:update:regional_dex_numbers
          docker compose exec -T php php bin/console app:update:games_availabilities
          docker compose exec -T php php bin/console app:update:games_shinies_availabilities
          docker compose exec -T php php bin/console app:calculate:game_bundles_availabilities
          docker compose exec -T php php bin/console app:calculate:game_bundles_shinies_availabilities
          docker compose exec -T php php bin/console app:calculate:dex_availabilities
          docker compose exec -T php php bin/console app:calculate:pokemon_availabilities
      - name: Integration / Run
        run: |
          docker compose up newman --no-recreate --menu=false
      - name: Security / Symfony Security Check
        run: |
          docker compose exec -T php symfony security:check
```

- [ ] **Step 2: Replace the `jobs:` section**

Replace everything from `jobs:` to the end of the file with:

```yaml
jobs:
  unit-tests:
    name: Unit Tests
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v6
      - name: Prepare
        uses: ./.github/actions/docker-compose
      - name: PHPUnit / Run
        run: |
          docker compose exec -T php php vendor/bin/phpunit tests/src/Unit --exclude-group="time-testing"

  integration-tests:
    name: Integration Tests
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v6
      - name: Prepare
        uses: ./.github/actions/docker-compose
      - name: PHPUnit / Run
        run: |
          docker compose exec -T php php vendor/bin/phpunit tests/src/Integration --exclude-group="time-testing"

  api-integration:
    name: API Integration (Postman)
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v6
      - name: Prepare
        uses: ./.github/actions/docker-compose
      - name: Integration / Copy .env file
        shell: bash
        run: cp .env.int .env
      - name: Integration / Initialize
        run: |
          docker compose exec -T php php bin/console doctrine:database:create --if-not-exists
          docker compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction
          docker compose exec -T php php bin/console app:update:labels
          docker compose exec -T php php bin/console app:update:games_collections_and_dex
          docker compose exec -T php php bin/console app:update:pokemons
          docker compose exec -T php php bin/console app:update:regional_dex_numbers
          docker compose exec -T php php bin/console app:update:games_availabilities
          docker compose exec -T php php bin/console app:update:games_shinies_availabilities
          docker compose exec -T php php bin/console app:calculate:game_bundles_availabilities
          docker compose exec -T php php bin/console app:calculate:game_bundles_shinies_availabilities
          docker compose exec -T php php bin/console app:calculate:dex_availabilities
          docker compose exec -T php php bin/console app:calculate:pokemon_availabilities
      - name: Integration / Run
        run: |
          docker compose up newman --no-recreate --menu=false

  measures:
    name: Measures (Coverage & Mutation Testing)
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v6
      - name: Prepare
        uses: ./.github/actions/docker-compose
      - name: Measures / Run Coverage
        run: |
          docker compose exec \
            -e XDEBUG_MODE=coverage \
            -T php php vendor/bin/phpunit \
            --exclude-group="browser-testing" \
            --coverage-clover=coverage.xml \
            --coverage-xml=build/coverage/coverage-xml \
            --log-junit=build/coverage/junit.xml
      - name: Measures / Composer install Infection
        shell: bash
        run: docker compose exec -T php composer install --working-dir=tools/infection --prefer-dist --no-progress --no-interaction
      - name: Measures / Run Infection
        run: |
          docker compose exec -T php php tools/infection/vendor/bin/infection \
            --threads=4 --no-progress \
            --skip-initial-tests --coverage=build/coverage \
            --min-msi=100 --min-covered-msi=100 \
            --filter=src

  security:
    name: Security
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v6
      - name: Prepare
        uses: ./.github/actions/docker-compose
      - name: Security / Symfony Security Check
        run: |
          docker compose exec -T php symfony security:check
```

- [ ] **Step 3: Validate YAML syntax**

Run: `python3 -c "import yaml; yaml.safe_load(open('.github/workflows/ci_tests.yml'))" && echo OK`
Expected: `OK`

- [ ] **Step 4: Confirm every original step survived the split**

Run: `git diff .github/workflows/ci_tests.yml` and check by eye against the Step 1
baseline: every `run:` command block must be byte-identical to the original, with
exactly two intentional exceptions — `tests/src/` narrowed to `tests/src/Unit` in the
`unit-tests` job's PHPUnit command, and to `tests/src/Integration` in the
`integration-tests` job's. The `api-integration` job's steps (`cp .env.int .env`, the
twelve `bin/console` commands, `docker compose up newman ...`) must be copied verbatim
from the original `Integration / Copy .env file` / `Integration / Initialize` /
`Integration / Run` steps — same step names, same commands, same order.

- [ ] **Step 5: Commit**

```bash
git add .github/workflows/ci_tests.yml
git commit -m "ci: split ci_tests.yml into parallel unit/integration/api-integration/measures/security jobs"
```

---

### Task 2: Add Docker image archive cache to the shared composite action

**Files:**
- Modify: `.github/actions/docker-compose/action.yml`

**Interfaces:**
- Consumes: nothing new — same composite action interface (`uses: ./.github/actions/docker-compose`, no inputs), called identically by all 5 jobs from Task 1.
- Produces: no new outputs; behavior change only (skips build on cache hit).

- [ ] **Step 1: Read the current file to confirm no drift**

Run: `cat .github/actions/docker-compose/action.yml`

Confirm it still matches this baseline (stop and check with the user if it doesn't):

```yaml
name: Docker Compose Pull Up And Check
description: Use to docker compose pull and up then checks if services are correctly running
author: Renaud Douze
runs:
  using: "composite"
  steps:
    - name: Copy .env file
      shell: bash
      run: cp .env.ci .env

    - name: Export host UID/GID for Docker build
      shell: bash
      run: |
        echo "APP_UID=$(id -u)" >> "$GITHUB_ENV"
        echo "APP_GID=$(id -g)" >> "$GITHUB_ENV"

    - name: Login to Docker Hub
      uses: docker/login-action@v4
      with:
        username: ${{ env.DOCKERHUB_USERNAME }}
        password: ${{ env.DOCKERHUB_TOKEN }}

    - name: Setup Docker Buildx
      uses: docker/setup-buildx-action@v4

    - name: Docker Buildx Bake
      uses: docker/bake-action@v7
      with:
        load: true
        set: |
          *.cache-to=type=gha,mode=max                                                                                
          *.cache-from=type=gha 
      
    - name: Pull images
      shell: bash
      run: docker compose pull --ignore-pull-failures || true

    - name: Start services
      shell: bash
      run: docker compose --verbose up --build --wait

    - name: Composer install
      shell: bash
      run: docker compose exec -T php composer install --prefer-dist --no-progress --no-interaction

    - name: Create test database
      shell: bash
      run: |
        docker compose exec -T php bin/console -e test doctrine:database:create --if-not-exists
        docker compose exec -T php bin/console -e test doctrine:migrations:migrate --no-interaction

    - name: Doctrine Schema Validator
      shell: bash
      run: docker compose exec -T php bin/console doctrine:schema:validate --skip-sync
```

- [ ] **Step 2: Replace the file contents**

```yaml
name: Docker Compose Pull Up And Check
description: Use to docker compose pull and up then checks if services are correctly running
author: Renaud Douze
runs:
  using: "composite"
  steps:
    - name: Copy .env file
      shell: bash
      run: cp .env.ci .env

    - name: Export host UID/GID for Docker build
      shell: bash
      run: |
        echo "APP_UID=$(id -u)" >> "$GITHUB_ENV"
        echo "APP_GID=$(id -g)" >> "$GITHUB_ENV"

    - name: Restore Docker images archive cache
      id: docker-images-cache
      uses: actions/cache/restore@v5
      with:
        path: /tmp/docker-images-cache/images.tar
        key: docker-images-${{ runner.os }}-${{ hashFiles('.docker/php/Dockerfile', '.docker/php/conf.d/**', '.docker/moco/Dockerfile', 'docker-compose.yaml') }}-${{ env.APP_UID }}-${{ env.APP_GID }}

    - name: Load cached Docker images
      if: steps.docker-images-cache.outputs.cache-hit == 'true'
      shell: bash
      run: docker load -i /tmp/docker-images-cache/images.tar

    - name: Login to Docker Hub
      if: steps.docker-images-cache.outputs.cache-hit != 'true'
      uses: docker/login-action@v4
      with:
        username: ${{ env.DOCKERHUB_USERNAME }}
        password: ${{ env.DOCKERHUB_TOKEN }}

    - name: Setup Docker Buildx
      if: steps.docker-images-cache.outputs.cache-hit != 'true'
      uses: docker/setup-buildx-action@v4

    - name: Docker Buildx Bake
      if: steps.docker-images-cache.outputs.cache-hit != 'true'
      uses: docker/bake-action@v7
      with:
        load: true
        set: |
          *.cache-to=type=gha,mode=max                                                                                
          *.cache-from=type=gha 

    - name: Pull images
      if: steps.docker-images-cache.outputs.cache-hit != 'true'
      shell: bash
      run: docker compose pull --ignore-pull-failures || true

    - name: Start services
      shell: bash
      run: docker compose --verbose up --wait

    - name: Save Docker images archive cache
      if: steps.docker-images-cache.outputs.cache-hit != 'true'
      shell: bash
      run: |
        mkdir -p /tmp/docker-images-cache
        docker save $(docker compose config --images php moco.sheets.int moco.sheets.test) -o /tmp/docker-images-cache/images.tar

    - name: Save Docker images archive cache (upload)
      if: steps.docker-images-cache.outputs.cache-hit != 'true'
      uses: actions/cache/save@v5
      with:
        path: /tmp/docker-images-cache/images.tar
        key: ${{ steps.docker-images-cache.outputs.cache-primary-key }}

    - name: Composer install
      shell: bash
      run: docker compose exec -T php composer install --prefer-dist --no-progress --no-interaction

    - name: Create test database
      shell: bash
      run: |
        docker compose exec -T php bin/console -e test doctrine:database:create --if-not-exists
        docker compose exec -T php bin/console -e test doctrine:migrations:migrate --no-interaction

    - name: Doctrine Schema Validator
      shell: bash
      run: docker compose exec -T php bin/console doctrine:schema:validate --skip-sync
```

Note the changes, mirroring exactly what `pokenini-back` ended up with after its final
review caught and fixed a bug in the first draft of this same change (see that repo's
commit history: `44f52e3` introduced the cache, `42fcb5e` fixed it):

1. The archive-save step (`docker save ...`) runs AFTER `Start services`, not before.
   `docker compose up` (with `--build` dropped) is what actually builds and tags the 3
   built services under the compose-generated names — `docker buildx bake` alone does
   not tag them, since none of the 3 declare an explicit `image:` key in
   `docker-compose.yaml`. Saving before `up` would find those 3 image names missing
   and fail on every cache-miss run.
2. The cache uses split `actions/cache/restore@v5` / `actions/cache/save@v5` steps
   (not the unified `actions/cache@v5`), so the save happens as an ordinary step right
   after the tar is written — not deferred to a `post-if: success()` hook that a later
   step failing (e.g. `Composer install`, `Create test database`) would silently skip.
   The save step reuses `steps.docker-images-cache.outputs.cache-primary-key` (the
   exact key the restore step resolved) rather than re-typing the key expression, so
   the two can never drift out of sync.
3. `docker save` is scoped to the 3 built services only
   (`docker compose config --images php moco.sheets.int moco.sheets.test`) — `adminer`,
   `database`, `newman`, and `web` are pulled, not built, so archiving them wastes
   cache space for no benefit.
4. `Composer install`, `Create test database`, and `Doctrine Schema Validator` are
   unconditional and unchanged, same as before this task.

- [ ] **Step 3: Validate YAML syntax**

Run: `python3 -c "import yaml; yaml.safe_load(open('.github/actions/docker-compose/action.yml'))" && echo OK`
Expected: `OK`

- [ ] **Step 4: Validate the cache key's `hashFiles` globs and the archived service names actually exist**

Run: `ls .docker/php/Dockerfile .docker/php/conf.d/ .docker/moco/Dockerfile docker-compose.yaml`
Expected: all 4 paths exist.

Run: `grep -E '^\s+(php|moco\.sheets\.int|moco\.sheets\.test):' docker-compose.yaml`
Expected: 3 matches — confirms the 3 service names passed to `docker compose config
--images` in Step 2 are exactly the build-service names in `docker-compose.yaml` (a
typo here would silently cache 0 or the wrong images).

- [ ] **Step 5: Commit**

```bash
git add .github/actions/docker-compose/action.yml
git commit -m "ci: cache Docker Compose images as an archive to skip rebuilds across parallel jobs"
```

---

### Task 3: Push and verify on a real CI run

**Files:** none (verification only).

**Interfaces:** none.

- [ ] **Step 1: Check in with the user before pushing**

This pushes a branch (and likely opens a PR) — confirm with the user first. Ask which
branch name they want if they haven't already specified one.

- [ ] **Step 2: Push the branch**

```bash
git push -u origin HEAD
```

- [ ] **Step 3: Open a PR (if the user wants one) and watch the run**

```bash
gh pr create --title "ci: split tests into parallel jobs, cache Docker images" --fill
gh run watch
```

Expected: 5 separate check runs appear (`Unit Tests`, `Integration Tests`,
`API Integration (Postman)`, `Measures (Coverage & Mutation Testing)`, `Security`)
instead of the single `All tests` check, all passing with the same results the current
`allin` job would have produced.

- [ ] **Step 4: Push a second, no-op commit (e.g. `git commit --allow-empty -m "ci: retrigger"`) and watch again**

Expected: in the Actions log for the `Prepare` step of each of the 5 jobs, the
`Restore Docker images archive cache` step reports a cache hit, and the
`Login to Docker Hub` / `Setup Docker Buildx` / `Docker Buildx Bake` / `Pull images`
steps are skipped.
