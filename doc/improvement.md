# Améliorations — pokenini-api

## Qualité du code

---

## Maintenabilité

---

## DevX

---

## Symfony 8.1 — Fonctionnalités à adopter

Priorisées par impact / effort pour ce projet (REST API + Messenger + Doctrine).

### Étape 1 — `#[Serialize]` sur les controllers *(impact fort, effort faible)*

- Annoter les méthodes de controllers qui retournent des Response DTOs pour éliminer le boilerplate `JsonResponse` / `json()`
- Vérifier la compatibilité avec la sérialisation actuelle (Symfony Serializer)
- Cibles prioritaires : tous les endpoints qui retournent un DTO directement

### Étape 2 — Messenger : retries et resets *(impact moyen, effort faible)*

- Auditer la config actuelle des transports (`MESSENGER_TRANSPORT_DSN`)
- Configurer les resets pour les workers longue durée (évite les connexions DB périmées après un long idle)
- Évaluer les "smarter retries" pour les jobs Google Sheets (actuellement susceptibles d'échouer sur quota)

### Étape 3 — Console : argument resolvers *(impact faible, effort faible)*

- Moderniser les commandes (`app:update:pokemons`, etc.) pour utiliser le binding d'arguments par type (UUID/ULID, enums)
- Réduire le boilerplate de parsing manuel dans `execute()`

### Étape 4 — DI : lazy env-var pour workers *(impact moyen, effort moyen)*

- Identifier les services injectés dans les MessageHandlers qui chargent des env vars lourdes au boot
- Activer le lazy autoloading pour les workers Messenger longue durée

### Étape 5 — HttpClient : mocking par client *(à évaluer)*

- Comparer le mocking natif par client vs. Moco pour les tests d'intégration Google Sheets
- Migrer si le gain de simplicité est réel (suppression du conteneur Moco en tests)

### Hors scope pour l'instant

- **HTTP-less apps** : pas adapté à une API REST
- **UUIDv7** : migration des entités possible mais coûteuse (chantier schema + data + tests)

