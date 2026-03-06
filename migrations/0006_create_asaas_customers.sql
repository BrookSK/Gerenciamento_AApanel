CREATE TABLE IF NOT EXISTS asaas_customers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED NOT NULL,
    asaas_customer_id VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_asaas_customers_client_id (client_id),
    UNIQUE KEY uq_asaas_customers_asaas_customer_id (asaas_customer_id),
    CONSTRAINT fk_asaas_customers_client_id FOREIGN KEY (client_id) REFERENCES clients(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
