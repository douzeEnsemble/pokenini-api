

# Pokémon Living/Alternate/Gender Extended Dex!

## To Begin

### TL;DR

```
make stop build start && \
docker-compose exec php sh -c '
    php bin/console app:import:pokemon resources/data/pokemon_list.csv
    php bin/console app:import:game_availability resources/data/bulbapedia_availability.csv
    php bin/console app:calculate:game_bundle_availability'
    php bin/console app:calculate:dex_availability'
    echo "Finished"
'
```

or 

```
docker-compose exec php sh -c '
    php bin/console doct:data:drop --force && php bin/console doct:data:c && php bin/console doct:migr:mig --no-interaction
    php bin/console app:import:pokemon resources/data/pokemon_list.csv
    php bin/console app:import:game_availability resources/data/bulbapedia_availability.csv
    php bin/console app:calculate:game_bundle_availability
    php bin/console app:calculate:dex_availability
    echo "Finished"
'
```

### Install

```
docker-compose build --pull --no-cache
docker-compose up -d 
```

### Restart

```
docker-compose down --remove-orphans && docker-compose up -d --force-recreate
```

## Pokémons

### Import pokemon list

By default, there is no pokemon, you have to import them

```
docker-compose exec php sh -c '
    php bin/console app:import:pokemon resources/data/pokemon_list.csv
'
```

### Import bulbapedia's games' availabilty list

By default, there is no game availabilty, you have to import them

```
docker-compose exec php sh -c '
    php bin/console app:import:game_availability resources/data/bulbapedia_availability.csv
'
```

### Calculate games' bundles' availabilty

Game bundle availability are calculated from games' availabilities

```
docker-compose exec php sh -c '
    php bin/console app:calculate:game_bundle_availability
'
```

### Calculate dexes' availabilty

Dex availability are calculated from dex rules

```
docker-compose exec php sh -c '
    php bin/console app:calculate:dex_availability
'
```

### Tips

### Open bash into php  container

```
docker-compose run --rm php /bin/sh
```

`exit` in it to quit.

### Composer

To install a package

```
docker-compose exec php sh -c '
    composer require gedmo/doctrine-extensions
    php bin/console cache:clear
'
```

### Reset database and migrations

Reset database and redo all migrations
```
docker-compose exec php sh -c '
    php bin/console doct:data:drop --force && php bin/console doct:data:c && php bin/console doct:migr:mig --no-interaction & \
    php bin/console doct:data:drop --force --env=test && php bin/console doct:data:c --env=test
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
    php bin/console doct:migr:exec 'DoctrineMigrations\Version20220621220300' --down --no-interaction && php bin/console doct:migr:exec 'DoctrineMigrations\Version20220621220300' --up --no-interaction
'
```
