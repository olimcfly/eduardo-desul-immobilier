-- ============================================
-- IMMO LOCAL+ — Migration Complète
-- Version : 1.0.0
-- Date : 2026-04-03
-- ============================================

-- SECTION 0 : Fonctions utilitaires
CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE OR REPLACE FUNCTION public.update_updated_at()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION public.ensure_user_policy(p_table_name text)
RETURNS void
LANGUAGE plpgsql
AS $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = p_table_name
      AND policyname = p_table_name || '_user_policy'
  ) THEN
    EXECUTE format(
      'CREATE POLICY %I ON public.%I FOR ALL USING (auth.uid() = user_id) WITH CHECK (auth.uid() = user_id)',
      p_table_name || '_user_policy',
      p_table_name
    );
  END IF;
END;
$$;

CREATE OR REPLACE FUNCTION public.ensure_updated_at_trigger(p_table_name text)
RETURNS void
LANGUAGE plpgsql
AS $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_trigger t
    JOIN pg_class c ON c.oid = t.tgrelid
    JOIN pg_namespace n ON n.oid = c.relnamespace
    WHERE n.nspname = 'public'
      AND c.relname = p_table_name
      AND t.tgname = 'set_updated_at_' || p_table_name
      AND NOT t.tgisinternal
  ) THEN
    EXECUTE format(
      'CREATE TRIGGER %I BEFORE UPDATE ON public.%I FOR EACH ROW EXECUTE FUNCTION public.update_updated_at()',
      'set_updated_at_' || p_table_name,
      p_table_name
    );
  END IF;
END;
$$;

-- SECTION 1 : Utilisateurs & Comptes
CREATE TABLE IF NOT EXISTS public.users (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  email text UNIQUE NOT NULL,
  password_hash text,
  full_name text,
  advisor_status text DEFAULT 'active',
  last_login_at timestamptz,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.profiles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  firstname text,
  lastname text,
  phone text,
  avatar_url text,
  agency_name text,
  city text,
  postal_code text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_profiles_user_id ON public.profiles(user_id);
CREATE INDEX IF NOT EXISTS idx_profiles_phone ON public.profiles(phone);

CREATE TABLE IF NOT EXISTS public.subscriptions (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  plan_code text NOT NULL,
  provider text DEFAULT 'stripe',
  status text NOT NULL DEFAULT 'trialing',
  period_start timestamptz,
  period_end timestamptz,
  canceled_at timestamptz,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_subscriptions_user_id ON public.subscriptions(user_id);
CREATE INDEX IF NOT EXISTS idx_subscriptions_status ON public.subscriptions(status);

CREATE TABLE IF NOT EXISTS public.roles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  code text NOT NULL,
  label text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW(),
  UNIQUE(user_id, code)
);
CREATE INDEX IF NOT EXISTS idx_roles_user_id ON public.roles(user_id);

CREATE TABLE IF NOT EXISTS public.permissions (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  code text NOT NULL,
  label text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW(),
  UNIQUE(user_id, code)
);
CREATE INDEX IF NOT EXISTS idx_permissions_user_id ON public.permissions(user_id);

CREATE TABLE IF NOT EXISTS public.role_permissions (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  role_id uuid NOT NULL REFERENCES public.roles(id) ON DELETE CASCADE,
  permission_id uuid NOT NULL REFERENCES public.permissions(id) ON DELETE CASCADE,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW(),
  UNIQUE(user_id, role_id, permission_id)
);
CREATE INDEX IF NOT EXISTS idx_role_permissions_user_id ON public.role_permissions(user_id);
CREATE INDEX IF NOT EXISTS idx_role_permissions_role_id ON public.role_permissions(role_id);
CREATE INDEX IF NOT EXISTS idx_role_permissions_permission_id ON public.role_permissions(permission_id);

-- SECTION 2 : Biens Immobiliers
CREATE TABLE IF NOT EXISTS public.properties (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  slug text,
  title text NOT NULL,
  transaction_type text NOT NULL DEFAULT 'vente',
  property_type text NOT NULL DEFAULT 'appartement',
  status text NOT NULL DEFAULT 'pending',
  price numeric(14,2),
  surface numeric(10,2),
  rooms integer,
  bedrooms integer,
  bathrooms integer,
  floor integer,
  address text,
  city text,
  postal_code text,
  sector text,
  latitude numeric(10,8),
  longitude numeric(11,8),
  description text,
  characteristics jsonb,
  dpe_class text,
  ges_class text,
  built_year integer,
  exclusivity boolean DEFAULT false,
  main_photo_url text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
ALTER TABLE public.properties ADD COLUMN IF NOT EXISTS user_id uuid;
CREATE INDEX IF NOT EXISTS idx_properties_user_id ON public.properties(user_id);
CREATE INDEX IF NOT EXISTS idx_properties_status ON public.properties(status);
CREATE INDEX IF NOT EXISTS idx_properties_type ON public.properties(property_type);
CREATE INDEX IF NOT EXISTS idx_properties_city ON public.properties(city);

CREATE TABLE IF NOT EXISTS public.property_photos (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  property_id uuid NOT NULL REFERENCES public.properties(id) ON DELETE CASCADE,
  file_url text NOT NULL,
  alt_text text,
  sort_order integer DEFAULT 0,
  is_primary boolean DEFAULT false,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_property_photos_user_id ON public.property_photos(user_id);
CREATE INDEX IF NOT EXISTS idx_property_photos_property_id ON public.property_photos(property_id);

CREATE TABLE IF NOT EXISTS public.property_documents (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  property_id uuid NOT NULL REFERENCES public.properties(id) ON DELETE CASCADE,
  document_type text,
  file_url text NOT NULL,
  document_name text,
  mime_type text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_property_documents_user_id ON public.property_documents(user_id);
CREATE INDEX IF NOT EXISTS idx_property_documents_property_id ON public.property_documents(property_id);

CREATE TABLE IF NOT EXISTS public.property_history (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  property_id uuid NOT NULL REFERENCES public.properties(id) ON DELETE CASCADE,
  event_type text NOT NULL,
  old_values jsonb,
  new_values jsonb,
  note text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_property_history_user_id ON public.property_history(user_id);
CREATE INDEX IF NOT EXISTS idx_property_history_property_id ON public.property_history(property_id);

-- SECTION 3 : Contacts & Clients
CREATE TABLE IF NOT EXISTS public.contacts (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  property_id uuid REFERENCES public.properties(id) ON DELETE SET NULL,
  first_name text,
  last_name text,
  email text,
  phone text,
  source text DEFAULT 'contact',
  status text DEFAULT 'nouveau',
  project_type text,
  budget numeric(14,2),
  notes text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_contacts_user_id ON public.contacts(user_id);
CREATE INDEX IF NOT EXISTS idx_contacts_email ON public.contacts(email);
CREATE INDEX IF NOT EXISTS idx_contacts_phone ON public.contacts(phone);
CREATE INDEX IF NOT EXISTS idx_contacts_status ON public.contacts(status);

CREATE TABLE IF NOT EXISTS public.contact_notes (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  contact_id uuid NOT NULL REFERENCES public.contacts(id) ON DELETE CASCADE,
  note text NOT NULL,
  note_type text DEFAULT 'manual',
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_contact_notes_user_id ON public.contact_notes(user_id);
CREATE INDEX IF NOT EXISTS idx_contact_notes_contact_id ON public.contact_notes(contact_id);

CREATE TABLE IF NOT EXISTS public.contact_tags (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  contact_id uuid NOT NULL REFERENCES public.contacts(id) ON DELETE CASCADE,
  tag text NOT NULL,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW(),
  UNIQUE(user_id, contact_id, tag)
);
CREATE INDEX IF NOT EXISTS idx_contact_tags_user_id ON public.contact_tags(user_id);
CREATE INDEX IF NOT EXISTS idx_contact_tags_contact_id ON public.contact_tags(contact_id);

CREATE TABLE IF NOT EXISTS public.neuropersona_profiles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  contact_id uuid REFERENCES public.contacts(id) ON DELETE SET NULL,
  persona_name text,
  fears text,
  motivations text,
  objections text,
  preferred_channels jsonb,
  score integer,
  generated_by_ia boolean DEFAULT true,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_neuropersona_profiles_user_id ON public.neuropersona_profiles(user_id);
CREATE INDEX IF NOT EXISTS idx_neuropersona_profiles_contact_id ON public.neuropersona_profiles(contact_id);

-- SECTION 4 : Prospection & Territoire
CREATE TABLE IF NOT EXISTS public.zones_prospection (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  zone_name text NOT NULL,
  city text,
  postal_code text,
  radius_km numeric(6,2),
  strategy_level text,
  target_mandates integer,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_zones_prospection_user_id ON public.zones_prospection(user_id);

CREATE TABLE IF NOT EXISTS public.secteurs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  zone_id uuid REFERENCES public.zones_prospection(id) ON DELETE SET NULL,
  sector_name text NOT NULL,
  avg_price_m2 numeric(12,2),
  competition_level text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_secteurs_user_id ON public.secteurs(user_id);
CREATE INDEX IF NOT EXISTS idx_secteurs_zone_id ON public.secteurs(zone_id);

CREATE TABLE IF NOT EXISTS public.portes (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  secteur_id uuid REFERENCES public.secteurs(id) ON DELETE SET NULL,
  address text,
  contact_name text,
  contact_phone text,
  contact_email text,
  door_status text DEFAULT 'a_traiter',
  last_visit_at timestamptz,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_portes_user_id ON public.portes(user_id);
CREATE INDEX IF NOT EXISTS idx_portes_secteur_id ON public.portes(secteur_id);
CREATE INDEX IF NOT EXISTS idx_portes_status ON public.portes(door_status);

CREATE TABLE IF NOT EXISTS public.prospection_history (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  porte_id uuid REFERENCES public.portes(id) ON DELETE SET NULL,
  zone_id uuid REFERENCES public.zones_prospection(id) ON DELETE SET NULL,
  action_type text NOT NULL,
  result_status text,
  comment text,
  action_date timestamptz DEFAULT NOW(),
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_prospection_history_user_id ON public.prospection_history(user_id);
CREATE INDEX IF NOT EXISTS idx_prospection_history_porte_id ON public.prospection_history(porte_id);

-- SECTION 5 : Marketing & Attraction
CREATE TABLE IF NOT EXISTS public.campaigns (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  name text NOT NULL,
  channel text,
  objective text,
  status text DEFAULT 'draft',
  budget numeric(14,2),
  start_date date,
  end_date date,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_campaigns_user_id ON public.campaigns(user_id);
CREATE INDEX IF NOT EXISTS idx_campaigns_status ON public.campaigns(status);

CREATE TABLE IF NOT EXISTS public.sources_leads (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  code text NOT NULL,
  label text NOT NULL,
  channel text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW(),
  UNIQUE(user_id, code)
);
CREATE INDEX IF NOT EXISTS idx_sources_leads_user_id ON public.sources_leads(user_id);

CREATE TABLE IF NOT EXISTS public.leads (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  campaign_id uuid REFERENCES public.campaigns(id) ON DELETE SET NULL,
  source_id uuid REFERENCES public.sources_leads(id) ON DELETE SET NULL,
  contact_id uuid REFERENCES public.contacts(id) ON DELETE SET NULL,
  property_id uuid REFERENCES public.properties(id) ON DELETE SET NULL,
  email text,
  phone text,
  status text DEFAULT 'new',
  score integer DEFAULT 0,
  payload jsonb,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_leads_user_id ON public.leads(user_id);
CREATE INDEX IF NOT EXISTS idx_leads_campaign_id ON public.leads(campaign_id);
CREATE INDEX IF NOT EXISTS idx_leads_source_id ON public.leads(source_id);
CREATE INDEX IF NOT EXISTS idx_leads_email ON public.leads(email);
CREATE INDEX IF NOT EXISTS idx_leads_phone ON public.leads(phone);
CREATE INDEX IF NOT EXISTS idx_leads_status ON public.leads(status);

CREATE TABLE IF NOT EXISTS public.google_my_business_data (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  location_id text,
  account_id text,
  impressions integer DEFAULT 0,
  calls integer DEFAULT 0,
  website_clicks integer DEFAULT 0,
  direction_requests integer DEFAULT 0,
  average_rating numeric(3,2),
  reviews_count integer,
  measured_on date,
  raw_payload jsonb,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_google_my_business_data_user_id ON public.google_my_business_data(user_id);

CREATE TABLE IF NOT EXISTS public.seo_tracking (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  keyword text NOT NULL,
  target_url text,
  current_position integer,
  previous_position integer,
  impressions integer DEFAULT 0,
  clicks integer DEFAULT 0,
  measured_on date,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_seo_tracking_user_id ON public.seo_tracking(user_id);
CREATE INDEX IF NOT EXISTS idx_seo_tracking_keyword ON public.seo_tracking(keyword);

CREATE TABLE IF NOT EXISTS public.social_posts (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  campaign_id uuid REFERENCES public.campaigns(id) ON DELETE SET NULL,
  property_id uuid REFERENCES public.properties(id) ON DELETE SET NULL,
  platform text NOT NULL,
  title text,
  content text NOT NULL,
  media_urls jsonb,
  post_type text DEFAULT 'post',
  status text DEFAULT 'draft',
  scheduled_at timestamptz,
  published_at timestamptz,
  external_ref text,
  metrics jsonb,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_social_posts_user_id ON public.social_posts(user_id);
CREATE INDEX IF NOT EXISTS idx_social_posts_status ON public.social_posts(status);
CREATE INDEX IF NOT EXISTS idx_social_posts_platform ON public.social_posts(platform);

-- SECTION 6 : Pipeline Commercial
CREATE TABLE IF NOT EXISTS public.mandats (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  contact_id uuid REFERENCES public.contacts(id) ON DELETE SET NULL,
  property_id uuid REFERENCES public.properties(id) ON DELETE SET NULL,
  mandat_number text,
  mandat_type text,
  status text DEFAULT 'brouillon',
  signed_at timestamptz,
  end_at timestamptz,
  commission_rate numeric(5,2),
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_mandats_user_id ON public.mandats(user_id);
CREATE INDEX IF NOT EXISTS idx_mandats_status ON public.mandats(status);

CREATE TABLE IF NOT EXISTS public.rendez_vous (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  contact_id uuid REFERENCES public.contacts(id) ON DELETE SET NULL,
  property_id uuid REFERENCES public.properties(id) ON DELETE SET NULL,
  mandat_id uuid REFERENCES public.mandats(id) ON DELETE SET NULL,
  meeting_type text,
  status text DEFAULT 'planifie',
  starts_at timestamptz,
  ends_at timestamptz,
  location text,
  notes text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_rendez_vous_user_id ON public.rendez_vous(user_id);
CREATE INDEX IF NOT EXISTS idx_rendez_vous_status ON public.rendez_vous(status);

CREATE TABLE IF NOT EXISTS public.offres (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  property_id uuid REFERENCES public.properties(id) ON DELETE SET NULL,
  contact_id uuid REFERENCES public.contacts(id) ON DELETE SET NULL,
  amount numeric(14,2) NOT NULL,
  status text DEFAULT 'soumise',
  offer_date timestamptz DEFAULT NOW(),
  expiration_date timestamptz,
  conditions text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_offres_user_id ON public.offres(user_id);
CREATE INDEX IF NOT EXISTS idx_offres_status ON public.offres(status);

CREATE TABLE IF NOT EXISTS public.transactions (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  mandat_id uuid REFERENCES public.mandats(id) ON DELETE SET NULL,
  offer_id uuid REFERENCES public.offres(id) ON DELETE SET NULL,
  property_id uuid REFERENCES public.properties(id) ON DELETE SET NULL,
  contact_id uuid REFERENCES public.contacts(id) ON DELETE SET NULL,
  status text DEFAULT 'en_cours',
  sale_price numeric(14,2),
  commission_amount numeric(14,2),
  signed_compromise_at timestamptz,
  signed_final_at timestamptz,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON public.transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_transactions_status ON public.transactions(status);

CREATE TABLE IF NOT EXISTS public.suivi_dossier (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  transaction_id uuid REFERENCES public.transactions(id) ON DELETE SET NULL,
  step_code text,
  step_label text,
  status text DEFAULT 'todo',
  due_at timestamptz,
  done_at timestamptz,
  comment text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_suivi_dossier_user_id ON public.suivi_dossier(user_id);
CREATE INDEX IF NOT EXISTS idx_suivi_dossier_transaction_id ON public.suivi_dossier(transaction_id);

-- SECTION 7 : Noah IA Modules
CREATE TABLE IF NOT EXISTS public.ia_sessions (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  module_name text NOT NULL,
  context jsonb,
  session_status text DEFAULT 'active',
  started_at timestamptz DEFAULT NOW(),
  ended_at timestamptz,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_ia_sessions_user_id ON public.ia_sessions(user_id);

CREATE TABLE IF NOT EXISTS public.ia_prompts_history (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  ia_session_id uuid REFERENCES public.ia_sessions(id) ON DELETE SET NULL,
  module_name text,
  prompt text NOT NULL,
  response text,
  model_name text,
  tokens_in integer,
  tokens_out integer,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_ia_prompts_history_user_id ON public.ia_prompts_history(user_id);
CREATE INDEX IF NOT EXISTS idx_ia_prompts_history_session_id ON public.ia_prompts_history(ia_session_id);

CREATE TABLE IF NOT EXISTS public.ancre_positionnements (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  ia_session_id uuid REFERENCES public.ia_sessions(id) ON DELETE SET NULL,
  zone text,
  cible text,
  objective text,
  proposition_1 text,
  proposition_2 text,
  proposition_3 text,
  recommendation text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_ancre_positionnements_user_id ON public.ancre_positionnements(user_id);

CREATE TABLE IF NOT EXISTS public.offre_conseiller_pitchs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  ia_session_id uuid REFERENCES public.ia_sessions(id) ON DELETE SET NULL,
  context text,
  version_simple text,
  version_differenciante text,
  version_resultat text,
  recommendation text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_offre_conseiller_pitchs_user_id ON public.offre_conseiller_pitchs(user_id);

CREATE TABLE IF NOT EXISTS public.syntheses_strategiques (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  ia_session_id uuid REFERENCES public.ia_sessions(id) ON DELETE SET NULL,
  current_positioning text,
  current_offer text,
  current_zone text,
  strategic_summary text,
  priority_actions jsonb,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_syntheses_strategiques_user_id ON public.syntheses_strategiques(user_id);

CREATE TABLE IF NOT EXISTS public.actions_jour (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  ia_session_id uuid REFERENCES public.ia_sessions(id) ON DELETE SET NULL,
  action_label text NOT NULL,
  action_type text,
  priority text,
  due_date date,
  is_done boolean DEFAULT false,
  done_at timestamptz,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_actions_jour_user_id ON public.actions_jour(user_id);

-- SECTION 8 : API & Intégrations
CREATE TABLE IF NOT EXISTS public.api_keys (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  provider text NOT NULL,
  key_name text,
  key_value_encrypted text,
  key_masked text,
  is_active boolean DEFAULT true,
  last_used_at timestamptz,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_api_keys_user_id ON public.api_keys(user_id);

CREATE TABLE IF NOT EXISTS public.api_logs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  api_key_id uuid REFERENCES public.api_keys(id) ON DELETE SET NULL,
  provider text,
  endpoint text,
  method text,
  status_code integer,
  duration_ms integer,
  request_payload jsonb,
  response_payload jsonb,
  error_message text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_api_logs_user_id ON public.api_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_api_logs_api_key_id ON public.api_logs(api_key_id);

CREATE TABLE IF NOT EXISTS public.integrations (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  integration_type text NOT NULL,
  provider text NOT NULL,
  status text DEFAULT 'inactive',
  config jsonb,
  last_sync_at timestamptz,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_integrations_user_id ON public.integrations(user_id);
CREATE INDEX IF NOT EXISTS idx_integrations_status ON public.integrations(status);

CREATE TABLE IF NOT EXISTS public.webhooks (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  integration_id uuid REFERENCES public.integrations(id) ON DELETE SET NULL,
  event_name text NOT NULL,
  target_url text NOT NULL,
  secret text,
  is_active boolean DEFAULT true,
  last_called_at timestamptz,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_webhooks_user_id ON public.webhooks(user_id);
CREATE INDEX IF NOT EXISTS idx_webhooks_integration_id ON public.webhooks(integration_id);

-- SECTION 9 : Analytics & Performance
CREATE TABLE IF NOT EXISTS public.kpis_conseiller (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  period_start date NOT NULL,
  period_end date NOT NULL,
  leads_count integer DEFAULT 0,
  rdv_count integer DEFAULT 0,
  mandats_count integer DEFAULT 0,
  transactions_count integer DEFAULT 0,
  ca_amount numeric(14,2) DEFAULT 0,
  conversion_rate numeric(6,2) DEFAULT 0,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_kpis_conseiller_user_id ON public.kpis_conseiller(user_id);

CREATE TABLE IF NOT EXISTS public.objectifs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  target_type text NOT NULL,
  period text NOT NULL,
  target_value numeric(14,2) NOT NULL,
  achieved_value numeric(14,2) DEFAULT 0,
  status text DEFAULT 'active',
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_objectifs_user_id ON public.objectifs(user_id);
CREATE INDEX IF NOT EXISTS idx_objectifs_status ON public.objectifs(status);

CREATE TABLE IF NOT EXISTS public.rapports (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  report_type text,
  title text,
  report_date date,
  payload jsonb,
  exported_file_url text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_rapports_user_id ON public.rapports(user_id);

CREATE TABLE IF NOT EXISTS public.activite_logs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  entity_type text,
  entity_id uuid,
  action text NOT NULL,
  metadata jsonb,
  ip_address text,
  user_agent text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_activite_logs_user_id ON public.activite_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_activite_logs_action ON public.activite_logs(action);

-- SECTION 10 : Paramètres Système
CREATE TABLE IF NOT EXISTS public.settings (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  setting_key text NOT NULL,
  setting_value jsonb,
  setting_type text DEFAULT 'text',
  setting_group text DEFAULT 'general',
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW(),
  UNIQUE(user_id, setting_key)
);
CREATE INDEX IF NOT EXISTS idx_settings_user_id ON public.settings(user_id);

CREATE TABLE IF NOT EXISTS public.notifications (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  channel text,
  category text,
  title text,
  message text,
  status text DEFAULT 'unread',
  sent_at timestamptz,
  read_at timestamptz,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON public.notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_status ON public.notifications(status);

CREATE TABLE IF NOT EXISTS public.templates (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  template_type text,
  channel text,
  name text NOT NULL,
  subject text,
  content text NOT NULL,
  variables jsonb,
  is_active boolean DEFAULT true,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_templates_user_id ON public.templates(user_id);

CREATE TABLE IF NOT EXISTS public.audit_logs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  actor_id uuid REFERENCES public.users(id) ON DELETE SET NULL,
  action text NOT NULL,
  target_table text,
  target_id uuid,
  old_values jsonb,
  new_values jsonb,
  ip_address text,
  user_agent text,
  deleted_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT NOW(),
  updated_at timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON public.audit_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_actor_id ON public.audit_logs(actor_id);

-- RLS + policies + triggers (tables avec user_id)
DO $$
DECLARE
  t text;
  tables text[] := ARRAY[
    'profiles','subscriptions','roles','permissions','role_permissions',
    'properties','property_photos','property_documents','property_history',
    'contacts','contact_notes','contact_tags','neuropersona_profiles',
    'zones_prospection','secteurs','portes','prospection_history',
    'campaigns','sources_leads','leads','google_my_business_data','seo_tracking','social_posts',
    'mandats','rendez_vous','offres','transactions','suivi_dossier',
    'ia_sessions','ia_prompts_history','ancre_positionnements','offre_conseiller_pitchs','syntheses_strategiques','actions_jour',
    'api_keys','api_logs','webhooks','integrations',
    'kpis_conseiller','objectifs','rapports','activite_logs',
    'settings','notifications','templates','audit_logs'
  ];
BEGIN
  FOREACH t IN ARRAY tables LOOP
    EXECUTE format('ALTER TABLE public.%I ENABLE ROW LEVEL SECURITY', t);
    PERFORM public.ensure_user_policy(t);
    PERFORM public.ensure_updated_at_trigger(t);
  END LOOP;
END
$$;

-- Trigger sur users (sans politique user_id)
SELECT public.ensure_updated_at_trigger('users');

-- Renforcement non-destructif des colonnes standard
DO $$
DECLARE
  t text;
  tables_with_user text[] := ARRAY[
    'profiles','subscriptions','roles','permissions','role_permissions',
    'properties','property_photos','property_documents','property_history',
    'contacts','contact_notes','contact_tags','neuropersona_profiles',
    'zones_prospection','secteurs','portes','prospection_history',
    'campaigns','sources_leads','leads','google_my_business_data','seo_tracking','social_posts',
    'mandats','rendez_vous','offres','transactions','suivi_dossier',
    'ia_sessions','ia_prompts_history','ancre_positionnements','offre_conseiller_pitchs','syntheses_strategiques','actions_jour',
    'api_keys','api_logs','webhooks','integrations',
    'kpis_conseiller','objectifs','rapports','activite_logs',
    'settings','notifications','templates','audit_logs'
  ];
  tables_all text[] := ARRAY[
    'users','profiles','subscriptions','roles','permissions','role_permissions',
    'properties','property_photos','property_documents','property_history',
    'contacts','contact_notes','contact_tags','neuropersona_profiles',
    'zones_prospection','secteurs','portes','prospection_history',
    'campaigns','sources_leads','leads','google_my_business_data','seo_tracking','social_posts',
    'mandats','rendez_vous','offres','transactions','suivi_dossier',
    'ia_sessions','ia_prompts_history','ancre_positionnements','offre_conseiller_pitchs','syntheses_strategiques','actions_jour',
    'api_keys','api_logs','webhooks','integrations',
    'kpis_conseiller','objectifs','rapports','activite_logs',
    'settings','notifications','templates','audit_logs'
  ];
BEGIN
  FOREACH t IN ARRAY tables_with_user LOOP
    EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS user_id uuid', t);
  END LOOP;

  FOREACH t IN ARRAY tables_all LOOP
    EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS deleted_at timestamptz', t);
    EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS created_at timestamptz NOT NULL DEFAULT NOW()', t);
    EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS updated_at timestamptz NOT NULL DEFAULT NOW()', t);
  END LOOP;
END
$$;
