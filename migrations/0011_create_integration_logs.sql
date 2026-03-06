CREATE TABLE IF NOT EXISTS integration_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    provider VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    reference_type VARCHAR(50) NULL,
    reference_id VARCHAR(100) NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'ok',
    message VARCHAR(255) NULL,
    request_json LONGTEXT NULL,
    response_json LONGTEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_integration_logs_provider (provider),
    KEY idx_integration_logs_reference (reference_type, reference_id),
    KEY idx_integration_logs_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
