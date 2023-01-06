# Pokémon Living/Alternate/Gender Extended Dex!

## To Begin

### TL;DR

```
make stop build start && \
docker-compose exec php sh -c '
    php bin/console doct:data:drop --force --if-exists && php bin/console doct:data:c && php bin/console doct:migr:mig --no-interaction
    php bin/console doct:data:drop --force --if-exists --env=test && php bin/console doct:data:c --env=test && php bin/console doct:migr:mig --no-interaction --env=test
    php bin/console app:update:labels
    php bin/console app:update:games_and_dex
    php bin/console app:update:pokemons
    php bin/console app:update:regional_dex_numbers
    php bin/console app:update:games_availabilities
    php bin/console app:calculate:game_bundles_availabilities
    php bin/console app:calculate:dex_availabilities
    echo "Finished"
' && \
make quality tests integration
```

or

```
docker-compose exec php sh -c '
    php bin/console doct:data:drop --force --if-exists && php bin/console doct:data:c && php bin/console doct:migr:mig --no-interaction
    php bin/console doct:data:drop --force --if-exists --env=test && php bin/console doct:data:c --env=test && php bin/console doct:migr:mig --no-interaction --env=test
    php bin/console app:update:labels
    php bin/console app:update:games_and_dex
    php bin/console app:update:pokemons
    php bin/console app:update:regional_dex_numbers
    php bin/console app:update:games_availabilities
    php bin/console app:calculate:game_bundles_availabilities
    php bin/console app:calculate:dex_availabilities
    echo "Finished"
' && \
make quality tests integration
```

### Install

```
make install start
```

or a hard way
```
docker-compose build --pull --no-cache
docker-compose up -d
```

### Restart

```
docker-compose down --remove-orphans && docker-compose up -d --force-recreate
```

## Labels, games and dex

```
php bin/console app:update:labels
php bin/console app:update:games_and_dex
```

## Pokémons

### Import pokemon list

By default, there is no pokemon, you have to import them

```
docker-compose exec php sh -c '
    php bin/console app:update:pokemons
'
```

### Import regional dex number list

By default, there is no regional dex number, you have to import them

```
docker-compose exec php sh -c '
    php bin/console app:update:regional_dex_numbers
'
```

### Import bulbapedia's games' availabilty list

By default, there is no game availabilty, you have to import them

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
docker-compose exec php sh -c '
    php bin/console doct:data:drop --force --if-exists && php bin/console doct:data:c && php bin/console doct:migr:mig --no-interaction
    php bin/console doct:data:drop --force --if-exists --env=test && php bin/console doct:data:c --env=test && php bin/console doct:migr:mig --no-interaction --env=test
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
