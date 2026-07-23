-- =============================================================================
-- 003_audit_ip_retention.sql
-- French Class Prestige — Horizon Core
-- Purge / anonymisation des adresses IP dans audit_logs (rétention 12 mois).
--
-- Décision : l'IP n'est conservée que pour la sécurité / l'anti-abus / l'analyse
-- d'incident, 12 mois glissants maximum. Passé ce délai, on l'ANONYMISE
-- (mise à null) sans supprimer la ligne d'audit. Aucune IP n'est recopiée ni
-- journalisée par la fonction (elle ne renvoie qu'un compteur).
--
-- Exécutable :
--   * automatiquement (tâche planifiée du plugin, appel RPC quotidien) ;
--   * manuellement : select public.purge_audit_ip();   -- rétention par défaut 12 mois
--                    select public.purge_audit_ip(6);   -- ou une autre durée
-- Idempotent : réexécutable sans effet de bord.
-- =============================================================================

create or replace function public.purge_audit_ip(retention_months integer default 12)
returns integer
language plpgsql
as $$
declare
    v_count integer;
begin
    with updated as (
        update public.audit_logs
           set ip_address = null
         where ip_address is not null
           and created_at < now() - make_interval(months => retention_months)
        returning 1
    )
    select count(*) into v_count from updated;
    return v_count;
end;
$$;
