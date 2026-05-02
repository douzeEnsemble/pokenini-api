# Data Management

The application orchestrates complex Pokémon data by using a combination of a persistent Postgresql database (via Doctrine ORM) and a primary source of truth from an external Google Sheet.

## Doctrine ORM and Postgresql

- **Migrations**: Database schema changes are managed via Doctrine Migrations (`make init-db` runs `doctrine:migration:migrate`).
- **Entities**: All database tables are strictly typed Doctrine Entities (`src/Entity`).
- **Repositories**: Standard Doctrine repositories (`src/Repository`) are used for querying data.

## Data Synchronization

The application features a unique synchronization process to load base data (like pokédex entries, availabilities, games) into the application.

- **Google Sheets API**: Data is fetched using the Google API Client (`google/apiclient`).
- **Updaters**: Classes in `src/Updater` and `src/Calculator` handle data mapping and transformation from external API objects into internal Entities.
- **Commands**: The `Makefile` defines `make data-app` which runs a sequence of Symfony console commands:
    - `app:update:labels`
    - `app:update:games_collections_and_dex`
    - `app:update:pokemons`
    - `app:update:regional_dex_numbers`
    - `app:update:games_availabilities`
    - `app:update:games_shinies_availabilities`
    - `app:update:collections_availabilities`
    - `app:calculate:game_bundles_availabilities`
    - `app:calculate:game_bundles_shinies_availabilities`
    - `app:calculate:dex_availabilities`
    - `app:calculate:pokemon_availabilities`

These synchronization commands are critical for initializing both the dev environment and ensuring accurate application state.

## NVD API Key

Updating OWAP records requires an NVD API Key. This should be exported in the `.env` configuration (`NVD_API_KEY`).
