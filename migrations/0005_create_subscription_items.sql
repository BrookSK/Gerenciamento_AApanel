CREATE TABLE IF NOT EXISTS subscription_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    subscription_id INT UNSIGNED NOT NULL,
    resource_type VARCHAR(50) NOT NULL,
    resource_name VARCHAR(190) NULL,
    aapanel_resource_id VARCHAR(100) NULL,
    metadata_json LONGTEXT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_items_subscription_id (subscription_id),
    KEY idx_items_type (resource_type),
    KEY idx_items_status (status),
    CONSTRAINT fk_items_subscription_id FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
