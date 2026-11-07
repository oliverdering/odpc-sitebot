-- D.R.O.L. schema extension: unified, source-linked model facts and capability evidence.
-- Migration only. Review against the deployed MariaDB schema before applying.
-- This file does NOT modify the production database by itself.
CREATE TABLE IF NOT EXISTS drol_model_facts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 hardware_id INT UNSIGNED NOT NULL,
 fact_key VARCHAR(100) NOT NULL,
 value_text VARCHAR(500) NOT NULL,
 unit VARCHAR(40) DEFAULT NULL,
 source_id INT UNSIGNED NOT NULL,
 source_locator VARCHAR(500) DEFAULT NULL,
 evidence_level ENUM('E0','E1','E2','E3') NOT NULL DEFAULT 'E0',
 checked_on DATE NOT NULL,
 checked_by VARCHAR(150) NOT NULL,
 valid_for_firmware VARCHAR(150) DEFAULT NULL,
 notes TEXT DEFAULT NULL,
 PRIMARY KEY(id),
 KEY idx_fact_hardware_key (hardware_id,fact_key),
 CONSTRAINT fk_drol_fact_hardware FOREIGN KEY(hardware_id) REFERENCES drol_hardware(id),
 CONSTRAINT fk_drol_fact_source FOREIGN KEY(source_id) REFERENCES drol_sources(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS drol_capability_evidence (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 hardware_id INT UNSIGNED NOT NULL,
 capability ENUM('OPEN_FIRMWARE','OPEN_HARDWARE','SDK_API','MISSION_CONTROL','EXTERNAL_AI_PROCESSING','FIRMWARE_MODIFICATION') NOT NULL,
 assessment ENUM('YES','PARTIAL','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
 sdk_line VARCHAR(120) DEFAULT NULL,
 firmware_version VARCHAR(120) DEFAULT NULL,
 controller_model VARCHAR(120) DEFAULT NULL,
 host_platform VARCHAR(120) DEFAULT NULL,
 function_name VARCHAR(150) DEFAULT NULL,
 evidence_level ENUM('E0','E1','E2','E3') NOT NULL DEFAULT 'E0',
 source_id INT UNSIGNED DEFAULT NULL,
 source_locator VARCHAR(500) DEFAULT NULL,
 checked_on DATE DEFAULT NULL,
 checked_by VARCHAR(150) DEFAULT NULL,
 expires_on DATE DEFAULT NULL,
 test_reference VARCHAR(500) DEFAULT NULL,
 notes TEXT DEFAULT NULL,
 PRIMARY KEY(id),
 KEY idx_capability_hardware (hardware_id,capability),
 KEY idx_capability_freshness (checked_on,expires_on),
 CONSTRAINT fk_drol_cap_hardware FOREIGN KEY(hardware_id) REFERENCES drol_hardware(id),
 CONSTRAINT fk_drol_cap_source FOREIGN KEY(source_id) REFERENCES drol_sources(id),
 CONSTRAINT ck_drol_cap_yes_requires_source CHECK (assessment NOT IN ('YES','PARTIAL') OR (source_id IS NOT NULL AND checked_on IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- E0 = unverified/unknown; E1 = primary-source documentation;
-- E2 = reproducible lab test with logs/version; E3 = independently repeated test.
-- Distinguish evidence level from positive/negative capability assessment.
-- Do not auto-promote E0 to E1 from a manufacturer homepage alone.
