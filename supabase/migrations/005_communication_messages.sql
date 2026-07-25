-- =============================================================================
-- 005_communication_messages.sql
-- French Class Prestige — Horizon Core (Semaine 5)
-- Couche de communication : table « source de vérité » + outbox résiliente.
--
-- Rôle des tables (à documenter clairement) :
--   * communication_messages : SOURCE DE VÉRITÉ de chaque message (entrant/
--     sortant), tous canaux (email maintenant ; whatsapp/sms/push plus tard).
--     Sert aussi d'OUTBOX : cycle de vie pending → processing → sent →
--     delivered → read, ou failed / retry_scheduled / cancelled.
--   * communications (existante) : conservée pour son rôle HISTORIQUE/synthétique
--     — suivi du lien wa.me (prepared/opened). Pas de second historique complet.
--   * communication_threads / consent_channel_grants / webhook_events : prévues
--     pour le lot WhatsApp API (non créées ici pour éviter des tables inutilisées).
--
-- Règles : timestamptz partout ; contraintes sur channel/direction/message_type/
-- status ; index sur enquiry_id, status, provider_message_id, created_at ;
-- unicité sur provider_message_id quand présent (idempotence future webhooks) ;
-- RLS activée (accès service_role uniquement) ; réexécutable sans destruction.
-- Minimisation : payload ne stocke que le nécessaire au rendu (clé + variables).
-- =============================================================================

create table if not exists public.communication_messages (
    id                  uuid primary key default gen_random_uuid(),
    enquiry_id          uuid references public.enquiries(id) on delete set null,
    thread_id           uuid,                          -- rattachement conversation (lot WhatsApp)
    channel             text not null
                        check (channel in ('email', 'whatsapp', 'sms', 'push')),
    direction           text not null default 'outbound'
                        check (direction in ('outbound', 'inbound')),
    message_type        text not null default 'transactional'
                        check (message_type in ('transactional', 'marketing')),
    provider            text,                          -- ex : 'brevo' (jamais lu par le métier)
    provider_message_id text,
    sender              text,
    recipient           text not null,
    template_id         text,                          -- clé de template interne Horizon
    locale              text not null default 'fr',
    subject             text,
    status              text not null default 'pending'
                        check (status in ('pending', 'processing', 'sent', 'delivered',
                                          'read', 'failed', 'retry_scheduled', 'cancelled')),
    error_code          text,
    error_message       text,
    attempt_count       integer not null default 0,
    next_retry_at       timestamptz,
    last_attempt_at     timestamptz,
    sent_at             timestamptz,
    delivered_at        timestamptz,
    read_at             timestamptz,
    failed_at           timestamptz,
    payload             jsonb not null default '{}'::jsonb,   -- {template, params} — minimal
    created_at          timestamptz not null default now()
);

create index if not exists comm_messages_enquiry_idx
    on public.communication_messages (enquiry_id, created_at desc);
create index if not exists comm_messages_status_idx
    on public.communication_messages (status);
create index if not exists comm_messages_due_idx
    on public.communication_messages (status, next_retry_at);
create index if not exists comm_messages_created_idx
    on public.communication_messages (created_at desc);
create unique index if not exists comm_messages_provider_msg_uidx
    on public.communication_messages (provider, provider_message_id)
    where provider_message_id is not null;

alter table public.communication_messages enable row level security;
