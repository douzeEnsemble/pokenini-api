# Conventions de codage — pokenini-api

## Langage et framework

- **PHP 8.5** (image `php:8.5.5-fpm-alpine3.23`)
- **Symfony 8.0** (tous les composants en `8.0.*`)
- **Doctrine ORM 3.6+** avec migrations
- Outils qualité actifs (tous en standalone dans `tools/`) :
  - `php-cs-fixer` — formatage
  - `phpstan` niveau 9 — analyse statique
  - `psalm` strict — analyse statique complémentaire
  - `phpmd` — détection de code complexe
  - `deptrac` — contrôle des dépendances inter-couches
  - `infection` — tests de mutation (100 % MSI requis)
  - `jsonlint` — validation JSON

## Style de code

Formatter : **PHP-CS-Fixer** avec les rulesets `@PER-CS`, `@Symfony`, `@PSR12`, `@PhpCsFixer`, `@PHP83Migration`, `declare_strict_types`.

Config de référence : `.php-cs-fixer.dist.php`

Commandes :
```bash
make code-quality     # contrôle complet
make phpcsfixer-fix   # auto-fix
```

## Nommage

| Élément | Convention | Exemple |
|---------|-----------|---------|
| Classes | PascalCase + suffixe imposé | `PokemonsRepository`, `ElectionService` |
| Méthodes | camelCase | `getNPokemonsToChoose()` |
| Variables | camelCase | `$trainerExternalId` |
| Constantes | UPPER\_SNAKE\_CASE | *(non observées dans le code métier)* |
| Fichiers | PascalCase, 1 classe par fichier | `PokemonsRepository.php` |
| Tables DB | snake\_case | `pokemon`, `game_bundle` |
| Colonnes DB | snake\_case | `national_dex_number`, `french_name` |

### Suffixes imposés par couche

| Couche | Suffixe obligatoire |
|--------|-------------------|
| Contrôleurs | `Controller` |
| Services | `Service` |
| Services calculateur | `CalculatorService` |
| Services updater | `UpdaterService` |
| Repositories | `Repository` |
| Calculateurs | `Calculator` |
| Updaters | `Updater` |
| Handlers | `Handler` |
| ActionStarters | `ActionStarter` |
| Messages | *(nom de l'action, ex. `UpdatePokemons`)* |
| DTOs de requête | `QueryOptions` |
| DTOs de réponse | `Response` |
| Factories | `Factory` (le plus souvent `…ResponseFactory`) |
| Tests | `Test` |

### Avertissement connu

La propriété `$statictic` (faute de frappe persistante pour `$statistic`) est présente dans :
- `src/Updater/AbstractUpdater.php:27`
- `src/Calculator/AbstractCalculator.php:13`

## Structure des fichiers

```
src/
├── ActionEnder/      Traits pour finaliser les ActionLogs
├── ActionStarter/    Crée l'ActionLog et instancie le Message
├── Calculator/       Calculs purs (disponibilités, ELO)
├── Command/          Commandes CLI (console)
├── Controller/       Endpoints REST (routing par attributs)
│   └── Debug/        Contrôleurs de débogage
├── DTO/              Objets de requête (OptionsResolver) et réponses sérialisées
├── Entity/           Entités Doctrine + Traits réutilisables
│   └── Traits/       BaseEntityTrait, SoftDeleteable, NamedTrait, etc.
├── Exception/        Exceptions métier
├── Helper/           Utilitaires purs (A1Notation)
├── Message/          Messages Symfony Messenger (POPO)
├── MessageHandler/   Handlers async (orchestrent Service/Updater/Calculator)
│   └── Traits/       CalculateHandlerTrait, UpdateHandlerTrait
├── Repository/       Accès données Doctrine
│   └── Trait/        FiltersTrait (filtres SQL dynamiques pour album)
├── Service/          Orchestration métier + appels Google Sheets
│   ├── Album/        Services de lecture de l'album
│   ├── CalculatorService/ Façades pour les calculateurs
│   └── UpdaterService/    Façades pour les updaters
└── Updater/          Parse les données Google Sheets → UPSERT SQL natif
    └── Forms/        Updaters spécialisés pour les formes Pokémon

tests/src/
├── Common/           Traits et données partagés entre tests
├── Integration/      Tests avec vraie DB + Moco (APP_ENV=int/test)
│   ├── Command/
│   ├── Controller/
│   └── Postman/      Collection Newman pour l'API
└── Unit/             Tests unitaires (mocks, sans DB)

resources/
├── auth/             credentials.json Google (git-ignoré)
└── sql/              Requêtes SQL natives (pokemons-get_n_to_pick.sql, etc.)

fixtures/             Données de test Alice (YAML)
tools/                Outils qualité installés en isolation (chaque outil a son composer.json)
```

## Patterns récurrents

### 1. Entités avec propriétés publiques et traits

Les entités n'utilisent pas de getters/setters privés — les propriétés sont `public`.
Les traits réutilisables (`BaseEntityTrait`, `SoftDeleteable`, etc.) sont composés par `use`.

Référence : `src/Entity/Pokemon.php:14-65`, `src/Entity/Traits/BaseEntityTrait.php`

```php
#[ORM\Entity]
final class Pokemon
{
    use BaseEntityTrait;    // id UUID
    use NamedTrait;         // name
    use FrenchNamedTrait;   // french_name
    use SoftDeleteable;     // deleted_at

    #[ORM\Column]
    public string $slug;
}
```

### 2. DTO avec OptionsResolver

Les DTOs de requête valident et normalisent les query params via `OptionsResolver` dans le constructeur.

Référence : `src/DTO/TrainerPokemonEloQueryOptions.php:20-60`

### 3. Pattern ActionStarter / Message / Handler / ActionEnder

Pour chaque opération async :
1. `ActionStarter` crée un `ActionLog` en DB et retourne un `Message`
2. Le `Controller` ou la `Command` dispatch le `Message` via Messenger
3. Le `MessageHandler` exécute le travail et utilise `ActionEnderTrait` pour finaliser l'`ActionLog`

Référence : `src/ActionStarter/AbstractActionStarter.php`, `src/ActionEnder/ActionEnderTrait.php`

### 4. Updaters avec UPSERT SQL natif

Les `Updater` n'utilisent pas Doctrine pour les écritures : ils exécutent des `INSERT ... ON CONFLICT DO UPDATE` natifs PostgreSQL via DBAL pour la performance.

Référence : `src/Updater/PokemonsUpdater.php:50-100`

### 5. SQL dans des fichiers `.sql`

Les requêtes complexes (sélection de Pokémon pour l'album) sont externalisées dans `resources/sql/`.

Référence : `src/Repository/PokemonsRepository.php:90-110`

### 6. Filtres SQL dynamiques dans les repositories

Le `FiltersTrait` injecte dynamiquement des clauses SQL dans le placeholder `-- {album_filters}`.

Référence : `src/Repository/Trait/FiltersTrait.php`

### 7. Réponses API : DTO Response + Factory + Serializer

Chaque endpoint qui retourne du contenu sérialise un DTO immutable de `src/DTO/Response/`
(classe `final`, propriétés `public readonly`, clés snake_case via `#[SerializedName]`
uniquement quand le nom PHP diffère de la clé JSON). Une `Factory` statique de `src/Factory/`
transforme les données brutes (lignes SQL `mixed`, entités, value-objects) en DTOs typés ;
le contrôleur fait `JsonResponse::fromJsonString($serializer->serialize($response, 'json'))`.

Référence : `src/Factory/TypeResponseFactory.php`, `src/Controller/TypesController.php`

## Annotations et style

| Annotation | Usage | Obligatoire |
|-----------|-------|------------|
| `declare(strict_types=1)` | Tous les fichiers PHP | Oui |
| `final` | Toutes les classes concrètes (controllers, commands, etc.) | Oui (standard) |
| `#[\Override]` | Méthodes implémentant une interface ou surchargeant | Oui si applicable |
| `@psalm-suppress PropertyNotSetInConstructor` | Updaters (propriétés initialisées dans `execute()`) | Sur les Updaters/Calculators abstraits |
| `@SuppressWarnings("PHPMD.TooManyFields")` | Entités avec > N champs | Sur les entités volumineuses |
| `/** @var ... */` pour les casts de type | Avant les return statements | Oui si nécessaire pour PHPStan/Psalm |
| `@codeCoverageIgnoreStart/End` | Branches logiquement inatteignables | Uniquement si vraiment impossible |

## Tests

### Nommage
- Classes de test : `Final{SujetTest}Test extends PHPUnit\TestCase` (unit) ou `extends AbstractTestControllerApi` (integration)
- Méthodes : `testVerbeComportement()` en camelCase, ex. `testGetListFromDex()`
- DataProviders : non observés dans les exemples explorés

### Annotations / attributs obligatoires

```php
/**
 * @internal
 */
#[CoversClass(SubjectClass::class)]
final class SubjectClassTest extends TestCase { ... }
```

### Fixtures et données de test

- Fixtures Alice YAML dans `fixtures/` — chargées par `RefreshDatabaseTrait` (hautelook/alice-bundle)
- Mocks HTTP : Moco server (Java) avec des fichiers JSON dans `tests/resources/moco/Sheets/`
- Helpers partagés : traits dans `tests/src/Common/Traits/` (CounterTrait, GetterTrait, HasserTrait, ReportTrait)

## Règles framework spécifiques

- **Routage** : uniquement par attributs PHP (`#[Route]`), jamais via YAML/XML
- **Sérialisation** : Symfony Serializer, pas de librairie tierce
- **Validation des inputs** : `OptionsResolver` dans les DTOs, pas de `#[Assert\...]` sur les entités
- **Sécurité** : HTTP Basic Auth (user `web`, configuré dans `security.yaml`)
- **Cache** : APCu pour le cache applicatif (Google Sheets)
- **Transactions** : `flush()` + `clear()` dans les calculateurs après chaque batch d'entités

## À faire / À éviter

**À faire**
- Déclarer `declare(strict_types=1)` en tête de chaque fichier
- Marquer les classes concrètes `final`
- Utiliser `#[\Override]` sur toute méthode qui implémente/surcharge
- Placer les requêtes SQL complexes dans `resources/sql/` (pas inline)
- Utiliser `@codeCoverageIgnoreStart/End` seulement pour les branches techniquement inatteignables
- Annoter les classes de test avec `@internal` et `#[CoversClass]`

**À éviter**
- Setters/getters sur les entités : les propriétés sont publiques
- Logique métier dans les contrôleurs : déléguer aux services
- Appeler une couche inférieure en sautant une couche (contrôle Deptrac)
- Catch générique `\Exception` sans re-throw ou log structuré
- Valeurs hardcodées pour l'ELO (utiliser `ELO_DEFAULT`, `ELO_K_FACTOR`, `ELO_D_DIFFERENCE`)
