-- =============================================================================
-- 001_initial_schema.sql
-- French Class Prestige — Horizon Core (Sprint 1)
-- Schéma initial : contacts, organizations, enquiries, enquiry_details,
-- communications, audit_logs (+ support de génération de référence FCP).
--
-- Règles :
--   * UUID, horodatage UTC (timestamptz).
--   * Référence publique générée CÔTÉ SERVEUR uniquement (trigger).
--   * RLS activée sur toutes les tables : aucun accès anonyme direct.
--     Le plugin fcp-horizon-bridge accède via la clé service_role (bypass RLS).
--   * Idempotent autant que techniquement raisonnable.
-- =============================================================================

create extension if not exists pgcrypto;   -- gen_random_uuid()

-- -----------------------------------------------------------------------------
-- Fonction utilitaire : mise à jour automatique de updated_at
-- -----------------------------------------------------------------------------
create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
    new.updated_at = now();
    return new;
end;
$$;

-- -----------------------------------------------------------------------------
-- contacts
-- -----------------------------------------------------------------------------
create table if not exists public.contacts (
    id                  uuid primary key default gen_random_uuid(),
    type                text not null default 'individual'
                        check (type in ('individual', 'company')),
    first_name          text,
    last_name           text,
    email               text not null,
    phone               text,
    preferred_language  text not null default 'fr',
    preferred_channel   text not null default 'email'
                        check (preferred_channel in ('email', 'whatsapp', 'phone')),
    consent_marketing   boolean not null default false,
    consent_marketing_at timestamptz,
    created_at          timestamptz not null default now(),
    updated_at          timestamptz not null default now()
);

create unique index if not exists contacts_email_key
    on public.contacts (lower(email));

drop trigger if exists trg_contacts_updated_at on public.contacts;
create trigger trg_contacts_updated_at
    before update on public.contacts
    for each row execute function public.set_updated_at();

-- -----------------------------------------------------------------------------
-- organizations (préparée ; utilisée pour le profil « société »)
-- -----------------------------------------------------------------------------
create table if not exists public.organizations (
    id          uuid primary key default gen_random_uuid(),
    name        text not null,
    vat_number  text,
    created_at  timestamptz not null default now(),
    updated_at  timestamptz not null default now()
);

drop trigger if exists trg_organizations_updated_at on public.organizations;
create trigger trg_organizations_updated_at
    before update on public.organizations
    for each row execute function public.set_updated_at();

-- -----------------------------------------------------------------------------
-- Compteur annuel pour la référence publique FCP-AAAA-000000
-- -----------------------------------------------------------------------------
create table if not exists public.enquiry_reference_counters (
    year        integer primary key,
    last_value  integer not null default 0
);

-- Génère la prochaine référence pour l'année donnée, de façon atomique.
-- Format imposé : FCP-<AAAA>-<6 chiffres>. Compteur remis à zéro chaque année.
create or replace function public.next_enquiry_reference(p_year integer)
returns text
language plpgsql
as $$
declare
    v_next integer;
begin
    insert into public.enquiry_reference_counters (year, last_value)
    values (p_year, 1)
    on conflict (year)
    do update set last_value = public.enquiry_reference_counters.last_value + 1
    returning last_value into v_next;

    return 'FCP-' || p_year::text || '-' || lpad(v_next::text, 6, '0');
end;
$$;

-- -----------------------------------------------------------------------------
-- enquiries (demandes)
-- -----------------------------------------------------------------------------
create table if not exists public.enquiries (
    id                uuid primary key default gen_random_uuid(),
    public_reference  text unique,                    -- généré par trigger (jamais côté navigateur)
    contact_id        uuid not null references public.contacts(id) on delete restrict,
    organization_id   uuid references public.organizations(id) on delete set null,
    category          text not null default 'mobility',
    subcategory       text,
    status            text not null default 'new'
                      check (status in (
                          'new', 'qualified', 'quote_draft', 'quote_sent',
                          'accepted', 'rejected', 'expired', 'mission', 'closed'
                      )),
    source            text not null default 'website',
    preferred_channel text not null default 'email'
                      check (preferred_channel in ('email', 'whatsapp', 'phone')),
    summary           text,
    locale            text not null default 'fr',
    assigned_to       uuid,
    created_at        timestamptz not null default now(),
    updated_at        timestamptz not null default now()
);

create index if not exists enquiries_contact_created_idx
    on public.enquiries (contact_id, created_at desc);
create index if not exists enquiries_status_idx
    on public.enquiries (status);

drop trigger if exists trg_enquiries_updated_at on public.enquiries;
create trigger trg_enquiries_updated_at
    before update on public.enquiries
    for each row execute function public.set_updated_at();

-- Attribution de la référence publique côté serveur, à l'insertion.
create or replace function public.assign_enquiry_reference()
returns trigger
language plpgsql
as $$
begin
    if new.public_reference is null then
        new.public_reference := public.next_enquiry_reference(
            extract(year from coalesce(new.created_at, now()))::integer
        );
    end if;
    return new;
end;
$$;

drop trigger if exists trg_enquiries_reference on public.enquiries;
create trigger trg_enquiries_reference
    before insert on public.enquiries
    for each row execute function public.assign_enquiry_reference();

-- -----------------------------------------------------------------------------
-- enquiry_details (détails de mission)
-- -----------------------------------------------------------------------------
create table if not exists public.enquiry_details (
    id            uuid primary key default gen_random_uuid(),
    enquiry_id    uuid not null references public.enquiries(id) on delete cascade,
    service_date  date,
    service_time  time,
    origin        text,
    destination   text,
    passengers    integer check (passengers is null or passengers >= 1),
    luggage       integer check (luggage is null or luggage >= 0),
    flight_number text,
    train_number  text,
    duration_hours numeric,
    event_type    text,
    event_size    integer,
    notes         text,
    flexible_json jsonb not null default '{}'::jsonb,
    created_at    timestamptz not null default now()
);

create index if not exists enquiry_details_enquiry_idx
    on public.enquiry_details (enquiry_id);

-- -----------------------------------------------------------------------------
-- communications (traçabilité des envois — cf. arbitrage 7)
--   status : prepared | opened | sent | failed
--   « sent » uniquement lorsqu'une preuve technique existe.
--   L'ouverture d'un lien wa.me = 'opened', jamais 'sent'.
-- -----------------------------------------------------------------------------
create table if not exists public.communications (
    id           uuid primary key default gen_random_uuid(),
    enquiry_id   uuid references public.enquiries(id) on delete cascade,
    channel      text not null check (channel in ('whatsapp', 'email', 'phone')),
    type         text not null,                       -- ex : enquiry_receipt, internal_alert
    recipient    text,
    status       text not null default 'prepared'
                 check (status in ('prepared', 'opened', 'sent', 'failed')),
    provider_id  text,                                -- id fournisseur (Brevo) si disponible
    error        text,
    created_at   timestamptz not null default now()
);

create index if not exists communications_enquiry_idx
    on public.communications (enquiry_id, channel);

-- -----------------------------------------------------------------------------
-- audit_logs (journal minimal — cf. arbitrage 6)
--   Aucun secret ni donnée inutilement sensible.
-- -----------------------------------------------------------------------------
create table if not exists public.audit_logs (
    id           uuid primary key default gen_random_uuid(),
    actor_id     uuid,                                -- null = système / visiteur public
    action       text not null,                       -- ex : enquiry.created, enquiry.status_changed
    table_name   text,
    record_id    uuid,
    old_values   jsonb,
    new_values   jsonb,
    ip_address   inet,
    request_id   text,
    created_at   timestamptz not null default now()
);

create index if not exists audit_logs_record_idx
    on public.audit_logs (table_name, record_id);
create index if not exists audit_logs_created_idx
    on public.audit_logs (created_at desc);

-- -----------------------------------------------------------------------------
-- Row Level Security : activée partout, aucune policy anonyme.
-- => Seule la clé service_role (serveur / plugin) peut lire/écrire.
--    Les policies fines par rôle Horizon seront ajoutées à l'activation de l'auth.
-- -----------------------------------------------------------------------------
alter table public.contacts        enable row level security;
alter table public.organizations   enable row level security;
alter table public.enquiries       enable row level security;
alter table public.enquiry_details enable row level security;
alter table public.communications  enable row level security;
alter table public.audit_logs      enable row level security;
