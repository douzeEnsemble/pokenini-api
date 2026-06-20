# Design — Messenger : reset_on_message pour workers longue durée

**Date :** 2026-06-20
**Scope :** Étape 2 du plan Symfony 8.1 (`doc/improvement.md`)

## Contexte

Le worker Messenger tourne en production dans un conteneur Docker dédié avec restart automatique :

```yaml
command: php /app/bin/console messenger:consume async
```

Après une période d'inactivité prolongée (pas de messages à consommer), la connexion PostgreSQL peut être coupée côté serveur ou firewall. Quand le prochain message arrive, l'EntityManager Doctrine tente d'utiliser une connexion périmée et échoue.

Configuration actuelle (`config/packages/messenger.yaml`) :
- Transport unique `async` (Doctrine, `doctrine://default?auto_setup=0`)
- `max_retries: 0` — les messages qui échouent vont directement en `failed`
- Pas de reset entre messages

## Décision

Ajouter `reset_on_message: true` sur le transport `async`. Pas de retries (inchangé).

**Pourquoi pas de retries :** les handlers `Update*` appellent Google Sheets (quota/réseau) et les handlers `Calculate*` font des calculs DB purs. Les deux types d'échecs sont soit des problèmes persistants (quota dépassé, bug) soit résolus par un re-dispatch manuel. Ajouter des retries automatiques masquerait les vrais problèmes sans apporter de valeur certaine.

## Changement

**Fichier :** `config/packages/messenger.yaml`

```yaml
framework:
  messenger:
    failure_transport: failed

    transports:
      async:
        dsn: "%env(MESSENGER_TRANSPORT_DSN)%"
        options:
          reset_on_message: true
        retry_strategy:
          max_retries: 0
      failed: "doctrine://default?queue_name=failed"
      sync: "sync://"

    routing:
      "*": async

when@test:
  framework:
    messenger:
      transports:
        async: "test://"
        sync: "test://"
```

## Effet

Après chaque message traité, Symfony appelle `ServiceResetterInterface::reset()` sur tous les services enregistrés. Pour Doctrine, cela ferme et rouvre la connexion DB et vide l'identity map de l'EntityManager. Le worker peut rester inactif indéfiniment sans risquer de connexion périmée au message suivant.

## Tests

Aucun test à écrire : `reset_on_message` est une option de configuration Messenger sans logique applicative à couvrir. Le comportement est garanti par Symfony.
