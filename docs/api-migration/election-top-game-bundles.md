# Election Top — game_bundles & game_bundles_shiny Population

**Endpoint:** `GET /election/top`
**Change type:** Additive (new fields populated — was always present but always empty `[]`)
**Status:** Live

## Summary

The `game_bundles` and `game_bundles_shiny` arrays in `GET /election/top` Pokémon objects were previously always returned as empty arrays `[]`. They now contain the actual game-bundle slugs from the `pokemon_availabilities` table, matching the behavior already live in `GET /album/{trainerExternalId}/{dexSlug}`.

## Response Comparison

### Before

```json
{
  "pokemon": {
    "slug": "pikachu",
    "game_bundles": [],
    "game_bundles_shiny": []
  }
}
```

### After

```json
{
  "pokemon": {
    "slug": "pikachu",
    "game_bundles": [
      { "slug": "redgreenblueyellow" },
      { "slug": "goldsilvercrystal" }
    ],
    "game_bundles_shiny": [
      { "slug": "heartgoldsoulsilver" }
    ]
  }
}
```

## Impact Assessment

### pokenini-back

**Change required:** Forward the populated arrays — update Moco fixture for `GET /election/top` if it hard-codes `"game_bundles": []`.

### pokenini-web

**Change required:** Optionally render game bundle badges for top-ELO Pokémon. The field was always present so no structural change needed.

## Questions?

See the design spec: `docs/superpowers/specs/2026-05-25-api-response-restructuring-design.md`
