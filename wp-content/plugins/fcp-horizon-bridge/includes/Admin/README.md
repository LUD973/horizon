# Admin/ — back-office Horizon (Semaine 4)

Couche **présentation** du back-office (lecture principalement). Accès sécurisé,
moindre privilège. Aucune règle métier ici : elle appelle `Domain/` et `Data/`.

Arrive en Semaine 4 :
- Authentification / contrôle d'accès aux écrans Horizon.
- Tableau de bord simple.
- Liste des demandes.
- Fiche détaillée d'une demande (données client, informations mission,
  communications, journal `audit_logs`, statut).

Exceptions d'écriture explicitement autorisées (arbitrage 1) :
- modification contrôlée du statut d'une demande ;
- ajout d'une note interne courte.

Hors périmètre Sprint 1 : gestion complète des devis, missions, chauffeurs,
véhicules, paiements.
