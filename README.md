# Pokémon Living/Alternate/Gender Extended Dex!

## To Begin

### Prerequistes

#### NVD API KEY

In order to update OWAP records, you need an NVD API Key. You can request one on https://nvd.nist.gov/developers/request-an-api-key.
Then define it to your env with

```
export NVD_API_KEY=374dc342-3ca3-47a7-8133-794cb256e581
```

### TL;DR

```
make stop build start quality tests integration
```
or
```
make quality tests integration
```

### Install

```
make start
```

### Restart

```
make stop start
```

## Tips

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

### Debug easily

To save html into a file that you can open with your browser

```php
file_put_contents('tests/last.html', $client->getCrawler()->html());
```

### Adminer

[http://localhost:8082/?pgsql=database&username=app&db=app&ns=public]()

### PHPStan baseline

To update the `phpstan-baseline.neon` file

```
make sh
tools/phpstan/vendor/bin/phpstan --generate-baseline --memory-limit=-1
```

### Docker Image build

```shell
docker login --username RenaudDouze --password ghp_token ghcr.io
```

```shell
docker build --target php_prod -f ./.docker/php/Dockerfile -t ghcr.io/douzeensemble/pokenini:latest .
docker push ghcr.io/douzeensemble/pokenini:latest
```
or

```shell
make img-build
```

### Restore Postgresl dump (pg_dump)

```shell
cat postgresql_database.dump | docker compose exec -iT database pg_restore -U app -d app --no-privileges --no-owner -x
```

```shell
cat postgresql_database.dump | docker exec -i $(docker ps --filter name=pokenini-release_database --format "{{.ID}}") pg_restore -U app -d app --no-privileges --no-owner -x
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

### Send and Consume a message

```
make sf c="messenger:stop-workers" && \
curl -X POST --insecure  "http://localhost/istration/update/labels" \
    -H 'Authorization: Basic d2ViOmRvdXpl' \
    -H 'cache-control: no-cache' && \
make sf c="messenger:consume async -vv --limit=1"
```

### Process

#### Change a pokemon slug

1. Look, in "Dex" sheet, for the slug. Replace it by the new one
2. Look, in "Games Availability" sheet, for the slug. Replace it by the new one
3. Look, in "Games Shinies Availability" sheet, for the slug. Replace it by the new one
4. Look, in "Collections Availability" sheet, for the slug. Replace it by the new one
5. Look, in "Regional Dex Number" sheet, for the slug. Replace it by the new one
6. Look, in "Pokémons" sheet, for the slug.
    a. For icon name,
        1. you will need to change it into the sheet into "Icon" column
        2. if not automatically updated, change it into "Sprites url"
        3. if not automatically updated, change it into "Shiny Sprites url"
        4. if not automatically updated, change it into "PokemonDB icon name"
        5. and into the icon repository, use the copy method to avoid missing image
```
mv images/big/regular/pumpkaboo.png images/big/regular/pumpkaboo-average.png
mv images/big/regular/pumpkaboo.webp images/big/regular/pumpkaboo-average.webp
mv images/big/shiny/pumpkaboo.png images/big/shiny/pumpkaboo-average.png
mv images/big/shiny/pumpkaboo.webp images/big/shiny/pumpkaboo-average.webp
mv images/small/regular/pumpkaboo.png images/small/regular/pumpkaboo-average.png
mv images/small/regular/pumpkaboo.webp images/small/regular/pumpkaboo-average.webp
mv images/small/shiny/pumpkaboo.png images/small/shiny/pumpkaboo-average.png
mv images/small/shiny/pumpkaboo.webp images/small/shiny/pumpkaboo-average.webp
```

1. Check if the new slug is not used
```sql
SELECT		*
FROM			pokemon
WHERE			slug = 'new-slug'
```
1. Execute this query to change the slug
```sql
UPDATE 	pokemon
SET		slug = 'new-slug'
WHERE 	slug = 'old-slug'
```
1. Check that the new slug is uptodate
```sql
SELECT		*
FROM			pokemon
WHERE			slug = 'new-slug'
```
1. Update pokemons data in https://www.pokenini.fr/fr/istration page
2. Check into an album if slug is ok by checking html source code
3. Check into an album if icon is ok by checking html source code
4. Delete original icon name into the icon repository

### Debug 

### Check if json are valid or not

Dans le container (`make sh`)

``` bash
find tests/resources/moco -type f -name "*.json" -exec tools/jsonlint/vendor/bin/jsonlint {} \;
```
