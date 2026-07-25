# Procès-verbal de recette — Back-office Horizon (Semaine 4)

- **Date** : 2026-07-25
- **Environnement** : `staging.frenchclassprestige.com` (Infomaniak, WordPress + Divi), **séparé de la production**
- **Base** : Supabase région **Frankfurt** (`projet staging`)
- **Plugin** : `fcp-horizon-bridge` **v0.3.0**
- **Migrations appliquées** : `001`, `002`, `003`, `004`
- **Périmètre** : back-office Horizon (lecture + 2 écritures autorisées)

## Résultats des tests

| # | Test | Résultat |
|---|------|----------|
| 1 | Sauvegarde staging + version initiale | ✅ |
| 2 | Remplacement plugin → v0.3.0 actif | ✅ |
| 3 | Migrations `003` + `004` exécutées | ✅ |
| 4 | Vérif `enquiry_notes` + `purge_audit_ip` | ✅ |
| 5 | Menu « Horizon » + capacité `fcp_horizon_access` | ✅ |
| 6 | Tableau de bord + comptage | ✅ |
| 7 | Liste des demandes | ✅ |
| 8 | Fiche détaillée (Client, Mission, Statut, Communications, Notes, Journal) | ✅ |
| 9 | Changement de statut contrôlé (transition valide) | ✅ |
| 10 | Ajout d'une note interne | ✅ |
| 11 | Vérif Supabase : `enquiries.status`, `enquiry_notes`, `audit_logs` (`status_changed`, `note.added`) | ✅ |
| 12 | Aucune écriture non autorisée ; transitions contrôlées ; référence inexistante gérée | ✅ |
| 13 | Utilisateur sans capacité → accès refusé, aucune fuite | ✅ |
| 14 | Mobile / erreurs PHP / console / logs | ✅ |

## Anomalies détectées

- **Aucune anomalie bloquante.**
- Observation mineure : une demande de test était déjà en statut « Qualifiée » (état issu d'un test antérieur) — sans incidence, transition rejouée avec succès.

## Corrections nécessaires

- **Aucune** avant la suite. Points de suivi **non bloquants** (déjà au backlog) :
  - Rôles FCP fins (`fcp_sales`, `fcp_operations`, `fcp_finance`) — pour l'instant capacité accordée à l'administrateur.
  - Finition visuelle Home + navigation (Divi), responsive/accessibilité (S5–S6).
  - Brevo / Didomi / Plausible (S5).
  - Purge des données de test staging avant bascule production.

## Décision

- **Recommandation : GO pour la Semaine 5.**
- La chaîne **Demande → Horizon → consultation → suivi de statut** est validée de bout en bout sur staging, sans anomalie bloquante.
- Décision finale (GO / NO GO) : _à prononcer par le responsable de validation._
