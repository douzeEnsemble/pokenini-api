# Project Overview: pokenini-api

The `pokenini-api` is a backend application built with **Symfony 8.0** and **PHP 8.4**. It appears to be a Pokémon-related data management system, possibly related to tracking collections, elections (Elo-based rankings), and pokédex data.

## Technology Stack

- **PHP**: 8.4+
- **Framework**: Symfony 8.0
- **Database / ORM**: Doctrine ORM 3.x
- **Async Processing**: Symfony Messenger
- **External API**: Google Sheets API (used for data synchronization)
- **Quality Tools**:
    - **Psalm**: Static analysis with strict typing.
    - **PHPStan**: Static analysis.
    - **PHP-CS-Fixer**: Coding style enforcement.
    - **PHPUnit**: Unit and integration testing.
    - **Infection**: Mutation testing.
    - **Deptrac**: Dependency analysis.

## Core Domain Entities

Based on the `src/Entity` directory, the core entities include:
- `Pokemon`
- `Dex`
- `Election`
- `Trainer` (likely users/collectors)
- `Album` (likely collections)
- `Game`, `GameBundle`

## Infrastructure

- **Docker**: The project includes a Docker setup (`.docker/`, `docker-compose.yaml`).
- **CI/CD**: GitHub Actions workflows are present.
- **Makefile**: Comprehensive Makefile for development tasks.
