# 16 — Plan d'implémentation solo (Horizon / French Class Prestige)

Version courte et **exécutable** pour un seul développeur full-stack, cible
**5–6 semaines**. Remplace, pour l'exécution, l'ordonnancement de
`16_IMPLEMENTATION_PLAN.md` (conservé comme référence). Source de vérité du
séquencement.

> **ADN (rappel impératif).** French Class Prestige est une Maison française de
> mobilité et de services premium. Jamais « plateforme / marketplace /
> comparateur / intermédiaire / centrale / réseau ». Le mot « partenaire » est
> interdit en communication publique. La technologie reste discrète. Le client
> choisit French Class Prestige.

---

## Arbitrages actés (validations finales)

1. **Back-office en S1** (lecture principale) : auth, tableau de bord, liste,
   fiche, communications, journal `audit_logs`, statuts. Exceptions d'écriture :
   changement de statut contrôlé + note interne courte. Pas de devis/missions/
   chauffeurs/véhicules/paiements complets.
2. **Documentation minimale** : ce plan, README d'install/exploitation,
   checklist de déploiement, doc technique liée au code. Rien d'autre sans
   demande explicite.
3. **Mono-dépôt** factorisable ultérieurement sans refonte métier.
4. **Vocabulaire public** : « Ils nous font confiance » (Home/logos),
   « Nos engagements », « Notre sélection ». « Prestataires sélectionnés » en
   interne uniquement. Mots interdits proscrits.
5. **Référence** : `FCP-2026-000123` — préfixe `FCP`, année 4 chiffres, compteur
   annuel 6 chiffres, remis à zéro chaque année, unicité en base, **génération
   serveur uniquement**, index UNIQUE.
6. **Journal** : table `audit_logs` (actor_id, action, table_name, record_id,
   old/new_values, created_at, ip_address, request_id). Aucun secret journalisé.
7. **communications** dès S1. Statuts : `prepared` / `opened` / `sent` (preuve
   technique requise) / `failed`. `wa.me` ouvert = `opened`, jamais `sent`.
8. **Sécurité** : rate limit 5/min/IP, honeypot, validation serveur stricte,
   CORS restrictif, secrets en env, contrôle d'accès back-office, moindre
   privilège, scan de secrets en CI.
9. **CI légère** : lint PHP/JS/CSS, PHPUnit, tests d'intégration essentiels,
   contrôle des migrations, scan de secrets ; couverture ~70 % sur la logique
   **métier critique** (pas uniforme), sans blocage artificiel initial.
10. **Performance** (fin de sprint) : Lighthouse mobile ≥ 80, CWV « Good » autant
    que possible, TTI < 3 s mobile, images optimisées, scripts tiers maîtrisés.
11. **Feature flags** : `concierge_enabled` / `membership_enabled` /
    `ai_enabled` = `false`. Aucun parcours incomplet ni lien trompeur. La
    Conciergerie peut afficher « Ouverture prochaine » sans soumission possible.
12. **Migrations** : `001_initial_schema.sql`, `002_feature_flags_seed.sql`,
    numérotation séquentielle, idempotentes quand raisonnable, aucune modif prod
    manuelle sans migration tracée.

---

## Séquencement linéaire

### Semaine 1 — Socle & première verticale  ▸ **ARRÊT V1**
1. Bootstrap mono-dépôt : `.gitignore`, `.env.example`, README, CI, composer/phpunit.
2. Squelette plugin `fcp-horizon-bridge` (Support/Data/Domain/Api), autoloader.
3. Migrations `001` + `002` (contacts, organizations, enquiries, enquiry_details,
   communications, audit_logs, feature_flags, référence FCP, RLS).
4. Endpoint `GET /fcp/v1/health` (état + joignabilité Supabase).
5. Thème enfant `fcp-child` : jetons de design, navigation persistante, Home
   (hero, sélecteur d'intention, confiance, footer), mobile-first.
- **Dépend de** : accès Supabase + WP staging.
- **Livrable démontrable** : Home + nav en staging, `/health` = 200, schéma migré,
  test unitaire de la référence FCP vert.

### Semaine 2 — Parcours public complet  ▸ **ARRÊT V2**
6. Formulaire intelligent Mobilité (profil particulier/société), brouillon local.
7. Validation navigateur + **serveur stricte** (nonce, honeypot, sanitisation,
   rate limit 5/min/IP, CORS).
8. Écran récapitulatif avant envoi.
9. `POST /fcp/v1/enquiries` : rapprochement/création contact → enquiry +
   enquiry_details → référence FCP (trigger) → `audit_logs` (`enquiry.created`).
10. Lien WhatsApp `wa.me` pré-rempli (récapitulatif formel, garde-fou longueur) +
    trace `communications` (`prepared` → `opened`) + écran de confirmation.
- **Dépend de** : contrat `enquiries` (S1).
- **Livrable** : chaîne publique Demande → enregistrement → WhatsApp → confirmation, mobile.

### Semaine 3 — Fiabilisation données & intégration
11. Durcissement API (Idempotency-Key, schéma serveur, logs sobres).
12. `GET /fcp/v1/enquiries/{ref}/receipt`.
13. Tests d'intégration principaux (anti-doublon, unicité référence, écriture).
- **Livrable** : parcours robuste + tests verts.

### Semaine 4 — Back-office Horizon (lecture)  ▸ **ARRÊT V3 en amont**
14. Accès sécurisé (contrôle d'accès, moindre privilège).
15. Tableau de bord + liste des demandes.
16. Fiche : données client, informations mission, communications, journal
    `audit_logs`, statut.
17. Exceptions écriture : changement de statut contrôlé + note interne courte.
- **Livrable** : flux complet de bout en bout démontré dans Horizon.

### Semaine 5 — Statuts, conformité, intégrations
18. Transitions de statut contrôlées (journalisées).
19. Didomi (consentement) + politique accessible + Plausible.
20. Brevo : alerte interne + accusé client ; `communications` → `sent`/`failed`
    sur preuve technique. Opt-in marketing séparé et explicite.
21. Corrections UX, responsive (360/390/768/1024/1440), accessibilité clavier/focus/labels.
- **Livrable** : parcours conforme RGPD, envois réels en staging.

### Semaine 6 — Stabilisation & Release Candidate  ▸ **ARRÊT V4 (avant prod)**
22. Performance (Lighthouse ≥ 80, CWV, TTI < 3 s), accessibilité AA de base.
23. Tests de non-régression, README d'exploitation, checklist de déploiement, RC.
- **Livrable** : RC validée en staging, prête pour votre GO production.

**Stretch (non bloquant)** : transformation Demande → Devis (structure `quotes`
+ action back-office), uniquement si l'avance le permet.

---

## Chemin critique

```
Env local+staging → Squelette plugin → Schéma Supabase + référence FCP
→ POST /enquiries → Récapitulatif + wa.me + confirmation
→ Accès back-office → Liste → Fiche → Statuts
```

Hors chemin critique (reportables sans bloquer la mise en service) : design fin,
témoignages, Brevo, Didomi, Plausible, performance.

---

## Risques principaux

| # | Risque | Mitigation |
|---|--------|-----------|
| R1 | Logique métier dans Divi | Divi = présentation ; tout dans `fcp-horizon-bridge`. |
| R2 | `wa.me` : longueur/encodage du récapitulatif | Récap condensé + URL-encode + garde-fou ; version complète = reçu Horizon. |
| R3 | Clé `service_role` exposée | Appels serveur uniquement ; env ; RLS activée. |
| R4 | Unicité référence en concurrence | Compteur annuel atomique + contrainte UNIQUE (trigger). |
| R5 | Bande passante solo | Chemin critique protégé ; reports assumés. |
| R6 | Statut `communications` trompeur | `opened` ≠ `sent` ; `sent` seulement sur preuve. |
| R7 | Séparation staging/prod | Bases, clés, domaines distincts ; pas de données réelles en dev. |
| R8 | Écriture back-office hors périmètre | Lecture par défaut ; seules 2 exceptions autorisées. |

---

## Points d'arrêt pour validation

- **V1 (fin S1)** : env + schéma + Home/nav + `/health`.
- **V2 (fin S2)** : parcours public complet démontré.
- **V3 (avant S5)** : activation envois réels Brevo + confirmation vocabulaire public.
- **V4 (fin S6)** : RC validée **avant tout déploiement production** (déclenché par vous).
- **Ad hoc** : toute sortie de périmètre.

---

## Tables Sprint 1

Actives : `contacts`, `organizations`, `enquiries`, `enquiry_details`,
`communications`, `audit_logs`, `feature_flags` (+ support
`enquiry_reference_counters`). Optionnelle/stretch : `quotes`.
Inactives préparées ailleurs : missions, concierge_*, memberships, etc.

---

## Branches Git

`main` (protégée, prod) ← `staging` (déploiement staging) ← `feat/*` (une par
étape). Session courante : travail sur `claude/fcp-execution-phase-bwtydk` ;
promotion vers `staging`/`main` uniquement sur validation.

---

## Périmètre réaliste 5–6 semaines

**Tient** : Home + nav, formulaire intelligent, validation client+serveur,
récapitulatif, `wa.me`, enregistrement + référence FCP, API v1
(`health`/`enquiries`/`receipt`), back-office lecture (dashboard/liste/fiche/
journal/statuts + 2 exceptions d'écriture), consentement + Brevo + Plausible,
tests ~70 % sur le critique, durcissement + RC.
**Sous condition** : parcours Corporate/Private riches, Demande → Devis, Didomi
complet, multilingue actif.

## À reporter

Application chauffeur, espace client complet, abonnements/Stripe, Conciergerie
active (reste visible « Ouverture prochaine », flag `false`), agents IA,
statistiques complexes, portail externe, automatisations avancées, multilingue
actif EN/PT.

---

## Journal d'exécution

### Recette Sprint 1 (staging) — VALIDÉE
Environnement : `staging.frenchclassprestige.com` (Infomaniak, WordPress + Divi),
séparé de la production ; base **Supabase Frankfurt** (`projet staging`).
Chaîne complète validée de bout en bout : formulaire → validation serveur →
référence `FCP-2026-…` (serveur) → `contacts`/`enquiries`/`enquiry_details` →
`audit_logs` → `communications` (`prepared`→`opened`, jamais `sent`) → wa.me →
anti-doublon (1 contact / N demandes) → consentement traitement obligatoire.

### Semaine 3 — correctifs & durcissements livrés
- **Jeton robuste** : `GET /form-token` (récupéré en REST, no-store) → règle le
  « jeton invalide » lié au cache / à l'état connecté observé en recette.
- **Idempotency-Key** sur `POST /enquiries` (anti-doublon sur retries réseau).
- **`GET /enquiries/{ref}/receipt`** : reçu public, données non sensibles, rate-limité.
- **Compat clés Supabase** : `service_role` (JWT legacy) **et** `sb_secret_…`.
- **Rétention IP 12 mois** : migration `003` (`purge_audit_ip`) + tâche WP-cron
  quotidienne (`fcp_horizon_purge_ips`) + exécution manuelle SQL possible.

### Semaine 4 — Back-office Horizon livré
- Accès protégé par **capacité** `fcp_horizon_access` (moindre privilège,
  accordée à l'administrateur ; rôles FCP fins = évolution).
- **Tableau de bord** (total + répartition par statut).
- **Liste des demandes** (référence, client, trajet, date, statut, lien fiche).
- **Fiche** : client, mission, communications, notes internes, **journal
  `audit_logs`**, statut.
- **Écritures autorisées uniquement** : changement de **statut contrôlé**
  (transitions validées par `EnquiryStatus`, journalisé) + **note interne
  courte** (table `enquiry_notes`, migration `004`, journalisée). Nonce +
  capacité sur chaque écriture.
- Tests : transitions de statut (unitaire) ; 32 tests verts au total.

### Backlog acté (post-recette)
- Suivi des numéros de vol (Horizon).
- Adresses pré-enregistrées aéroports/gares (autocomplétion départ/destination).
- **Assistant IA** : discret, ton humain, **human-in-the-loop** pour tout envoi
  client ; transparence minimale si l'utilisateur demande sa nature (pas de
  déni). Cadre : EU AI Act art. 50 + loyauté (pas de tromperie). Sprint dédié,
  `ai_enabled=false` d'ici là.
- Finalisation visuelle Home + navigation (Divi), responsive/a11y (S5–S6).
