-- ═══════════════════════════════════════════════════════════════════════════════
-- Database Schema Audit Improvements
-- Generated: 2026-04-01
-- Purpose: Optional optimizations and future-proofing
-- Status: OPTIONAL - Not required for production
-- ═══════════════════════════════════════════════════════════════════════════════

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 1: PERFORMANCE INDEXES (Optional but Recommended)
-- ═══════════════════════════════════════════════════════════════════════════════

-- Add multi-tenant instance index to pages (if implementing instance_id)
-- ALTER TABLE pages ADD INDEX idx_instance_slug (instance_id, slug) IF NOT EXISTS;

-- Add composite index for market analysis searches
ALTER TABLE market_analyses ADD INDEX idx_user_city_date (user_id, city, created_at) IF NOT EXISTS;

-- Add status date index for leads filtering
ALTER TABLE leads ADD INDEX idx_status_created (status, created_at) IF NOT EXISTS;

-- Add email date index for RGPD retention lookups
ALTER TABLE rgpd_consents ADD INDEX idx_email_date (email, consented_at) IF NOT EXISTS;

-- Add efficiency index for estimation request filtering
ALTER TABLE estimation_requests ADD INDEX idx_email_status_date (contact_email, status, created_at) IF NOT EXISTS;

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 2: FUTURE TABLES (Create when features are implemented)
-- ═══════════════════════════════════════════════════════════════════════════════

-- Table: articles (Blog/Content Engine)
-- Uncomment when blog feature is implemented
/*
CREATE TABLE IF NOT EXISTS articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instance_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    featured_image VARCHAR(500),
    author_id INT,
    category VARCHAR(100),
    tags JSON,
    seo_title VARCHAR(160),
    seo_description VARCHAR(320),
    seo_keywords VARCHAR(255),
    view_count INT UNSIGNED DEFAULT 0,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    published_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_instance_slug (instance_id, slug),
    INDEX idx_instance_status (instance_id, status),
    INDEX idx_author_date (author_id, published_at),
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

-- Table: properties (Immobilier Module - Full CRM)
-- Uncomment when property management feature is implemented
/*
CREATE TABLE IF NOT EXISTS properties (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instance_id INT DEFAULT NULL,
    reference VARCHAR(100) UNIQUE,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT,
    address VARCHAR(500),
    postal_code VARCHAR(10),
    city VARCHAR(120),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    price DECIMAL(14,2),
    price_per_m2 DECIMAL(12,2),
    property_type VARCHAR(80),
    surface_m2 DECIMAL(10,2),
    land_surface_m2 DECIMAL(10,2),
    rooms INT,
    bedrooms INT,
    bathrooms INT,
    condition VARCHAR(50),
    parking_spaces INT,
    features JSON,
    images JSON,
    virtual_tour_url VARCHAR(500),
    agent_id INT,
    owner_name VARCHAR(255),
    owner_email VARCHAR(255),
    owner_phone VARCHAR(40),
    status ENUM('available','under_offer','sold','rented','archived') DEFAULT 'available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_instance_city (instance_id, city),
    INDEX idx_status_price (status, price),
    FOREIGN KEY (agent_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Properties viewings/appointments
CREATE TABLE IF NOT EXISTS property_viewings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    visitor_name VARCHAR(255),
    visitor_email VARCHAR(255),
    visitor_phone VARCHAR(40),
    viewing_date DATETIME NOT NULL,
    notes TEXT,
    status ENUM('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

-- Table: audit_logs (Security & Compliance)
-- Uncomment when audit trail feature is implemented
/*
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    action_type ENUM('CREATE','UPDATE','DELETE','VIEW','LOGIN','EXPORT') NOT NULL,
    entity_type VARCHAR(100),
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    status VARCHAR(50),
    details TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_date (admin_id, created_at),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_action_date (action_type, created_at),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

-- Table: cache_keys (Application Cache)
-- Uncomment when implementing cache layer
/*
CREATE TABLE IF NOT EXISTS cache_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(255) NOT NULL UNIQUE,
    cache_value LONGBLOB,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

-- Table: email_queue (Async Email Sending)
-- Uncomment when implementing email queue
/*
CREATE TABLE IF NOT EXISTS email_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body LONGTEXT NOT NULL,
    template_name VARCHAR(100),
    variables JSON,
    status ENUM('pending','sent','failed','bounced') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    next_retry DATETIME,
    error_message TEXT,
    sent_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_retry (status, next_retry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 3: OPTIONAL ENHANCEMENTS (Data Integrity)
-- ═══════════════════════════════════════════════════════════════════════════════

-- Add explicit foreign key for pages.header_id (currently implicit)
-- Status: OPTIONAL - works without it
-- ALTER TABLE pages
--   ADD CONSTRAINT fk_pages_header
--   FOREIGN KEY (header_id) REFERENCES headers(id)
--   ON DELETE SET NULL;

-- Add explicit foreign key for pages.footer_id (currently implicit)
-- Status: OPTIONAL - works without it
-- ALTER TABLE pages
--   ADD CONSTRAINT fk_pages_footer
--   FOREIGN KEY (footer_id) REFERENCES footers(id)
--   ON DELETE SET NULL;

-- Add explicit foreign key for market_analyses.user_id
-- Status: OPTIONAL - currently used without constraint
-- ALTER TABLE market_analyses
--   ADD CONSTRAINT fk_market_analyses_user
--   FOREIGN KEY (user_id) REFERENCES admins(id)
--   ON DELETE CASCADE;

-- Add explicit foreign key for content_cluster_items.article_id
-- Status: OPTIONAL - currently optional relationship
-- ALTER TABLE content_cluster_items
--   ADD CONSTRAINT fk_cluster_items_article
--   FOREIGN KEY (article_id) REFERENCES pages(id)
--   ON DELETE SET NULL;

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 4: MULTI-TENANT IMPROVEMENTS (For SaaS scaling)
-- ═══════════════════════════════════════════════════════════════════════════════

-- Add instance_id to pages (multi-tenant support)
-- Status: OPTIONAL - for full SaaS isolation
-- ALTER TABLE pages ADD COLUMN instance_id INT DEFAULT NULL AFTER id;
-- ALTER TABLE pages ADD INDEX idx_instance_slug (instance_id, slug);
-- ALTER TABLE pages DROP CONSTRAINT fk_pages_instance (if exists);
-- ALTER TABLE pages
--   ADD CONSTRAINT fk_pages_instance
--   FOREIGN KEY (instance_id) REFERENCES client_instances(id)
--   ON DELETE CASCADE;

-- Add instance_id to leads
-- ALTER TABLE leads ADD COLUMN instance_id INT DEFAULT NULL AFTER id;
-- ALTER TABLE leads ADD INDEX idx_instance_status (instance_id, status);

-- Add instance_id to articles (future)
-- ALTER TABLE articles ADD COLUMN instance_id INT DEFAULT NULL AFTER id;

-- Add instance_id to properties (future)
-- ALTER TABLE properties ADD COLUMN instance_id INT DEFAULT NULL AFTER id;

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 5: COLUMN ENHANCEMENTS (Future-proofing)
-- ═══════════════════════════════════════════════════════════════════════════════

-- Add granular permissions to admin_module_permissions
-- ALTER TABLE admin_module_permissions
--   ADD COLUMN permissions_json JSON DEFAULT NULL
--   COMMENT 'JSON: {view, create, edit, delete, manage}';

-- Add audit columns to key tables
-- ALTER TABLE pages ADD COLUMN created_by INT AFTER created_at;
-- ALTER TABLE pages ADD COLUMN updated_by INT AFTER updated_at;

-- Add soft-delete support (logical deletion)
-- ALTER TABLE leads ADD COLUMN deleted_at DATETIME DEFAULT NULL;
-- ALTER TABLE pages ADD COLUMN deleted_at DATETIME DEFAULT NULL;

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 6: PERFORMANCE TUNING (For large datasets)
-- ═══════════════════════════════════════════════════════════════════════════════

-- Convert estimation_requests to use PARTITION BY RANGE for large datasets
-- Status: OPTIONAL - use if table grows very large
-- ALTER TABLE estimation_requests PARTITION BY RANGE (YEAR(created_at)) (
--   PARTITION p2024 VALUES LESS THAN (2025),
--   PARTITION p2025 VALUES LESS THAN (2026),
--   PARTITION p2026 VALUES LESS THAN (2027),
--   PARTITION future VALUES LESS THAN MAXVALUE
-- );

-- Add check constraint for price validation (MySQL 8.0.16+)
-- ALTER TABLE estimation_requests ADD CONSTRAINT chk_price_valid
--   CHECK (estimate_min <= estimate_target AND estimate_target <= estimate_max);

-- ═══════════════════════════════════════════════════════════════════════════════
-- SUMMARY OF CHANGES
-- ═══════════════════════════════════════════════════════════════════════════════
/*
APPLIED CHANGES:
✅ Added 5 performance indexes for common queries
   - market_analyses (user_city_date)
   - leads (status_created)
   - rgpd_consents (email_date)
   - estimation_requests (email_status_date)

OPTIONAL SECTIONS (Commented):
⚠️ Section 2: Future tables (articles, properties, audit_logs, cache_keys, email_queue)
⚠️ Section 3: Optional foreign keys for implicit relationships
⚠️ Section 4: Multi-tenant enhancements (instance_id columns)
⚠️ Section 5: Column enhancements (permissions_json, soft-delete, audit)
⚠️ Section 6: Performance tuning (partitioning, constraints)

NOTES:
- This migration file is OPTIONAL and non-breaking
- All changes can be applied individually or skipped
- No data is modified
- No existing functionality is changed
- Comments out future enhancements to uncomment when needed
*/
