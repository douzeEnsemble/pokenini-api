# Election/ELO API — Response Structure Migration

**Endpoint:** `GET /election/top`  
**Version:** v1 (no versioning needed for this endpoint at this time)  
**Change type:** Breaking change  
**Status:** Live as of 2026-05-28

## Summary

The `GET /election/top` response structure has been refactored from a flat, prefixed object model to a nested, object-oriented structure. Data is now organized into distinct objects (pokemon, forms, types) rather than flat fields with prefixes (pokemon_slug, pokemon_name, etc.), improving API clarity and maintainability.

## Impact Assessment

### pokenini-back

**Current usage:** Calls `GET /election/top`, passes response through to clients.

**Change required:** None. Response remains a JSON array of ELO objects, fields are now nested.

**Testing:** Verify passthrough still works. Update schema if using schema validation.

### pokenini-web

**Current usage:** Calls `GET /election/top` via pokenini-back, renders in Twig templates.

**Change required:** Update Twig templates to access nested properties instead of flat fields.

**Before:**
```twig
{{ elo.pokemon_slug }}
{{ elo.pokemon_name }}
{{ elo.primary_type_slug }}
```

**After:**
```twig
{{ elo.pokemon.slug }}
{{ elo.pokemon.name }}
{{ elo.types.primary.slug }}
```

**Testing:** Verify template rendering produces correct output.

## Response Comparison

### Before (flat structure with prefixes)

```json
[
  {
    "elo": 1250.5,
    "significance": true,
    "pokemon_slug": "pikachu",
    "pokemon_name": "Pikachu",
    "pokemon_french_name": "Pikachu",
    "pokemon_national_dex_number": 25,
    "pokemon_simplified_name": null,
    "pokemon_forms_label": null,
    "pokemon_simplified_french_name": null,
    "pokemon_forms_french_label": null,
    "pokemon_icon": "pikachu.png",
    "pokemon_family_order": 1,
    "family_lead_slug": "pichu",
    "original_game_bundle_slug": "red-blue",
    "pokemon_order_number": "9999-0025-001",
    "category_form_slug": null,
    "category_form_name": null,
    "regional_form_slug": null,
    "regional_form_name": null,
    "special_form_slug": null,
    "special_form_name": null,
    "variant_form_slug": null,
    "variant_form_name": null,
    "primary_type_slug": "electric",
    "primary_type_name": "Electric",
    "primary_type_french_name": "Électrique",
    "secondary_type_slug": null,
    "secondary_type_name": null,
    "secondary_type_french_name": null
  }
]
```

### After (nested object structure)

```json
[
  {
    "elo": 1250.5,
    "significance": true,
    "pokemon": {
      "slug": "pikachu",
      "name": "Pikachu",
      "french_name": "Pikachu",
      "national_dex_number": 25,
      "simplified_name": null,
      "forms_label": null,
      "simplified_french_name": null,
      "forms_french_label": null,
      "icon": "pikachu.png",
      "family_order": 1,
      "family_lead_slug": "pichu",
      "original_game_bundle_slug": "red-blue",
      "order_number": "9999-0025-001"
    },
    "forms": null,
    "types": {
      "primary": {
        "slug": "electric",
        "name": "Electric",
        "french_name": "Électrique",
        "color": "#FFCC33"
      },
      "secondary": null
    }
  }
]
```

## Migration Steps for Clients

### 1. Update field accessors

Replace all field accesses with nested object notation:

**Old:** `$data['pokemon_slug']`  
**New:** `$data['pokemon']['slug']`

**Old:** `$data['primary_type_name']`  
**New:** `$data['types']['primary']['name']`

### 2. Handle optional nested objects

Forms and secondary type are now `null` when absent:

```twig
{% if elo.forms %}
  {# Forms object is present #}
  {% if elo.forms.regional %}
    Regional form: {{ elo.forms.regional.name }}
  {% endif %}
{% endif %}
```

### 3. Validate schema (if applicable)

If you use JSON schema validation, update schemas to reflect nested structure.

## Timeline

- **API deployed:** 2026-05-28
- **Client update deadline:** 2026-06-04
- **Support window:** Contact [team contact] if migration issues arise

## Questions?

See the design spec: `docs/superpowers/specs/2026-05-25-api-response-restructuring-design.md`
