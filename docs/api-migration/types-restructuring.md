# Types API — Response Structure Migration

**Endpoint:** `GET /types`  
**Version:** v1 (no versioning needed for this endpoint at this time)  
**Change type:** Breaking change  
**Status:** Live as of [DATE]

## Summary

The `GET /types` response structure has been refactored from a flat object model to a more explicitly nested object model. This improves API consistency and clarity.

## Impact Assessment

### pokenini-back

**Current usage:** Calls `GET /types`, passes response through to clients.

**Change required:** None. Response remains a JSON array of type objects.

**Testing:** Verify passthrough still works. No schema changes needed.

### pokenini-web

**Current usage:** Calls `GET /types` via pokenini-back, renders in Twig templates.

**Change required:** None. Response structure is identical — fields have same names (slug, name, french_name, color).

**Testing:** Verify Twig rendering still produces correct output.

## Response Comparison

### Before (structure is identical, showing for reference)

```json
[
  {
    "slug": "electric",
    "name": "Electric",
    "french_name": "Électrique",
    "color": "#FFCC33"
  }
]
```

### After

```json
[
  {
    "slug": "electric",
    "name": "Electric",
    "french_name": "Électrique",
    "color": "#FFCC33"
  }
]
```

**Note:** For the Types endpoint, the response structure is identical. The refactoring is internal (DTOs + Serializer). For future endpoints (Election/ELO), nested structures will differ significantly — see `docs/api-migration/` for those when released.

## Timeline

- **API deployed:** [DATE — fill in on release]
- **Client update deadline:** [DATE + 1 week]
- **Support window:** Contact [team contact] if migration issues arise

## Questions?

See the design spec: `docs/superpowers/specs/2026-05-25-api-response-restructuring-design.md`
