# Architecture and Patterns

The project follows a modern Symfony architecture with a strong emphasis on strict typing and functional separation.

## Layered Structure

1.  **Controllers**: Located in `src/Controller`. They are thin, handling request validation and delegating to services. They use Symfony's `#[Route]` attributes.
2.  **Services**: Located in `src/Service`. This is where the core business logic resides. Services are often small and focused (functional naming).
3.  **DTOs**: Located in `src/DTO`. Used to pass structured data between controllers, services, and repositories. They often include validation logic in their constructors.
4.  **Repositories**: Located in `src/Repository`. Doctrine repositories used for data access.
5.  **Entities**: Located in `src/Entity`. Doctrine ORM entities representing the database schema.
6.  **Updaters**: Located in `src/Updater`. Specialized services for updating data, often from external sources like Google Sheets.

## Coding Standards

- **Strict Typing**: All files should have `declare(strict_types=1);`.
- **PHP 8.4 Features**: The project uses modern PHP features like constructor property promotion and readonly properties.
- **Service Injection**: Extensive use of dependency injection via constructors.
- **Error Handling**: Use of standard Symfony HTTP exceptions in controllers.

## Key Patterns

- **Functional Services**: Instead of large monolithic services, the project favors many small, specialized services (e.g., `ElectionUpdateEloService`).
- **DTO-based Validation**: Data is validated upon DTO creation, ensuring that services only receive valid data.
- **Messenger (CQRS-lite)**: Use of messages and handlers for certain operations (found in `src/Message` and `src/MessageHandler`).
