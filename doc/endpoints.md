# Endpoints HTTP de l'API Pokénini

Documentation exhaustive des endpoints REST exposés par `pokenini-api` (Symfony 8, routage par attributs PHP).

## Authentification et base URL

- **Authentification : HTTP Basic**, stateless, sur **toutes** les routes (`access_control: ^/` exige `ROLE_API`).
  Un seul utilisateur existe : `web` (mot de passe défini par la variable d'environnement `WEB_PASSWORD`, hashé bcrypt).
  En dev/test, le mot de passe est `douze`.
- Sans authentification valide : **401 Unauthorized** sur n'importe quelle route.
- **Base URL** : aucun préfixe global de route (ni version, ni locale) — `config/routes.yaml` charge simplement les attributs de `src/Controller/`.
  Dans les tests Postman/Newman la base est `http://web:8080`.
- Toutes les réponses JSON sont en `Content-Type: application/json`. Les clés sont en `snake_case` (via `#[SerializedName]`).

```bash
# Forme générale d'un appel
curl -u web:douze http://web:8080/types
```

## Tableau récapitulatif

| # | Méthode | Chemin | Description |
|---|---------|--------|-------------|
| 1 | GET | `/types` | Liste des types Pokémon |
| 2 | GET | `/catch_states` | Liste des états de capture |
| 3 | GET | `/collections` | Liste des collections |
| 4 | GET | `/game_bundles` | Liste des bundles de jeux |
| 5 | GET | `/forms/category` | Formes « catégorie » |
| 6 | GET | `/forms/regional` | Formes régionales |
| 7 | GET | `/forms/special` | Formes spéciales |
| 8 | GET | `/forms/variant` | Formes variantes |
| 9 | GET | `/reports` | Statistiques d'usage globales |
| 10 | GET | `/dex/can_hold_election` | Dex pouvant tenir une élection |
| 11 | GET | `/dex/{trainerExternalId}/list` | Liste des dex d'un dresseur |
| 12 | PUT | `/dex/{trainerExternalId}/{dexSlug}` | Modifier les attributs d'un dex de dresseur |
| 13 | GET | `/album/{trainerExternalId}/{dexSlug}` | Album (dex + pokémons + rapports), filtrable |
| 14 | PUT | `/album/{trainerExternalId}/{dexSlug}/{pokemonSlug}` | Créer l'état de capture d'un pokémon |
| 15 | PATCH | `/album/{trainerExternalId}/{dexSlug}/{pokemonSlug}` | Mettre à jour l'état de capture d'un pokémon |
| 16 | GET | `/pokemons/to_choose` | N pokémons à départager (élection) |
| 17 | POST | `/election/vote` | Enregistrer un vote d'élection (ELO) |
| 18 | GET | `/election/top` | Top N ELO d'une élection |
| 19 | GET | `/election/metrics` | Métriques d'une élection |
| 20 | GET | `/action_logs` | Journal des dernières actions asynchrones |
| 21 | POST | `/istration/calculate/game_bundles_availabilities` | (Async) Calcul disponibilités par bundle |
| 22 | POST | `/istration/calculate/game_bundles_shinies_availabilities` | (Async) Calcul disponibilités shiny par bundle |
| 23 | POST | `/istration/calculate/dex_availabilities` | (Async) Calcul disponibilités par dex |
| 24 | POST | `/istration/calculate/pokemon_availabilities` | (Async) Calcul disponibilités par pokémon |
| 25 | POST | `/istration/update/labels` | (Async) Sync labels depuis Google Sheets |
| 26 | POST | `/istration/update/games_collections_and_dex` | (Async) Sync jeux, collections et dex |
| 27 | POST | `/istration/update/pokemons` | (Async) Sync pokémons |
| 28 | POST | `/istration/update/regional_dex_numbers` | (Async) Sync numéros de dex régionaux |
| 29 | POST | `/istration/update/games_availabilities` | (Async) Sync disponibilités par jeu |
| 30 | POST | `/istration/update/games_shinies_availabilities` | (Async) Sync disponibilités shiny par jeu |
| 31 | POST | `/istration/update/collections_availabilities` | (Async) Sync disponibilités par collection |
| 32 | GET | `/debogage/dex/{slug}` | (Debug) Détail brut d'un dex |
| 33 | GET | `/debogage/dex/{slug}/availabilities` | (Debug) Pokémons disponibles dans un dex |
| 34 | GET | `/debogage/pokemon/{slug}` | (Debug) Détail brut d'un pokémon |
| 35 | GET | `/debogage/pokemon/{slug}/availabilities` | (Debug) Disponibilités d'un pokémon |
| 36 | DELETE | `/debogage/pokemon/{slug}/caches` | (Debug) Purge des caches de disponibilités d'un pokémon |

> **Note** : le préfixe d'administration est littéralement `/istration` (et non `/administration`), et le préfixe de debug est `/debogage`.

---

## Référentiels

### 1. GET `/types`

Liste de tous les types Pokémon (18).

**Paramètres** : aucun.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/types
```

Exemple de réponse (`200`) :

```json
[
  {
    "slug": "normal",
    "name": "Normal",
    "french_name": "Normal",
    "color": "#A8A878"
  },
  {
    "slug": "fighting",
    "name": "Fighting",
    "french_name": "Combat",
    "color": "#C03028"
  }
]
```

Codes de statut : `200`, `401`.

---

### 2. GET `/catch_states`

Liste des états de capture possibles (utilisés comme corps des requêtes d'upsert d'album).

**Paramètres** : aucun.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/catch_states
```

Exemple de réponse (`200`) :

```json
[
  {
    "slug": "no",
    "name": "No",
    "french_name": "Non",
    "color": "#e57373"
  },
  {
    "slug": "maybe",
    "name": "Maybe",
    "french_name": "Peut être",
    "color": "blue"
  },
  {
    "slug": "maybenot",
    "name": "Maybe not",
    "french_name": "Peut être pas",
    "color": "yellow"
  },
  {
    "slug": "yes",
    "name": "Yes",
    "french_name": "Oui",
    "color": "#66bb6a"
  }
]
```

Codes de statut : `200`, `401`.

---

### 3. GET `/collections`

Liste des collections (sous-ensembles thématiques de pokémons).

**Paramètres** : aucun.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/collections
```

Exemple de réponse (`200`) :

```json
[
  {
    "slug": "swshdynamaxadventuresbosses",
    "name": "Sword, Shield - Dynamax Adventures bosses",
    "french_name": "Sword, Shield - Boss des expéditions Dynamax"
  },
  {
    "slug": "svmassoutbreakspaldea",
    "name": "Scarlet, Violet - Paldea's outbreaks",
    "french_name": "Scarlet, Violet - Apparitions massives de Paldea"
  }
]
```

Codes de statut : `200`, `401`.

---

### 4. GET `/game_bundles`

Liste des bundles de jeux (groupes de versions d'une même génération).

**Paramètres** : aucun.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/game_bundles
```

Exemple de réponse (`200`) :

```json
[
  {
    "slug": "redgreenblueyellow",
    "name": "Red, Green, Blue, Yellow",
    "french_name": "Rouge, Vert, Bleu, Jaune",
    "generation": { "slug": "1" }
  },
  {
    "slug": "rubysapphireemerald",
    "name": "Ruby, Sapphire, Emerald",
    "french_name": "Rubis, Saphir, Émeraude",
    "generation": { "slug": "3" }
  },
  {
    "slug": "blackwhite",
    "name": "Black, White",
    "french_name": "Noir, Blanc",
    "generation": { "slug": "5" }
  }
]
```

Codes de statut : `200`, `401`.

---

### 5. GET `/forms/category`

Formes « catégorie » (starter, mythique, légendaire…).

**Paramètres** : aucun.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/forms/category
```

Exemple de réponse (`200`) :

```json
[
  {
    "slug": "starter",
    "name": "Starter",
    "french_name": "de Départ"
  },
  {
    "slug": "mythical",
    "name": "Mythical",
    "french_name": "Fabuleux"
  }
]
```

Codes de statut : `200`, `401`.

---

### 6. GET `/forms/regional`

Formes régionales (Alola, Galar, Hisui…). Même structure que `/forms/category`.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/forms/regional
```

Exemple de réponse (`200`) :

```json
[
  {
    "slug": "alolan",
    "name": "Alolan",
    "french_name": "d'Alola"
  },
  {
    "slug": "galarian",
    "name": "Galarian",
    "french_name": "de Galar"
  }
]
```

Codes de statut : `200`, `401`.

---

### 7. GET `/forms/special`

Formes spéciales (Méga, Gigamax…). Même structure que `/forms/category`.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/forms/special
```

Exemple de réponse (`200`) :

```json
[
  {
    "slug": "mega",
    "name": "Mega",
    "french_name": "Mega"
  },
  {
    "slug": "gigantamax",
    "name": "Gigantamax",
    "french_name": "Gigamax"
  }
]
```

Codes de statut : `200`, `401`.

---

### 8. GET `/forms/variant`

Formes variantes (sexe, forme alternative…). Même structure que `/forms/category`.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/forms/variant
```

Exemple de réponse (`200`) :

```json
[
  {
    "slug": "gender",
    "name": "Gender",
    "french_name": "Sexe"
  },
  {
    "slug": "alternate",
    "name": "Alternate",
    "french_name": "Alternatif"
  }
]
```

Codes de statut : `200`, `401`.

---

### 9. GET `/reports`

Statistiques d'usage globales : nombre d'états de capture définis par dresseur, usage des dex, usage des états de capture.

**Paramètres** : aucun.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/reports
```

Exemple de réponse (`200`) :

```json
{
  "catch_state_counts_defined_by_trainer": [
    {
      "count": 28,
      "trainer": {
        "external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554"
      }
    },
    {
      "count": 3,
      "trainer": {
        "external_id": "bd307a3ec329e10a2cff8fb87480823da114f8f4"
      }
    }
  ],
  "dex_usage": [
    {
      "count": 2,
      "dex": {
        "name": "Red / Green / Blue / Yellow",
        "french_name": "Rouge / Vert / Bleu / Jaune"
      }
    },
    {
      "count": 2,
      "dex": {
        "name": "Home",
        "french_name": "Home"
      }
    }
  ],
  "catch_state_usage": [
    {
      "count": 11,
      "catch_state": {
        "name": "No",
        "french_name": "Non",
        "color": "#e57373"
      }
    },
    {
      "count": 11,
      "catch_state": {
        "name": "Yes",
        "french_name": "Oui",
        "color": "#66bb6a"
      }
    }
  ]
}
```

Codes de statut : `200`, `401`.

---

## Dex

### 10. GET `/dex/can_hold_election`

Liste des dex éligibles à une élection (flag `can_hold_election`).

**Paramètres de query** (DTO `DexQueryOptions`) :

| Paramètre | Type | Défaut | Description |
|-----------|------|--------|-------------|
| `include_unreleased_dex` | bool | `false` | Inclure les dex non publiés |
| `include_premium_dex` | bool | `false` | Inclure les dex premium |

Exemple de requête :

```bash
curl -u web:douze "http://web:8080/dex/can_hold_election?include_unreleased_dex=0&include_premium_dex=0"
```

Exemple de réponse (`200`) :

```json
[
  {
    "slug": "home",
    "original_slug": "home",
    "name": "Home",
    "french_name": "Home",
    "flags": {
      "is_shiny": false,
      "is_private": false,
      "is_on_home": false,
      "is_display_form": true,
      "is_released": true,
      "is_premium": false,
      "is_custom": false
    },
    "description": "",
    "french_description": "",
    "dex_total_count": 22
  }
]
```

Codes de statut : `200`, `401`.

---

### 11. GET `/dex/{trainerExternalId}/list`

Liste des dex d'un dresseur (avec ses personnalisations éventuelles : dex custom, slug personnalisé…).

**Paramètres de route** :

| Paramètre | Type | Description |
|-----------|------|-------------|
| `trainerExternalId` | string | Identifiant externe du dresseur (hash) |

**Paramètres de query** (DTO `DexQueryOptions`) :

| Paramètre | Type | Défaut | Description |
|-----------|------|--------|-------------|
| `include_unreleased_dex` | bool | `false` | Inclure les dex non publiés |
| `include_premium_dex` | bool | `false` | Inclure les dex premium |

Exemple de requête :

```bash
curl -u web:douze "http://web:8080/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list?include_premium_dex=1"
```

Exemple de réponse (`200`) :

```json
[
  {
    "dex": { "slug": "rubysapphireemerald" },
    "name": "Ruby / Sapphire / Emerald",
    "french_name": "Rubis / Saphir / Émeraude",
    "slug": "rubysapphireemerald",
    "flags": {
      "is_shiny": false,
      "is_private": true,
      "is_on_home": false,
      "is_display_form": true,
      "is_released": true,
      "is_premium": false,
      "is_custom": false
    },
    "display_template": "box"
  },
  {
    "dex": { "slug": "homeshiny" },
    "name": "Home\nShiny",
    "french_name": "Home\nChromatique",
    "slug": "home_shiny",
    "flags": {
      "is_shiny": true,
      "is_private": true,
      "is_on_home": false,
      "is_display_form": true,
      "is_released": true,
      "is_premium": true,
      "is_custom": true
    },
    "display_template": "box"
  }
]
```

Codes de statut : `200`, `401`.

---

### 12. PUT `/dex/{trainerExternalId}/{dexSlug}`

Modifie les attributs du dex d'un dresseur (visibilité, affichage sur la home).

**Paramètres de route** :

| Paramètre | Type | Description |
|-----------|------|-------------|
| `trainerExternalId` | string | Identifiant externe du dresseur |
| `dexSlug` | string | Slug du dex |

**Body** (JSON, DTO `TrainerDexAttributes`) :

| Champ | Type | Défaut | Description |
|-------|------|--------|-------------|
| `is_private` | bool | `false` | Dex privé |
| `is_on_home` | bool | `false` | Dex affiché sur la page d'accueil |

Exemple de requête :

```bash
curl -u web:douze -X PUT \
  -H "Content-Type: application/json" \
  -d '{"is_private": true, "is_on_home": false}' \
  http://web:8080/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow
```

Exemple de réponse (`200`) : corps vide.

Codes de statut : `200`, `400` (corps absent, JSON invalide ou champ inconnu/mal typé), `401`.

---

## Album

### 13. GET `/album/{trainerExternalId}/{dexSlug}`

Retourne l'album d'un dresseur pour un dex : le dex, la liste des pokémons (avec état de capture), un rapport global et un rapport filtré.

**Paramètres de route** :

| Paramètre | Type | Description |
|-----------|------|-------------|
| `trainerExternalId` | string | Identifiant externe du dresseur |
| `dexSlug` | string | Slug du dex (ex : `redgreenblueyellow`, `home`, `homeshiny`) |

**Paramètres de query — filtres d'album** (DTO `AlbumFilters`, tous des tableaux, défaut `[]`) :

| Paramètre | Description |
|-----------|-------------|
| `primary_types[]` | Filtre sur le type primaire |
| `secondary_types[]` | Filtre sur le type secondaire |
| `any_types[]` | Filtre sur l'un ou l'autre type |
| `category_forms[]` | Filtre sur la forme catégorie |
| `regional_forms[]` | Filtre sur la forme régionale |
| `special_forms[]` | Filtre sur la forme spéciale |
| `variant_forms[]` | Filtre sur la forme variante |
| `catch_states[]` | Filtre sur l'état de capture |
| `original_game_bundles[]` | Filtre sur le bundle d'origine |
| `game_bundle_availabilities[]` | Filtre sur la disponibilité par bundle |
| `game_bundle_shiny_availabilities[]` | Filtre sur la disponibilité shiny par bundle |
| `families[]` | Filtre sur la famille (slug du chef de famille) |
| `collection_availabilities[]` | Filtre sur la disponibilité par collection |

Syntaxe des valeurs de filtre :
- valeur `null` (chaîne littérale) = « sans valeur » (ex : `variant_forms[]=null` → pokémons sans forme variante) ;
- préfixe/présence de `!` = négation (ex : `catch_states[]=!yes` → tout sauf `yes`) ;
- les valeurs vides sont ignorées.

Exemple de requête :

```bash
curl -u web:douze \
  "http://web:8080/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow?primary_types[]=grass&catch_states[]=!yes"
```

Exemple de réponse (`200`, tronquée à un pokémon) :

```json
{
  "dex": {
    "slug": "redgreenblueyellow",
    "original_slug": "redgreenblueyellow",
    "name": "Red / Green / Blue / Yellow",
    "french_name": "Rouge / Vert / Bleu / Jaune",
    "flags": {
      "is_shiny": false,
      "is_private": false,
      "is_on_home": false,
      "is_display_form": true,
      "is_released": true,
      "is_premium": false,
      "is_custom": false
    },
    "display_template": "box",
    "region": {
      "name": "Kanto",
      "french_name": "Kanto"
    },
    "selection_rule": "",
    "description": "First generation Pokédex",
    "french_description": "Pokédex de la première génération",
    "version": "20230221.085100"
  },
  "pokemons": [
    {
      "pokemon": {
        "slug": "bulbasaur",
        "name": "Bulbasaur",
        "french_name": "Bulbizarre",
        "national_dex_number": 1,
        "regional_dex_number": 1,
        "simplified_name": "Bulbasaur",
        "forms_label": "",
        "simplified_french_name": "Bulbizarre",
        "forms_french_label": "",
        "icon": "bulbasaur",
        "family_order": 0,
        "family_lead": { "slug": "bulbasaur" },
        "original_game_bundle": { "slug": "redgreenblueyellow" },
        "order_number": "0001-0001-000",
        "game_bundles": [
          { "slug": "redgreenblueyellow" },
          { "slug": "goldsilvercrystal" }
        ],
        "game_bundles_shiny": [
          { "slug": "redgreenblueyellow" },
          { "slug": "goldsilvercrystal" }
        ]
      },
      "catch_state": {
        "slug": "no",
        "name": "No",
        "french_name": "Non",
        "color": "#e57373"
      },
      "forms": {
        "category": { "slug": "starter", "name": "Starter", "french_name": "de Départ" },
        "regional": null,
        "special": null,
        "variant": null
      },
      "types": {
        "primary": { "slug": "grass", "name": "Grass", "french_name": "Plante", "color": "#78C850" },
        "secondary": { "slug": "poison", "name": "Poison", "french_name": "Poison", "color": "#A040A0" }
      }
    }
  ],
  "report": {
    "total": 7,
    "total_caught": 0,
    "total_uncaught": 7,
    "detail": [
      { "catch_state": { "slug": "no", "name": "No", "french_name": "Non", "color": "#e57373" }, "count": 4 },
      { "catch_state": { "slug": "maybe", "name": "Maybe", "french_name": "Peut être", "color": "blue" }, "count": 1 },
      { "catch_state": { "slug": "maybenot", "name": "Maybe not", "french_name": "Peut être pas", "color": "yellow" }, "count": 2 },
      { "catch_state": { "slug": "yes", "name": "Yes", "french_name": "Oui", "color": "#66bb6a" }, "count": 0 }
    ]
  },
  "filtered_report": {
    "total": 7,
    "total_caught": 0,
    "total_uncaught": 7,
    "detail": [
      { "catch_state": { "slug": "no", "name": "No", "french_name": "Non", "color": "#e57373" }, "count": 4 },
      { "catch_state": { "slug": "maybe", "name": "Maybe", "french_name": "Peut être", "color": "blue" }, "count": 1 },
      { "catch_state": { "slug": "maybenot", "name": "Maybe not", "french_name": "Peut être pas", "color": "yellow" }, "count": 2 },
      { "catch_state": { "slug": "yes", "name": "Yes", "french_name": "Oui", "color": "#66bb6a" }, "count": 0 }
    ]
  }
}
```

Notes :
- `dex` vaut `null` si le couple dresseur/dex est inconnu ;
- `catch_state` vaut `null` si le dresseur n'a pas encore défini d'état pour ce pokémon ;
- `report` ignore les filtres, `filtered_report` les applique.

Codes de statut : `200`, `401`.

---

### 14. PUT `/album/{trainerExternalId}/{dexSlug}/{pokemonSlug}`

Crée (upsert) l'état de capture d'un pokémon dans l'album d'un dresseur. Crée aussi l'association dresseur/dex si elle n'existe pas encore.

**Paramètres de route** :

| Paramètre | Type | Description |
|-----------|------|-------------|
| `trainerExternalId` | string | Identifiant externe du dresseur |
| `dexSlug` | string | Slug du dex |
| `pokemonSlug` | string | Slug du pokémon |

**Body** : texte brut (pas de JSON) — le slug de l'état de capture (`no`, `maybe`, `maybenot`, `yes`…).

Exemple de requête :

```bash
curl -u web:douze -X PUT \
  -H "Content-Type: text/plain" \
  -d 'yes' \
  http://web:8080/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/bulbasaur
```

Exemple de réponse (`201`) : corps vide.

Codes de statut : `201`, `400` (corps vide ou slug inexistant — violation de contrainte), `401`.

---

### 15. PATCH `/album/{trainerExternalId}/{dexSlug}/{pokemonSlug}`

Identique au PUT (même upsert), mais répond `200` au lieu de `201`.

Exemple de requête :

```bash
curl -u web:douze -X PATCH \
  -H "Content-Type: text/plain" \
  -d 'maybe' \
  http://web:8080/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/bulbasaur
```

Exemple de réponse (`200`) : corps vide.

Codes de statut : `200`, `400`, `401`.

---

## Élections (ELO)

### 16. GET `/pokemons/to_choose`

Retourne N pokémons à présenter au dresseur pour une élection. Le champ `type` indique le mode : `pick` (premier passage, sélection) ou `vote` (départage ELO).

**Paramètres de query** (DTO `TrainerPokemonEloListQueryOptions`) :

| Paramètre | Type | Requis / défaut | Description |
|-----------|------|-----------------|-------------|
| `trainer_external_id` | string | **requis** | Identifiant externe du dresseur |
| `dex_slug` | string | **requis** | Slug du dex |
| `count` | int | **requis** | Nombre de pokémons à retourner |
| `election_slug` | string | `""` | Slug de l'élection (ex : `favorite`, `affinee`) |
| + tous les filtres d'album | string[] | `[]` | Voir l'endpoint 13 (`primary_types[]`, `catch_states[]`, etc.) |

Exemple de requête :

```bash
curl -u web:douze \
  "http://web:8080/pokemons/to_choose?count=12&dex_slug=home&trainer_external_id=7b52009b64fd0a2a49e6d8a939753077792b0554"
```

Exemple de réponse (`200`, tronquée à un item) :

```json
{
  "type": "pick",
  "items": [
    {
      "pokemon": {
        "slug": "bulbasaur",
        "name": "Bulbasaur",
        "french_name": "Bulbizarre",
        "national_dex_number": 1,
        "regional_dex_number": null,
        "simplified_name": "Bulbasaur",
        "forms_label": "",
        "simplified_french_name": "Bulbizarre",
        "forms_french_label": "",
        "icon": "bulbasaur",
        "family_order": 0,
        "family_lead": { "slug": "bulbasaur" },
        "original_game_bundle": { "slug": "redgreenblueyellow" },
        "order_number": "9999-0001-000",
        "game_bundles": [
          { "slug": "redgreenblueyellow" }
        ],
        "game_bundles_shiny": [
          { "slug": "redgreenblueyellow" }
        ]
      },
      "forms": {
        "category": { "slug": "starter", "name": "Starter", "french_name": "de Départ" },
        "regional": null,
        "special": null,
        "variant": null
      },
      "types": {
        "primary": { "slug": "grass", "name": "Grass", "french_name": "Plante", "color": "#78C850" },
        "secondary": { "slug": "poison", "name": "Poison", "french_name": "Poison", "color": "#A040A0" }
      }
    }
  ]
}
```

Codes de statut : `200`, `401`, `500` (paramètre requis manquant — erreur `OptionsResolver` non interceptée).

---

### 17. POST `/election/vote`

Enregistre un vote d'élection : les gagnants et les perdants d'un duel/sélection. Met à jour les scores ELO et retourne les nouveaux scores.

**Body** (JSON, DTO `ElectionVote`) :

| Champ | Type | Requis / défaut | Description |
|-------|------|-----------------|-------------|
| `trainer_external_id` | string | **requis** | Identifiant externe du dresseur |
| `dex_slug` | string | `""` | Slug du dex |
| `election_slug` | string | `""` | Slug de l'élection |
| `winners_slugs` | string[] | **requis** | Slugs des pokémons gagnants |
| `losers_slugs` | string[] | **requis** | Slugs des pokémons perdants |

Exemple de requête :

```bash
curl -u web:douze -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554",
    "dex_slug": "demo",
    "election_slug": "",
    "winners_slugs": ["butterfree"],
    "losers_slugs": ["caterpie", "metapod"]
  }' \
  http://web:8080/election/vote
```

Exemple de réponse (`200`) :

```json
{
  "election_vote": {
    "trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554",
    "dex": { "slug": "demo" },
    "election_slug": "",
    "winners": [
      { "slug": "butterfree" }
    ],
    "losers": [
      { "slug": "caterpie" },
      { "slug": "metapod" }
    ]
  },
  "pokemons_elo": {
    "winners": [
      {
        "pokemon": { "slug": "butterfree" },
        "elo": 1016
      }
    ],
    "losers": [
      {
        "pokemon": { "slug": "caterpie" },
        "elo": 984
      },
      {
        "pokemon": { "slug": "metapod" },
        "elo": 984
      }
    ]
  }
}
```

Codes de statut : `200`, `400` (corps vide, JSON invalide ou champ requis manquant/mal typé), `401`.

---

### 18. GET `/election/top`

Top N des pokémons d'une élection, triés par score ELO.

**Paramètres de query** (DTO `TrainerPokemonEloQueryOptions`) :

| Paramètre | Type | Requis / défaut | Description |
|-----------|------|-----------------|-------------|
| `trainer_external_id` | string | **requis** | Identifiant externe du dresseur |
| `dex_slug` | string | **requis** | Slug du dex |
| `election_slug` | string | `""` | Slug de l'élection |
| `count` | int | `5` | Nombre de résultats |

Exemple de requête :

```bash
curl -u web:douze \
  "http://web:8080/election/top?trainer_external_id=7b52009b64fd0a2a49e6d8a939753077792b0554&dex_slug=home&election_slug=favorite&count=5"
```

Exemple de réponse (`200`, tronquée à un item) :

```json
[
  {
    "pokemon": {
      "slug": "butterfree",
      "name": "Butterfree",
      "french_name": "Papilusion",
      "national_dex_number": 12,
      "regional_dex_number": null,
      "simplified_name": "Butterfree",
      "forms_label": "",
      "simplified_french_name": "Papilusion",
      "forms_french_label": "",
      "icon": "butterfree",
      "family_order": 2,
      "family_lead": { "slug": "caterpie" },
      "original_game_bundle": { "slug": "redgreenblueyellow" },
      "order_number": "9999-0012-002",
      "game_bundles": [
        { "slug": "redgreenblueyellow" }
      ],
      "game_bundles_shiny": [
        { "slug": "redgreenblueyellow" }
      ]
    },
    "forms": null,
    "types": {
      "primary": { "slug": "bug", "name": "Bug", "french_name": "Insecte", "color": "#A8B820" },
      "secondary": { "slug": "flying", "name": "Flying", "french_name": "Vol", "color": "#A890F0" }
    },
    "elo": 1016.0,
    "significance": true
  }
]
```

Codes de statut : `200`, `401`, `500` (paramètre requis manquant).

---

### 19. GET `/election/metrics`

Métriques d'avancement d'une élection (vues, victoires, plafonds).

**Paramètres de query** (DTO `TrainerPokemonEloQueryOptions`) :

| Paramètre | Type | Requis / défaut | Description |
|-----------|------|-----------------|-------------|
| `trainer_external_id` | string | **requis** | Identifiant externe du dresseur |
| `dex_slug` | string | **requis** | Slug du dex |
| `election_slug` | string | `""` | Slug de l'élection |
| `count` | int | `5` | Accepté mais non utilisé par cet endpoint |

Exemple de requête :

```bash
curl -u web:douze \
  "http://web:8080/election/metrics?trainer_external_id=7b52009b64fd0a2a49e6d8a939753077792b0554&dex_slug=demo"
```

Exemple de réponse (`200`) :

```json
{
  "view_count": {
    "sum": 0,
    "max": 0
  },
  "win_count": {
    "sum": 0,
    "max": 0
  },
  "under_max_view_count": 15,
  "max_view_count": 15,
  "dex_total_count": 21
}
```

Codes de statut : `200`, `401`, `500` (paramètre requis manquant).

---

## Suivi des actions asynchrones

### 20. GET `/action_logs`

Journal des deux dernières exécutions (`current` et `last`) de chaque action asynchrone (déclenchées par les endpoints `/istration/...`). La réponse est un objet indexé par le type d'action.

Types d'action possibles : `update_labels`, `update_games_collections_and_dex`, `update_pokemons`, `update_regional_dex_numbers`, `update_games_availabilities`, `update_games_shinies_availabilities`, `update_collections_availabilities`, `calculate_game_bundles_availabilities`, `calculate_game_bundles_shinies_availabilities`, `calculate_dex_availabilities`, `calculate_pokemon_availabilities`.

**Paramètres** : aucun.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/action_logs
```

Exemple de réponse (`200`, tronquée) :

```json
{
  "update_pokemons": {
    "current": {
      "created_at": "2026-06-10 08:15:32",
      "done_at": "2026-06-10 08:16:01",
      "execution_time": "00:00:29",
      "details": {
        "created": "3",
        "updated": "12"
      },
      "error_trace": null
    },
    "last": {
      "created_at": "2026-06-09 22:00:11",
      "done_at": null,
      "execution_time": null,
      "details": null,
      "error_trace": null
    }
  },
  "calculate_dex_availabilities": {
    "current": {
      "created_at": "2026-06-10 08:20:00",
      "done_at": "2026-06-10 08:20:45",
      "execution_time": "00:00:45",
      "details": null,
      "error_trace": null
    },
    "last": null
  }
}
```

Notes :
- `done_at` à `null` = action en cours (ou jamais terminée) ;
- `error_trace` non `null` = action en échec ;
- `details` est le rapport JSON écrit par le `MessageHandler` à la fin de l'action.

Codes de statut : `200`, `401`.

---

## Administration (actions asynchrones)

Tous les endpoints suivants fonctionnent de la même façon :

- **POST sans corps** ;
- créent un `ActionLog` en base et dispatchent un message Symfony Messenger (transport Doctrine) ;
- répondent immédiatement **`201 Created` avec un corps vide** — le traitement est asynchrone ;
- le suivi se fait via `GET /action_logs` (clé = nom de l'action).

Exemple de requête (valable pour les 11 endpoints, en adaptant le chemin) :

```bash
curl -u web:douze -X POST http://web:8080/istration/update/pokemons
```

Exemple de réponse (`201`) : corps vide.

Codes de statut : `201`, `401`.

### Calculs (`/istration/calculate`)

| # | Chemin | Action (clé dans `/action_logs`) | Description |
|---|--------|----------------------------------|-------------|
| 21 | POST `/istration/calculate/game_bundles_availabilities` | `calculate_game_bundles_availabilities` | Recalcule les disponibilités agrégées par bundle de jeux |
| 22 | POST `/istration/calculate/game_bundles_shinies_availabilities` | `calculate_game_bundles_shinies_availabilities` | Recalcule les disponibilités shiny agrégées par bundle |
| 23 | POST `/istration/calculate/dex_availabilities` | `calculate_dex_availabilities` | Recalcule les pokémons disponibles par dex |
| 24 | POST `/istration/calculate/pokemon_availabilities` | `calculate_pokemon_availabilities` | Recalcule les disponibilités par pokémon |

### Synchronisations Google Sheets (`/istration/update`)

| # | Chemin | Action (clé dans `/action_logs`) | Description |
|---|--------|----------------------------------|-------------|
| 25 | POST `/istration/update/labels` | `update_labels` | Sync des labels (noms, formes) |
| 26 | POST `/istration/update/games_collections_and_dex` | `update_games_collections_and_dex` | Sync des jeux, collections et dex |
| 27 | POST `/istration/update/pokemons` | `update_pokemons` | Sync des pokémons |
| 28 | POST `/istration/update/regional_dex_numbers` | `update_regional_dex_numbers` | Sync des numéros de dex régionaux |
| 29 | POST `/istration/update/games_availabilities` | `update_games_availabilities` | Sync des disponibilités par jeu |
| 30 | POST `/istration/update/games_shinies_availabilities` | `update_games_shinies_availabilities` | Sync des disponibilités shiny par jeu |
| 31 | POST `/istration/update/collections_availabilities` | `update_collections_availabilities` | Sync des disponibilités par collection |

---

## Debug (`/debogage`)

Les entités sont résolues par slug via `#[MapEntity]` : un slug inconnu produit un **404**.

### 32. GET `/debogage/dex/{slug}`

Détail brut d'un dex (toutes colonnes, y compris `identifier` UUID et `deleted_at`).

**Paramètres de route** :

| Paramètre | Type | Description |
|-----------|------|-------------|
| `slug` | string | Slug du dex |

Exemple de requête :

```bash
curl -u web:douze http://web:8080/debogage/dex/redgreenblueyellow
```

Exemple de réponse (`200`) :

```json
{
  "identifier": "0c9c1ea9-0a1a-4b0f-9d7e-2f4a4f4ce0a1",
  "slug": "redgreenblueyellow",
  "name": "Red / Green / Blue / Yellow",
  "french_name": "Rouge / Vert / Bleu / Jaune",
  "order_number": 10,
  "selection_rule": "",
  "flags": {
    "is_shiny": false,
    "is_premium": false,
    "is_display_form": true,
    "is_released": true,
    "can_hold_election": false
  },
  "display_template": "box",
  "region": {
    "identifier": "3f2b8a7e-5c44-4d1b-8f7a-9e1d2c3b4a55",
    "slug": "kanto",
    "name": "Kanto",
    "french_name": "Kanto",
    "order_number": 1,
    "deleted_at": null
  },
  "description": "First generation Pokédex",
  "french_description": "Pokédex de la première génération",
  "last_changed_at": "20230221.085100",
  "election_order_number": 0,
  "deleted_at": null
}
```

Codes de statut : `200`, `401`, `404`.

---

### 33. GET `/debogage/dex/{slug}/availabilities`

Liste des slugs de pokémons disponibles dans un dex (résultat du calcul `dex_availabilities`).

Exemple de requête :

```bash
curl -u web:douze http://web:8080/debogage/dex/redgreenblueyellow/availabilities
```

Exemple de réponse (`200`) :

```json
{
  "pokemons": [
    { "slug": "bulbasaur" },
    { "slug": "ivysaur" },
    { "slug": "venusaur" },
    { "slug": "caterpie" },
    { "slug": "metapod" },
    { "slug": "butterfree" },
    { "slug": "douze" }
  ]
}
```

Codes de statut : `200`, `401`, `404`.

---

### 34. GET `/debogage/pokemon/{slug}`

Détail brut d'un pokémon (toutes colonnes, formes et types détaillés).

**Paramètres de route** :

| Paramètre | Type | Description |
|-----------|------|-------------|
| `slug` | string | Slug du pokémon (ex : `venusaur-mega`) |

Exemple de requête :

```bash
curl -u web:douze http://web:8080/debogage/pokemon/venusaur-mega
```

Exemple de réponse (`200`) :

```json
{
  "identifier": "7a1b2c3d-4e5f-6a7b-8c9d-0e1f2a3b4c5d",
  "slug": "venusaur-mega",
  "name": "Mega Venusaur",
  "french_name": "Mega Florizarre",
  "simplified_name": "Venusaur",
  "simplified_french_name": "Florizarre",
  "forms_label": "Mega",
  "forms_french_label": "Mega",
  "national_dex_number": 3,
  "family": "bulbasaur",
  "bankable": false,
  "bankableish": null,
  "icon_name": "venusaur-mega",
  "family_order": 3,
  "original_game_bundle": {
    "identifier": "9b8c7d6e-5f4a-3b2c-1d0e-f9a8b7c6d5e4",
    "slug": "xy",
    "name": "X, Y",
    "french_name": "X, Y",
    "order_number": 60,
    "generation": {
      "identifier": "1a2b3c4d-5e6f-7a8b-9c0d-e1f2a3b4c5d6",
      "slug": "6",
      "name": "Generation 6",
      "deleted_at": null
    },
    "deleted_at": null
  },
  "forms": {
    "category": null,
    "regional": null,
    "special": {
      "identifier": "2b3c4d5e-6f7a-8b9c-0d1e-f2a3b4c5d6e7",
      "slug": "mega",
      "name": "Mega",
      "french_name": "Mega",
      "order_number": 1,
      "deleted_at": null
    },
    "variant": null
  },
  "types": {
    "primary": {
      "identifier": "3c4d5e6f-7a8b-9c0d-1e2f-a3b4c5d6e7f8",
      "slug": "grass",
      "name": "Grass",
      "french_name": "Plante",
      "order_number": 5,
      "color": "#78C850",
      "deleted_at": null
    },
    "secondary": {
      "identifier": "4d5e6f7a-8b9c-0d1e-2f3a-b4c5d6e7f8a9",
      "slug": "poison",
      "name": "Poison",
      "french_name": "Poison",
      "order_number": 8,
      "color": "#A040A0",
      "deleted_at": null
    }
  },
  "deleted_at": null
}
```

Codes de statut : `200`, `401`, `404`.

---

### 35. GET `/debogage/pokemon/{slug}/availabilities`

Disponibilités calculées d'un pokémon, par jeu et par bundle (normal et shiny). Chaque liste contient des objets `{ game | game_bundle, is_available }`.

Exemple de requête :

```bash
curl -u web:douze http://web:8080/debogage/pokemon/venusaur-mega/availabilities
```

Exemple de réponse (`200`, tronquée) :

```json
{
  "games_availabilities": [
    { "game": { "slug": "x" }, "is_available": true },
    { "game": { "slug": "y" }, "is_available": true },
    { "game": { "slug": "omegaruby" }, "is_available": true },
    { "game": { "slug": "alphasapphire" }, "is_available": true }
  ],
  "games_shinies_availabilities": [
    { "game": { "slug": "x" }, "is_available": true },
    { "game": { "slug": "y" }, "is_available": true }
  ],
  "game_bundles_availabilities": [
    { "game_bundle": { "slug": "goldsilvercrystal" }, "is_available": false },
    { "game_bundle": { "slug": "xy" }, "is_available": true }
  ],
  "game_bundles_shinies_availabilities": [
    { "game_bundle": { "slug": "goldsilvercrystal" }, "is_available": false },
    { "game_bundle": { "slug": "xy" }, "is_available": true }
  ]
}
```

Codes de statut : `200`, `401`, `404`.

---

### 36. DELETE `/debogage/pokemon/{slug}/caches`

Purge les caches de disponibilités d'un pokémon (jeux, jeux shiny, bundles, bundles shiny, collections).

Exemple de requête :

```bash
curl -u web:douze -X DELETE http://web:8080/debogage/pokemon/venusaur-mega/caches
```

Exemple de réponse (`200`) : corps vide.

Codes de statut : `200`, `401`, `404`.
