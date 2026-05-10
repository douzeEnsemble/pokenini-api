# Améliorations — pokenini-api

## Qualité du code

---

## Maintenabilité

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
