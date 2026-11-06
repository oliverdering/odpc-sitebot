-- D.R.O.L. catalog baseline. Applied to the initially empty IONOS MariaDB on 2026-11-06.
-- No credentials belong in this file.
CREATE TABLE IF NOT EXISTS drol_manufacturers (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 name VARCHAR(190) NOT NULL,
 website VARCHAR(500) DEFAULT NULL,
 PRIMARY KEY (id), UNIQUE KEY uq_manufacturer_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS drol_sources (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 title VARCHAR(255) NOT NULL,
 url VARCHAR(1000) NOT NULL,
 source_type VARCHAR(50) NOT NULL,
 checked_on DATE DEFAULT NULL,
 PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS drol_hardware (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 manufacturer_id INT UNSIGNED NOT NULL,
 model VARCHAR(190) NOT NULL,
 category VARCHAR(100) NOT NULL,
 hardware_revision VARCHAR(100) DEFAULT NULL,
 description TEXT DEFAULT NULL,
 verification_status ENUM('VERIFIED','DERIVED','EXPERIMENTAL','UNVERIFIED','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
 last_verified DATE DEFAULT NULL,
 PRIMARY KEY (id),
 UNIQUE KEY uq_hardware (manufacturer_id,model),
 CONSTRAINT fk_drol_hardware_manufacturer FOREIGN KEY (manufacturer_id) REFERENCES drol_manufacturers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS drol_hardware_sources (
 hardware_id INT UNSIGNED NOT NULL,
 source_id INT UNSIGNED NOT NULL,
 PRIMARY KEY (hardware_id,source_id),
 CONSTRAINT fk_drol_hs_hardware FOREIGN KEY (hardware_id) REFERENCES drol_hardware(id),
 CONSTRAINT fk_drol_hs_source FOREIGN KEY (source_id) REFERENCES drol_sources(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
