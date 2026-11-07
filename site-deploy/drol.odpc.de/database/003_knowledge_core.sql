-- D.R.O.L. V003: additive knowledge core. DO NOT apply without schema backup and staging review.
-- Existing drol_manufacturers, drol_hardware, drol_sources, drol_model_facts and
-- drol_capability_evidence remain intact. No DROP/ALTER of legacy tables.
CREATE TABLE IF NOT EXISTS drol_attributes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(100) NOT NULL UNIQUE,
 datatype ENUM('number','text','boolean','enum','json') NOT NULL,
 unit VARCHAR(40) NULL,
 ttl_days SMALLINT UNSIGNED NOT NULL DEFAULT 365,
 filterable TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS drol_entity_aliases (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 hardware_id INT UNSIGNED NOT NULL,
 alias VARCHAR(190) NOT NULL,
 alias_type ENUM('sku','model_code','fcc_id','regional','marketing','former_name') NOT NULL,
 UNIQUE KEY uq_drol_alias (alias,alias_type),
 CONSTRAINT fk_drol_alias_hw FOREIGN KEY(hardware_id) REFERENCES drol_hardware(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS drol_source_snapshots (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 source_id INT UNSIGNED NOT NULL,
 retrieved_at DATETIME NOT NULL,
 sha256 CHAR(64) NOT NULL,
 http_status SMALLINT UNSIGNED NULL,
 archive_url VARCHAR(1000) NULL,
 snapshot_ref VARCHAR(500) NULL,
 UNIQUE KEY uq_drol_source_hash (source_id,sha256),
 CONSTRAINT fk_drol_snap_source FOREIGN KEY(source_id) REFERENCES drol_sources(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS drol_candidate_facts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 run_id VARCHAR(80) NOT NULL,
 hardware_id INT UNSIGNED NULL,
 entity_hint VARCHAR(190) NULL,
 attribute_id INT UNSIGNED NOT NULL,
 value_raw TEXT NOT NULL,
 value_normalized JSON NULL,
 snapshot_id BIGINT UNSIGNED NOT NULL,
 source_quote VARCHAR(500) NOT NULL,
 source_locator VARCHAR(500) NULL,
 diff_kind ENUM('new','changed','same','conflict') NOT NULL,
 status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
 reviewed_by VARCHAR(150) NULL,
 reviewed_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_drol_candidates_queue (status,created_at),
 CONSTRAINT fk_drol_candidate_hw FOREIGN KEY(hardware_id) REFERENCES drol_hardware(id),
 CONSTRAINT fk_drol_candidate_attribute FOREIGN KEY(attribute_id) REFERENCES drol_attributes(id),
 CONSTRAINT fk_drol_candidate_snapshot FOREIGN KEY(snapshot_id) REFERENCES drol_source_snapshots(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS drol_firmware_releases (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 hardware_id INT UNSIGNED NOT NULL,
 version VARCHAR(100) NOT NULL,
 region VARCHAR(20) NOT NULL DEFAULT '',
 channel VARCHAR(50) NOT NULL DEFAULT '',
 released_on DATE NULL,
 source_id INT UNSIGNED NOT NULL,
 snapshot_id BIGINT UNSIGNED NULL,
 UNIQUE KEY uq_drol_fw (hardware_id,version,region,channel),
 CONSTRAINT fk_drol_fw_hw FOREIGN KEY(hardware_id) REFERENCES drol_hardware(id),
 CONSTRAINT fk_drol_fw_source FOREIGN KEY(source_id) REFERENCES drol_sources(id),
 CONSTRAINT fk_drol_fw_snapshot FOREIGN KEY(snapshot_id) REFERENCES drol_source_snapshots(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS drol_source_watch (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 source_id INT UNSIGNED NOT NULL,
 watch_kind ENUM('rss','github_release','html','sitemap','pdf') NOT NULL,
 interval_hours SMALLINT UNSIGNED NOT NULL DEFAULT 24,
 enabled TINYINT(1) NOT NULL DEFAULT 0,
 last_checked_at DATETIME NULL,
 last_content_sha256 CHAR(64) NULL,
 UNIQUE KEY uq_drol_watch (source_id,watch_kind),
 CONSTRAINT fk_drol_watch_source FOREIGN KEY(source_id) REFERENCES drol_sources(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS drol_release_batches (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 git_sha CHAR(40) NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 fact_count INT UNSIGNED NOT NULL DEFAULT 0,
 notes VARCHAR(500) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS drol_change_log (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 actor VARCHAR(150) NOT NULL,
 action VARCHAR(40) NOT NULL,
 object_type VARCHAR(80) NOT NULL,
 object_id BIGINT UNSIGNED NOT NULL,
 before_json JSON NULL,
 after_json JSON NULL,
 release_batch_id BIGINT UNSIGNED NULL,
 KEY idx_drol_change_object (object_type,object_id,changed_at),
 CONSTRAINT fk_drol_change_batch FOREIGN KEY(release_batch_id) REFERENCES drol_release_batches(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Explicitly absent: automatic approval of AI-generated claims and direct public DB reads.
