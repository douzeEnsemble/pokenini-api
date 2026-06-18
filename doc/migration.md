# Guide de migration — Refactoring des réponses API

Ce document recense tous les changements de réponses introduits par la branche `feature/refactoring_responses`.
Les clés sont désormais systématiquement en `snake_case` via `#[SerializedName]`.

## Sommaire des breaking changes

| Endpoint | Nature |
|----------|--------|
| `GET /forms/category`, `/forms/regional`, `/forms/special`, `/forms/variant` | Supprimés — remplacés par `GET /forms` |
| `GET /game_bundles` | `generation_slug` → `generation.slug` (objet imbriqué) |
| `GET /reports` | Ajout du champ `slug` dans les objets imbriqués |
| `GET /album/{trainerId}/{dexSlug}` | `game_bundles` ajouté à chaque pokémon ; `french_name` ajouté aux formes |
| `GET /pokemons/to_choose` | `game_bundles` + `color` ajoutés |
| `POST /election/vote` | `trainer_external_id` → `trainer.external_id` ; ajout de `score` |
| `GET /election/top` | Structure entièrement imbriquée (`pokemon`, `forms`, `types`, `score`) |
| `GET /election/metrics` | `under_max_view_count` + `max_view_count` → `completion.under_max_count` + `completion.at_max_count` |
| `GET /action_logs` | Objet à clés dynamiques → tableau avec champ `action_type` |
| `GET /debogage/pokemon/{slug}` | `family` → `family.slug` ; `bankable`/`bankableish` → `bank.bankable`/`bank.bankableish` |
| `GET /debogage/pokemon/{slug}/availabilities` | 4 tableaux plats → `games.normal`, `games.shiny`, `game_bundles.normal`, `game_bundles.shiny` |

---

## Renommages globaux (snake_case)

Ces renommages s'appliquent sur **tous** les endpoints qui renvoyaient ces clés en camelCase.

| Avant | Après |
|-------|-------|
| `frenchName` | `french_name` |
| `familyOrder` | `family_order` |
| `familyLead` | `family_lead` |
| `originalGameBundle` | `original_game_bundle` |
| `gameBundles` | `game_bundles` |
| `nationalDexNumber` | `national_dex_number` |
| `regionalDexNumber` | `regional_dex_number` |
| `orderNumber` | `order_number` |
| `electionSlug` | `election_slug` |
| `catchState` | `catch_state` |
| `actionType` | `action_type` |
| `createdAt` | `created_at` |
| `doneAt` | `done_at` |
| `executionTime` | `execution_time` |
| `errorTrace` | `error_trace` |

---

## Détail par endpoint

### GET /forms — Consolidation des 4 endpoints

**Avant** — 4 endpoints séparés, chacun retournant un tableau plat :
```
GET /forms/category  → [{ "slug": "starter", "name": "Starter", "french_name": "de Départ" }, ...]
GET /forms/regional  → [{ "slug": "alolan",  "name": "Alolan",  "french_name": "d'Alola" },   ...]
GET /forms/special   → [{ "slug": "mega",    "name": "Mega",    "french_name": "Mega" },       ...]
GET /forms/variant   → [{ "slug": "gender",  "name": "Gender",  "french_name": "Sexe" },       ...]
```

**Après** — Un seul endpoint `GET /forms` retournant un objet avec 4 tableaux :
```json
{
  "category": [{ "slug": "starter", "name": "Starter", "french_name": "de Départ" }],
  "regional":  [{ "slug": "alolan",  "name": "Alolan",  "french_name": "d'Alola" }],
  "special":   [{ "slug": "mega",    "name": "Mega",    "french_name": "Mega" }],
  "variant":   [{ "slug": "gender",  "name": "Gender",  "french_name": "Sexe" }]
}
```

**Migration** : remplacer les 4 appels par un seul `GET /forms`, puis lire `response.category`, `response.regional`, etc.

---

### GET /game_bundles — Imbrication de generation

**Avant** :
```json
[{ "slug": "redgreenblueyellow", "name": "...", "french_name": "...", "generation_slug": "1" }]
```

**Après** :
```json
[{ "slug": "redgreenblueyellow", "name": "...", "french_name": "...", "generation": { "slug": "1" } }]
```

**Migration** : `item.generation_slug` → `item.generation.slug`

---

### GET /reports — Ajout des slugs dans les objets imbriqués

**Avant** — les objets imbriqués n'avaient pas de `slug` :
```json
{
  "dex_usage": [
    { "count": 2, "dex": { "name": "Home", "french_name": "Home" } }
  ],
  "catch_state_usage": [
    { "count": 11, "catch_state": { "name": "No", "french_name": "Non", "color": "#e57373" } }
  ]
}
```

**Après** :
```json
{
  "catch_state_counts_defined_by_trainer": [...],
  "dex_usage": [
    { "count": 2, "dex": { "slug": "home", "name": "Home", "french_name": "Home" } }
  ],
  "catch_state_usage": [
    { "count": 11, "catch_state": { "slug": "no", "name": "No", "french_name": "Non", "color": "#e57373" } }
  ]
}
```

**Migration** : `slug` désormais disponible sur `dex` et `catch_state`. Nouveau champ `catch_state_counts_defined_by_trainer` ajouté.

---

### GET /election/top — Structure entièrement imbriquée

**Avant** — objet plat :
```json
{
  "elo": 1250.5,
  "significance": true,
  "pokemon_slug": "pikachu",
  "pokemon_name": "Pikachu",
  "pokemon_french_name": "Pikachu",
  "national_dex_number": 25,
  "primary_type_slug": "electric",
  "primary_type_name": "Electric",
  "primary_type_french_name": "Électrique",
  "secondary_type_slug": null
}
```

**Après** — objets imbriqués :
```json
{
  "pokemon": {
    "slug": "pikachu",
    "labels": { "name": "Pikachu", "french_name": "Pikachu" },
    "national_dex_number": 25,
    "game_bundles": { "normal": [...], "shiny": [...] }
  },
  "forms": null,
  "types": {
    "primary": { "slug": "electric", "name": "Electric", "french_name": "Électrique", "color": "#FFCC33" },
    "secondary": null
  },
  "score": {
    "elo": 1250.5,
    "significance": true
  }
}
```

**Migration** :

| Avant | Après |
|-------|-------|
| `elo` | `score.elo` |
| `significance` | `score.significance` |
| `pokemon_slug` | `pokemon.slug` |
| `pokemon_name` | `pokemon.labels.name` |
| `pokemon_french_name` | `pokemon.labels.french_name` |
| `national_dex_number` | `pokemon.national_dex_number` |
| `primary_type_slug` | `types.primary.slug` |
| `primary_type_name` | `types.primary.name` |
| `secondary_type_slug` | `types.secondary?.slug` |

---

### POST /election/vote — Imbrication du trainer

**Avant** :
```json
{
  "election_vote": {
    "trainer_external_id": "7b52009b...",
    "dex": { "slug": "demo" }
  }
}
```

**Après** :
```json
{
  "election_vote": {
    "trainer": { "external_id": "7b52009b..." },
    "dex": { "slug": "demo" }
  },
  "pokemons_elo": { ... }
}
```

**Migration** : `election_vote.trainer_external_id` → `election_vote.trainer.external_id`. Nouveau champ `pokemons_elo` ajouté.

---

### GET /election/metrics — Objet completion

**Avant** :
```json
{
  "view_count": { "sum": 0, "max": 0 },
  "win_count":  { "sum": 0, "max": 0 },
  "under_max_view_count": 15,
  "max_view_count": 15,
  "dex_total_count": 21
}
```

**Après** :
```json
{
  "view_count": { "sum": 0, "max": 0 },
  "win_count":  { "sum": 0, "max": 0 },
  "completion": {
    "at_max_count": 15,
    "under_max_count": 15
  },
  "dex_total_count": 21
}
```

**Migration** :

| Avant | Après |
|-------|-------|
| `max_view_count` | `completion.at_max_count` |
| `under_max_view_count` | `completion.under_max_count` |

---

### GET /action_logs — Tableau avec action_type

**Avant** — objet à clés dynamiques :
```json
{
  "update_pokemons": {
    "current": { "created_at": "...", "done_at": "...", "execution_time": 0.5 },
    "last": { ... }
  },
  "calculate_dex_availabilities": { "current": null, "last": { ... } }
}
```

**Après** — tableau :
```json
[
  {
    "action_type": "update_pokemons",
    "current": { "created_at": "...", "done_at": "...", "execution_time": 0.5 },
    "last": { ... }
  },
  {
    "action_type": "calculate_dex_availabilities",
    "current": null,
    "last": { ... }
  }
]
```

**Migration** : itérer sur le tableau et filtrer par `action_type` plutôt qu'accéder par clé d'objet.

---

### GET /debogage/pokemon/{slug} — family et bank

**Avant** :
```json
{
  "family": "bulbasaur",
  "bankable": false,
  "bankableish": null
}
```

**Après** :
```json
{
  "family": { "slug": "bulbasaur" },
  "bank": {
    "bankable": false,
    "bankableish": null
  }
}
```

**Migration** :

| Avant | Après |
|-------|-------|
| `family` (string) | `family.slug` |
| `bankable` | `bank.bankable` |
| `bankableish` | `bank.bankableish` |

---

### GET /debogage/pokemon/{slug}/availabilities — Regroupement games/game_bundles

**Avant** — 4 tableaux à la racine :
```json
{
  "gamesAvailabilities": [...],
  "gamesShiniesAvailabilities": [...],
  "gameBundlesAvailabilities": [...],
  "gameBundlesShiniesAvailabilities": [...]
}
```

**Après** — regroupé sous `games` et `game_bundles` :
```json
{
  "games": {
    "normal": [...],
    "shiny": [...]
  },
  "game_bundles": {
    "normal": [...],
    "shiny": [...]
  }
}
```

**Migration** :

| Avant | Après |
|-------|-------|
| `gamesAvailabilities` | `games.normal` |
| `gamesShiniesAvailabilities` | `games.shiny` |
| `gameBundlesAvailabilities` | `game_bundles.normal` |
| `gameBundlesShiniesAvailabilities` | `game_bundles.shiny` |

---

## Changements additifs (non-breaking)

Ces changements ajoutent de nouveaux champs sans en supprimer — aucune migration requise, mais les clients peuvent en profiter.

| Endpoint | Ajout |
|----------|-------|
| `GET /types` | — |
| `GET /catch_states` | — |
| `GET /election/top` | `pokemon.game_bundles`, `types.primary.color`, `types.secondary.color` |
| `GET /pokemons/to_choose` | `pokemon.game_bundles`, `types.primary.color`, `types.secondary.color` |
| `GET /album/{trainerId}/{dexSlug}` | `pokemon.game_bundles`, `forms.*.french_name` |
| `GET /reports` | `catch_state_counts_defined_by_trainer` |
