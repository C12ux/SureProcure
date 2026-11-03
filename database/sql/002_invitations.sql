-- =====================================================================
-- SureProcure: invitations (vendors and procurement officers join by invitation)
-- Run once in: Supabase Dashboard > SQL Editor
-- Only a SHA-256 hash of each link token is stored, never the token itself.
-- =====================================================================

BEGIN;

CREATE TABLE IF NOT EXISTS public.invitations (
  id            bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  email         varchar(255) NOT NULL,
  role          varchar(30)  NOT NULL CHECK (role IN ('vendor','procurement_officer')),
  company_name  varchar(255),                       -- vendors only
  token_hash    char(64)     NOT NULL UNIQUE,
  invited_by    bigint REFERENCES public.users(id) ON DELETE SET NULL,
  user_id       bigint REFERENCES public.users(id) ON DELETE SET NULL,   -- account created from this invite
  expires_at    timestamp(0) NOT NULL,
  accepted_at   timestamp(0),
  revoked_at    timestamp(0),
  created_at    timestamp(0) NOT NULL DEFAULT now(),
  updated_at    timestamp(0) NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS invitations_email_idx ON public.invitations (lower(email));

ALTER TABLE public.invitations ENABLE ROW LEVEL SECURITY;

COMMIT;
