# PublicSite/ — présentation publique (Semaine 2)

Couche **présentation** du parcours public (rendu du formulaire, récapitulatif,
écran de confirmation). Aucune règle métier ici : elle appelle la couche
`Domain/` et n'accède jamais directement à Supabase.

Arrive en Semaine 2 :
- `FormRenderer` — rendu du formulaire intelligent Mobilité (profil particulier / société).
- `SummaryRenderer` — récapitulatif avant envoi.
- `WhatsAppLink` — construction du lien `wa.me` avec récapitulatif formel (garde-fou de longueur).
- shortcode `[fcp_enquiry_form]` pour intégration dans une page Divi (sans logique dans Divi).
