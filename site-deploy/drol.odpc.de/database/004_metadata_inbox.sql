-- Proposed additive migration. Apply only after backup and staging approval.
CREATE TABLE IF NOT EXISTS drol_metadata_inbox (
 candidate_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
 source_url VARCHAR(1000) NOT NULL,
 title VARCHAR(250) NOT NULL,
 metadata_json JSON NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
