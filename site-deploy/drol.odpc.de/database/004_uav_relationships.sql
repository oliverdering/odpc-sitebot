-- D.R.O.L. V004: cross-manufacturer UAV knowledge graph (additive, review before applying).
-- No claims of compatibility are inferred from graph membership.
CREATE TABLE IF NOT EXISTS drol_knowledge_entities (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 entity_key VARCHAR(190) NOT NULL UNIQUE,
 entity_type ENUM('drone','controller','flight_controller','firmware','sdk','sensor','camera','companion_computer','software','accessory','use_case') NOT NULL,
 manufacturer VARCHAR(190) NULL,
 display_name VARCHAR(250) NOT NULL,
 lifecycle ENUM('unknown','current','legacy','announced','discontinued') NOT NULL DEFAULT 'unknown',
 review_status ENUM('seed','reviewed') NOT NULL DEFAULT 'seed',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_drol_entities_type_mfr (entity_type,manufacturer)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS drol_knowledge_relations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 subject_id BIGINT UNSIGNED NOT NULL,
 predicate ENUM('compatible_with','uses','supports','runs','connects_to','can_capture_for','successor_of','related_to') NOT NULL,
 object_id BIGINT UNSIGNED NOT NULL,
 assessment ENUM('unknown','claimed','verified','incompatible') NOT NULL DEFAULT 'unknown',
 evidence_level ENUM('E0','E1','E2','E3') NOT NULL DEFAULT 'E0',
 source_url VARCHAR(1000) NULL,
 source_quote VARCHAR(500) NULL,
 reviewed_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_drol_knowledge_relation(subject_id,predicate,object_id),
 KEY idx_drol_relations_object(object_id),
 CONSTRAINT fk_drol_relation_subject FOREIGN KEY(subject_id) REFERENCES drol_knowledge_entities(id),
 CONSTRAINT fk_drol_relation_object FOREIGN KEY(object_id) REFERENCES drol_knowledge_entities(id),
 CONSTRAINT chk_drol_no_self_relation CHECK(subject_id<>object_id),
 CONSTRAINT chk_drol_verified_has_source CHECK(assessment<>'verified' OR (source_url IS NOT NULL AND evidence_level<>'E0'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS drol_knowledge_sources (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 entity_id BIGINT UNSIGNED NOT NULL,
 source_url VARCHAR(1000) NOT NULL,
 source_kind ENUM('manufacturer','official_developer','documentation','community','research') NOT NULL,
 evidence_level ENUM('E0','E1','E2','E3') NOT NULL DEFAULT 'E0',
 checked_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_drol_entity_source(entity_id),
 CONSTRAINT fk_drol_knowledge_source_entity FOREIGN KEY(entity_id) REFERENCES drol_knowledge_entities(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- No automatic E1+ promotion; importers may only insert E0 seeds/candidates.
