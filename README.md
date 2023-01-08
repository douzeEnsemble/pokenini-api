# Pokémon Living/Alternate/Gender Extended Dex!

## To Begin

### TL;DR

```
make stop install start quality tests integration
```

or

```
make quality tests integration
```

### Install

```
make install start
```

### Restart

```
make stop start
```

## Labels, games and dex

```
php bin/console app:update:labels
php bin/console app:update:games_and_dex
```

## Pokémons

### Import pokemon list

```
docker-compose exec php sh -c '
    php bin/console app:update:pokemons
'
```

### Import regional dex number list

```
docker-compose exec php sh -c '
    php bin/console app:update:regional_dex_numbers
'
```

### Import bulbapedia's games' availabilty list

```
docker-compose exec php sh -c '
    php bin/console app:update:games_availabilities
'
```

### Calculate games' bundles' availabilty

Game bundle availability are calculated from games' availabilities

```
docker-compose exec php sh -c '
    php bin/console app:calculate:game_bundles_availabilities
'
```

### Calculate dex' availabilty

Dex availability are calculated from dex rules

```
docker-compose exec php sh -c '
    php bin/console app:calculate:dex_availabilities
'
```

### Tips

### Open bash into php  container

```
make sh
```

`exit` in it to quit.

### Composer

To install a package

```
make composer c="require gedmo/doctrine-extensions"
```

### Reset database and migrations

Reset database and redo all migrations
```
make init_db
'
```

Generate full migration as database is empty. You will have to copy to the first one to avoid issues
```
docker-compose exec php sh -c '
    php bin/console doct:migr:diff --from-empty-schema --no-interaction
'
```

To execute a migration over and over
```
docker-compose exec php sh -c '
    php bin/console doct:migr:exec 'DoctrineMigrations\\Version20221113212114' --down --no-interaction && php bin/console doct:migr:exec 'DoctrineMigrations\\Version20221113212114' --up --no-interaction
'
```
