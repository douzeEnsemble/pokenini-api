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
make tests-unit         # Unit tests only
make tests-integration  # Integration tests only
make coverage           # Tests + 100% coverage check
make infection          # Mutation testing (100% MSI required)
make measures           # Coverage + infection combined
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
make integration   # Run Postman/Newman API integration tests
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
| Calculator | `src/Calculator/` | Pure availability and ELO calculations |
| Updater | `src/Updater/` | Parses Google Sheets data and persists entities |
| MessageHandler | `src/MessageHandler/` | Async Symfony Messenger handlers |
| ActionStarter/ActionEnder | `src/ActionStarter/`, `src/ActionEnder/` | Wraps async workflows |
| Repository | `src/Repository/` | Doctrine data access |
| DTO | `src/DTO/` | Query parameter objects and serialized responses |
| Entity | `src/Entity/` | Doctrine ORM mappings, no business logic |
| Command | `src/Command/` | CLI sync and calculation commands |

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
  → Symfony Messenger (doctrine transport)
  → MessageHandler
  → Service / Updater / Calculator
  → ActionEnder
```

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

## Key environment variables

| Variable | Purpose |
|----------|---------|
| `DATABASE_URL` | PostgreSQL DSN (separate DBs for dev/test/int) |
| `GOOGLE_API_SHEETS_URL` | Google Sheets base URL (points to Moco in test/int) |
| `SPREADSHEET_ID` | Google Sheet ID for data source |
| `GOOGLE_CREDENTIALS_PATH` | Path to `resources/auth/credentials.json` (git-ignored) |
| `MESSENGER_TRANSPORT_DSN` | Doctrine-backed async transport |
| `ELO_DEFAULT`, `ELO_K_FACTOR`, `ELO_D_DIFFERENCE` | ELO rating config |

## Debugging tip

To capture HTML from a failed integration test:
```php
file_put_contents('tests/last.html', $client->getCrawler()->html());
```
