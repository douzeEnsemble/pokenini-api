# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Start the full environment (build images, install vendors, create DBs, load data)
make start

# Open bash in the PHP container
make sh

# Run a Symfony console command
make sf c="<command>"

# Run a composer command
make composer c="<args>"
```

### Testing
```bash
make tests              # All tests (unit + integration)
make tests-unit         # Unit tests only  (alias: make tu)
make tests-integration  # Integration tests only  (alias: make ti)
make coverage           # Tests + 100% coverage check
make infection          # Mutation testing (100% MSI required)
make measures           # Coverage + infection combined
```

Run a single test or filter by name:
```bash
# Single test file
docker compose exec php php vendor/bin/phpunit tests/src/Unit/DTO/TrainerPokemonEloQueryOptionsTest.php

# Filter by test method name
docker compose exec php php vendor/bin/phpunit --filter testGetForAPublicDex tests/src/Integration/
```

### Quality
```bash
make quality            # All quality checks
make code-quality       # PHP checks (cs-fixer, phpmd, psalm, phpstan, deptrac, jsonlint)
make infra-quality      # Docker/dotenv/editorconfig linting
make phpcsfixer-fix     # Auto-fix code style
```

### Database & Data
```bash
make init-db                                          # Reset all databases
make sf c="doctrine:migration:migrate"                # Run pending migrations
make sf c="doctrine:migration:diff --no-interaction"  # Generate migration from entity changes
make sf c="app:update:pokemons"                       # Sync Pokemon data from Google Sheets
make sf c="app:update:games_collections_and_dex"      # Sync games and dex
make sf c="app:calculate:game_bundles_availabilities" # Recalculate derived availability data
```

### Other
```bash
make integration   # Run Postman/Newman API integration tests (requires full DB init first)
make cc            # Clear Symfony cache (dev + test)
make logs          # Tail Docker logs
make security      # Composer audit + Symfony security checker
```

## Architecture

The codebase follows a strict layered architecture enforced by Deptrac (`deptrac.yaml`). Dependency direction is: **Controller → Service → Calculator → Repository**. Updaters and MessageHandlers sit alongside Services.

### Layer responsibilities

| Layer | Path | Purpose |
|-------|------|---------|
| Controller | `src/Controller/` | REST endpoints (attribute-based routing), no business logic |
| Service | `src/Service/` | Orchestration, Google Sheets integration |
| Factory | `src/Factory/` | Transform raw data (SQL results) into Response DTOs |
| Calculator | `src/Calculator/` | Pure availability and ELO calculations |
| Updater | `src/Updater/` | Parses Google Sheets data and persists entities |
| MessageHandler | `src/MessageHandler/` | Async Symfony Messenger handlers |
| ActionStarter/ActionEnder | `src/ActionStarter/`, `src/ActionEnder/` | Wraps async workflows |
| Repository | `src/Repository/` | Doctrine data access |
| DTO | `src/DTO/` | Query parameter objects and serialized responses |
| Entity | `src/Entity/` | Doctrine ORM mappings, no business logic |
| Command | `src/Command/` | CLI sync and calculation commands |

### `final` conventions
- **`final`**: Controller, DTO, Command, Message, Exception — never subclassed
- **Non-`final`**: Service, Calculator, Repository, Updater — intentionally non-final to allow PHPUnit mocking

### Data flow: Google Sheets sync
```
Google Sheets API
  → SpreadsheetService (reads via Google API Client)
  → Updater (parses rows, maps to entities)
  → Repository (persist)
  → Calculator (derive availability aggregates)
```

### Async flow: Messenger
```
ActionStarter / Controller
  → ActionLog created in DB (tracks the action lifecycle)
  → Symfony Messenger (doctrine transport)
  → MessageHandler
  → Service / Updater / Calculator
  → ActionEnderTrait::endActionLog() — sets doneAt + JSON report on the ActionLog
```

Each async action has a matching quartet: `Message`, `ActionStarter`, `MessageHandler`, and the handler uses `ActionEnderTrait`. See `src/ActionStarter/UpdatePokemonsActionStarter.php` as the canonical example.

### DTOs
DTOs validate their input via `OptionsResolver` (not Symfony Validator constraints). Validation, type coercion, and defaults are all declared in `configureOptions(OptionsResolver $resolver)`. See `src/DTO/TrainerPokemonEloListQueryOptions.php`.

**Response DTOs** (`src/DTO/Response/`) are readonly objects used to serialize data to the client. They're always `final` and immutable via constructor promotion. Transformation from SQL results (which may have mixed types) to Response DTOs is done by **Factories** (`src/Factory/`).

### Factories
Factories transform raw data (typically from SQL queries) into properly-typed Response DTOs. Each factory is a static utility class that normalizes types and handles data mapping. Use factories when:
- SQL query results have mixed types that need casting to DTO constructor expectations
- Complex data transformation from database structure to API response format is needed

See `src/Factory/TypeResponseFactory.php` for the canonical example: it converts SQL rows (with mixed types) to `TypeResponse` DTOs by explicitly casting string fields.

### Entities
Entities use public properties directly — no getters or setters. Shared behavior is composed via traits in `src/Entity/Traits/` (`BaseEntityTrait` provides UUID v4 id + `getIdentifier()`; `SoftDeleteable` adds `deletedAt`).

### Complex SQL queries
Repositories delegate complex queries to SQL files in `resources/sql/` loaded via `SqlFileLoader`. Dynamic filter fragments are injected via a placeholder (`-- {album_filters}`) replaced at runtime by `FiltersTrait`. Add new complex queries as `.sql` files there rather than inline strings.

### Test environments
- **Unit tests** (`tests/src/Unit/`): mocked dependencies, no DB
- **Integration tests** (`tests/src/Integration/`): real DB (`APP_ENV=int`), Moco mock server replacing the Google Sheets API
- **API tests**: Postman collection run via Newman (`make integration`)
- Moco mock responses live in `tests/resources/moco/Sheets/`

## Quality requirements

- **100% code coverage** — enforced by `make coverage`
- **100% Mutation Score Index** — enforced by `make infection`
- **PHPStan level 9** and **Psalm strict** — no untyped properties or return types
- **Deptrac** — no cross-layer dependency violations
- All classes use `declare(strict_types=1)`
- Each test class must have `/** @internal */` + `#[CoversClass(TargetClass::class)]`

Each quality tool is isolated under `tools/<name>/` with its own `composer.json`. Install a tool's dependencies before running it: `make composer c="install --working-dir=tools/phpstan"` (the quality targets in Makefile do this automatically).

## Key environment variables

| Variable | Purpose |
|----------|---------|
| `DATABASE_URL` | PostgreSQL DSN (separate DBs for dev/test/int) |
| `GOOGLE_API_SHEETS_URL` | Google Sheets base URL (points to Moco in test/int) |
| `SPREADSHEET_ID` | Google Sheet ID for data source |
| `GOOGLE_CREDENTIALS_PATH` | Path to `resources/auth/credentials.json` (git-ignored) |
| `MESSENGER_TRANSPORT_DSN` | Doctrine-backed async transport |
| `ELO_DEFAULT`, `ELO_K_FACTOR`, `ELO_D_DIFFERENCE` | ELO rating config |
| `WEB_PASSWORD` | Bcrypt-hashed password for the single API user (`web`) |
| `ICON_API_PASSWORD` | Bcrypt-hashed password for the scoped `icon` user (`ROLE_ICON`, access to `/istration/dex-banner-layers` only) |

## Debugging tip

To capture HTML from a failed integration test:
```php
file_put_contents('tests/last.html', $client->getCrawler()->html());
```
