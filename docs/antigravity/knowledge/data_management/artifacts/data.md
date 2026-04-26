# Data Management

The project uses a hybrid approach to data management: a relational database for application state and Google Sheets as a primary source for reference data.

## Relational Database (Doctrine ORM)

- **ORM**: Doctrine ORM 3.x.
- **Migrations**: Symfony Migrations are used to manage schema changes.
- **Strict Typing**: Entities use PHP 8.4 features and strict typing for properties.
- **Repositories**: Custom repository methods are used for complex queries.

## External Data Synchronization (Google Sheets)

A significant portion of the data (Pokémon species, game lists, dex lists) is synchronized from a Google Spreadsheet.

- **SpreadsheetService**: Handles the connection to the Google Sheets API.
- **Updaters**: Specialized classes (extending `AbstractUpdater`) handle the mapping between spreadsheet rows and database records.
- **Upsert Strategy**: Updaters use `INSERT ... ON CONFLICT (slug) DO UPDATE` to ensure data is updated if it already exists, preventing duplicates.
- **Soft Deletion**: Updaters often handle a `deleted_at` column to remove records that are no longer in the spreadsheet.

## Key Update Commands

Data synchronization is triggered via Symfony console commands (defined in `src/Command`), which are also available via `make data-app`.

- `app:update:pokemons`: Syncs Pokémon species data.
- `app:update:games_collections_and_dex`: Syncs reference data for games and dexes.
- `app:calculate:*`: Runs post-sync calculations (e.g., availabilities).
