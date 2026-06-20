# Design — `#[Serialize]` sur les controllers

**Date :** 2026-06-20
**Scope :** `pokenini-api` — controllers REST

---

## Objectif

Éliminer le boilerplate `JsonResponse::fromJsonString($serializer->serialize($dto, 'json'))` et l'injection de `SerializerInterface` dans chaque méthode de controller, en utilisant l'attribut `#[Serialize]` de Symfony 8.1.

---

## Contexte

- Symfony 8.1 est installé ; `#[Serialize]` est disponible dans `vendor/symfony/http-kernel/Attribute/Serialize.php`.
- `SerializeControllerResultAttributeListener` intercepte le `ViewEvent` : si la méthode retourne un non-`Response`, il appelle `$serializer->serialize($result, $format)` et construit la `Response` avec `Content-Type: application/json` — identique au comportement actuel.
- Aucun context de sérialisation custom n'est utilisé dans le projet : la migration est directe.

---

## Périmètre

### Méthodes éligibles (18)

| Controller | Méthode | Nouveau return type |
|---|---|---|
| `ActionLogsController` | `get()` | `array` (`@return ActionLogResponse[]`) |
| `AlbumIndexController` | `index()` | `AlbumIndexResponse` |
| `CatchStatesController` | `get()` | `array` (`@return CatchStateResponse[]`) |
| `CollectionsController` | `get()` | `array` (`@return CollectionResponse[]`) |
| `DexCanHoldElectionController` | `list()` | `array` (`@return DexResponse[]`) |
| `DexController` | `list()` | `array` (`@return TrainerDexResponse[]`) |
| `ElectionVoteController` | `vote()` | `ElectionVoteResultResponse` |
| `FormsController` | `get()` | `FormTypesResponse` |
| `GameBundlesController` | `get()` | `array` (`@return GameBundleResponse[]`) |
| `PokemonsController` | `getNPokemonsToChoose()` | `ElectionPokemonsListResponse` |
| `ReportsController` | `get()` | `ReportResponse` |
| `TrainerPokemonEloController` | `top()` | `array` (`@return ElectionEloResponse[]`) |
| `TrainerPokemonEloController` | `metrics()` | `ElectionMetricsResponse` |
| `TypesController` | `get()` | `array` (`@return TypeResponse[]`) |
| `DebugDexController` | `dex()` | `DexDebugResponse` |
| `DebugDexController` | `dexAvailabilities()` | `DexAvailabilitiesResponse` |
| `DebugPokemonController` | `pokemon()` | `PokemonDebugResponse` |
| `DebugPokemonController` | `pokemonAvailabilities()` | `PokemonAvailabilitiesResponse` |

### Méthodes hors scope (inchangées)

Retournent une réponse vide ou non-DTO : `AdminCalculateController` (toutes), `AdminUpdateController` (toutes), `AlbumUpsertController` (toutes), `DexController::put()`, `DebugPokemonController::pokemonCaches()`.

---

## Transformation appliquée à chaque méthode

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
public function get(SomeService $service): array  // ou SomeResponse pour les DTOs objets
{
    return SomeFactory::fromSqlRows($service->getAll());
}
```

**Règles :**
1. Ajouter `#[Serialize]` sur la méthode (import `Symfony\Component\HttpKernel\Attribute\Serialize`).
2. Changer le return type : objet DTO → type concret ; tableau de DTOs → `array` avec `@return SomeResponse[]`.
3. Retirer `SerializerInterface $serializer` des paramètres.
4. Retirer les `use` de `JsonResponse` et `SerializerInterface` si plus utilisés dans le fichier.

---

## Comportement préservé

- **Status HTTP :** `#[Serialize]` defaulte à 200 — identique au `JsonResponse` actuel.
- **Content-Type :** `application/json` — identique (`getMimeType('json')` = `'application/json'`).
- **Corps JSON :** même sérialiseur, même absence de context → sortie identique.

---

## Tests

Aucune modification de tests nécessaire. Les tests d'intégration (`WebTestCase`) vérifient le status code, le Content-Type et la structure JSON — tous inchangés.

Validation finale : `make quality && make measures` dans le container Docker.
