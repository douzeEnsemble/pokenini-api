# Quality Tools and Testing

The project maintains extremely high quality standards, enforced through various static and dynamic analysis tools.

## Static Analysis

- **Psalm**: Configured at `errorLevel="1"` (strictest). Used to ensure type safety and find potential bugs. Suppressions are rare and well-documented.
- **PHPStan**: Also used for secondary static analysis at a high level.
- **PHP-CS-Fixer**: Enforces coding standards (PSR-12 and additional custom rules).
- **Deptrac**: Enforces architectural boundaries, preventing unwanted dependencies between layers (e.g., ensuring entities don't depend on controllers).
- **PHPMD**: Detects code smells and complex code blocks.

## Testing Strategy

- **PHPUnit**: Used for both Unit and Integration tests.
- **Coverage**: The project aims for **100% code coverage**. This is enforced in the `Makefile`.
- **Mutation Testing (Infection)**: The project aims for **100% Mutation Score Indicator (MSI)**. This ensures that the tests are actually effective at catching regressions, not just covering lines.
- **API Mocking**: Integration tests often use mocked external services (e.g., Google Sheets API) via tools like `moco`.

## Common Commands

- `make quality`: Runs all quality checks (infra and code).
- `make tests`: Runs all PHPUnit tests.
- `make coverage`: Generates coverage reports and checks for 100% coverage.
- `make infection`: Runs mutation testing and checks for 100% MSI.
- `make phpcsfixer-fix`: Automatically fixes coding style issues.
