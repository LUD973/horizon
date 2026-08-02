> ⚠️ **Historique — remplacé en Semaine 6.** Didomi a été remplacé par
> **tarteaucitron.js** (voir `docs/CONSENT_TARTEAUCITRON.md`). Ce document est
> conservé comme trace de l'implémentation d'origine (Semaine 5), le code
> Didomi n'existe plus dans le plugin.

# Consentement (Didomi) — Documentation technique

## Ce qui est automatisé par le plugin
- Injection du **snippet officiel Didomi**, tel que configuré, en tout début
  de `<head>` (`wp_head`, priorité 1), **une seule fois** par page.
- **Aucune sortie** si `DIDOMI_SDK_EMBED` n'est pas configuré : pas de bandeau,
  pas d'appel réseau, pas d'erreur. Le site reste pleinement fonctionnel.
- Façade JS `window.fcpConsent` (`assets/js/consent.js`) :
  - `hasConsent('functional'|'analytics'|'marketing')` — `functional` toujours
    `true` ; `analytics`/`marketing` = **deny-by-default** tant que Didomi n'a
    pas confirmé le consentement.
  - `onChange(callback)` — plusieurs abonnés possibles, informés à l'état
    initial puis à chaque changement.
  - `openPreferences()` — réouvre le panneau Didomi (`Didomi.preferences.show()`),
    prêt pour un futur lien **« Gérer mes cookies »** (non ajouté au footer dans
    ce lot — hors périmètre).
- Configuration centralisée (`Support\Config`) : aucune valeur en dur, aucun
  identifiant fictif.

## Ce qui doit être configuré dans la console Didomi (action manuelle)
1. **Console Didomi** → votre site → onglet **Publish**.
2. Copier le **snippet exact** proposé (balise(s) `<script>`).
3. Coller ce snippet dans la variable de configuration serveur
   `DIDOMI_SDK_EMBED` (voir ci-dessous — **pas dans le dépôt Git**).
4. Vérifier/renseigner les **identifiants de purpose** utilisés par votre
   organisation Didomi pour « Analytics » et « Marketing » (ils peuvent
   différer des valeurs par défaut du plugin) → `DIDOMI_PURPOSE_ANALYTICS`,
   `DIDOMI_PURPOSE_MARKETING`.
5. Si Plausible est déclaré comme **vendor personnalisé** dans Didomi (lot
   Plausible), récupérer son identifiant de vendor → `DIDOMI_VENDOR_PLAUSIBLE`.
6. **Apparence du bandeau** (charte FCP, sans dark pattern) : à régler dans la
   console Didomi — boutons **Accepter**, **Refuser** et **Personnaliser** de
   poids visuel égal, aucune case pré-cochée pour le marketing, ton sobre
   cohérent avec l'ADN de la Maison.

## Variables d'environnement (noms uniquement — aucune valeur ici)
| Variable | Rôle | Sensible ? |
|---|---|---|
| `DIDOMI_NOTICE_ID` | Informatif (référence de la notice) — n'entraîne aucun comportement automatique | non |
| `DIDOMI_SDK_EMBED` | **Snippet officiel complet** copié depuis Publish | non (public, mais à traiter comme config, jamais dans Git) |
| `DIDOMI_PURPOSE_ANALYTICS` | Identifiant de purpose « Analytics » (défaut : `analytics`) | non |
| `DIDOMI_PURPOSE_MARKETING` | Identifiant de purpose « Marketing » (défaut : `advertising`) | non |
| `DIDOMI_VENDOR_PLAUSIBLE` | Identifiant de vendor Plausible si déclaré (réservé, lot Plausible) | non |

> `DIDOMI_SDK_EMBED` contient du balisage `<script>` multi-lignes : à définir
> de préférence via `define('DIDOMI_SDK_EMBED', '...');` dans `wp-config.php`
> (les variables d'environnement shell gèrent mal le multi-lignes).

## Séparation stricte des consentements (rappel)
- **Didomi** gère uniquement les traceurs/services du **site** (analytics,
  marketing publicitaire éventuel).
- Les consentements **métier Horizon** (traitement de la demande, opt-in
  marketing du formulaire) restent enregistrés dans `contacts` — **jamais**
  dans Didomi, **jamais** dupliqués. Ce lot ne modifie rien à `EnquiryValidator`
  ni aux tables `contacts`/`enquiries`.
- La catégorie interne **« functional »** exprime « toujours autorisé » et
  n'est **jamais** envoyée à l'API Didomi (fonctionnalités strictement
  nécessaires : nonce, session, navigation).

## Mécanisme de chargement conditionnel natif (à appliquer lors du lot Plausible)
Le blocage réel des scripts soumis à consentement est un mécanisme **fourni
par Didomi lui-même** — ce plugin ne réimplémente aucun moteur de
clonage/remplacement de nœuds. Deux voies officielles existent côté Didomi :

1. **Blocage automatique par vendor** (recommandé si disponible) : déclarer
   Plausible comme vendor dans la console Didomi et activer le blocage
   automatique par domaine — **aucun code requis** côté plugin.
2. **Balisage manuel** : envelopper le script avec les attributs `data-purpose`
   / `data-vendor` selon la convention exacte de **votre** configuration Didomi
   (Tag Manager / Web SDK). ⚠️ **L'attribut `type` exact (`text/plain`,
   `didomi/javascript` ou autre) dépend de la version/du mode d'intégration
   Didomi utilisé** — il doit être vérifié dans leur documentation au moment
   de brancher Plausible, plutôt que supposé à l'avance. Aucune valeur n'est
   donc figée dans le code de ce lot.

Dans les deux cas, `fcpConsent.hasConsent('analytics')` (et, si un vendor est
déclaré, la vérification côté Didomi) reste la source de vérité côté Horizon
pour toute logique applicative qui aurait besoin de connaître l'état du
consentement (indépendamment du mécanisme de blocage choisi).

## Tests
- **Unitaires** (`ConsentCategoryTest`, `ConsentConfigTest`) : catégories
  valides, `functional` jamais géré par Didomi, configuration lue sans valeur
  en dur, comportement par défaut sans configuration.
- **Hors périmètre PHPUnit** (vérifiés en recette staging, cf. ci-dessous) :
  injection unique réelle dans le navigateur, absence de sortie sans
  configuration, réaction à `consent.changed`, robustesse si le SDK est lent —
  logique intrinsèquement côté navigateur, cohérent avec la couverture ciblée
  du projet (Domain testé unitairement ; le reste vérifié en recette).
- **Régression** : 48 tests au total, **0 impact** sur les 43 tests
  précédents (Communication/Brevo, formulaire, back-office, référence FCP…).

## Procédure de recette (staging)
1. **Sans configuration** : `DIDOMI_SDK_EMBED` vide → ouvrir le site → aucune
   bannière, aucune erreur console, `window.fcpConsent` présent,
   `hasConsent('analytics')` renvoie `false`, `hasConsent('functional')` `true`.
2. **Avec configuration** : coller le snippet officiel dans `wp-config.php` →
   recharger → bandeau visible, conforme à la charte (boutons équilibrés).
3. Ouvrir la console navigateur → vérifier une **seule** injection du SDK
   (pas de script dupliqué dans le `<head>`).
4. **Refuser tout** → `fcpConsent.hasConsent('analytics')` = `false`.
5. **Accepter Analytics** → passe à `true` ; un `onChange` enregistré est bien
   notifié.
6. Revenir sur le site plus tard (cookie déjà déposé) → l'état initial reflète
   le choix précédent dès `didomiOnReady`.
7. Vérifier qu'aucune régression n'affecte `/demande/` (parcours complet) ni
   le back-office Horizon.
8. (Si un lien « Gérer mes cookies » est ajouté ultérieurement) vérifier que
   `fcpConsent.openPreferences()` rouvre bien le panneau.

## Points de vigilance
- Le **snippet exact** doit être celui de Publish — ne pas le reconstruire.
- Les **identifiants de purpose** par défaut (`analytics`/`advertising`) sont
  des valeurs usuelles Didomi mais **doivent être vérifiés** contre votre
  organisation.
- L'**attribut exact** de blocage natif (`type=...`) n'est **pas** figé dans ce
  lot — à vérifier au moment du lot Plausible.
- Contenu de `DIDOMI_SDK_EMBED` : traité comme configuration de confiance
  (imprimé sans échappement) — ne doit **jamais** provenir d'une entrée
  utilisateur ni être committé avec une valeur réelle.
