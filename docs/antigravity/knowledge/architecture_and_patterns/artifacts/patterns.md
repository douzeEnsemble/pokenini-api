# Architecture and Patterns

The project follows a modern Symfony architecture with a strong emphasis on strict typing and functional separation.

## Layered Structure

1.  **Controllers** (`src/Controller`): Very thin, mostly handling request mapping to DTOs and delegating processing to dedicated services. Uses `#[Route]` attributes.
2.  **Services** (`src/Service`): Core business logic resides here. Services are highly specialized and focused (functional naming).
3.  **DTOs** (`src/DTO`): Used as rigid data structures to pass data between controllers, services, and repositories. Often includes validation in constructors.
4.  **Repositories** (`src/Repository`): Doctrine repositories for standard data access.
5.  **Entities** (`src/Entity`): Doctrine ORM entities mapped to the PostgreSQL database schemas.
6.  **Pipeline Services** (`src/ActionStarter`, `src/ActionEnder`, `src/Updater`, `src/Calculator`): Specialized services responsible for synchronizing, transforming, and persisting data, often connected to the external source of truth (Google Sheets).

## Coding Standards

- **Strict Typing**: Every PHP file uses `declare(strict_types=1);`.
- **PHP 8.4 Features**: The codebase extensively uses modern features like constructor property promotion, readonly properties/classes, and strictly typed returns.
- **Service Injection**: Dependency injection is heavily used through constructors.
- **Error Handling**: Use of standard Symfony HTTP exceptions and custom exceptions in `src/Exception`.

## Key Patterns

- **Functional Services**: Instead of large monolithic services, the project favors many small, granular classes (e.g., specific Elo calculators or updaters).
- **CQRS / Message Bus**: Operations that can run asynchronously or require decoupled execution are handled via Symfony Messenger (`src/Message` and `src/MessageHandler`).
- **DTO-based Validation**: Controllers don't receive raw arrays but validated DTOs, ensuring robust typing upstream.
