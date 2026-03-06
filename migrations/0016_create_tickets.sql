CREATE TABLE IF NOT EXISTS tickets (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED NOT NULL,
    assigned_user_id INT UNSIGNED NULL,
    subject VARCHAR(190) NOT NULL,
    type VARCHAR(60) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'open',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    closed_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_tickets_client_id (client_id),
    KEY idx_tickets_assigned_user_id (assigned_user_id),
    KEY idx_tickets_status (status),
    CONSTRAINT fk_tickets_client_id FOREIGN KEY (client_id) REFERENCES clients(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tickets_assigned_user_id FOREIGN KEY (assigned_user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
