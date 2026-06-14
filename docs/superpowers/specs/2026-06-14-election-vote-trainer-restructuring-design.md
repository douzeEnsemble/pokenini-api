# Design — `POST /election/vote` : `trainer_external_id` → `trainer: { external_id }`

## Contexte

`POST /election/vote` expose `trainer_external_id` à plat dans le body de la requête et dans la réponse. C'est incohérent avec `/reports` où le trainer est `{ external_id: "..." }`. Ce refactoring aligne les deux endpoints et introduit un DTO partagé `TrainerExternalIdResponse`.

## Changement de format

**Requête (body) — avant**
```json
{ "trainer_external_id": "7b52009b...", "dex_slug": "demo", ... }
```
**Requête (body) — après**
```json
{ "trainer": { "external_id": "7b52009b..." }, "dex_slug": "demo", ... }
```

**Réponse — avant**
```json
{ "election_vote": { "trainer_external_id": "7b52009b...", "dex": { "slug": "demo" }, ... } }
```
**Réponse — après**
```json
{ "election_vote": { "trainer": { "external_id": "7b52009b..." }, "dex": { "slug": "demo" }, ... } }
```

## Fichiers modifiés

### Renommage DTO partagé
- `src/DTO/Response/ReportTrainerResponse.php` → `TrainerExternalIdResponse.php`
  - Classe renommée `TrainerExternalIdResponse`
  - Tous les usages mis à jour : `ReportResponseFactory`, `TrainerCatchStateCountResponse`

### Côté input (parsing du body)
- `src/DTO/ElectionVote.php`
  - OptionsResolver : `trainer_external_id` (string requis) → `trainer` (array requis)
  - Extraction de `$options['trainer']['external_id']` dans le constructeur
  - La propriété `$trainerExternalId` est conservée (nom PHP interne inchangé)

### Côté output (sérialisation)
- `src/DTO/Response/ElectionVoteDataResponse.php`
  - Remplace `trainerExternalId: string` + `#[SerializedName('trainer_external_id')]` par `trainer: TrainerExternalIdResponse`
- `src/Factory/ElectionVoteResultResponseFactory.php`
  - `buildElectionVoteData()` : instancie `new TrainerExternalIdResponse($vote->trainerExternalId)` au lieu de passer le string

### Tests
- `tests/src/Unit/DTO/ElectionVoteTest.php` — body JSON avec `trainer: { external_id }` au lieu de `trainer_external_id`
- `tests/src/Unit/DTO/Response/ElectionVoteDataResponseTest.php` — assertions sur `->trainer->externalId`
- `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php` — assertions sur `->electionVote->trainer->externalId`
- `tests/src/Unit/DTO/Response/ReportTrainerResponseTest.php` → renommé `TrainerExternalIdResponseTest.php`
- `tests/src/Unit/DTO/Response/TrainerCatchStateCountResponseTest.php` — import mis à jour
- `tests/src/Unit/DTO/Response/ReportResponseTest.php` — import mis à jour
- `tests/src/Integration/Controller/ElectionVoteControllerTest.php` — body JSON et assertions réponse

## Décisions

- La propriété PHP `$trainerExternalId` dans `ElectionVote` est conservée telle quelle (nom interne, pas exposé).
- `ReportTrainerResponse` est supprimé et remplacé par `TrainerExternalIdResponse` dans tout le codebase (pas de coexistence).
- Breaking change côté `pokenini-back` à coordonner séparément.
