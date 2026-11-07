-- =====================================================================
-- SureProcure: fields from the Supplier Accreditation Form + new document types
-- Run once in: Supabase Dashboard > SQL Editor
-- =====================================================================

BEGIN;

-- Form sections B, G and H, plus submission tracking
ALTER TABLE public.vendors
  ADD COLUMN IF NOT EXISTS representative_name  varchar(255),   -- B. Authorized representative
  ADD COLUMN IF NOT EXISTS representative_title varchar(150),   -- B. Position / title
  ADD COLUMN IF NOT EXISTS mobile_number        varchar(50),    -- B. Mobile
  ADD COLUMN IF NOT EXISTS landline             varchar(50),    -- B. Landline
  ADD COLUMN IF NOT EXISTS years_in_industry    smallint,       -- G.1 Years in industry
  ADD COLUMN IF NOT EXISTS complies_hse         boolean,        -- H.2 Complies with HSE standards
  ADD COLUMN IF NOT EXISTS hse_details          text,           -- H.2 Certifications or policies
  ADD COLUMN IF NOT EXISTS declared_by          varchar(255),   -- J. Declaration (typed signature)
  ADD COLUMN IF NOT EXISTS declared_at          timestamp(0),
  ADD COLUMN IF NOT EXISTS submitted_at         timestamp(0);   -- when the vendor sent it for review

-- Section I asks for more documents than the original list allowed
ALTER TABLE public.documents DROP CONSTRAINT IF EXISTS documents_type_check;

ALTER TABLE public.documents ADD CONSTRAINT documents_type_check CHECK (type IN (
  'sec_dti_registration','business_permit','bir_2303','philgeps_registration',
  'articles_of_incorporation','general_information_sheet','audited_financials','other_license',
  'company_profile','organizational_chart','product_catalog','client_reference',
  'code_of_ethics','hse_certification',
  'cv','government_id','certification','portfolio','reference_letter',
  'quotation','purchase_order','contract','proof_of_payment','other'
));

COMMIT;
