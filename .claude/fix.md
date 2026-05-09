# Correctifs — pokenini-api

## Recherche préliminaire

```bash
grep -rn "TODO\|FIXME\|HACK\|XXX\|@deprecated\|@todo" src/
```
**Aucun TODO/FIXME/HACK/XXX/@deprecated trouvé** dans `src/`.

---

## Haute priorité

---

## Priorité moyenne

---

## Basse priorité

- [ ] [basse] `@SuppressWarnings("PHPMD.TooManyFields")` sur `Pokemon` masque un signal architectural
  Fichier : `src/Entity/Pokemon.php:12`
  Suggestion : Évaluer si certains champs peuvent être extraits dans un Value Object ou une entité associée (ex. `PokemonNames` pour les variantes de noms), afin de réduire le nombre de champs sans perte fonctionnelle.

- [ ] [basse] `src/Controller/.gitignore`, `src/Entity/.gitignore`, `src/Repository/.gitignore` vides ou inexpliqués
  Fichier : `src/Controller/.gitignore`, `src/Entity/.gitignore`, `src/Repository/.gitignore`
  Suggestion : Documenter pourquoi ces `.gitignore` sont nécessaires, ou les supprimer s'ils sont vides et inutiles.
