# API Response Restructuring — Backlog

Améliorations restantes pour éviter les données à plat dans les réponses.
Voir les plans existants dans `docs/superpowers/plans/` pour le pattern DTO + Factory + Serializer à suivre.

---

## À traiter

- [ ] **`GET /election/metrics`** — `max_view_count` et `under_max_view_count` regroupés sous `completion`

  Ces deux champs sont des **compteurs de pokémons** (pas des valeurs de vues) : `max_view_count` = nombre de pokémons au maximum de vues avec 100% de victoires ; `under_max_view_count` = idem à `max - 1` vue. Ils forment une paire logique de complétion d'élection, distincte de `view_count.max` (qui est la valeur maximale brute).

  **Avant**
  ```json
  {
    "view_count": { "sum": 0, "max": 0 },
    "win_count": { "sum": 0, "max": 0 },
    "under_max_view_count": 15,
    "max_view_count": 15,
    "dex_total_count": 21
  }
  ```
  **Après**
  ```json
  {
    "view_count": { "sum": 0, "max": 0 },
    "win_count": { "sum": 0, "max": 0 },
    "completion": {
      "at_max_count": 15,
      "under_max_count": 15
    },
    "dex_total_count": 21
  }
  ```

---

- [ ] **`GET /action_logs`** — clés dynamiques en top-level → array avec `action_type`

  **Avant**
  ```json
  {
    "update_pokemons": {
      "current": { "created_at": "2026-06-10 08:15:32", "done_at": "2026-06-10 08:16:01", "execution_time": "00:00:29", "details": { "created": "3", "updated": "12" }, "error_trace": null },
      "last": { "created_at": "2026-06-09 22:00:11", "done_at": null, "execution_time": null, "details": null, "error_trace": null }
    },
    "calculate_dex_availabilities": {
      "current": { "created_at": "2026-06-10 08:20:00", "done_at": "2026-06-10 08:20:45", "execution_time": "00:00:45", "details": null, "error_trace": null },
      "last": null
    }
  }
  ```
  **Après**
  ```json
  [
    {
      "action_type": "update_pokemons",
      "current": { "created_at": "2026-06-10 08:15:32", "done_at": "2026-06-10 08:16:01", "execution_time": "00:00:29", "details": { "created": "3", "updated": "12" }, "error_trace": null },
      "last": { "created_at": "2026-06-09 22:00:11", "done_at": null, "execution_time": null, "details": null, "error_trace": null }
    },
    {
      "action_type": "calculate_dex_availabilities",
      "current": { "created_at": "2026-06-10 08:20:00", "done_at": "2026-06-10 08:20:45", "execution_time": "00:00:45", "details": null, "error_trace": null },
      "last": null
    }
  ]
  ```
  > Breaking change — à coordonner avec `pokenini-back`. L'ordre du tableau suit l'ordre alphabétique des `action_type`.

---

- [ ] **`GET /reports`** — `slug` manquant dans `dex_usage.dex` et `catch_state_usage.catch_state`

  Les deux sont incohérents avec les autres endpoints. À vérifier : la requête SQL actuelle jointure peut-être uniquement `name`/`french_name` — une modification SQL peut être nécessaire en plus du DTO.

  **Avant**
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
  **Après**
  ```json
  {
    "dex_usage": [
      { "count": 2, "dex": { "slug": "home", "name": "Home", "french_name": "Home" } }
    ],
    "catch_state_usage": [
      { "count": 11, "catch_state": { "slug": "no", "name": "No", "french_name": "Non", "color": "#e57373" } }
    ]
  }
  ```

---

- [ ] **`GET /debogage/pokemon/{slug}`** — `family` plate + `bankable`/`bankableish` épars

  **Avant**
  ```json
  {
    "family": "bulbasaur",
    "bankable": false,
    "bankableish": null
  }
  ```
  **Après**
  ```json
  {
    "family": { "slug": "bulbasaur" },
    "bank": { "bankable": false, "bankableish": null }
  }
  ```

---

- [ ] **`GET /debogage/pokemon/{slug}/availabilities`** — 4 listes plates → groupées par dimension *(breaking change)*

  **Avant**
  ```json
  {
    "games_availabilities": [
      { "game": { "slug": "x" }, "is_available": true }
    ],
    "games_shinies_availabilities": [
      { "game": { "slug": "x" }, "is_available": true }
    ],
    "game_bundles_availabilities": [
      { "game_bundle": { "slug": "xy" }, "is_available": true }
    ],
    "game_bundles_shinies_availabilities": [
      { "game_bundle": { "slug": "xy" }, "is_available": true }
    ]
  }
  ```
  **Après**
  ```json
  {
    "games": {
      "normal": [{ "game": { "slug": "x" }, "is_available": true }],
      "shiny":  [{ "game": { "slug": "x" }, "is_available": true }]
    },
    "game_bundles": {
      "normal": [{ "game_bundle": { "slug": "xy" }, "is_available": true }],
      "shiny":  [{ "game_bundle": { "slug": "xy" }, "is_available": true }]
    }
  }
  ```
