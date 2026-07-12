# Design: automated Composer updates workflow

## Purpose

Automate what `make updates` does locally (bump every Composer dependency across the
main app and the 6 quality-tool sub-projects) via GitHub Actions, on a daily schedule,
opening/updating a pull request — a personal equivalent of Dependabot.

## Non-goals

- No per-tool/per-package PR splitting (Dependabot-style). One bundled PR, matching the
  historical manual workflow (see commits like `7616b28 Composers updates`).
- No change to the existing `ci_codequality.yml` / `ci_tests.yml` / `security.yml`
  workflows beyond the fact that they will now also run against the auto-generated PR
  (they already trigger on `pull_request: ~`).
- No Docker/DB/Moco stack involved — `composer update` needs only PHP + Composer.

## Trigger

New workflow file: `.github/workflows/composer_updates.yml`

- `schedule`: daily cron, `17 4 * * *` (04:17 UTC, off-the-hour to avoid GitHub Actions
  congestion).
- `workflow_dispatch`: allows a manual run on demand.

## Job

Single job `composer-updates` on `ubuntu-latest`.

1. **Checkout** — plain `actions/checkout@v6` with the default `GITHUB_TOKEN` (no
   `PAT_TOKEN`). The PAT is deliberately kept off this step: it would otherwise sit as a
   persisted git credential for the rest of the job, during which every `composer update`
   step executes third-party package/plugin code — needlessly widening what a compromised
   dependency could exfiltrate. The PAT is only actually required on the PR-creation step
   below (see step 4), since that's what authenticates the push/PR and is what determines
   whether the PR triggers other workflows (anti-recursion protection: GitHub refuses to
   let a `GITHUB_TOKEN`-authored push/PR trigger other workflows, and we need the PR to
   trigger `ci_codequality`, `ci_tests`, and `security` normally).
2. **PHP setup** — reuse `./.github/actions/local-php` (already used by
   `local-composer`/`tools-composer`). It copies `.env.ci` to `.env` and installs PHP 8.5
   with the extension set already validated by existing CI jobs. No Docker compose stack
   is started.
3. **Composer updates** — one step per directory, run in the same order as the `updates`
   Makefile target, each `composer update --bump-after-update --with-all-dependencies
   --optimize-autoloader`:
   - main app (`--working-dir=./`)
   - `tools/deptrac`
   - `tools/infection`
   - `tools/jsonlint`
   - `tools/php-cs-fixer`
   - `tools/phpmd`
   - `tools/phpstan`
   - `tools/psalm`

   Any step failing fails the job (default shell behavior) — no partial/broken PR is
   created in that case.
4. **Open/update PR** — `peter-evans/create-pull-request@v7`:
   - `token: ${{ secrets.PAT_TOKEN }}`
   - `branch: chore/composer-updates` (fixed name, so subsequent daily runs update the
     same branch/PR instead of piling up new ones)
   - `delete-branch: true` (branch is cleaned up once the PR is merged/closed)
   - `commit-message` / `title`: `Composers updates`
   - `labels: dependencies` (existing repo label)
   - No explicit diff-check needed: the action itself no-ops (no branch push, no PR) when
     there is no diff to commit.

**Hardening:** top-level `permissions: contents: read` (the default `GITHUB_TOKEN` is
unused for writes since all pushes/PR operations go through `PAT_TOKEN`) and a
`concurrency` group (`composer-updates`, `cancel-in-progress: true`) so an ad-hoc
`workflow_dispatch` run can't race the daily `schedule` run over the same
`chore/composer-updates` branch.

## Secrets

- `PAT_TOKEN` (already agreed): a fine-grained Personal Access Token scoped to this repo
  only, with `contents: write` and `pull-requests: write` permissions. Must be added as a
  repository secret before the workflow can push/open PRs. This is a manual, one-time
  action item outside the scope of the code change itself.

## Failure handling

- A `composer update` failure (e.g. dependency conflict) fails the whole job; standard
  GitHub Actions failure notification (email) is the signal — no extra alerting needed.
- No changes → no branch push → no PR → no notification (silent no-op), same as
  Dependabot's behavior on a no-op day.

## Testing / validation

This is a CI-only change with no PHP application code involved — validation is a
`workflow_dispatch` manual run after merge (and confirming a PR gets opened/updated and
that its checks run correctly), plus reviewing the workflow YAML against the existing
composite actions it reuses (`local-php`).
