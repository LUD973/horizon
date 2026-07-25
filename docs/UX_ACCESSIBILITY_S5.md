# UX, responsive, accessibilité — Documentation technique (fin Semaine 5)

## Périmètre réel de ce lot
Seuls les éléments **réellement servis** en staging ont été modifiés : le
formulaire de demande et son écran de confirmation (`fcp-horizon-bridge`).
**Constat important** : les fichiers `wp-content/themes/fcp-child/template-parts/
{header,hero,intent-selector,footer}.php` et `assets/js/nav.js` **existent dans
le dépôt mais ne sont inclus par aucun `get_template_part()`** — ils sont
**inertes** depuis leur création en Sprint 1 (S1). La Home réelle en staging a
été construite **directement dans l'éditeur visuel Divi**. Ce constat est
documenté ici tel quel, **sans action** dans ce lot (cf. section dédiée).

## 1. Ce qui relève du plugin Horizon (livré, code)

### C1 — Focus après erreur
- **Fichier** : `includes/PublicSite/FormRenderer.php`
- **Changement** : `tabindex="-1"` ajouté sur `#fcp-form-errors`, `role="alert"`
  et `aria-live="assertive"` conservés à l'identique.
- **Effet** : l'appel `errorsBox.focus()`, déjà présent dans `form.js` mais
  jusqu'ici sans effet (un `<div>` sans `tabindex` n'est pas focusable
  programmatiquement), devient réellement opérant.
- **Risque** : très faible — attribut additif, aucune logique modifiée.

### C2 — Focus après succès
- **Fichiers** : `FormRenderer.php` (`tabindex="-1"` sur `#fcp-confirmation`),
  `assets/js/form.js` (`onSuccess()`).
- **Changement** : après révélation (`hidden = false`) et défilement
  (`revealScrollInto`), un appel `confirmation.focus({ preventScroll: true })`
  déplace le focus clavier/lecteur d'écran vers la confirmation, **sans**
  provoquer un second saut de défilement (option `preventScroll`).
- **Effet** : les utilisateurs clavier/lecteur d'écran sont informés du succès
  réel sans dépendre uniquement de l'annonce `aria-live="polite"`.
- **Risque** : faible. `preventScroll` est largement supporté ; à défaut, un
  navigateur ancien ferait un focus classique (léger saut supplémentaire
  possible, non bloquant) — limite mineure assumée.

### C3 — État d'envoi
- **Fichiers** : `assets/js/form.js` (`setSending()`), `assets/css/form.css`
  (`.fcp-cta:disabled`).
- **Changement** : pendant la tentative réseau réelle, le bouton d'envoi
  affiche « Envoi en cours… », est désactivé (`disabled`, style visuel
  `opacity: 0.6`), et `aria-busy="true"` est posé sur le formulaire.
  Restauration (texte, `disabled`, `aria-busy`) **uniquement** en cas d'échec
  (422, autre statut, erreur réseau) — après un **succès réel**, le formulaire
  entier est masqué juste après (`onSuccess`), donc aucune restauration
  inutile sur un bouton devenu invisible.
- **Logique métier inchangée** : aucune modification de l'ordre d'appel réseau,
  de l'Idempotency-Key, ni des branches de traitement de la réponse.
- **Risque** : faible, purement présentation.

### C4 — Réduction des mouvements
- **Fichier** : `assets/js/form.js` (`prefersReducedMotion()`, `revealScrollInto()`).
- **Changement** : les deux appels `scrollIntoView` (récapitulatif, confirmation)
  passent par un helper commun qui utilise `behavior: 'auto'` (instantané) si
  `window.matchMedia('(prefers-reduced-motion: reduce)').matches` est vrai,
  `'smooth'` sinon. **Repli sûr** si `matchMedia` est indisponible (`try/catch`
  → comportement fluide par défaut, jamais d'exception).
- **Risque** : très faible.

### Tests ajoutés
- `tests/Unit/FormRendererMarkupTest.php` — vérifie par le rendu réel de
  `FormRenderer::render()` la présence de `tabindex="-1"` sur `#fcp-form-errors`
  (avec `role`/`aria-live` conservés) et sur `#fcp-confirmation` (avec
  `aria-live="polite"` conservé).
- `tests/bootstrap.php` — ajout de **stubs WordPress minimaux, réservés aux
  tests** (`wp_create_nonce`, `esc_attr`, `esc_html_e`), gardés par
  `function_exists()` : aucun effet en environnement WordPress réel, permet de
  rendre `FormRenderer` en PHPUnit pur.
- **Résultat** : 56 tests / 153 assertions (54 précédents + 2 nouveaux), **0
  régression**.

## 2. Ce qui relève du thème WordPress/Divi (code)
**Rien modifié.** `tokens.css`/`home.css` ciblent des classes qui n'existent
que dans les template-parts inertes (voir constat ci-dessus) — aucune
modification utile tant que la décision de câblage n'est pas prise (reportée,
cf. section 4).

## 3. Ce qui doit être réalisé manuellement dans Divi
- Cohérence visuelle de la Home réelle (hiérarchie des titres, texte alternatif
  des images, contraste des blocs Divi).
- Comportement clavier/mobile du menu de navigation **Divi natif** (le module
  Divi gère son propre menu ; `nav.js` n'y est pas actif).
- Ajout éventuel de liens `tel:`/`mailto:` visibles (aucun n'existe aujourd'hui
  hors du bouton WhatsApp de confirmation) — contenu, pas logique métier ;
  `analytics.js` les détectera automatiquement dès qu'ils existeront, sans
  code supplémentaire.
- Réglage de l'apparence du bandeau Didomi (boutons équilibrés, cf. lot Didomi).

## 4. Statut des template-parts inertes (documenté, non modifié)
| Élément | Statut |
|---|---|
| `template-parts/header.php` | présent dans le dépôt, **non inclus** par le thème actuel |
| `template-parts/hero.php` | présent dans le dépôt, **non inclus** par le thème actuel |
| `template-parts/intent-selector.php` | présent dans le dépôt, **non inclus** par le thème actuel |
| `template-parts/footer.php` | présent dans le dépôt, **non inclus** par le thème actuel |
| `assets/js/nav.js` | présent, enqueue sitewide, **cible des classes absentes de la Home Divi réelle** |
| `tokens.css`/`home.css` (règles `.fcp-header`, `.fcp-hero`…) | chargées sitewide, **inertes** sur la Home réelle |

**Décision de câblage, d'archivage ou de suppression : reportée après la
Release Candidate du Sprint 1.** Aucune tentative d'activation silencieuse
dans ce lot.

## 5. C5 à C7 — mesure de recette d'abord (non codés par anticipation)
Voir `docs/RECETTE_UX_S5.md`. **Aucune couleur, aucune taille de cible tactile,
aucun correctif de zoom n'a été modifié par anticipation** — ces trois points
ne seront corrigés que si un écart est **mesuré** en staging, avec traçabilité
(couleur/fond mesurés, ratio avant/après, correction appliquée).

## Sécurités respectées
Aucune modification de `Communication`/`Brevo`, de la logique `Didomi`/
`Plausible`, des événements analytics, des consentements métier Horizon.
Aucune nouvelle dépendance. 56 tests conservés (54 + 2 nouveaux). Aucun
changement en production — staging uniquement.
