CREATE TABLE IF NOT EXISTS ticket_messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ticket_id INT UNSIGNED NOT NULL,
    sender_type VARCHAR(20) NOT NULL,
    sender_id INT UNSIGNED NULL,
    message_text TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ticket_messages_ticket_id (ticket_id),
    CONSTRAINT fk_ticket_messages_ticket_id FOREIGN KEY (ticket_id) REFERENCES tickets(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
