# Améliorations — pokenini-api

## Qualité du code

---

## Tests

### 7 — Absence de tests sur les cas d'erreur des Updaters (InvalidSheetDataException)

**Problème** : Les tests d'intégration des commandes testent le chemin nominal, mais pas les cas où la feuille Google Sheets a des colonnes manquantes ou mal nommées (ce qui lève `InvalidSheetDataException`).
**Fichier(s)** : `tests/src/Integration/Command/UpdatePokemonsCommandTest.php`
**Correction** : Ajouter un fichier Moco avec des headers invalides et tester que la commande échoue proprement et enregistre l'erreur dans l'ActionLog.

---

## Sécurité

### 8 — HTTP Basic Auth avec credentials en clair dans les tests

**Problème** : Les credentials d'authentification (`web` / `douze`) sont hardcodés dans les tests d'intégration.
**Fichier(s)** : `tests/src/Integration/Controller/AbstractTestControllerApi.php:57`, `tests/src/Integration/Controller/PokemonsControllerTest.php`
**Correction** : Extraire les credentials dans des constantes ou des variables d'environnement de test, pour faciliter leur changement sans modifier les tests.

### 9 — Endpoints admin non protégés par un rôle dédié

**Problème** : Les endpoints `/istration/update/*` et `/istration/calculate/*` sont derrière le même HTTP Basic Auth que les endpoints utilisateur. Il n'y a pas de rôle `ROLE_ADMIN` dédié.
**Fichier(s)** : `config/packages/security.yaml`, `src/Controller/AdminUpdateController.php`
**Correction** : Créer un rôle `ROLE_ADMIN` distinct avec des credentials séparés dans `security.yaml`, et restreindre les routes admin à ce rôle.

---

## Maintenabilité

### 10 — Absence d'interface ou de contrat sur les entités portant `SoftDeleteable`

**Problème** : Le soft delete est implémenté via un trait mais aucune interface ne garantit la présence de `$deletedAt` sur les entités concernées. Les repositories qui filtrent `deleted_at IS NULL` doivent le faire manuellement sans vérification statique.
**Fichier(s)** : `src/Entity/Traits/SoftDeleteable.php`, `src/Repository/PokemonsRepository.php:25`
**Correction** : Créer une interface `SoftDeleteableInterface` avec `getDeletedAt(): ?\DateTime` et la faire implémenter par les entités qui utilisent le trait.

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
