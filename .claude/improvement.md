# Améliorations — pokenini-api

## Qualité du code

---

## Maintenabilité

---

## DevX

### 13 — La commande `make data-app` est longue et non idempotente si un updater échoue

**Problème** : Si `app:update:games_collections_and_dex` échoue à mi-parcours, les commandes suivantes dans `data-app` peuvent produire des données incohérentes. Il n'y a pas de mécanisme de rollback ou de reprise partielle.
**Fichier(s)** : `Makefile` (target `data-app`), `bin/console app:update:*`
**Correction** : Documenter l'ordre d'exécution et les dépendances entre les commandes, et envisager un script `data-app` qui vérifie le succès de chaque étape avant de passer à la suivante (ex. avec `&&` dans le Makefile plutôt que des lignes séparées).
