# Inventaire des dépendances — pokenini-api

## Composer — dépendances runtime

| Dépendance | Version actuelle | Action recommandée |
|-----------|-----------------|-------------------|
| `php` | `>=8.4` (8.5.5 en prod) | OK — PHP 8.5 actif |
| `doctrine/doctrine-bundle` | `^3.2.2` | Surveiller — Doctrine 4.x en préparation |
| `doctrine/doctrine-migrations-bundle` | `^4.0.0` | OK |
| `doctrine/orm` | `^3.6.3` | OK |
| `google/apiclient` | `^2.19.2` | Surveiller — vérifier changelog sécurité |
| `symfony/browser-kit` | `8.0.*` | OK — LTS Symfony 8.0 |
| `symfony/console` | `8.0.*` | OK |
| `symfony/doctrine-messenger` | `8.0.*` | OK |
| `symfony/dotenv` | `8.0.*` | OK |
| `symfony/expression-language` | `8.0.*` | OK |
| `symfony/flex` | `^2.10.0` | OK |
| `symfony/framework-bundle` | `8.0.*` | OK |
| `symfony/http-client` | `8.0.*` | OK |
| `symfony/http-foundation` | `8.0.*` | OK |
| `symfony/messenger` | `8.0.*` | OK |
| `symfony/monolog-bundle` | `^4.0.2` | OK |
| `symfony/options-resolver` | `8.0.*` | OK |
| `symfony/password-hasher` | `8.0.*` | OK |
| `symfony/property-info` | `8.0.*` | OK |
| `symfony/runtime` | `8.0.*` | OK |
| `symfony/security-bundle` | `8.0.*` | OK |
| `symfony/serializer` | `8.0.*` | OK |
| `symfony/translation` | `8.0.*` | OK |
| `symfony/uid` | `8.0.*` | OK |
| `symfony/validator` | `8.0.*` | OK |
| `symfony/yaml` | `8.0.*` | OK |

## Composer — dépendances dev

| Dépendance | Version actuelle | Action recommandée |
|-----------|-----------------|-------------------|
| `hautelook/alice-bundle` | `^2.17.3` | OK |
| `phpunit/phpunit` | `^13.1.8` | OK — version récente |
| `symfony/debug-bundle` | `8.0.*` | OK |
| `symfony/phpunit-bridge` | `8.0.*` | OK |
| `symfony/stopwatch` | `8.0.*` | OK |
| `symfony/var-dumper` | `8.0.*` | OK |
| `zenstruck/messenger-test` | `^1.14.0` | OK |

## Outils qualité (`tools/*/composer.json`)

| Outil | Dépendance | Version actuelle | Action recommandée |
|-------|-----------|-----------------|-------------------|
| deptrac | `deptrac/deptrac` | `^4.6.0` | OK |
| infection | `infection/infection` | `^0.32.7` | OK |
| jsonlint | `seld/jsonlint` | `^1.11` | OK |
| php-cs-fixer | `friendsofphp/php-cs-fixer` | `^3.95.1` | OK |
| phpmd | `phpmd/phpmd` | `^2.15` | OK |
| phpstan | `phpstan/phpstan` | `^2.1.54` | OK |
| phpstan | `phpstan/phpstan-symfony` | `^2.0.15` | OK |
| phpstan | `phpstan/phpstan-doctrine` | `^2.0.21` | OK |
| phpstan | `phpstan/phpstan-phpunit` | `^2.0.16` | OK |
| psalm | `vimeo/psalm` | `6.16.1` (version exacte) | **Surveiller** — version épinglée sans `^`, bloque les mises à jour automatiques |
| psalm | `psalm/plugin-symfony` | `^5.3.0` | OK |
| psalm | `psalm/plugin-phpunit` | `^0.19.7` | OK |

## Docker / Infrastructure

| Service | Image | Version actuelle | Action recommandée |
|---------|-------|-----------------|-------------------|
| php | `php:fpm-alpine` | 8.5.5-fpm-alpine3.23 | OK — version récente |
| nginx | `nginx:alpine` | 1.29.8-alpine3.23 | OK |
| postgres | `postgres:alpine` | 14.22-alpine3.23 | **Mettre à jour** — PostgreSQL 14 entre en EOL fin 2026 ; migrer vers 16 ou 17 |
| adminer | `adminer:fastcgi` | 5.4.2-fastcgi | OK |
| moco | custom | 1.5.0 | Surveiller — vérifier releases upstream |
| newman | `postman/newman:alpine` | 6.1.3-alpine | Surveiller |
| symfony-cli | `ghcr.io/symfony-cli/symfony-cli` | 5.17.1 | Surveiller |

## CI / GitHub Actions

| Outil / Action | Version | Action recommandée |
|---------------|---------|-------------------|
| `actions/checkout` | `v6` | OK — version récente |
| `.github/actions/docker-compose` | locale | N/A — action interne |

## Outils non-gérés par un package manager

| Outil | Version | Action recommandée |
|-------|---------|-------------------|
| `local-php-security-checker` | binaire local dans `tools/php-security-checker/` | Surveiller — vérifier régulièrement les mises à jour du binaire |
| `dclint` | `zavoloklom/dclint:3.1.0-alpine` | OK |
| `dotenv-linter` | `dotenvlinter/dotenv-linter:4.0.0` | OK |
| `hadolint` | `hadolint/hadolint:v2.14.0-alpine` | OK |
| `editorconfig-checker` | `mstruebing/editorconfig-checker:v3.6.0` | OK |

## Variables d'environnement

| Variable | Valeur par défaut | Commentaire sécurité/prod |
|----------|------------------|--------------------------|
| `APP_SECRET` | `$ecretf0rt3st` | **Changer obligatoirement en prod** |
| `DATABASE_URL` | `postgresql://app:!ChangeMe!@database:5432/app` | **Changer mot de passe en prod** |
| `POSTGRES_PASSWORD` | `!ChangeMe!` | **Changer en prod** |
| `GOOGLE_CREDENTIALS_PATH` | `resources/auth/credentials.json` | Fichier git-ignoré, doit exister en prod |
| `SPREADSHEET_ID` | ID de la feuille Google | Configurer via secret CI/CD en prod |
| `GOOGLE_API_SHEETS_URL` | `http://moco.sheets.int` | Pointer vers la vraie API Google en prod |
| `MESSENGER_TRANSPORT_DSN` | `doctrine://default?auto_setup=0` | OK pour prod avec Doctrine transport |
| `ELO_DEFAULT` | *(non défini dans .env)* | Vérifier qu'une valeur est définie en prod |
| `ELO_K_FACTOR` | *(non défini dans .env)* | Vérifier qu'une valeur est définie en prod |
| `ELO_D_DIFFERENCE` | *(non défini dans .env)* | Vérifier qu'une valeur est définie en prod |
| `TRUSTED_PROXIES` | `127.0.0.1` | Adapter selon la topologie réseau en prod |

## Recommandations globales

1. **PostgreSQL 14 → 16/17** : PostgreSQL 14 entre en fin de support fin 2026 ; planifier la migration vers PostgreSQL 16 ou 17 pour rester sur une version maintenue.
2. **Psalm version épinglée** : `vimeo/psalm: 6.16.1` (sans `^`) bloque les mises à jour automatiques — évaluer le passage à `^6.16` pour recevoir les correctifs.
3. **Variables ELO manquantes dans `.env`** : `ELO_DEFAULT`, `ELO_K_FACTOR`, `ELO_D_DIFFERENCE` ne sont pas définies dans le `.env` de base — s'assurer qu'elles sont documentées et présentes dans tous les environnements.
4. **`local-php-security-checker` binaire** : Ce binaire n'est pas géré par un package manager, ce qui peut le laisser obsolète. Envisager de le remplacer par `composer audit` (déjà présent dans `make security`) ou de l'intégrer via un outil géré.

## Commandes de vérification

```bash
# Dépendances Composer principales
docker compose exec php composer outdated

# Audit de sécurité
docker compose exec php composer audit

# Outils qualité
for tool in tools/deptrac tools/infection tools/jsonlint tools/php-cs-fixer tools/phpmd tools/phpstan tools/psalm; do
  echo "=== $tool ===" && docker compose exec php composer outdated -d "$tool"
done
```
