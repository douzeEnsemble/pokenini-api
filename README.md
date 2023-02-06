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

### Process

#### Change a pokemon slug

1. Look, in "Dex" sheet, for the slug. Replace it by the new one
2. Look, in "Pokémons" sheet, for the slug.
    a. For icon name,
        1. you will need to change it into the sheet into "Icon" column
        2. if not automatically updated, change it into "Sprites url"
        3. if not automatically updated, change it into "Shiny Sprites url"
        4. and into the icon repository, use the copy method to avoid missing image
3. Check if the new slug is not used
```sql
SELECT		*
FROM			pokemon
WHERE			slug = 'new-slug'
```
4. Execute this query to change the slug
```sql
UPDATE 	pokemon
SET		slug = 'new-slug'
WHERE 	slug = 'old-slug'
```
5. Check that the new slug is uptodate
```sql
SELECT		*
FROM			pokemon
WHERE			slug = 'new-slug'
```
6. Update pokemons data in https://www.pokenini.fr/fr/istration page
7. Check into an album if slug is ok by checkinh html source code
7. Check into an album if icon is ok by checkinh html source code
8. Delete original icon name into the icon repository