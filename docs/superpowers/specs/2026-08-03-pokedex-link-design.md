# Design — Liens entre pokédex d'un même trainer (synchronisation du statut de capture)

## Contexte

Un trainer possède plusieurs `TrainerDex` (National, par jeu, Shiny Living, Home, dex custom...), chacun avec son propre historique de capture (`Pokedex.catchState`, par `(pokemon, trainer_dex)`). Aujourd'hui, capturer un Pokémon dans un dex n'a aucun effet sur les autres : un trainer qui veut maintenir plusieurs dex à jour en parallèle (ex: National + Shiny Living) doit cocher chaque Pokémon deux fois.

Le besoin : permettre à un trainer de **lier deux de ses propres `TrainerDex`** pour que changer le statut de capture sur l'un répercute automatiquement le même changement sur l'autre. Le lien est choisi par l'utilisateur (quels dex, quel sens), pas automatique.

Hors scope (tranché en amont) :
- Lier les pokédex de **deux comptes différents** (deux `trainerExternalId` distincts) — non demandé, laissé de côté.
- Réconciliation rétroactive des divergences existantes au moment de la création d'un lien — le lien ne s'applique qu'aux changements futurs.
- Propagation asynchrone (queue Messenger) — la cascade se fait de façon synchrone, dans la requête HTTP d'origine.

## Décisions de modèle

- **Copie exacte de la valeur** : le statut de capture (`no`/`toevolve`/`tobreed`/`totransfer`/`totrade`/`yes`) est copié tel quel sur le dex lié — pas de réduction à un booléen capturé/non-capturé. Les deux dex partagent la même table `catch_state`, donc les slugs sont toujours valides des deux côtés.
- **Graphe, pas de paires exclusives** : un `TrainerDex` peut être lié à plusieurs autres simultanément. Les liens forment un graphe orienté quelconque (y compris des cycles via des liens bidirectionnels).
- **Propagation transitive** : un changement se propage à travers toute la chaîne atteignable (A→B→C), pas seulement aux voisins directs.
- **Terminaison par idempotence, pas par visited-set** : à chaque nœud atteint, on n'écrit et on ne continue la propagation **que si la valeur change réellement**. Si le nœud a déjà exactement ce `catch_state` pour ce Pokémon, on s'arrête là. Cette règle suffit à arrêter toute boucle (y compris revenir à l'origine, déjà à la bonne valeur) sans bookkeeping séparé — un changement de valeur ne peut se propager qu'un nombre fini de fois puisque chaque saut consomme un "changement" qui ne se reproduit pas la seconde fois qu'on atteint le même nœud avec la même valeur.
- **Disponibilité du Pokémon** : si le Pokémon n'existe pas dans le dex atteint (absent de sa `DexAvailability`), on saute l'écriture sur ce nœud mais on continue la traversée vers ses voisins.
- **Un lien "bidirectionnel" est un raccourci UI/API** pour créer les deux arêtes dirigées A→B et B→A d'un coup (et les supprimer ensemble) — le modèle de données ne connaît que des arêtes dirigées, ce qui évite un cas spécial dans l'algorithme de propagation.
- **Pas de réconciliation à la création** : créer un lien ne touche aucune donnée existante, seuls les changements de statut *postérieurs* au lien déclenchent une propagation.

## pokenini-api

### Entité `TrainerDexLink`

```php
#[Entity]
#[UniqueConstraint(name: 'trainer_dex_link_edge', columns: ['source_trainer_dex_id', 'target_trainer_dex_id'])]
class TrainerDexLink
{
    use BaseEntityTrait; // id (uuid v4) + getIdentifier()

    public string $trainerExternalId = '';
    public TrainerDex $sourceTrainerDex;
    public TrainerDex $targetTrainerDex;
    public ?string $pairId = null; // partagé par les 2 lignes d'un lien "bidirectionnel", null pour un lien unidirectionnel
    public \DateTimeImmutable $createdAt;
}
```

- `trainerExternalId` dupliqué depuis les deux `TrainerDex` (qui appartiennent nécessairement au même trainer, vérifié à la création) — simplifie les requêtes de listing sans jointure.
- `pairId` : uniquement pour que la suppression d'un lien bidirectionnel retire les deux arêtes en une action ; ignoré par l'algorithme de propagation, qui ne raisonne qu'en arêtes dirigées.
- Migration Doctrine : `make sf c="doctrine:migration:diff --no-interaction"`.

### Repository — `TrainerDexLinkRepository`

- `getOutgoingEdges(string $trainerExternalId, string $sourceTrainerDexId): list<array{target_trainer_dex_id: string, target_dex_slug: string}>` — utilisé par la propagation.
- `getForDex(string $trainerExternalId, string $dexSlug): list<array{...}>` — liens sortants ET entrants pour l'affichage (le volet montre "Vers ce dex" / "Depuis ce dex" / "Bidirectionnel").
- `exists(string $sourceTrainerDexId, string $targetTrainerDexId): bool` — check de doublon avant insert.
- `insert(string $trainerExternalId, string $sourceTrainerDexId, string $targetTrainerDexId, ?string $pairId): void`
- `deleteByIdOrPairId(string $trainerExternalId, string $id): void` — si la ligne a un `pairId`, supprime les deux lignes qui le partagent ; sinon supprime la ligne seule.

### Service — `TrainerDexLinkService`

Couche fine de validation au-dessus du repository, appelée par le contrôleur :
- `create(string $trainerExternalId, string $sourceDexSlug, string $targetDexSlug, bool $bidirectional): TrainerDexLink` — résout les deux `dexSlug` en `TrainerDex` du trainer (404 si un slug n'existe pas pour ce trainer), rejette `source === target` (400), rejette si l'arête existe déjà (409).
- `delete(string $trainerExternalId, string $linkId): void`
- `listForDex(string $trainerExternalId, string $dexSlug): array`

### Propagation — `PropagateCatchStateService`

Nouveau service, appelé par `PokedexService::upsert()` immédiatement après l'upsert d'origine :

```php
public function propagate(
    string $trainerExternalId,
    string $originTrainerDexId,
    string $pokemonSlug,
    string $catchStateSlug,
): void {
    $queue = $this->repository->getOutgoingEdges($trainerExternalId, $originTrainerDexId);

    while ($edge = array_shift($queue)) {
        $changed = $this->pokedexRepository->upsertIfDifferent(
            $edge['target_trainer_dex_id'],
            $pokemonSlug,
            $catchStateSlug,
        ); // no-op (et retourne false) si le Pokémon n'est pas dans ce dex, ou si le catch_state y est déjà identique

        if ($changed) {
            $queue = array_merge(
                $queue,
                $this->repository->getOutgoingEdges($trainerExternalId, $edge['target_trainer_dex_id']),
            );
        }
    }
}
```

- `PokedexRepository::upsertIfDifferent(...)` : variante de `upsert()` qui ne fait l'`UPDATE`/`INSERT` que si la valeur diffère (`WHERE catch_state_id IS DISTINCT FROM excluded.catch_state_id` côté SQL) et retourne un booléen "a changé", en créant au passage le `Pokedex`/`TrainerDex` cible si besoin (même logique que `TrainerDexService::insertIfNeeded` déjà appelée par `AlbumUpsertController`).
- `PokedexService::upsert()` appelle `propagate()` après son upsert d'origine, seulement si l'upsert d'origine a effectivement changé la valeur (même règle d'idempotence dès le premier maillon).
- Toute la cascade tient dans la transaction Doctrine existante de la requête — pas de Messenger.

### Contrôleur — `TrainerDexLinkController`

```
GET    /trainer_dex_link/{trainerExternalId}/{dexSlug}          — liste (sortants + entrants) pour ce dex
POST   /trainer_dex_link/{trainerExternalId}                    — body JSON {sourceDexSlug, targetDexSlug, bidirectional}
DELETE /trainer_dex_link/{trainerExternalId}/{linkId}
```

Réponses : `TrainerDexLinkResponse` (id, direction déduite : `to`/`from`/`both`, slug + nom + bannière du dex à l'autre bout — le front n'a pas à recalculer). Comme le reste de l'API, aucune authentification ici ; l'autorisation (le lien ne peut porter que sur les dex du trainer courant) est appliquée en amont par `pokenini-back`/`pokenini-web`.

### Tests

- Unitaires : `TrainerDexLinkServiceTest` (validation self-link, doublon, dex inconnu), `PropagateCatchStateServiceTest` (cascade transitive, cycle A↔B, cycle A→B→C→A, Pokémon absent d'un dex intermédiaire, arrêt par idempotence), `PokedexRepositoryUpsertIfDifferentTest`.
- Intégration : `TrainerDexLinkControllerTest` (CRUD + 404/400/409), `AlbumUpsertControllerTest` étendu avec un cas "lien actif → l'autre dex est mis à jour dans la même requête".

## pokenini-back

### Cache — correction nécessaire

`ModifyTrainerAlbumService::modifyAlbum()` n'invalide aujourd'hui que `AlbumCacheInvalidatorService::invalidate($dexSlug, $trainerId)` pour le `dexSlug` d'origine. Avec la cascade, d'autres dex sont désormais modifiés côté API **sans que le back ne le sache** — leur cache resterait périmé jusqu'à expiration du TTL.

Correction : `AlbumUpsertController::upsert()` (API) répond avec la liste des `dexSlug` réellement modifiés (origine incluse) au lieu d'un corps vide :
```json
{"updatedDexSlugs": ["national", "shiny-living"]}
```
`ModifyAlbumApiService::modify()` retourne ce tableau ; `ModifyTrainerAlbumService::modifyAlbum()` boucle dessus et appelle `$this->albumCacheInvalidatorService->invalidate($slug, $trainerId)` pour chacun.

### Endpoints (proxy fin, même pattern que l'existant)

```
GET    /album_link/{dexSlug}      — liste (sortants + entrants) pour ce dex
POST   /album_link/{dexSlug}      — body JSON {targetDexSlug, bidirectional}
DELETE /album_link/{linkId}
```

`TrainerDexLinkController` réexpose ces routes sous `ROLE_TRAINER`, `trainerExternalId` toujours résolu depuis la session (`UserTokenService::getLoggedUserToken()`), jamais depuis un paramètre — comme pour l'écriture de statut (`sourceDexSlug` de l'appel API est donc toujours le `dexSlug` de la route, pas un paramètre client). Aucune logique de cascade ici.

### Tests

- `ModifyTrainerAlbumServiceTest` mis à jour (invalidation multi-dex).
- `TrainerDexLinkControllerTest` (proxy + `ROLE_TRAINER`).

## pokenini-web

### Backend

- `TrainerDexLinkController` (même position qu'`AlbumUpsertController`), mêmes routes que `pokenini-back` (`GET`/`POST /album_link/{dexSlug}`, `DELETE /album_link/{linkId}`), simple relais vers `pokenini-back`.
- `ResponseObject/TrainerDexLink` — désérialisé de la réponse JSON du back.
- Le `PATCH`/`PUT` existant de statut de capture ne change pas : la cascade est invisible pour lui, elle se passe côté API avant que la réponse ne revienne.

### UI — section "Liens" dans l'offcanvas existant de l'album

Réutilisation de `templates/Album/_offcanvas.html.twig` (déjà ouvert via l'icône "..." de `_intro.html.twig`, `data-bs-toggle="offcanvas"`) : nouvelle section entre "Paramètres" (`is_private`/`is_on_home`) et "Informations", même style (`<h2 class="h5">`, `list-group`, `form-check`).

Contenu de la section :
- Liste des liens actifs du dex courant (miniature de bannière du dex à l'autre bout, nom, sens — `↔`/`→`/`←` — bouton suppression).
- Sélecteur d'ajout : grille de cartes avec **bannière du dex** (réutilise `dexBannerUrl`, comme les cartes de `/trainer`) pour choisir le dex cible parmi les autres `TrainerDex` du trainer — pas un `<select>` de noms.
- Choix du sens via un `btn-group` à 3 options radio (`→ Vers lui` / `← Depuis lui` / `↔ Les deux`).
- Uniquement visible si `allowedToEdit` (même garde que la section "Paramètres").

Maquette de référence (basée sur les vraies classes Bootstrap 5.3.8 du projet) : `docs/superpowers/specs/mockups/2026-08-03-pokedex-link/offcanvas-links-section.html`.

### JS — `public/js/album-links.js` (nouveau, même pattern que `album-edit.js`)

- Au chargement du volet : `fetch('/album_link/' + dexSlug)` (GET) pour peupler la liste + la grille de sélection (dex non déjà liés, dex courant exclu).
- Clic sur une carte du sélecteur + choix du sens → `POST /album_link/{dexSlug}` avec `{targetDexSlug, bidirectional}`.
- Clic sur la corbeille d'un lien → `DELETE /album_link/{linkId}`.
- Toasts succès/erreur, même mécanisme Bootstrap que `album-edit.js`.

### Tests

- Intégration : rendu de la section "Liens" dans l'offcanvas (avec/sans lien existant, `allowedToEdit` false → section absente).
- Browser (Panther) : créer un lien, changer un statut sur le dex A, vérifier que le dex B (lien bidirectionnel) reflète le changement après rechargement.

## Edge cases couverts

| Cas | Comportement |
|---|---|
| Lier un dex à lui-même | 400 |
| Lien déjà existant (même arête dirigée) | 409 |
| Dex premium non accessible (non-collector) | 404, même règle que l'édition de statut |
| Pokémon absent du dex atteint pendant la cascade | Écriture sautée sur ce nœud, traversée poursuivie au-delà |
| Cycle de liens (A↔B, ou A→B→C→A) | Terminaison automatique par idempotence (pas de re-propagation si la valeur est déjà celle attendue) |
| Suppression d'un lien bidirectionnel | Les deux arêtes (même `pairId`) sont supprimées ensemble |
