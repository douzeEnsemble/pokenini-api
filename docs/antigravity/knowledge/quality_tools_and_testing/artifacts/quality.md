# Quality Tools and Testing

The `pokenini-api` project maintains extremely high quality standards, enforced through various static and dynamic analysis tools configured via the `Makefile`.

## Static Analysis & Linters

- **PHPStan**: Configured at `level: 9` (strictest). Uses extensions for Doctrine, Symfony, and PHPUnit. Command: `make phpstan`.
- **Psalm**: Used for secondary static analysis at a high level with strict typing. Command: `make psalm`.
- **PHP-CS-Fixer**: Enforces coding standards. Commands: `make phpcsfixer` / `make phpcsfixer-fix`.
- **Deptrac**: Enforces architectural boundaries, preventing unwanted dependencies between layers. Command: `make deptrac`.
- **PHPMD**: Detects code smells and complex code blocks. Command: `make phpmd`.
- **Infra Linters**: `hadolint` for Dockerfile, `dotenv-linter` for .env files, `dclint` for docker-compose. Command: `make infra-quality`.

## Testing Strategy

- **PHPUnit**: Used for both Unit and Integration tests. Commands: `make tests-unit` (`tu`), `make tests-integration` (`ti`).
- **Coverage**: The project aims for **100% code coverage**, enforced by `tools/coverage/coverage.php build/coverage/coverage.xml 100 true`. Command: `make coverage`.
- **Mutation Testing (Infection)**: The project strictly requires **100% Mutation Score Indicator (MSI)** and **100% Covered MSI**. This ensures tests are highly robust. Command: `make infection`.
- **API Mocking**: Integration tests use Newman and mocked external services (`moco`) for external API calls like Google Sheets.

## Unified Quality Commands

The main commands to run before pushing code are:
- `make cq` (code-quality): Runs all static analysis and linters.
- `make t` (tests): Runs all PHPUnit tests.
- `make m` (measures): Runs coverage and infection checks.
- `make s` (security): Runs composer audit and symfony security checker.

A common workflow combines them: `make cq t m s`.
