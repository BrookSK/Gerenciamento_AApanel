CREATE TABLE IF NOT EXISTS subscriptions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    plan_type VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    billing_cycle VARCHAR(30) NOT NULL DEFAULT 'monthly',
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    start_date DATE NULL,
    next_due_date DATE NULL,
    asaas_subscription_id VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_subscriptions_client_id (client_id),
    KEY idx_subscriptions_status (status),
    CONSTRAINT fk_subscriptions_client_id FOREIGN KEY (client_id) REFERENCES clients(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
