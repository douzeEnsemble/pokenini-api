# Project Overview: pokenini-api

The `pokenini-api` is a backend application built with **Symfony 8.0** and **PHP 8.4**. It manages a Pokémon Living/Alternate/Gender Extended Dex.

## Technology Stack

- **PHP**: 8.4+
- **Framework**: Symfony 8.0
- **Database / ORM**: Doctrine ORM 3.x
- **Async Processing**: Symfony Messenger
- **External API**: Google Sheets API (used extensively for data synchronization via custom commands)

## Running the Application

The project uses Docker for its local environment and provides a comprehensive `Makefile` to interact with it.

### Common Commands
- `make start`: Installs dependencies, builds the docker image, starts the containers, clears cache, and initializes data.
- `make stop`: Stops the docker containers.
- `make destruct`: Destroys the containers and volumes.
- `make sh` / `make bash`: Opens a shell inside the PHP container.
- `make composer c="<command>"`: Runs Composer commands.
- `make sf c="<command>"`: Runs Symfony console commands.

## Architecture & Data
The API seems to heavily rely on syncing data from a Google Sheet (using the NVD API Key/Google Sheets client). Data is then stored in a PostgreSQL database using Doctrine. Waiters, Updaters, and Calculators process this data to manage relationships like game availability, shinies availability, collections, and regional dex numbers.
