# Améliorations — pokenini-api

## Qualité du code

---

## Maintenabilité

### 11 — Les fichiers SQL externalisés ne sont pas typés ni validés

**Problème** : Les fichiers `resources/sql/*.sql` sont lus via `file_get_contents()` sans aucune validation de structure. Un fichier manquant lève une `RuntimeException` couverte par `@codeCoverageIgnoreStart`, ce qui masque ce risque opérationnel.
**Fichier(s)** : `src/Repository/PokemonsRepository.php:88-104`
**Correction** : Centraliser la lecture des fichiers SQL dans un `SqlFileRepository` injectable, testable, et intégrer un test de fumée vérifiant que tous les fichiers `.sql` référencés existent bien au démarrage.

---

## DevX

### 12 — Pas de make target pour voir les violations Deptrac seules

**Problème** : `make code-quality` lance tous les outils en séquence. Pour déboguer rapidement une violation de dépendance, il faut connaître la commande Deptrac manuellement.
**Fichier(s)** : `Makefile`
**Correction** : Ajouter un target `make deptrac` dans le Makefile pour lancer uniquement Deptrac, comme il existe `make phpcsfixer-fix`.

### 13 — La commande `make data-app` est longue et non idempotente si un updater échoue

**Problème** : Si `app:update:games_collections_and_dex` échoue à mi-parcours, les commandes suivantes dans `data-app` peuvent produire des données incohérentes. Il n'y a pas de mécanisme de rollback ou de reprise partielle.
**Fichier(s)** : `Makefile` (target `data-app`), `bin/console app:update:*`
**Correction** : Documenter l'ordre d'exécution et les dépendances entre les commandes, et envisager un script `data-app` qui vérifie le succès de chaque étape avant de passer à la suivante (ex. avec `&&` dans le Makefile plutôt que des lignes séparées).
