CREATE TABLE IF NOT EXISTS asaas_events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_id VARCHAR(100) NULL,
    event_type VARCHAR(100) NULL,
    payload_json LONGTEXT NOT NULL,
    received_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_asaas_events_event_id (event_id),
    KEY idx_asaas_events_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
