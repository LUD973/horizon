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

## Synthèse à compléter après recette
- Contrôles automatisés : ✅ 56/56 tests, 0 régression.
- Contrôles manuels B/C/D : à cocher lors de la recette staging.
- C5/C6/C7 : mesures à consigner ; corrections **uniquement si écart constaté**.
- Décision GO/NO-GO Release Candidate : voir `docs/HORIZON_CONTINUITY_SPRINT1.md`
  après recette.
