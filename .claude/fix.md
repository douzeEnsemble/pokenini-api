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

