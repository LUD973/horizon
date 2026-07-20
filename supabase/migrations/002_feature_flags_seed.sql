-- =============================================================================
-- 002_feature_flags_seed.sql
-- French Class Prestige — Horizon Core (Sprint 1)
-- Table feature_flags + valeurs initiales.
--
-- Règle (arbitrage 11) : un module désactivé ne doit exposer aucun parcours
-- incomplet ni lien actif trompeur. La Conciergerie peut être *visible*
-- (« Ouverture prochaine ») mais ne permet aucune soumission tant que
-- concierge_enabled reste à false.
-- =============================================================================

create table if not exists public.feature_flags (
    id          uuid primary key default gen_random_uuid(),
    name        text not null unique,
    enabled     boolean not null default false,
    config      jsonb not null default '{}'::jsonb,
    created_at  timestamptz not null default now(),
    updated_at  timestamptz not null default now()
);

drop trigger if exists trg_feature_flags_updated_at on public.feature_flags;
create trigger trg_feature_flags_updated_at
    before update on public.feature_flags
    for each row execute function public.set_updated_at();

alter table public.feature_flags enable row level security;

-- Valeurs initiales (idempotentes).
insert into public.feature_flags (name, enabled, config) values
    ('concierge_enabled',  false, '{"display": "coming_soon"}'::jsonb),
    ('membership_enabled', false, '{}'::jsonb),
    ('ai_enabled',         false, '{}'::jsonb)
on conflict (name) do nothing;
