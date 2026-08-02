# Analytics (GoatCounter) — Documentation technique

Remplace Plausible (Semaine 5) à partir de la Semaine 6, pour rester sur une
solution **gratuite** (hébergement géré, faible volume), sans changer les
garanties de fond du lot analytics d'origine.

## Ce qui est automatisé par le plugin
- **Aucun script GoatCounter n'est jamais enqueué côté PHP** : `PublicSite\Analytics`
  ne fait que localiser la configuration (`endpoint`, `scriptUrl`, `configured`)
  pour `assets/js/analytics.js`, qui seul décide de charger le script — et
  seulement après consentement analytics confirmé via `window.fcpConsent`.
- Façade JS `window.fcpAnalytics` (`track`/`trackOnce`), **strictement
  identique** dans sa forme publique à l'implémentation Plausible précédente :
  file d'attente bornée (20) et dédupliquée par clé, mémoire de page
  uniquement (jamais persistée). N'interroge jamais le CMP directement —
  s'appuie uniquement sur `window.fcpConsent`.
- **Injection du script GoatCounter** strictement post-consentement, unique
  (garde d'état `not_started/loading/loaded/failed`), sans relance
  automatique après échec réseau/bloqueur.
- Utilise uniquement la fonction publique documentée
  `window.goatcounter.count({ path, title, event })` — aucun moteur de
  comptage maison. Les appels effectués pendant la brève fenêtre de
  chargement du script (avant `onload`) sont mis en attente localement puis
  rejoués une fois le script effectivement chargé — jamais après un échec.

## Différences avec le modèle Plausible (à connaître)
- GoatCounter raisonne en **chemin (`path`)**, pas en « nom d'événement +
  propriétés » : les propriétés éventuelles sont sérialisées en suffixe du
  `path` (ex. `enquiry_form_success?contact_channel=email`).
- GoatCounter ne fournit **pas** de file d'attente interne façon
  `window.plausible.q` : c'est pourquoi une petite file d'attente locale et
  transitoire a été ajoutée (voir ci-dessus), strictement bornée à la fenêtre
  de chargement du script.

## Configuration (noms uniquement, valeurs en `wp-config.php`)
- `GOATCOUNTER_ENDPOINT` — point d'entrée de comptage (ex.
  `https://votre-code.goatcounter.com/count`), doit être une URL HTTPS valide
  sinon neutralisé (= non configuré).
- `GOATCOUNTER_SCRIPT_URL` — optionnel, URL HTTPS du script (auto-hébergement
  possible). Par défaut : `https://gc.zgo.at/count.js` (CDN officiel).

## Procédure de recette
1. Sans `GOATCOUNTER_ENDPOINT` ou sans consentement analytics : **aucune
   requête** vers GoatCounter.
2. Avec consentement accordé : le script se charge, une entrée de page (et
   les événements de formulaire) apparaissent dans le tableau de bord
   GoatCounter.
3. Refus/retrait de consentement après un octroi : plus aucun envoi, file
   invalidée (non rejouée à une ré-acceptation dans la même page).
4. Une panne GoatCounter (bloqueur, réseau) ne produit **aucune erreur
   bloquante** et n'affecte jamais la création d'une demande Horizon.

## Points de vigilance
- Le compte GoatCounter (gratuit, faible volume) doit exister et son
  `endpoint` correspondre exactement au sous-domaine attribué.
- Aucune donnée personnelle transmise pour les clics de contact
  (`phone_clicked`/`email_clicked`/`whatsapp_clicked`) — comportement
  inchangé par rapport au lot Plausible.
