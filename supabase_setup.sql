-- ============================================================
-- RenovaSim — Supabase SQL Setup
-- Run this in: Supabase Dashboard > SQL Editor
-- ============================================================

-- 1. Add missing columns to projects table
ALTER TABLE projects
  ADD COLUMN IF NOT EXISTS description TEXT,
  ADD COLUMN IF NOT EXISTS building_type VARCHAR(100),
  ADD COLUMN IF NOT EXISTS location VARCHAR(100),
  ADD COLUMN IF NOT EXISTS estimations_count INT DEFAULT 0;

-- Make room_type and area_size nullable
ALTER TABLE projects
  ALTER COLUMN room_type DROP NOT NULL,
  ALTER COLUMN area_size DROP NOT NULL;

-- 2. Upgrade plan_features: add feature_key, feature_label, feature_value columns
ALTER TABLE plan_features
  ADD COLUMN IF NOT EXISTS feature_key   VARCHAR(100) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS feature_label VARCHAR(255) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS feature_value VARCHAR(255) NOT NULL DEFAULT '';

-- 3. Create estimations table
CREATE TABLE IF NOT EXISTS estimations (
  id                BIGSERIAL PRIMARY KEY,
  project_id        BIGINT NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
  user_id           BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  label             VARCHAR(255),
  mode              VARCHAR(50) DEFAULT 'wizard',
  job_type          VARCHAR(100),
  area              NUMERIC(8,2),
  location          VARCHAR(100),
  quality           VARCHAR(50),
  cost_min          NUMERIC(12,2) DEFAULT 0,
  cost_max          NUMERIC(12,2) DEFAULT 0,
  cost_display      NUMERIC(12,2) DEFAULT 0,
  confidence_score  NUMERIC(3,2) DEFAULT 0,
  confidence_label  VARCHAR(50),
  fastapi_response  JSONB,
  created_at        TIMESTAMPTZ DEFAULT NOW(),
  updated_at        TIMESTAMPTZ DEFAULT NOW()
);

-- 4. Create rab_shares table
CREATE TABLE IF NOT EXISTS rab_shares (
  id          BIGSERIAL PRIMARY KEY,
  project_id  BIGINT NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
  user_id     BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  token       VARCHAR(64) UNIQUE NOT NULL,
  visibility  VARCHAR(10) DEFAULT 'private' CHECK (visibility IN ('public','private')),
  expires_at  TIMESTAMPTZ NOT NULL,
  created_at  TIMESTAMPTZ DEFAULT NOW(),
  updated_at  TIMESTAMPTZ DEFAULT NOW()
);

-- 5. Seed pricing plans
INSERT INTO pricing_plans (slug, name, description, price, original_price, is_popular, is_active, created_at, updated_at)
VALUES
  ('free',       'Free Plan',   'Cocok untuk memulai perencanaan renovasi.',     0,      0,      false, true, NOW(), NOW()),
  ('pro',        'Pro Plan',    'Untuk renovasi serius dengan fitur lengkap.',   99000,  149000, true,  true, NOW(), NOW()),
  ('enterprise', 'Enterprise',  'Untuk tim dan kontraktor profesional.',         299000, 299000, false, true, NOW(), NOW())
ON CONFLICT (slug) DO NOTHING;

-- 6. Seed plan features (with all required columns)
DO $$
DECLARE
  free_id       BIGINT;
  pro_id        BIGINT;
  enterprise_id BIGINT;
BEGIN
  SELECT id INTO free_id       FROM pricing_plans WHERE slug = 'free';
  SELECT id INTO pro_id        FROM pricing_plans WHERE slug = 'pro';
  SELECT id INTO enterprise_id FROM pricing_plans WHERE slug = 'enterprise';

  -- Clear existing features first (to allow re-running this script)
  DELETE FROM plan_features WHERE pricing_plan_id IN (free_id, pro_id, enterprise_id);

  -- Free plan features
  INSERT INTO plan_features (pricing_plan_id, feature_key, feature_label, feature, feature_value, is_available, created_at, updated_at) VALUES
    (free_id, 'max_projects',                'Maksimal project',           'Maksimal project',           '2',         true,  NOW(), NOW()),
    (free_id, 'max_estimations_per_project', 'Estimasi per project',       'Estimasi per project',       '3',         true,  NOW(), NOW()),
    (free_id, 'ai_estimation',               'AI Estimation (RAI)',        'AI Estimation (RAI)',        'true',      true,  NOW(), NOW()),
    (free_id, 'rab_export',                  'Export RAB ke XLSX',         'Export RAB ke XLSX',         'false',     false, NOW(), NOW()),
    (free_id, 'share_rab',                   'Bagikan RAB ke kontraktor',  'Bagikan RAB ke kontraktor',  'false',     false, NOW(), NOW()),
    (free_id, 'multi_user',                  'Multi-user collaboration',   'Multi-user collaboration',   'false',     false, NOW(), NOW());

  -- Pro plan features
  INSERT INTO plan_features (pricing_plan_id, feature_key, feature_label, feature, feature_value, is_available, created_at, updated_at) VALUES
    (pro_id, 'max_projects',                'Maksimal project',           'Maksimal project',           'unlimited', true,  NOW(), NOW()),
    (pro_id, 'max_estimations_per_project', 'Estimasi per project',       'Estimasi per project',       'unlimited', true,  NOW(), NOW()),
    (pro_id, 'ai_estimation',               'AI Estimation (RAI)',        'AI Estimation (RAI)',        'true',      true,  NOW(), NOW()),
    (pro_id, 'rab_export',                  'Export RAB ke XLSX',         'Export RAB ke XLSX',         'true',      true,  NOW(), NOW()),
    (pro_id, 'share_rab',                   'Bagikan RAB ke kontraktor',  'Bagikan RAB ke kontraktor',  'true',      true,  NOW(), NOW()),
    (pro_id, 'multi_user',                  'Multi-user collaboration',   'Multi-user collaboration',   'false',     false, NOW(), NOW());

  -- Enterprise plan features
  INSERT INTO plan_features (pricing_plan_id, feature_key, feature_label, feature, feature_value, is_available, created_at, updated_at) VALUES
    (enterprise_id, 'max_projects',                'Maksimal project',           'Maksimal project',           'unlimited', true, NOW(), NOW()),
    (enterprise_id, 'max_estimations_per_project', 'Estimasi per project',       'Estimasi per project',       'unlimited', true, NOW(), NOW()),
    (enterprise_id, 'ai_estimation',               'AI Estimation (RAI)',        'AI Estimation (RAI)',        'true',      true, NOW(), NOW()),
    (enterprise_id, 'rab_export',                  'Export RAB ke XLSX',         'Export RAB ke XLSX',         'true',      true, NOW(), NOW()),
    (enterprise_id, 'share_rab',                   'Bagikan RAB ke kontraktor',  'Bagikan RAB ke kontraktor',  'true',      true, NOW(), NOW()),
    (enterprise_id, 'multi_user',                  'Multi-user collaboration',   'Multi-user collaboration',   'true',      true, NOW(), NOW());
END $$;
