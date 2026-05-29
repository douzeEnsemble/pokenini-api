# Game Bundles API — Response Structure Migration

**Endpoint:** `GET /game_bundles`
**Change type:** Breaking change
**Status:** Live as of [DATE — fill in on release]

## Summary

The `GET /game_bundles` response was refactored from a flat structure with a
`generation_slug` field to a nested structure with a `generation` object. This
aligns the endpoint with the object-oriented response format introduced for
`/types` and `/election/top` (issue #256).

## Impact Assessment

### pokenini-back

**Current usage:** Calls `GET /game_bundles`, caches and passes the response through.

**Change required:** Update any schema validation / DTO that maps `generation_slug`.
If the response is forwarded as-is, only mapped accessors need updating. Refresh the
Moco fixture used by pokenini-back's tests.

### pokenini-web

**Current usage:** Renders game bundles in Twig via pokenini-back.

**Change required:** Replace `generation_slug` accessors with the nested object.

**Before:**
```twig
{{ bundle.generation_slug }}
```

**After:**
```twig
{{ bundle.generation.slug }}
```

## Response Comparison

### Before (flat)

```json
[
  {
    "name": "Red, Green, Blue, Yellow",
    "french_name": "Rouge, Vert, Bleu, Jaune",
    "slug": "redgreenblueyellow",
    "generation_slug": "1"
  }
]
```

### After (nested generation)

```json
[
  {
    "slug": "redgreenblueyellow",
    "name": "Red, Green, Blue, Yellow",
    "french_name": "Rouge, Vert, Bleu, Jaune",
    "generation": { "slug": "1" }
  }
]
```

## Migration Steps for Clients

1. Replace `data['generation_slug']` with `data['generation']['slug']`.
2. Update JSON schemas / fixtures to the nested shape.
3. Verify rendering and caching still work end-to-end.

## Timeline

- **API deployed:** [DATE — fill in on release]
- **Client update deadline:** [DATE + 1 week]

## Questions?

See the design spec: `docs/superpowers/specs/2026-05-25-api-response-restructuring-design.md`
