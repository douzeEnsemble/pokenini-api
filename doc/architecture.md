# Architecture — pokenini-api

## Vue d'ensemble

Pokénini API est une API REST Symfony 8 qui permet de gérer une collection Pokémon : synchronisation des données depuis Google Sheets, calcul de disponibilités par jeu/dex, et système de classement ELO pour évaluer ses Pokémon préférés.

Fonctionnalités principales :
- Synchronisation des données Pokémon depuis Google Sheets (noms, formes, types, disponibilités)
- Calcul agrégé des disponibilités par game bundle, dex et collection
- Système ELO pour noter les Pokémon (élection, vote, classement)
- Gestion d'album personnel (filtres, statistiques, rapports)
- Endpoints admin pour déclencher des synchronisations asynchrones

---

## Carte des répertoires

```
pokenini-api/
├── src/                         Code source PHP
│   ├── ActionEnder/             Finalisation des ActionLog (trait)
│   ├── ActionStarter/           Création d'ActionLog + instanciation de Message
│   ├── Calculator/              Calculs purs de disponibilités
│   │   └── PokemonAvailabilities/  Sous-calculateurs disponibilités Pokémon
│   ├── Command/                 Commandes CLI Symfony console
│   ├── Controller/              Endpoints REST (attributs #[Route])
│   │   └── Debug/               Endpoints de débogage (non exposés en prod)
│   ├── DTO/                     Query params validés (OptionsResolver) + réponses
│   │   ├── AlbumFilter/         Filtres de l'album
│   │   ├── AlbumReport/         DTO rapport album
│   │   ├── DataChangeReport/    DTO rapport de synchronisation
│   │   └── Response/            DTOs de réponse immutables (sérialisés en JSON)
│   ├── Entity/                  Entités Doctrine ORM
│   │   └── Traits/              Traits réutilisables (UUID, softdelete, nom, ordre...)
│   ├── Exception/               Exceptions métier
│   ├── Factory/                 Transforme données brutes (SQL, entités) → DTOs Response
│   ├── Helper/                  Utilitaires purs (notation A1 Google Sheets)
│   ├── Message/                 Messages Symfony Messenger (POPO)
│   ├── MessageHandler/          Handlers async (orchestrent l'exécution)
│   │   └── Traits/              CalculateHandlerTrait, UpdateHandlerTrait
│   ├── Repository/              Accès données Doctrine (ServiceEntityRepository)
│   │   └── Trait/               FiltersTrait pour SQL dynamique
│   ├── Service/                 Orchestration métier
│   │   ├── Album/               Services lecture album
│   │   ├── CalculatorService/   Façades calculateurs
│   │   └── UpdaterService/      Façades updaters
│   └── Updater/                 Parse Google Sheets → UPSERT SQL natif
│       └── Forms/               Updaters des formes Pokémon
├── tests/
│   ├── src/
│   │   ├── Common/              Traits et données partagés
│   │   ├── Integration/         Tests avec DB réelle + Moco
│   │   └── Unit/                Tests unitaires (mocks)
│   └── resources/moco/Sheets/   Réponses mock Google Sheets (JSON)
├── fixtures/                    Données de test Alice YAML
├── resources/
│   ├── auth/                    credentials.json Google (git-ignoré)
│   └── sql/                     Requêtes SQL natives externalisées
├── tools/                       Outils qualité isolés (chacun son composer.json)
│   ├── deptrac/
│   ├── infection/
│   ├── jsonlint/
│   ├── php-cs-fixer/
│   ├── phpmd/
│   ├── phpstan/
│   ├── psalm/
│   └── coverage/
├── config/                      Configuration Symfony (packages, services, routes)
├── migrations/                  Migrations Doctrine
├── .docker/                     Dockerfiles et configs (php, nginx, moco)
├── .github/workflows/           CI GitHub Actions
└── docker-compose.yaml          Services dev (php, nginx, postgres, moco, newman)
```

---

## Rôle de chaque couche

| Couche | Rôle | Dépendances autorisées | Exemple de fichier |
|--------|------|----------------------|-------------------|
| **Controller** | Endpoints REST, pas de logique métier, reçoit Request et délègue | Service, ActionStarter, Factory, DTO, Serializer | `src/Controller/PokemonsController.php` |
| **Factory** | Transforme données brutes (lignes SQL, entités) en DTOs Response typés | DTO, Entity | `src/Factory/TypeResponseFactory.php` |
| **Service** | Orchestration métier, appels Google Sheets | Calculator, Updater, Repository, DTO | `src/Service/ElectionService.php` |
| **CalculatorService** | Façade pour les calculateurs (pattern Command) | Calculator, Repository, DTO | `src/Service/CalculatorService/DexAvailabilitiesCalculatorService.php` |
| **UpdaterService** | Façade pour les updaters | Updater, Repository | `src/Service/UpdaterService/PokemonsUpdaterService.php` |
| **Calculator** | Calculs purs de disponibilités, sans I/O externe | Repository, Entity, DTO | `src/Calculator/DexAvailabilityCalculator.php` |
| **Updater** | Parse les lignes Google Sheets → UPSERT SQL natif | Entity, Repository, SpreadsheetService | `src/Updater/PokemonsUpdater.php` |
| **Repository** | Accès données Doctrine (ORM + SQL natif DBAL) | Entity, DTO, DBAL | `src/Repository/PokemonsRepository.php` |
| **ActionStarter** | Crée ActionLog + instancie Message | Entity, Message, EntityManager | `src/ActionStarter/UpdatePokemonsActionStarter.php` |
| **ActionEnder** | Finalise ActionLog (rapport ou erreur) | Entity, Repository, DTO | `src/ActionEnder/ActionEnderTrait.php` |
| **MessageHandler** | Reçoit Message, orchestre execution async | Service, Updater, Calculator, ActionEnder | `src/MessageHandler/UpdatePokemonsHandler.php` |
| **Command** | Entrée CLI, déclenche directement un flux complet | ActionStarter, ActionEnder, Service | `src/Command/UpdatePokemonsCommand.php` |
| **DTO** | Validation des inputs (OptionsResolver) + shapes de réponses | OptionsResolver | `src/DTO/TrainerPokemonEloQueryOptions.php` |
| **Entity** | Entités Doctrine, propriétés publiques, sans logique | ORM, UUID | `src/Entity/Pokemon.php` |
| **Message** | POPO transporté par Messenger | *(aucune)* | `src/Message/UpdatePokemons.php` |

**Contrôle Deptrac** : toute violation de dépendance inter-couches est bloquante en CI (`deptrac.yaml`).

---

## Flux typique : synchronisation Google Sheets (async)

```
POST /istration/update/pokemons
  → AdminUpdateController
    → UpdatePokemonsActionStarter::start()
      → crée ActionLog en DB
      → retourne UpdatePokemons message
    → MessageBus::dispatch(message)
      → (réponse HTTP 200 immédiate)

[Worker Symfony Messenger]
  → UpdatePokemonsHandler::__invoke(message)
    → PokemonsUpdaterService::execute()
      → SpreadsheetService::getValues()   ← appel Google Sheets API
      → PokemonsUpdater::execute()
        → lit header, valide colonnes
        → pour chaque ligne → upsertRecord() → SQL UPSERT natif DBAL
    → ActionEnderTrait::endActionLog(message, report)
      → met à jour ActionLog avec rapport JSON + doneAt
```

---

## Flux typique : vote ELO

```
POST /election/vote
  → ElectionVoteController
    → ElectionVote DTO (OptionsResolver sur le body JSON)
    → ElectionService::vote(dto)
      → ElectionUpdateEloService::update(dto)
        → TrainerPokemonEloRepository::findOrCreate(trainer, pokemon)
        → calcul ELO (K-factor, D-difference)
        → persist + flush
      → retourne ElectionVoteResult DTO
    → JsonResponse via Serializer
```

---

## Flux typique : calcul de disponibilités (CLI)

```
bin/console app:calculate:dex_availabilities
  → CalculateDexAvailabilitiesCommand
    → CalculateDexAvailabilitiesActionStarter::start()
    → CalculateDexAvailabilitiesActionStarter::dispatch() via Messenger (sync en CLI)
      → CalculateDexAvailabilitiesHandler
        → DexAvailabilitiesCalculatorService::execute()
          → DexAvailabilitiesCalculator::calculate()
            → pour chaque Dex → DexAvailabilityCalculator::calculate(dex)
              → pour chaque Pokemon → DexPokemonAvailabilityCalculator::calculate()
              → persist DexAvailability + flush + clear
    → ActionEnderTrait::endActionLog()
```

---

## Points d'entrée

| Type | Fichier/URL | Détail |
|------|-------------|--------|
| HTTP GET | `/pokemons/to_choose` | Sélection de N Pokémon pour l'album |
| HTTP GET | `/album` | Index de l'album (avec filtres) |
| HTTP POST | `/album` | Upsert d'un état de catch |
| HTTP GET | `/dex` | Liste des dex |
| HTTP GET | `/game-bundles` | Liste des game bundles avec disponibilités |
| HTTP POST | `/election/vote` | Soumission d'un vote ELO |
| HTTP GET | `/election/can-hold` | Vérifie si un dex peut tenir une élection |
| HTTP POST | `/istration/update/*` | Déclenchement d'une synchro async (admin) |
| HTTP POST | `/istration/calculate/*` | Déclenchement d'un calcul async (admin) |
| CLI | `bin/console app:update:pokemons` | Sync Pokémon depuis Google Sheets |
| CLI | `bin/console app:calculate:dex_availabilities` | Recalcul des dispo dex |
| CLI | `bin/console app:update:games_collections_and_dex` | Sync jeux, collections, dex |

---

## Dépendances entre modules

| Couche | Peut dépendre de |
|--------|-----------------|
| Controller | Service, ActionStarter, Factory, DTO, Serializer, Messenger |
| Factory | DTO, Entity |
| Command | ActionStarter, ActionEnder, Service, EntityManager |
| Service | Calculator, Updater, Repository, DTO, Google API |
| CalculatorService | Calculator, Repository |
| UpdaterService | Updater |
| Calculator | Repository, Entity |
| Updater | Entity, Repository, SpreadsheetService, DBAL |
| MessageHandler | Service, Updater, Calculator, ActionEnder, Repository |
| ActionStarter | Entity (ActionLog), Message, EntityManager |
| ActionEnder | Entity (ActionLog), Repository |
| Repository | Entity, DTO, DBAL, Doctrine ORM |
| DTO | OptionsResolver, Symfony Serializer |
| Entity | Doctrine ORM, UUID |
| Message | *(aucune dépendance applicative)* |

---

## Environnements et infrastructure

| Env | Base de données | Google Sheets | Particularité |
|-----|----------------|---------------|---------------|
| `dev` | PostgreSQL local (`app`) | Vraie API (credentials requis) | Xdebug activable |
| `test` | PostgreSQL (`app_test`) | Moco (`moco.sheets.test`) | Fixtures Alice |
| `int` | PostgreSQL (`app_int`) | Moco (`moco.sheets.int`) | Tests Newman |
| `prod` | PostgreSQL externe | Vraie API | Opcache, no Xdebug |
| `ci` | PostgreSQL (docker-compose) | Moco | Credentials secrets GitHub |

---

## Docker / services

| Service | Image | Version | Rôle |
|---------|-------|---------|------|
| `php` | `php:fpm-alpine` (custom) | 8.5.5 | Exécution PHP + Composer + Symfony CLI |
| `web` | `nginx:alpine` | 1.29.8 | Reverse proxy HTTP |
| `database` | `postgres:alpine` | 14.22 | Base de données principale |
| `adminer` | `adminer:fastcgi` | 5.4.2 | Interface DB (dev) |
| `moco.sheets.test` | `moco` (custom) | 1.5.0 | Mock serveur Google Sheets (tests unitaires) |
| `moco.sheets.int` | `moco` (custom) | 1.5.0 | Mock serveur Google Sheets (intégration) |
| `newman` | `postman/newman:alpine` | 6.1.3 | Tests API Postman en CI |
