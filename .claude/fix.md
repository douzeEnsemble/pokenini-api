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

- [ ] [moyenne] Nom de méthode `crimpActionLog()` sans sémantique claire
  Fichier : `src/ActionEnder/ActionEnderTrait.php:55`
  Suggestion : Renommer en `finalizeActionLog()`. Deux appels à mettre à jour dans le même fichier (lignes 25 et 37).

- [ ] [moyenne] `ReportsController` déclare un constructeur vide inutile
  Fichier : `src/Controller/ReportsController.php`
  Suggestion : Supprimer le `public function __construct() {}` — aucun service injecté.

- [ ] [moyenne] `ActionLogsController` déclare un constructeur vide inutile
  Fichier : `src/Controller/ActionLogsController.php`
  Suggestion : Idem — supprimer le constructeur vide.

- [ ] [moyenne] Logique de transformation de données non triviale dans un contrôleur
  Fichier : `src/Controller/ActionLogsController.php:33-93`
  Suggestion : Extraire la logique de parsing JSON, splitting et groupement des ActionLogs dans un `ActionLogsService` dédié, et ne garder dans le contrôleur que l'appel au service + la sérialisation.

- [ ] [moyenne] `PokemonsUpdater::getSqlParametersFromPokemon()` transmet `bankableish` comme `int` même quand `null` est attendu
  Fichier : `src/Updater/PokemonsUpdater.php`
  Suggestion : Le cast `(int) $pokemon['bankableish']` transforme `null` en `0`, ce qui perd l'information `nullable`. Utiliser `null === $pokemon['bankableish'] ? null : (int) $pokemon['bankableish']` ou adapter le SQL pour gérer correctement les nullables.

---

## Basse priorité

- [ ] [basse] `infection_text.log` et `infection_summary.log` présents à la racine du dépôt
  Fichier : `infection_text.log`, `infection_summary.log`
  Suggestion : Ajouter ces fichiers dans `.gitignore` (ils sont des artefacts de build locaux, pas des fichiers de configuration).

- [ ] [basse] Les fichiers `.phpunit.cache/` sont volumineux et non ignorés dans `.gitignore`
  Fichier : `.phpunit.cache/` (plusieurs centaines de fichiers de cache de couverture)
  Suggestion : Vérifier que `.phpunit.cache/` est bien dans `.gitignore` et supprimer les fichiers committés accidentellement si présents.

- [ ] [basse] `@SuppressWarnings("PHPMD.TooManyFields")` sur `Pokemon` masque un signal architectural
  Fichier : `src/Entity/Pokemon.php:12`
  Suggestion : Évaluer si certains champs peuvent être extraits dans un Value Object ou une entité associée (ex. `PokemonNames` pour les variantes de noms), afin de réduire le nombre de champs sans perte fonctionnelle.

- [ ] [basse] `src/Controller/.gitignore`, `src/Entity/.gitignore`, `src/Repository/.gitignore` vides ou inexpliqués
  Fichier : `src/Controller/.gitignore`, `src/Entity/.gitignore`, `src/Repository/.gitignore`
  Suggestion : Documenter pourquoi ces `.gitignore` sont nécessaires, ou les supprimer s'ils sont vides et inutiles.
