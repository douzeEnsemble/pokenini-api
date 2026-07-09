# Design — Endpoints report pour les dex d'élection, à la manière des albums

## Contexte

Aujourd'hui, la donnée d'élection (top N pokémons par ELO + métriques de complétion) est exposée par deux endpoints séparés et non trainer-scopés dans leur usage réel : `GET /election/top` et `GET /election/metrics`, tous deux prenant `trainer_external_id`, `dex_slug`, `election_slug` et (pour `top`) `count` en query string. La page mono-dex d'élection de pokenini-web fait donc systématiquement 3 appels (top, liste des candidats au vote, metrics) fusionnés côté pokenini-back (`ElectionIndexController`).

Par ailleurs, `GET /dex/can_hold_election` liste les dex éligibles à l'élection mais sans aucune donnée liée au trainer (pas de top, pas de metrics) — la page dex-list d'élection de pokenini-web n'affiche donc aujourd'hui aucun badge de progression, contrairement à la page dex-list d'album qui vient de gagner un badge "X / Y capturés" (voir `docs/superpowers/specs/2026-07-09-album-dex-list-report-design.md`).

L'objectif : gérer les dex d'élection à la manière des albums — un seul endpoint qui renvoie la liste des dex d'élection avec top + metrics par dex, et un endpoint qui renvoie top + metrics pour un seul dex, en un seul appel chacun.

## Approche retenue

- **Single dex** : remplacer `/election/top` + `/election/metrics` par un unique `GET /election/{trainerExternalId}/{dexSlug}` renvoyant `{ top, metrics }`. Alternative écartée : garder les deux endpoints séparés en plus d'un nouveau combiné — rejetée, on maîtrise les deux seuls appelants (pokenini-back) et il n'y a aucune raison de garder trois façons d'obtenir la même donnée.
- **List** : nouveau `GET /election/{trainerExternalId}/list`, qui **remplace entièrement** `GET /dex/can_hold_election` (supprimé) — il assume désormais le rôle "quels dex sont éligibles à l'élection, avec leurs flags" et y ajoute `report` (top + metrics) par dex. Alternative écartée : garder `/dex/can_hold_election` en plus d'un nouvel endpoint dédié au report — rejetée à la demande explicite de suppression, ça évite deux sources de vérité sur "quels dex sont éligibles".
- **Stratégie de requêtage pour la liste** : boucle sur chaque dex éligible, en réutilisant tel quel `TrainerPokemonEloRepository::getTopN()` et `::getMetrics()` (N dex × 2 requêtes). Alternative écartée : batcher via fenêtres SQL (`ROW_NUMBER() OVER (PARTITION BY dex_id ...)`) pour un nombre de requêtes constant — écartée pour l'instant : complexité SQL et de test nettement supérieure (à maintenir à 100% MSI) pour un nombre de dex éligibles borné (quelques dizaines), alors que la boucle réutilise du code déjà testé.
- **Taille de payload** : même défaut `count = 5` pour les deux endpoints (pas de défaut réduit pour la liste) — l'appelant qui veut alléger la liste passe `?count=1`.

## pokenini-api

### Suppression

- `src/Controller/DexCanHoldElectionController.php` — supprimé.
- `src/Service/DexCanHoldElectionService.php` — supprimé (logique absorbée par le nouveau service, voir plus bas ; `DexRepository::getCanHoldElection()` est conservé et réutilisé directement).
- `src/DTO/TrainerPokemonEloQueryOptions.php` et `src/DTO/TrainerPokemonEloTopQueryOptions.php` — supprimés. Le second n'a aucun appelant en dehors de son propre test (résidu d'une tentative antérieure abandonnée de cette même fonctionnalité) ; le premier est remplacé par `ElectionReportQueryOptions`.
- Routes `GET /election/top`, `GET /election/metrics`, `GET /dex/can_hold_election` — supprimées.

### DTO

- `src/DTO/ElectionReportQueryOptions.php` (nouveau, remplace les deux DTO supprimés) : `{ electionSlug: string = '', count: int = 5 }`. `trainerExternalId` et `dexSlug` viennent désormais toujours du chemin de route, jamais de la query string — cohérent avec tous les autres endpoints trainer-scopés du projet (`DexController`, `AlbumIndexController`).
- `src/DTO/ElectionReport/Report.php` (nouveau, mirrors `AlbumReport\Report`) : objet interne non sérialisé directement, porteur des données brutes.
  ```php
  final class Report
  {
      /** @param array<array-key, array<string, mixed>> $top */
      public function __construct(
          public readonly array $top,
          /** @var array{view_count_sum: int, win_count_sum: int, view_count_max: int, win_count_max: int, under_max_view_count: int, max_view_count: int, dex_total_count: int} */
          public readonly array $metrics,
      ) {}
  }
  ```

### Service

- `src/Service/Election/ElectionReportService.php` (nouveau, mirrors `AlbumReportService`, dépend de `TrainerPokemonEloRepository` **et** `DexRepository` — absorbe le rôle de l'ancien `DexCanHoldElectionService`) :
  - `get(string $trainerExternalId, string $dexSlug, string $electionSlug, int $count): Report` — appelle `TrainerPokemonEloRepository::getTopN()` + `::getMetrics()`.
  - `getEligibleDex(DexQueryOptions $options): array<array-key, array<string, mixed>>` — délègue tel quel à `DexRepository::getCanHoldElection()` (inchangé).
  - `getBatch(string $trainerExternalId, string[] $dexSlugs, string $electionSlug, int $count): array<string, Report>` — boucle sur `$dexSlugs`, mêmes deux appels par dex, clé = dex slug.

  Le contrôleur ne dépend que de ce service (pas directement de `DexRepository` ni de `TrainerPokemonEloRepository`), cohérent avec le style du reste du contrôleur layer (`AlbumIndexController` ne dépend que de services, jamais de repositories directement).

### Response DTO / Factory

- `src/DTO/Response/ElectionReportResponse.php` (nouveau) : `{ top: ElectionEloResponse[], metrics: ElectionMetricsResponse }`.
- `src/Factory/ElectionReportResponseFactory.php` (nouveau) : `fromReport(Report $report): ElectionReportResponse`, délègue à `ElectionEloResponseFactory::fromSqlRows()` et `ElectionMetricsResponseFactory::fromArray()` (les deux existent déjà, inchangés).
- `src/DTO/Response/ElectionDexListItemResponse.php` (nouveau) : `{ dex: DexResponse, report: ElectionReportResponse }` — `DexResponse` existe déjà (utilisé aujourd'hui par `DexCanHoldElectionController`), inchangé.
- `src/Factory/ElectionDexListItemResponseFactory.php` (nouveau) : `fromSqlRows(array $dexRows, array $reports): ElectionDexListItemResponse[]`, mirrors `TrainerDexResponseFactory::fromSqlRows()` — fallback sur un `Report` vide (`top: [], metrics: <tous les compteurs à 0>`) si un dex éligible n'a pas d'entrée dans le batch (ne devrait pas arriver, évite un throw).

### Controller

- `src/Controller/TrainerPokemonEloController.php` renommé en `src/Controller/ElectionReportController.php`, actions `top()`/`metrics()` supprimées, remplacées par :
  - `list(string $trainerExternalId, Request $request): ElectionDexListItemResponse[]` sur `GET /election/{trainerExternalId}/list` — construit un `DexQueryOptions` (déjà existant, `include_unreleased_dex`/`include_premium_dex`) et un `ElectionReportQueryOptions` (`election_slug`, `count`) à partir de la query string, appelle `ElectionReportService::getEligibleDex()` puis `::getBatch()`.
  - `show(string $trainerExternalId, string $dexSlug, Request $request): ElectionReportResponse` sur `GET /election/{trainerExternalId}/{dexSlug}` — construit un `ElectionReportQueryOptions`, appelle `ElectionReportService::get()`.
  - **Ordre des méthodes important** : `list()` déclarée avant `show()` dans la classe, pour que Symfony (qui teste les routes attribute-based dans l'ordre de déclaration) fasse matcher `/election/{id}/list` avant que `/election/{id}/{dexSlug}` ne capture `list` comme valeur de `dexSlug`.

### Tests

- Test unitaire `ElectionReportServiceTest` (get + getBatch).
- Tests unitaires des factories (`ElectionReportResponseFactoryTest`, `ElectionDexListItemResponseFactoryTest`).
- Test unitaire `ElectionReportQueryOptionsTest` (remplace les tests des deux DTO supprimés).
- Test d'intégration `ElectionReportControllerTest` : `show()` (top+metrics cohérents avec les anciens tests de `/election/top` et `/election/metrics`), `list()` (présence du report par dex, filtrage `include_unreleased_dex`/`include_premium_dex` comme avant sur `/dex/can_hold_election`), et un test explicite que `/election/{id}/list` ne matche pas la route `{dexSlug}`.
- Suppression des tests devenus obsolètes : `DexCanHoldElectionControllerTest`, `DexCanHoldElectionServiceTest`, tests de `top()`/`metrics()` sur l'ancien contrôleur, `TrainerPokemonEloQueryOptionsTest`, `TrainerPokemonEloTopQueryOptionsTest`.

## pokenini-back

### Api Services

- `src/Service/Api/GetElectionTopApiService.php`, `GetElectionMetricsApiService.php` — supprimés.
- `src/Service/Api/GetElectionReportApiService.php` (nouveau) : `get(string $trainerId, string $dexSlug, string $electionSlug, int $count): array{top: ..., metrics: ...}`, appelle `GET /election/{trainerId}/{dexSlug}?election_slug=...&count=...`. Non caché (comme les deux endpoints qu'il remplace aujourd'hui) — pas d'invalidation à gérer pour cet appel.
- `src/Service/Api/GetElectionDexListApiService.php` : la méthode `getDexWithParam()` pointe désormais vers `GET /election/{trainerId}/list` (au lieu de `/dex/can_hold_election`) avec `election_slug`/`count` en plus des filtres existants. **Caché, mais désormais scopé par trainer** : la clé de cache (`KeyMaker::getElectionDexListKey()`) doit intégrer le `trainerId`, plus le tag `KeyMaker::getTrainerIdKey($trainerId)` (même mécanisme que la correction déjà faite pour `ModifyTrainerAlbumService` côté albums).

### Invalidation de cache — nouveau besoin

- `ModifyElectionVoteService::vote()` ne fait aujourd'hui aucune invalidation de cache (rien n'était caché côté élection). Maintenant que la liste devient trainer-scopée et cachée, il faut injecter un `ElectionCacheInvalidatorService` (nouveau, mirrors `AlbumCacheInvalidatorService`) et l'appeler après un vote réussi, avec le tag `KeyMaker::getTrainerIdKey($trainerId)`.

### Services / Controllers

- `src/Service/GetElectionTopService.php`, `GetElectionMetricsService.php` — supprimés, remplacés par un `GetElectionReportService::getReport(string $dexSlug, string $electionSlug): array` unique (résout le trainerId via `UserTokenService`, appelle `GetElectionReportApiService::get()`).
- `src/Controller/Election/ElectionIndexController.php` : remplace les deux appels (`$electionTopService->getTop()`, `$metricsService->getMetrics()`) par un seul appel à `GetElectionReportService::getReport()` ; l'appel à `GetPokemonsListService` (liste des candidats au vote) est inchangé, hors scope.
- `src/Controller/Election/ElectionDexListController.php` / `GetElectionDexListService` : passent désormais le `trainerId` (résolu via `UserTokenService`, comme le fait déjà `GetElectionTopService` aujourd'hui) à `GetElectionDexListApiService::get()`.

### Tests

- Suppression des tests des classes supprimées.
- Nouveau test unitaire `GetElectionReportServiceTest`, `GetElectionReportApiServiceTest`.
- Mise à jour `GetElectionDexListApiServiceTest` (clé de cache par trainer), `ElectionIndexControllerTest`, `ElectionDexListControllerTest`.
- Nouveau test unitaire `ModifyElectionVoteServiceTest` vérifiant l'appel à `ElectionCacheInvalidatorService::invalidate($trainerId)` après un vote.

## pokenini-web

### DTO / Response objects

- `src/ResponseObject/Election/ElectionDexListItem.php` (ou équivalent existant) : ajoute une propriété `report` (réutilise/adapte l'objet `Report` d'élection déjà utilisé sur la page mono-dex — `top` + `metrics`).
- Les services front qui consomment `GetElectionReportApiService`/`GetElectionDexListApiService` côté back sont mis à jour en conséquence (désérialisation du nouveau payload `{top, metrics}` au lieu de deux réponses séparées).

### Template

- `templates/AlbumDexList/_macro.html.twig`, macro `itemElection()` : ajoute un badge de progression, dans le même esprit que le badge album, basé sur `report.metrics.completion` / `dex_total_count` — même sémantique de "progress %" que celle déjà affichée sur la page mono-dex d'élection (`templates/Election/_bar_top.html.twig`, `metrics.roundCount` / `metrics.totalRoundCount`), à reprendre à l'identique pour rester cohérent visuellement. Pas de vignette du pokémon en tête de classement (décision explicite : badge de complétion seul, pas de widget supplémentaire).

### Tests

- Mise à jour des fixtures Moco (`tests/resources/moco/Back/responses/election/...`) pour le nouveau payload combiné.
- Mise à jour des tests de rendu du template si applicable.

## Décisions

- `/dex/can_hold_election` est supprimé ; `GET /election/{trainerExternalId}/list` en pokenini-api est la seule source pour "quels dex sont éligibles à l'élection" à partir de maintenant.
- La liste boucle sur les dex plutôt que de batcher via fenêtres SQL — à revisiter seulement si le nombre de dex éligibles ou la volumétrie deviennent un problème de performance mesuré.
- Même défaut `count = 5` pour les deux endpoints ; pas de troncature spécifique côté liste.
- La liste d'élection redevient cachée côté pokenini-back (comme elle l'était déjà avant, globalement) mais désormais scopée par trainer, avec invalidation sur vote — contrairement au report mono-dex qui reste non caché (comme aujourd'hui).
- Le badge web affiche uniquement la complétion (pas le pokémon en tête), pour rester simple et cohérent avec le badge album.
