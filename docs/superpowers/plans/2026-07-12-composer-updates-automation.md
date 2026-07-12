# Composer Updates Automation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a GitHub Actions workflow that runs `composer update --bump-after-update` across the main app and its 6 tool sub-projects daily, then opens/updates a single pull request with the result — a personal Dependabot equivalent.

**Architecture:** One new workflow file, `.github/workflows/composer_updates.yml`, triggered by `schedule` + `workflow_dispatch`. It reuses the existing `./.github/actions/local-php` composite action for PHP setup (no Docker needed), runs 8 sequential `composer update` steps mirroring the `updates` Makefile target, then uses `peter-evans/create-pull-request@v7` with a fixed branch name to open or refresh one PR.

**Tech Stack:** GitHub Actions, Composer, `peter-evans/create-pull-request@v7`, `actions/checkout@v6`.

## Global Constraints

- Bundle all 8 `composer update` calls (main app + `deptrac`, `infection`, `jsonlint`, `php-cs-fixer`, `phpmd`, `phpstan`, `psalm`) into one PR — no per-tool PR splitting.
- Reuse the existing `./.github/actions/local-php` composite action for PHP setup — do not start the Docker compose stack.
- Use `secrets.PAT_TOKEN` (already agreed as the secret name) for both checkout and PR creation, so the resulting PR is not authored by the default `GITHUB_TOKEN` and therefore actually triggers `ci_codequality.yml`, `ci_tests.yml`, and `security.yml` (which listen on `pull_request: ~`).
- Fixed PR branch name `chore/composer-updates` with `delete-branch: true`, so daily runs update the same PR instead of creating new ones.
- Commit/PR title: `Composers updates` (matches historical manual commits, e.g. `7616b28`).
- Apply the existing `dependencies` repo label to the PR.
- Cron: `17 4 * * *` (04:17 UTC daily).
- Per user's standing instruction: do **not** commit any changes automatically. Leave changes staged/unstaged; only commit if the user explicitly asks.

---

### Task 1: Add the `composer_updates.yml` workflow

**Files:**
- Create: `.github/workflows/composer_updates.yml`

**Interfaces:**
- Consumes: `./.github/actions/local-php` (composite action, no inputs, sets up PHP 8.5 with the extensions the repo's composer projects need — already used by `local-composer` and `tools-composer`).
- Consumes: `secrets.PAT_TOKEN` (repository secret, must be created by the user out-of-band; a fine-grained PAT scoped to this repo with `contents: write` + `pull-requests: write`).
- Produces: nothing consumed by other tasks — this is the only task in this plan.

- [ ] **Step 1: Write the workflow file**

```yaml
name: Composer Updates

on:
  schedule:
    - cron: '17 4 * * *'
  workflow_dispatch: ~

jobs:
  composer-updates:
    name: Update Composer dependencies
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v6
        with:
          token: ${{ secrets.PAT_TOKEN }}

      - name: Prepare PHP
        uses: ./.github/actions/local-php

      - name: Update main app dependencies
        run: composer update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=./

      - name: Update deptrac dependencies
        run: composer update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/deptrac

      - name: Update infection dependencies
        run: composer update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/infection

      - name: Update jsonlint dependencies
        run: composer update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/jsonlint

      - name: Update php-cs-fixer dependencies
        run: composer update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/php-cs-fixer

      - name: Update phpmd dependencies
        run: composer update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/phpmd

      - name: Update phpstan dependencies
        run: composer update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/phpstan

      - name: Update psalm dependencies
        run: composer update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/psalm

      - name: Create Pull Request
        uses: peter-evans/create-pull-request@v7
        with:
          token: ${{ secrets.PAT_TOKEN }}
          branch: chore/composer-updates
          delete-branch: true
          commit-message: "Composers updates"
          title: "Composers updates"
          labels: dependencies
```

- [ ] **Step 2: Validate YAML syntax**

Run: `python3 -c "import yaml; yaml.safe_load(open('.github/workflows/composer_updates.yml'))" && echo OK`
Expected: `OK` (no exception)

- [ ] **Step 3: Cross-check against the Makefile's `updates` target**

Run: `grep -A10 '^updates:' Makefile`
Expected: the 8 `--working-dir` values (`./`, `tools/deptrac`, `tools/infection`, `tools/jsonlint`, `tools/php-cs-fixer`, `tools/phpmd`, `tools/phpstan`, `tools/psalm`) match exactly, same order, same flags (`--bump-after-update --with-all-dependencies --optimize-autoloader`), as the 8 `run:` steps just written.

- [ ] **Step 4: Cross-check the reused composite action's path**

Run: `test -f .github/actions/local-php/action.yml && echo FOUND`
Expected: `FOUND` — confirms the `uses: ./.github/actions/local-php` reference in the new workflow resolves to a real action.

- [ ] **Step 5: Stage the new file (do not commit)**

```bash
git add .github/workflows/composer_updates.yml
git status --short
```

Expected: `A  .github/workflows/composer_updates.yml` shown as staged. Do **not** run `git commit` — per the user's standing instruction, commits happen only when explicitly requested.

---

## Post-merge manual follow-up (not an automated task)

These cannot be executed from within this repo/session and are the user's responsibility:

1. Create a fine-grained GitHub PAT scoped to `douzeEnsemble/pokenini-api` with `contents: write` and `pull-requests: write`, and add it as the repository secret `PAT_TOKEN` (Settings → Secrets and variables → Actions).
2. After merging this workflow to `main`, trigger one `workflow_dispatch` run manually (Actions tab) to confirm a PR gets opened against `chore/composer-updates` and that it correctly triggers `ci_codequality`, `ci_tests`, and `security`.
