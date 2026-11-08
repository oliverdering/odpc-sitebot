-- AdminMonkey knowledge tables for existing ai.odpc.de MariaDB 11.8
-- Apply only after WordPress backup and verifying active database connection.
-- Prefix amk_ avoids collision with WordPress tables.
CREATE TABLE IF NOT EXISTS amk_sources (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 canonical_url VARCHAR(768) NOT NULL,
 product_area VARCHAR(80) NOT NULL,
 first_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 last_checked DATETIME NULL,
 UNIQUE KEY uq_source_url (canonical_url(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS amk_documents (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 source_id BIGINT UNSIGNED NOT NULL,
 content_sha256 CHAR(64) NOT NULL,
 title VARCHAR(512) NOT NULL,
 content_text MEDIUMTEXT NOT NULL,
 review_status ENUM('unreviewed','approved','rejected') NOT NULL DEFAULT 'unreviewed',
 captured_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_source_version (source_id, content_sha256),
 KEY ix_review (review_status, captured_at),
 CONSTRAINT fk_amk_source FOREIGN KEY (source_id) REFERENCES amk_sources(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS amk_evaluations (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 track VARCHAR(40) NOT NULL,
 evaluator_version VARCHAR(64) NOT NULL,
 questions_total INT UNSIGNED NOT NULL,
 correct_total INT UNSIGNED NOT NULL,
 evidence_json JSON NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY ix_track_date (track, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
