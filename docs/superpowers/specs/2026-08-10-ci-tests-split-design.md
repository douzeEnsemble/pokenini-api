# Design: split `ci_tests.yml` into parallel jobs + Docker image archive cache (pokenini-api)

## Purpose

Same motivation as the already-implemented and approved design in `pokenini-back`
(`pokenini-back`'s `docs/superpowers/specs/2026-08-10-ci-tests-split-design.md`, applied
there via PR #154): `ci_tests.yml` currently runs everything in one sequential job
(`allin`) on one runner. Split it into parallel jobs so failures are isolated and the
pipeline runs faster, and add a Docker image archive cache to the shared composite
action so the now-multiplied image build isn't repeated per job.

This repo's split differs from `pokenini-back`'s in two structural ways, both confirmed
by reading the actual workflow/compose files (not assumed from the back precedent):

1. `pokenini-api` owns a PostgreSQL database (`database` service in
  `docker-compose.yaml`) and a Google-Sheets mock (`moco.sheets.int` /
  `moco.sheets.test`, both built from `.docker/moco/Dockerfile`). `pokenini-back` has
  neither.
2. The current single job's `run:` steps include a phase that PHPUnit does not cover at
  all: `Integration / Copy .env file` → `Integration / Initialize` → `Integration / Run`
  seed a full dataset into a separate `int`-environment database (via `cp .env.int .env`
  then a dozen `bin/console app:update:*`/`app:calculate:*` commands) and then run a
  Postman collection through `docker compose up newman`. This is unrelated to the
  PHPUnit tests under `tests/src/Integration/` (those run against the `test` env,
  whose database the shared composite action already creates on every job). Per this
  repo's `CLAUDE.md`: "API tests: Postman collection run via Newman (`make integration`)"
  — a third, distinct test category alongside Unit and (PHPUnit) Integration.

## Current state (baseline)

Single job `allin` in `.github/workflows/ci_tests.yml`:
1. `phpunit tests/src/ --exclude-group="time-testing"` (full suite: Unit + Integration)
2. Coverage run (full suite again, `--exclude-group="browser-testing"`, Xdebug)
3. Infection composer install + run (consumes step 2's coverage output)
4. `cp .env.int .env`
5. Twelve `bin/console` calls seeding the `int` database (labels, games/dex,
  pokemons, regional dex numbers, games availabilities, games shinies availabilities,
  game bundles availabilities, game bundles shinies availabilities, dex
  availabilities, pokemon availabilities)
6. `docker compose up newman --no-recreate --menu=false` (runs the Postman collection
  at `tests/src/Integration/Postman/collection.json` against the seeded `int` DB)
7. `symfony security:check`

As in `pokenini-back`, `grep`-ing the whole test suite for PHPUnit `#[Group(...)]`
attributes found **zero** — `time-testing` and `browser-testing` are dead flags here
too (confirmed by the earlier research pass). Both are kept exactly as-is; not removed.

The shared composite action `.github/actions/docker-compose/action.yml` additionally
does two things `pokenini-back`'s equivalent doesn't: creates the `test`-environment
database (`bin/console -e test doctrine:database:create` + `doctrine:migrations:migrate`)
and runs `doctrine:schema:validate --skip-sync`, both after `Composer install`. These
run identically inside every job that calls the composite action, same as today.

The Makefile already exposes `tests-unit` (`tests/src/Unit`) and `tests-integration`
(`tests/src/Integration`) separately, plus a `make integration` target for the
Newman/Postman phase and `make measures` (coverage + infection). Per `CLAUDE.md`:
Unit tests mock dependencies (no DB); Integration tests hit a real DB with Moco
replacing the Google Sheets API.

## Job split

`ci_tests.yml` becomes 5 independent jobs, each using
`./.github/actions/docker-compose` (unchanged trigger conditions):

| Job | Runs |
|---|---|
| `unit-tests` | `phpunit tests/src/Unit --exclude-group="time-testing"` |
| `integration-tests` | `phpunit tests/src/Integration --exclude-group="time-testing"` |
| `api-integration` | The Newman/Postman phase (steps 4-6 above), verbatim, as its own job — `cp .env.int .env`, the twelve seed commands, then `docker compose up newman --no-recreate --menu=false` |
| `measures` | Coverage step then Infection, in that order, in the same job (unchanged from current steps 2-3 — infection consumes the coverage this job's own previous step just produced, so these stay sequential in one job, same reasoning as `pokenini-back`) |
| `security` | `symfony security:check` (unchanged) |

`api-integration` is named to match this repo's existing `make integration` Makefile
target and its `CLAUDE.md` description ("API tests"), and to avoid colliding with the
PHPUnit-focused `integration-tests` job name (which covers `tests/src/Integration`,
not this Newman/Postman phase — the current workflow's step names literally reuse the
word "Integration" for both concepts, which this split intentionally disambiguates).

Every job still spins up the full `docker-compose.yaml` stack via the shared composite
action — including its `test`-database creation and schema validation — even jobs like
`api-integration` that don't need the `test` DB. Scoping which services/DB-setup steps
run per job is out of scope here, same call as in `pokenini-back`.

## Docker image archive cache

Same mechanism as `pokenini-back`'s (already implemented, reviewed, and fixed there —
this design reuses that exact, corrected flow rather than the original buggy first
draft):

- **Cache key**: `hashFiles('.docker/php/Dockerfile', '.docker/php/conf.d/**',
  '.docker/moco/Dockerfile', 'docker-compose.yaml')` plus host `UID`/`GID`.
  Confirmed by reading `.docker/php/Dockerfile`: the `php_dev` target (the one
  `docker-compose.yaml` builds) does not `COPY` application source or
  `composer.lock` — only `.docker/php/conf.d/*.ini` files and a pinned `symfony-cli`
  binary, exactly like `pokenini-back`. `vendor/` is bind-mounted and installed fresh
  per job via the composite action's existing `Composer install` step — untouched by
  this cache.
- **Archived images**: only the 3 *built* services — `php`, `moco.sheets.int`,
  `moco.sheets.test` (`docker compose config --images php moco.sheets.int
  moco.sheets.test`). Excluded: `adminer`, `database` (Postgres), `newman`, `web`
  (nginx) — all pulled, not built, so archiving them wastes cache space for no
  build-time benefit (same reasoning `pokenini-back`'s final review landed on for
  excluding `nginx` there).
- **Flow**, incorporating the fix already applied in `pokenini-back` after its final
  review caught a load-bearing bug there:
  - Restore step uses `actions/cache/restore@v5` (not the unified `actions/cache@v5`)
    so the save isn't tied to the whole job's `post-if: success()` semantics.
  - On hit: `docker load` the archive; skip Docker Hub login, Buildx setup, bake, and
    pull entirely.
  - On miss: existing login/buildx/bake/pull steps run unchanged, then `Start services`
    (`docker compose up --wait`, `--build` dropped) — this is what actually builds and
    tags the 3 services under the compose-generated names (`bake` alone does not tag
    them, since none of the 3 declare an explicit `image:` key) — then an explicit
    `actions/cache/save@v5` step saves the tar immediately after, scoped to the 3
    service names.
  - `Composer install`, `Create test database`, and `Doctrine Schema Validator` run
    unconditionally after, unchanged.

### Non-goals

- No caching of `vendor/` (bind-mounted, installed fresh per job today — untouched).
- No dedicated `build-images` job with `needs:` fan-out — same best-effort
  shared-cache approach as `pokenini-back`.
- No change to the `test`/`int` database creation logic, the Postman collection, or
  the seed command list — this is purely a CI job-topology and build-caching change.

## Testing / validation

YAML syntax check. Push the branch and confirm in the Actions UI: 5 jobs run in
parallel, each finishes with the same pass/fail result the current single job produces
for the corresponding step, and the second CI run on the same branch (no Dockerfile
change) shows a cache hit with no build steps executed on any of the 5 jobs.
