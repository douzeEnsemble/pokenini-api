# Design — Report par dex sur `GET /dex/{trainerExternalId}/list`

## Contexte

`GET /album/{trainerId}/{dexSlug}` calcule toujours la liste complète des pokémons d'un dex même quand seul le report (total/capturés/non-capturés) est utile. Un endpoint bulk multi-dex qui expose uniquement les reports réduirait la charge pour les écrans qui affichent une vue d'ensemble sur plusieurs dex.

Cas d'usage concret : la page dex-list de pokenini-web (`AlbumDexList/index.html.twig`) affiche une carte par dex sans aucune donnée de progression aujourd'hui. On veut y ajouter un badge "X / Y capturés" par dex, alimenté par `GET /dex/{trainerExternalId}/list` (déjà appelé par cette page), sans passer par N appels à `/album/{trainerId}/{dexSlug}`.

Le volet `/election` évoqué initialement n'est pas concerné : `/election/top` et `/election/metrics` sont déjà deux endpoints séparés avec leurs propres requêtes SQL légères — pas de calcul redondant à corriger là.

## Approche retenue

Enrichir `TrainerDexResponse` (réponse de `/dex/{trainerExternalId}/list`) avec un champ `report`, calculé via deux requêtes SQL groupées par `dex_slug` sur l'ensemble des dex du trainer en une seule fois — plutôt qu'un nouvel endpoint bulk dédié (évite un aller-retour HTTP et une logique de fusion côté back/web).

Alternative écartée : nouvel endpoint `GET /album/{trainerId}/reports` séparé — plus flexible mais ajoute un round-trip HTTP et de la logique de merge côté back/web pour un seul cas d'usage actuel.

## pokenini-api

### Repositories
- `DexAvailabilitiesRepository::getBatchedTotal(trainerExternalId): array<int, array{dex_slug: string, total: int}>`
  Équivalent groupé de `getTotal()`, sans les jointures de filtres (pas de `AlbumFilters` ici, vue d'ensemble non filtrée).
- `PokedexRepository::getBatchedCatchStatesCounts(trainerExternalId): array<int, array{dex_slug: string, slug: string, name: string, french_name: string, color: string, count: int}>`
  Équivalent groupé de `getCatchStatesCounts()`, même simplification (pas de jointures de filtres).

### Service
- `AlbumReportService::getBatch(trainerExternalId): array<string, Report>`
  Assemble les deux requêtes batchées en un `Report` par `dex_slug`, même logique de calcul que `get()` (total, `totalCaught` sur le compte du catch-state `yes`, `totalUncaught` décrémenté pour tout slug ≠ `no`).

### DTO / Factory
- `TrainerDexResponse` : ajoute `public readonly AlbumReportResponse $report` (non-nullable — le coût du calcul batché est negligeable, pas besoin de le rendre optionnel).
- `TrainerDexResponseFactory::fromSqlRow(array $row, Report $report)` et `fromSqlRows(array $rows, array $reports)` : rattachent le report par `dex_slug` (fallback sur un `Report` vide si un dex n'a pas de ligne dans les requêtes batchées — ne devrait pas arriver en pratique mais évite un throw).

### Controller
- `DexController::list()` : appelle `AlbumReportService::getBatch($trainerExternalId)` et passe le résultat à `TrainerDexResponseFactory::fromSqlRows()`.

### Tests
- Tests unitaires des deux nouvelles méthodes de repository (cas zéro-ligne, dex orphelin/sans `trainer_dex`).
- Test unitaire `AlbumReportServiceGetBatchTest`.
- Mise à jour des tests factory (`TrainerDexResponseFactoryTest`) et des fixtures `DexControllerTestData`.
- Test d'intégration `DexControllerTest` vérifiant la présence et la valeur de `report` dans `/dex/{trainerExternalId}/list`.

## pokenini-back

### Cache — correction nécessaire
`GET /dex/{trainerExternalId}/list` est mis en cache par `GetDexListApiService`, taggé `KeyMaker::getDexKey()` + `KeyMaker::getTrainerIdKey($trainerId)`. `ModifyTrainerAlbumService::modifyAlbum()` (appelé à chaque capture/décapture) n'invalide aujourd'hui que le tag `getAlbumKey()` — jamais `getTrainerIdKey($trainerId)`. Une fois `report` ajouté, le badge afficherait une progression périmée après chaque capture jusqu'à expiration du TTL.

Correction : injecter `AlbumCacheInvalidatorService` (singulier, déjà utilisé par `ModifyTrainerDexService`) dans `ModifyTrainerAlbumService` et appeler `->invalidate($dexSlug, $trainerId)` en plus de l'invalidation `AlbumsCacheInvalidatorService` existante. Réutilise un mécanisme déjà en place.

- `GetDexListApiService` : aucun changement (pass-through JSON brut, ne type pas la réponse).

### Tests
- Test unitaire `ModifyTrainerAlbumServiceTest` vérifiant l'appel à `AlbumCacheInvalidatorService::invalidate($dexSlug, $trainerId)`.

## pokenini-web

- `ResponseObject/Album/DexListItem` : ajoute `report: ?Report` (réutilise `ResponseObject/Album/Report.php`, déjà utilisé pour la page album mono-dex).
- `templates/AlbumDexList/_macro.html.twig` : ajoute un badge de progression sur la macro `item()`. La macro `itemElection()` n'est pas concernée — l'élection utilise `/dex/can_hold_election` via `GetElectionDexListApiService`, un chemin entièrement séparé.

### Tests
- Mise à jour des fixtures/tests de rendu du template si applicable.

## Décisions

- `report` est non-nullable et toujours calculé sur `/dex/{trainerExternalId}/list` : pas de paramètre pour le désactiver, le coût des deux requêtes groupées est negligeable comparé au calcul par dex.
- Pas de nouvel endpoint : le report est embarqué dans la réponse existante déjà consommée par la page dex-list.
- Le volet `/election` de la remarque initiale est explicitement hors scope (déjà résolu par la séparation existante `/election/top` / `/election/metrics`).
- La correction de l'invalidation de cache dans `ModifyTrainerAlbumService` fait partie de ce travail : sans elle, la nouvelle donnée `report` serait incorrecte après une capture.
