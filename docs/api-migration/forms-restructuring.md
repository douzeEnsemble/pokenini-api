# Forms Endpoints — Response Restructuring

**Endpoints:**
- `GET /forms/category`
- `GET /forms/regional`
- `GET /forms/special`
- `GET /forms/variant`

**Change type:** Internal refactoring (non-breaking)
**Status:** Live

## Summary

The four `GET /forms/*` endpoints have been refactored to use the DTO + Factory + Serializer
pattern, aligning them with the rest of the API. The response shape is identical to the
previous implementation — no field was added, removed, or renamed.

All four endpoints share a single `FormResponse` DTO and a single `FormResponseFactory`,
since they all return objects with the same three fields (`slug`, `name`, `french_name`).

## Response Comparison

### Before

```json
[
  { "slug": "starter", "name": "Starter", "french_name": "de Départ" }
]
```

### After (identical)

```json
[
  { "slug": "starter", "name": "Starter", "french_name": "de Départ" }
]
```

No fields were added, removed, or renamed.

## Affected Endpoints

| Endpoint | Shared DTO | Shared Factory |
|----------|-----------|---------------|
| `GET /forms/category` | `FormResponse` | `FormResponseFactory` |
| `GET /forms/regional` | `FormResponse` | `FormResponseFactory` |
| `GET /forms/special` | `FormResponse` | `FormResponseFactory` |
| `GET /forms/variant` | `FormResponse` | `FormResponseFactory` |

## Impact Assessment

### pokenini-back

**Change required:** None. The response shape is identical.

**Testing:** Run existing integration tests to confirm no regression.

### pokenini-web

**Change required:** None. The response shape is identical.

**Testing:** Run existing tests to confirm no regression.
