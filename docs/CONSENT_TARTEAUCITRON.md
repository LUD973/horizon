# Consentement (tarteaucitron.js) — Documentation technique

Remplace Didomi (Semaine 5) à partir de la Semaine 6, pour rester sur une
solution **gratuite et open source**, sans changer la garantie de fond :
séparation stricte entre le consentement du **site** (traceurs/services) et
le consentement **métier** Horizon (`contacts.consent_marketing`, jamais
transmis au CMP).

## Ce qui est automatisé par le plugin
- Chargement du **script officiel tarteaucitron.js**, tel quel (CDN jsDelivr
  par défaut, auto-hébergement possible via `TARTEAUCITRON_SCRIPT_URL`), en
  tout début de `<head>` (`wp_head`, priorité 1), **une seule fois** par page.
- **Aucune sortie** si `TARTEAUCITRON_PRIVACY_URL` n'est pas configuré : pas de
  bandeau, pas d'appel réseau, pas d'erreur. Le site reste pleinement
  fonctionnel.
- Façade JS `window.fcpConsent` (`assets/js/consent.js`), **strictement
  identique** dans sa forme publique à l'implémentation Didomi précédente :
  - `hasConsent('functional'|'analytics'|'marketing')` — `functional` toujours
    `true` ; `analytics`/`marketing` = **deny-by-default** tant que
    tarteaucitron n'a pas donné de réponse positive.
  - `onChange(callback)` — appelé immédiatement avec l'état courant, puis à
    chaque changement réel.
  - `openPreferences()` — réouvre le panneau tarteaucitron
    (`tarteaucitron.userInterface.openPanel()`).
- Deux services tarteaucitron enregistrés (`fcp-analytics` type `analytic`,
  `fcp-marketing` type `ads`) : leurs callbacks officielles `js`/`fallback`
  (rappelées par tarteaucitron à **chaque** changement d'état, pas seulement
  au chargement) alimentent l'état interne de la façade. **Aucune autre
  logique de consentement n'est réimplémentée.**

## Mécanisme d'intégration (points officiels utilisés)
- `tarteaucitron.services[key] = { type, needConsent, js, fallback }`
- `tarteaucitron.job.push(key)`
- `tarteaucitron.init({ privacyUrl, orientation, ... })`
- `tarteaucitron.userInterface.openPanel()`

Aucun de ces points n'est reconstruit ou deviné : ce sont les mécanismes
publics documentés du projet (voir dépôt officiel `AmauriC/tarteaucitron.js`).

## Configuration (noms uniquement, valeurs en `wp-config.php`)
- `TARTEAUCITRON_PRIVACY_URL` — lien **public** vers votre politique de
  confidentialité (pas un secret). Vide = CMP désactivé, aucune bannière.
- `TARTEAUCITRON_SCRIPT_URL` — optionnel, URL HTTPS du script coeur
  (auto-hébergement). Par défaut : CDN officiel jsDelivr.

## Procédure de recette
1. **Sans configuration** : aucune bannière, aucune erreur, formulaire
   inchangé.
2. **Avec configuration** : recharger → bandeau visible.
3. Ouvrir la console navigateur → vérifier une **seule** injection du script
   coeur (pas de script dupliqué dans le `<head>`).
4. **Refuser tout** → `fcpConsent.hasConsent('analytics')` = `false`.
5. **Accepter** (au moins la catégorie analytique) → passe à `true` ; un
   `onChange` enregistré est bien notifié.
6. Revenir sur le site plus tard (cookie déjà déposé) → l'état initial reflète
   le choix précédent dès le chargement.
7. Vérifier qu'aucune régression n'affecte `/demande/` (parcours complet) ni
   le back-office Horizon.
8. Vérifier que le lien « Gérer mes cookies » (si ajouté au thème) rouvre bien
   le panneau via `fcpConsent.openPreferences()`.

## Points de vigilance
- Le **script coeur** doit être celui officiellement distribué — ne pas le
  reconstruire ni le modifier.
- `TARTEAUCITRON_PRIVACY_URL` doit pointer vers une page réellement
  existante et à jour.
- Sans lien avec `contacts.consent_marketing` (métier, séparé, jamais
  transmis au CMP).
