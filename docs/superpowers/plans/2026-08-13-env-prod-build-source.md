# Replace `.env.prod` with `.env.dev` as Docker build source — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stop shipping a fake-secrets `.env.prod` file whose only purpose is being renamed to `.env` during the Docker build; use the already-maintained `.env.dev` for that instead, and delete `.env.prod`.

**Architecture:** One-line change in the `php_prod` stage of `.docker/php/Dockerfile` (`mv .env.prod .env` → `mv .env.dev .env`), followed by removing `.env.prod` from the repo. No application code, no runtime behavior change — the file is still deleted before the image is finalized (`rm .env && touch .env`), and the container still receives real config from the deploying orchestration at runtime.

**Tech Stack:** Docker multi-stage build, Symfony 8 Dotenv component.

## Global Constraints

- Do not commit anything. Leave all changes in the working tree for the user to review and commit themselves. Skip every "Commit" step that would normally appear in this plan.
- No git history rewriting, no force-push, no destructive git commands.
- Verification is a `docker build`, not a PHPUnit run — there is no test suite step here.

See `docs/superpowers/specs/2026-08-13-env-prod-build-source-design.md` for the full rationale, including why a bare deletion of `.env.prod` was already tried once (in `pokenini-back`) and had to be reverted.

---

### Task 1: Switch the `php_prod` build source and remove `.env.prod`

**Files:**
- Modify: `.docker/php/Dockerfile:97`
- Delete: `.env.prod`
- Modify: `.dockerignore` (`!.env.prod` → `!.env.dev`, required or the build fails)

**Interfaces:** None — this is a standalone infrastructure change with no code consumers.

- [ ] **Step 1: Edit the Dockerfile**

In `.docker/php/Dockerfile`, line 97, change:

```dockerfile
RUN mv .env.prod .env && chown www-data:www-data .env
```

to:

```dockerfile
RUN mv .env.dev .env && chown www-data:www-data .env
```

(`.env.dev` is already present in the build context via the preceding `COPY . ./`, so no other Dockerfile line needs to change.)

- [ ] **Step 2: Delete `.env.prod`**

Run: `rm .env.prod`

This leaves the deletion unstaged in the working tree — do not `git add` or `git rm` it; the user will handle staging/commit themselves.

- [ ] **Step 3: Build the prod image and check the build log**

Run: `make img-build`

Expected: build succeeds. In the build log, find the `cache:clear` step (part of `composer run-script --no-dev post-install-cmd`) and confirm its output explicitly names the **"prod"** environment, e.g. a line containing `Cache for the "prod" environment`. This confirms Docker's `ENV APP_ENV=prod` (set earlier in the Dockerfile) still wins over the `APP_ENV=dev` line now present in the copied `.env.dev`, i.e. Symfony's Dotenv did not override the already-set environment variable.

If the log instead shows the "dev" environment, STOP — this would mean the override assumption in the design doc is wrong and the change needs rethinking; do not proceed to Step 4.

- [ ] **Step 4: Verify the final image ships an empty `.env`**

Run: `docker run --rm ghcr.io/douzeensemble/pokenini-api:latest cat .env`

Expected: empty output (0 bytes). This confirms the pre-existing `RUN rm .env && touch .env` cleanup step still runs after the copied `.env.dev` content was consumed for cache warmup, exactly as it did before with `.env.prod`.

- [ ] **Step 5: Leave changes uncommitted**

Do not run `git add` or `git commit`. Run `git status` to show the user the pending changes (modified `Dockerfile`, deleted `.env.prod`) and stop here.
