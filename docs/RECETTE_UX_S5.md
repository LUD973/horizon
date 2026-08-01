# Guide de recette — UX / Responsive / Accessibilité (Semaine 5, fin)

Environnement : **staging** uniquement. Périmètre : formulaire `/demande/`
(`fcp-horizon-bridge`). La Home Divi et sa navigation sont hors périmètre code
de ce lot (cf. `docs/UX_ACCESSIBILITY_S5.md`, section 3).

## Déploiement
1. Remplacer le plugin par la version **0.7.0** (ZIP fourni).
2. Aucune migration SQL dans ce lot.

## A. Contrôles automatisés déjà couverts (rappel)
- ☐ `vendor/bin/phpunit` → 56 tests / 153 assertions verts.
- ☐ `FormRendererMarkupTest` confirme les deux `tabindex="-1"`.

## B. Recette manuelle — largeurs et navigation

| Contrôle | Méthode | Attendu |
|---|---|---|
| 320 px | DevTools responsive | formulaire lisible, aucun débordement horizontal |
| 375 px | DevTools responsive | idem |
| 768 px | DevTools responsive | grille 2 colonnes des champs (`fcp-form__grid`) |
| Desktop | fenêtre large | mise en page centrée, `max-width: 720px` |
| Clavier uniquement | Tab/Shift+Tab/Entrée/Échap, sans souris | tout le parcours accessible, ordre logique (profil → champs → consentements → vérifier → récap → confirmer) |
| Zoom 200 % | Ctrl/Cmd + « + » | pas de troncature ni de recouvrement, formulaire toujours utilisable sans scroll horizontal |

## C. Focus et annonces

| Contrôle | Méthode | Attendu |
|---|---|---|
| Erreur de validation (client) | soumettre incomplet | bloc d'erreurs visible, **focus visible** dessus (anneau doré) |
| Erreur de validation (serveur 422) | contourner la validation client ou provoquer un 422 | idem, focus sur le bloc d'erreurs |
| Erreur réseau | couper le réseau pendant l'envoi | message « Connexion impossible… », focus sur le bloc d'erreurs, **aucune perte de saisie** |
| Succès réel | soumettre une demande de test valide | confirmation affichée, **focus visible** dessus, lecteur d'écran annonce le contenu (`aria-live="polite"`) |
| État « Envoi en cours… » | observer pendant l'appel réseau (throttling « Slow 3G » recommandé) | texte du bouton changé, bouton désactivé (visuellement estompé), `aria-busy="true"` sur le formulaire (DevTools → Éléments) |
| Restauration après échec | provoquer un échec puis observer | texte du bouton restauré, `aria-busy="false"`, bouton réactivé |
| Pas de double restauration après succès | soumettre avec succès | le formulaire est masqué ; aucune erreur ni scintillement du bouton (désormais invisible) |
| Lecteur d'écran (VoiceOver/NVDA) | parcourir le formulaire, provoquer une erreur puis un succès | annonce correcte du rôle `alert` et de la confirmation `polite`, labels des champs lus correctement |
| Réduction des mouvements | activer « Réduire les animations » (réglage système) | passage au récapitulatif et à la confirmation **sans** défilement animé |

## D. Consentement / analytics

| Contrôle | Méthode | Attendu |
|---|---|---|
| Didomi accepté | accepter le bandeau | formulaire inchangé, aucun impact fonctionnel |
| Didomi refusé | refuser le bandeau | formulaire inchangé, aucun impact fonctionnel |
| Plausible activé | consentement analytics accordé | événements envoyés (déjà vérifié au lot Plausible), aucune régression ici |
| Plausible inactif | sans configuration ou consentement refusé | aucune requête, formulaire inchangé |
| Aucune erreur console | ouvrir la console sur tous les scénarios ci-dessus | aucune erreur JS |

## E. C5 — Contraste (mesure obligatoire avant toute correction)

Utiliser un outil de contraste (DevTools « Inspecter » sur l'élément → onglet
Accessibilité, ou WebAIM Contrast Checker) et consigner ci-dessous :

| Élément | Couleur texte mesurée | Couleur fond mesurée | Ratio mesuré | Seuil requis | Conforme ? | Correction appliquée | Ratio après correction |
|---|---|---|---|---|---|---|---|
| `.fcp-consent label` (texte gris) | *(à relever)* | *(à relever)* | *(à relever)* | 4,5:1 | *(à cocher)* | *(si non conforme)* | *(à relever)* |
| `.fcp-cta--gold` (bouton or) | *(à relever)* | *(à relever)* | *(à relever)* | 4,5:1 (texte bouton) | *(à cocher)* | *(si non conforme)* | *(à relever)* |
| `.fcp-form__errors` (texte erreur) | *(à relever)* | *(à relever)* | *(à relever)* | 4,5:1 | *(à cocher)* | *(si non conforme)* | *(à relever)* |

**Règle** : ne modifier `form.css` que pour la/les ligne(s) où « Conforme ? »
= **Non**. Aucune couleur changée par anticipation.

## F. C6 — Zones tactiles (mesure obligatoire avant toute correction)

Mesurer en DevTools (mode mobile) la hauteur/largeur réelle rendue de chaque
élément interactif principal :

| Élément | Dimension mesurée | Seuil | Conforme ? | Correction appliquée |
|---|---|---|---|---|
| Boutons `.fcp-cta` (Vérifier, Modifier, Confirmer) | *(à relever)* | 44×44 px | *(à cocher)* | *(si écart)* |
| Lien WhatsApp de confirmation | *(à relever)* | 44×44 px | *(à cocher)* | *(si écart)* |
| Cases à cocher de consentement | *(à relever)* | 44×44 px (zone cliquable, y compris le label) | *(à cocher)* | *(si écart)* |

## G. C7 — Zoom 200 % (mesure obligatoire avant toute correction)

| Contrôle | Attendu | Constaté | Correction appliquée |
|---|---|---|---|
| Reflow à 200 % | pas de scroll horizontal, pas de contenu tronqué | *(à relever)* | *(si écart)* |
| Boutons critiques (Confirmer, Vérifier) | toujours visibles et cliquables | *(à relever)* | *(si écart)* |

## Synthèse — Recette réalisée le 01/08/2026 (staging)

### Tableau des contrôles

| # | Contrôle | Résultat |
|---|---|---|
| 1 | Contraste (C5) | ✅ GO — `.fcp-consent label` 4,83:1 (eyedropper + calcul manuel), `.fcp-cta--gold` 6,48:1, `.fcp-form__errors` 10,27:1 — tous ≥ 4,5:1, aucune correction nécessaire |
| 2 | Zones tactiles (C6) | ✅ GO — boutons `.fcp-cta` et cases de consentement mesurés ≥ 44×44 px (2 mesures directes, 2 par construction via classe partagée) |
| 3 | Zoom navigateur 200 % (C7) | ✅ GO — aucun débordement horizontal, boutons entièrement visibles/cliquables |
| 4 | Responsive 320/375/768/Desktop | ✅ GO sur les 4 largeurs |
| 5 | Navigation clavier complète | ✅ GO — parcours entier accessible, ordre logique, focus visible |
| 6 | Focus après erreur (C1) | ✅ GO (après correctif v0.7.1) |
| 7 | Focus après succès (C2) | ✅ GO (après correctif v0.7.1) |
| 8 | Bouton « Envoi en cours… » (C3) | ✅ GO |
| 9 | `prefers-reduced-motion` (C4) | ✅ GO |
| 10-13 | Didomi/Plausible accepté/refusé/actif/inactif | ⚠️ GO partiel — comportement de repli validé (pas de bandeau, pas d'erreur) ; scénario réel non testable, config absente sur ce staging (A5) |
| 14 | Vérification réseau | ✅ GO — 200/201 sur les appels métier, aucune erreur, aucun appel externe suspect |
| 15 | Vérification console JavaScript | ✅ GO — seule erreur présente = extension navigateur tierce, aucune erreur du plugin |
| 16 | Parcours complet de bout en bout | ✅ GO |

### Anomalies détectées et statut final

| ID | Titre | Classification | Statut |
|---|---|---|---|
| A3 | Staging exécutait une version obsolète du plugin (0.3.0 au lieu de 0.7.0) | 🔴 Bloquante | ✅ Résolue (redéploiement) |
| A4 | Focus programmatique non fiable après affichage dynamique (C1 et C2) — timing d'exécution vs. recalcul d'affichage | 🟠 Majeure | ✅ Corrigée v0.7.1 (`focusSoon()`, `requestAnimationFrame`), vérifiée en conditions réelles (visiteur anonyme) |
| A2 | Récapitulatif non resynchronisé si un champ est modifié sans repasser par « Modifier » ; `hidden` neutralisé visuellement par un `display` du thème sur les balises `<section>` | 🟠 Majeure | ✅ Corrigée v0.7.2 (invalidation auto du récap) + v0.7.3 (CSS `[hidden]` ciblé), vérifiée |
| A1 | Bouton « Modifier » sans défilement automatique vers le formulaire | 🟡 Mineure | ✅ Corrigée en tant qu'effet du correctif A2 (le récap disparaît maintenant réellement) — le défilement automatique manquant reste un point d'amélioration UX mineur, non bloquant |
| A5 | Configuration Didomi/Plausible absente sur ce staging | 🟡 Mineure (config) | 🔓 Ouverte — action serveur (`wp-config.php`), pas de code ; empêche seulement la validation du scénario réel bandeau/analytics |

### Recommandations
- **A5** : restaurer `DIDOMI_SDK_EMBED` et `PLAUSIBLE_DOMAIN` sur le staging pour revalider en conditions réelles les lots 0.5.0/0.6.0 (Semaine 6).
- Conserver la vigilance de déploiement révélée par A3 : toujours vérifier le numéro de version affiché après toute mise à jour de plugin sur staging.
- Décision de câblage/archivage des template-parts inertes : toujours reportée après la Release Candidate (inchangé).

### Décision finale

## ✅ GO Release Candidate Sprint 1

Plus aucune anomalie **Bloquante** ou **Majeure** ouverte. Seules subsistent deux réserves **Mineures** (A5 : configuration serveur à restaurer ; défilement automatique du bouton « Modifier » resté non implémenté, cosmétique) — sans impact sur la fiabilité ni l'accessibilité du parcours.

**Version validée : v0.7.3.** Voir `docs/HORIZON_CONTINUITY_SPRINT1.md` pour la clôture officielle et le plan de Semaine 6.
