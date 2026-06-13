# Album Forms — french_name Field Addition

**Endpoint:** `GET /album/{trainerExternalId}/{dexSlug}`  
**Change type:** Additive (new field in form objects)  
**Status:** Live

## Summary

The `forms.{category|regional|special|variant}` objects in `GET /album/…` responses now include a `french_name` field, matching the structure already present in `GET /pokemons/to_choose` and `GET /election/top`.

## Response Comparison

### Before

```json
{
  "forms": {
    "category": { "slug": "starter", "name": "Starter" },
    "regional": null,
    "special": null,
    "variant": null
  }
}
```

### After

```json
{
  "forms": {
    "category": { "slug": "starter", "name": "Starter", "french_name": "de Départ" },
    "regional": null,
    "special": null,
    "variant": null
  }
}
```

## Impact Assessment

### pokenini-back

**Change required:** Forward the new `french_name` field — update Moco fixture `tests/resources/moco/` for the album endpoint if applicable.

### pokenini-web

**Change required:** Optionally render `french_name` in Twig templates for form labels. Field is additive so no breaking change.

## Form french_name values (from fixtures)

| Slug | Name | french_name |
|------|------|-------------|
| `starter` | Starter | de Départ |
| `legendary` | Legendary | Légendaire |
| `mythical` | Mythical | Fabuleux |
| `alolan` | Alolan | d'Alola |
| `galarian` | Galarian | de Galar |
| `hisuian` | Hisuian | de Hisui |
| `mega` | Mega | Mega |
| `gigantamax` | Gigantamax | Gigamax |
| `totem` | Totem | Dominant |
| `gender` | Gender | Sexe |
