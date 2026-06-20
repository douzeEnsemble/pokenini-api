# Plan — Migration `#[Serialize]` sur les controllers

## Global Constraints

- PHP 8.5, Symfony 8.1, `declare(strict_types=1)` dans chaque fichier
- PHPStan level 9 + Psalm strict : pas de types `mixed` non annotés
- Qualité : `make quality && make measures` doit passer (100% coverage, 100% MSI)
- Pas de nouveaux commentaires sauf si le WHY est non-évident
- Classes `final`, pas de logique dans les controllers

## Contexte

Le projet `pokenini-api` est une API REST Symfony 8.1 dans un container Docker.
Les controllers retournent tous leurs DTOs via `JsonResponse::fromJsonString($serializer->serialize($dto, 'json'))`.
L'attribut `#[Serialize]` de Symfony 8.1 (`Symfony\Component\HttpKernel\Attribute\Serialize`) permet de retourner le DTO directement et laisse le framework sérialiser — comportement identique, moins de boilerplate.

## Task 1 — Appliquer `#[Serialize]` sur toutes les méthodes éligibles

### Méthodes à transformer (18)

| Fichier | Méthode | Nouveau return type |
|---|---|---|
| `src/Controller/ActionLogsController.php` | `get()` | `array` + `@return ActionLogResponse[]` |
| `src/Controller/AlbumIndexController.php` | `index()` | `AlbumIndexResponse` |
| `src/Controller/CatchStatesController.php` | `get()` | `array` + `@return CatchStateResponse[]` |
| `src/Controller/CollectionsController.php` | `get()` | `array` + `@return CollectionResponse[]` |
| `src/Controller/DexCanHoldElectionController.php` | `list()` | `array` + `@return DexResponse[]` |
| `src/Controller/DexController.php` | `list()` | `array` + `@return TrainerDexResponse[]` |
| `src/Controller/ElectionVoteController.php` | `vote()` | `ElectionVoteResultResponse` |
| `src/Controller/FormsController.php` | `get()` | `FormTypesResponse` |
| `src/Controller/GameBundlesController.php` | `get()` | `array` + `@return GameBundleResponse[]` |
| `src/Controller/PokemonsController.php` | `getNPokemonsToChoose()` | `ElectionPokemonsListResponse` |
| `src/Controller/ReportsController.php` | `get()` | `ReportResponse` |
| `src/Controller/TrainerPokemonEloController.php` | `top()` | `array` + `@return ElectionEloResponse[]` |
| `src/Controller/TrainerPokemonEloController.php` | `metrics()` | `ElectionMetricsResponse` |
| `src/Controller/TypesController.php` | `get()` | `array` + `@return TypeResponse[]` |
| `src/Controller/Debug/DebugDexController.php` | `dex()` | `DexDebugResponse` |
| `src/Controller/Debug/DebugDexController.php` | `dexAvailabilities()` | `DexAvailabilitiesResponse` |
| `src/Controller/Debug/DebugPokemonController.php` | `pokemon()` | `PokemonDebugResponse` |
| `src/Controller/Debug/DebugPokemonController.php` | `pokemonAvailabilities()` | `PokemonAvailabilitiesResponse` |

### Transformation à appliquer sur chaque méthode

```php
// AVANT
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '', methods: ['GET'])]
public function get(SomeService $service, SerializerInterface $serializer): JsonResponse
{
    $responses = SomeFactory::fromSqlRows($service->getAll());
    return JsonResponse::fromJsonString($serializer->serialize($responses, 'json'));
}

// APRÈS
use Symfony\Component\HttpKernel\Attribute\Serialize;

#[Route(path: '', methods: ['GET'])]
#[Serialize]
public function get(SomeService $service): array   // ou SomeResponse pour les DTOs objets
{
    return SomeFactory::fromSqlRows($service->getAll());
}
```

**Règles :**
1. Ajouter `#[Serialize]` juste avant la méthode (import `Symfony\Component\HttpKernel\Attribute\Serialize`).
2. Changer le return type : objet DTO → type concret ; tableau de DTOs → `array` avec docblock `/** @return SomeResponse[] */`.
3. Supprimer `SerializerInterface $serializer` des paramètres.
4. Retirer les `use` de `JsonResponse` et `SerializerInterface` si plus utilisés dans le fichier.
5. Retourner le DTO / array directement.

### Méthodes hors scope (NE PAS TOUCHER)

`AdminCalculateController`, `AdminUpdateController`, `AlbumUpsertController`, `DexController::put()`, `DebugPokemonController::pokemonCaches()` — retournent une réponse vide ou non-DTO.

### Validation

```bash
# Dans le container Docker (docker compose exec php ...)
# OU via make :
make quality    # PHPStan, Psalm, CS-Fixer, Deptrac, etc.
make measures   # coverage 100% + mutation 100%
```

Les tests d'intégration existants valident le comportement HTTP — pas besoin de les modifier.
