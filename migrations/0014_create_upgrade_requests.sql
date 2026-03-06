CREATE TABLE IF NOT EXISTS upgrade_requests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED NOT NULL,
    subscription_id INT UNSIGNED NOT NULL,
    current_plan_id INT UNSIGNED NULL,
    target_plan_id INT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    applied_at TIMESTAMP NULL DEFAULT NULL,
    canceled_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_upgrade_requests_client_id (client_id),
    KEY idx_upgrade_requests_subscription_id (subscription_id),
    KEY idx_upgrade_requests_status (status),
    CONSTRAINT fk_upgrade_requests_client_id FOREIGN KEY (client_id) REFERENCES clients(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_upgrade_requests_subscription_id FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_upgrade_requests_target_plan_id FOREIGN KEY (target_plan_id) REFERENCES plans(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
