-- =============================================================================
-- 004_enquiry_notes.sql
-- French Class Prestige — Horizon Core (Sprint 4 / back-office)
-- Notes internes courtes attachées à une demande.
--
-- Écriture explicitement autorisée dans le back-office (arbitrage 1). Contenu
-- interne, jamais exposé au public. RLS activée : accès serveur uniquement.
-- =============================================================================

create table if not exists public.enquiry_notes (
    id          uuid primary key default gen_random_uuid(),
    enquiry_id  uuid not null references public.enquiries(id) on delete cascade,
    author      text,                       -- identifiant interne du rédacteur (login WP)
    body        text not null,
    created_at  timestamptz not null default now()
);

create index if not exists enquiry_notes_enquiry_idx
    on public.enquiry_notes (enquiry_id, created_at desc);

alter table public.enquiry_notes enable row level security;
