# Analytics (Plausible) — Documentation technique

## Ce qui est automatisé par le plugin
- **Aucun script Plausible n'est jamais enqueué côté PHP** : `PublicSite\Analytics`
  ne fait que localiser la configuration (`domain`, `scriptUrl`, `configured`)
  et charger `assets/js/analytics.js` (dépendant de `fcp-consent`).
- Le script réel `<script defer data-domain="…" src="…">` n'est créé **en
  JavaScript** que lorsque `window.fcpConsent.hasConsent('analytics')` devient
  vrai — jamais avant, jamais par défaut.
- Façade `window.fcpAnalytics` :
  - `track(name, properties)` — non déduppliqué (clics de contact : chaque
    clic est une action réelle distincte).
  - `trackOnce(key, name, properties)` — dédupliqué par clé (événements de
    formulaire : une clé par page pour `viewed`/`started`, une clé par
    **tentative** pour `submitted`/`success`/`error`).
- **N'interroge jamais Didomi directement** : seule source de vérité =
  `window.fcpConsent` (façade du lot précédent). `analytics.js` ne réimplémente
  aucun moteur de chargement — il utilise le **shim officiel Plausible**
  (`window.plausible` / `.q`), qui tolère un script pas encore chargé.
- Délégation de clic générique (`tel:`, `mailto:`, liens contenant `wa.me`) —
  fonctionne dès aujourd'hui sur le bouton WhatsApp de confirmation, et
  automatiquement sur tout futur lien du thème, sans modification.

## Ce qui doit être configuré (action manuelle)
1. Créer le site dans Plausible (ou votre instance auto-hébergée).
2. Renseigner `PLAUSIBLE_DOMAIN` (le domaine exact déclaré côté Plausible).
3. Si auto-hébergé : `PLAUSIBLE_SCRIPT_URL` (doit être en HTTPS).
4. **Vendor Didomi (optionnel)** : si Plausible est déclaré comme vendor
   personnalisé dans votre console Didomi, renseigner `DIDOMI_VENDOR_PLAUSIBLE`
   (identifiant exact du vendor). Dans ce cas, `hasConsent('analytics')` exige
   **à la fois** le purpose Analytics et ce vendor. Laissé vide : comportement
   inchangé (purpose seul).

## Variables d'environnement (noms uniquement)
| Variable | Rôle | Défaut |
|---|---|---|
| `PLAUSIBLE_DOMAIN` | Domaine déclaré dans Plausible | — (vide = non configuré) |
| `PLAUSIBLE_SCRIPT_URL` | URL du script (auto-hébergement) | `https://plausible.io/js/script.js` |
| `DIDOMI_VENDOR_PLAUSIBLE` | Vendor Didomi pour Plausible, si déclaré | — (vide = purpose seul) |

Toutes en configuration serveur — aucune valeur en dur, aucun secret (le
domaine et l'URL de script sont des informations publiques, mais traitées
comme configuration, jamais committées avec une valeur réelle propre à
l'installation de production tant que non désirée en dépôt public).

## Événements suivis
| Événement | Propriétés | Règle |
|---|---|---|
| `enquiry_form_viewed` | `service_category` | une fois par page |
| `enquiry_form_started` | `service_category` | une fois par page, à la 1ʳᵉ interaction réelle |
| `enquiry_form_submitted` | `service_category` | une fois par **tentative** réelle d'envoi |
| `enquiry_form_success` | `service_category`, `contact_channel` | uniquement sur confirmation serveur réelle (201/`success:true`) |
| `enquiry_form_error` | *(aucune)* | uniquement sur échec, sans détail ni donnée saisie |
| `whatsapp_clicked` / `phone_clicked` / `email_clicked` | *(aucune)* | délégation générique, chaque clic réel compte |

**Aucune donnée personnelle** n'est jamais transmise : ni nom, ni e-mail, ni
téléphone, ni référence de demande, ni trajet/adresse, ni date de mission, ni
`href`/texte de lien. `contact_channel` est une valeur d'énumération fixe
(`email`/`whatsapp`/`phone`), pas une donnée identifiante.

## Mécanisme « submitted / success / error » (mutuellement exclusifs)
Chaque tentative réelle d'envoi obtient un **identifiant de tentative**
(`attemptId`, compteur en mémoire, incrémenté à chaque soumission client
validée). Les clés de déduplication `submitted:<id>` / `success:<id>` /
`error:<id>` garantissent :
- qu'une tentative ne peut produire `submitted` qu'une fois ;
- que `success` et `error` sont mutuellement exclusifs pour une même tentative
  (branches de code disjointes de la même réponse réseau) ;
- qu'un **nouvel essai volontaire** après un échec (l'utilisateur corrige et
  renvoie) obtient un **nouvel** `attemptId` → `enquiry_form_submitted` peut
  légitimement se déclencher à nouveau (pas de verrou permanent).

## File d'attente avant consentement
- **Mémoire uniquement** — jamais de `localStorage`/`sessionStorage`/cookie/
  base de données. Perdue à tout rechargement de page (comportement voulu).
- **Bornée** à 20 entrées (`MAX_QUEUE`) — au-delà, l'entrée la plus ancienne
  est évincée (FIFO). Généreux pour ce formulaire (4–6 événements par cycle
  normal) tout en empêchant une accumulation illimitée.
- **Dédupliquée par clé** : `viewed`/`started` (clé = nom de l'événement, un
  seul exemplaire par page) ; `submitted`/`success`/`error` (clé scoping par
  tentative, cf. ci-dessus). Les clics de contact ne sont volontairement pas
  déduppliqués (chaque clic est réel) mais restent soumis à la borne globale.

## Retrait du consentement après un premier octroi — LIMITE CONNUE
Si l'analytics est accordé (script potentiellement déjà chargé) puis **retiré**
plus tard dans la même page :
- **Aucun nouvel événement n'est envoyé** (vérifié à chaque appel de `track()`) ;
- la file en cours est **immédiatement vidée** (invalidée) ;
- si l'utilisateur **ré-accepte** ensuite dans la même page, **rien de ce qui a
  été produit pendant la fenêtre de retrait n'est rejoué** — seuls les
  événements produits **après** la ré-acceptation seront envoyés normalement.
- **Limite technique assumée** : le fichier `script.js` déjà téléchargé par le
  navigateur ne peut pas être « déchargé ». La façade **empêche tout nouvel
  appel** (`window.plausible(...)`) pendant la fenêtre de retrait — c'est la
  garantie fournie, pas la suppression du fichier déjà en cache navigateur.

Distinction importante : ceci ne concerne que le cas « **révocation après un
octroi précédent** ». Le cas normal « pas encore décidé / refusé pour l'instant,
jamais encore accepté » **met en file** en attendant une éventuelle première
acceptation dans la même page (comportement standard attendu d'un bandeau de
consentement) — rien n'est envoyé tant que non accordé, dans les deux cas.

## Gestion du chargement du script
États explicites : `not_started` → `loading` → `loaded` | `failed`.
- Injection **unique** (garde d'état, pas de deuxième `<script>` créé).
- **Panne réseau / bloqueur de publicité** : `onerror` → état `failed`,
  **aucune exception visible**, le formulaire continue normalement.
- **Politique de nouvelle tentative : aucune, dans la page courante** (pas de
  boucle de réessai automatique). Un rechargement de page retente
  naturellement. *Limite assumée, documentée ici plutôt que masquée.*

## URL et pageviews
Le script Plausible gère lui-même le pageview standard (URL de la page en
cours) — **le plugin ne transmet jamais l'URL manuellement**. Points de
vigilance pour la recette :
- Vérifier qu'aucune page Horizon ne place de donnée personnelle dans le
  chemin, la query string ou le hash avant d'activer Plausible sur ces pages.
- Le **back-office Horizon** (`/wp-admin/…&ref=FCP-…`) n'est pas destiné à
  recevoir ce script (pages d'administration, non publiques) — vérifier en
  recette qu'aucun tracking n'y a d'effet significatif (le script n'y est de
  toute façon pas censé être exposé au public).

## Tests
- **Unitaires** (`PlausibleConfigTest`) : configuration absente → rien
  d'activé ; domaine valide accepté ; domaine invalide neutralisé ; URL de
  script personnalisée acceptée (HTTPS) ; URL invalide ou non-HTTPS neutralisée
  au profit du défaut.
- **`ConsentCategoryTest`/`ConsentConfigTest`** (lot Didomi) : inchangés.
- **Régression** : 54 tests au total, **0 impact** sur les 48 précédents
  (Communication/Brevo, Didomi, back-office, formulaire, référence FCP…).
- **Hors PHPUnit** (comportement navigateur, vérifié en recette staging) :
  file d'attente, dédoublonnage, retrait/ré-acceptation, échec réseau —
  cohérent avec la couverture ciblée du projet (Domain/Config testés
  unitairement ; comportement JS vérifié en recette).

## Procédure de recette (staging)
1. **Sans `PLAUSIBLE_DOMAIN`** : onglet réseau → ouvrir/remplir/soumettre
   `/demande/` → **aucune requête** vers Plausible à aucun moment.
2. **Configuré, consentement refusé** : toujours aucune requête, même après un
   envoi réussi.
3. **Configuré, consentement accepté** : recharger la page → une requête
   Plausible par événement (`viewed`, `started` au premier clic dans un champ,
   `submitted` à l'envoi, `success` ou `error`) ; **payload sans donnée
   personnelle** (vérifier dans l'onglet réseau).
4. **Doublon Didomi** : déclencher plusieurs fois un événement `consent.changed`
   côté Didomi (ou recharger) → pas de doublon d'événements envoyés.
5. **Retrait puis ré-acceptation dans la même page** : accepter → soumettre un
   formulaire de test partiel → retirer le consentement via Didomi → vérifier
   qu'aucune nouvelle requête ne part → ré-accepter → vérifier qu'**aucun**
   événement de la fenêtre de retrait n'est renvoyé, seuls les nouveaux le sont.
6. **Bloqueur/panne réseau** : bloquer le domaine Plausible dans le navigateur
   → parcours `/demande/` intégral fonctionne sans erreur console, la demande
   est bien créée dans Supabase.
7. **Succès/erreur mutuellement exclusifs** : provoquer un échec serveur (ex.
   Supabase temporairement indisponible) → `enquiry_form_error` envoyé, `success`
   jamais envoyé pour cette tentative ; corriger et renvoyer → nouvelle
   tentative, `enquiry_form_submitted` se déclenche à nouveau, puis `success`.
8. **Clics de contact** : cliquer le bouton WhatsApp de confirmation → un
   événement `whatsapp_clicked` envoyé, **sans** propriété ni URL/numéro.
9. **Aucune donnée personnelle** : inspecter chaque requête Plausible (onglet
   réseau) → confirmer l'absence de nom, e-mail, téléphone, référence, trajet.

## Points de vigilance
- Domaine/URL de script **validés côté serveur** avant d'atteindre le
  navigateur (neutralisation silencieuse si malformés).
- Vendor Didomi : si configuré mais ne correspondant pas exactement au slug
  réel, `analytics` restera `false` par sécurité (échec fermé) — à vérifier
  en recette.
- Aucune relance automatique après échec réseau dans la page courante (limite
  assumée, cf. ci-dessus).
- Aucun impact sur la couche Communication/Brevo ni sur l'enregistrement des
  demandes (les appels analytics sont défensifs, jamais bloquants).
