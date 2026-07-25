# Prompt de reprise — nouvelle session Claude Code

À coller en début de session si la session courante doit être remplacée.

---

Tu reprends le projet **Horizon / French Class Prestige** (mono-dépôt WordPress
+ Divi + plugin `fcp-horizon-bridge` + Supabase Frankfurt).

**Avant toute chose**, lis intégralement :
1. `docs/HORIZON_CONTINUITY_SPRINT1.md` — état réel du projet, dernier commit,
   fonctionnalités livrées, architecture, points de vigilance.
2. `16_IMPLEMENTATION_PLAN_SOLO.md` — plan solo, arbitrages, journal d'exécution.
3. `15_DECISIONS_LOG.md`, `docs/PV_RECETTE_BACKOFFICE_S4.md`,
   `docs/COMMUNICATION_LAYER.md` si besoin de détail.

**Repère Git** : branche `claude/fcp-execution-phase-bwtydk`, version plugin
**v0.4.0**. Vérifie `git log --oneline -5` et `git status` pour confirmer que
rien n'a changé depuis la rédaction du document de continuité.

**Règles impératives à respecter sans exception** :
- Développer uniquement sur la branche `claude/fcp-execution-phase-bwtydk` (ou
  celle en cours), jamais sur `main`.
- **Aucun secret dans Git** (clés Supabase/Brevo, tokens) — configuration
  serveur uniquement (`wp-config.php` hors dépôt côté staging).
- **Staging séparé de la production** ; aucune modification de production sans
  accord explicite de l'utilisateur.
- Vocabulaire public conforme à l'ADN French Class Prestige : jamais
  « partenaire / plateforme / marketplace / intermédiaire / comparateur /
  centrale de réservation / réseau ».
- Numéro WhatsApp officiel unique : **+33 6 56 89 86 11** (E.164
  `+33656898611`) — lien `wa.me` inchangé, aucune migration du compte WhatsApp.
- L'automatisation WhatsApp par API est **reportée** (coût Brevo ~300 €/mois) ;
  l'architecture (`WhatsAppProvider`) est prête mais non implémentée.
- Toujours committer et pousser les livrables ; lancer les tests
  (`vendor/bin/phpunit`) avant tout commit ; vérifier l'absence de secret.

**Prochaine action exacte au moment de la rédaction de ce prompt** :
Semaine 5 — **Didomi** (consentement), puis Plausible, puis UX/responsive/accessibilité.

**Méthode d'accompagnement** (si l'utilisateur guide des actions manuelles) :
une action à la fois, indiquer précisément où cliquer, ce qu'il doit voir, ce
qu'il peut transmettre, ne jamais demander de secret/mot de passe/clé API/token.

Confirme avoir lu `docs/HORIZON_CONTINUITY_SPRINT1.md` avant de proposer une
quelconque action de développement.
