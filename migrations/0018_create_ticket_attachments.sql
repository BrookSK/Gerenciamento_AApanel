CREATE TABLE IF NOT EXISTS ticket_attachments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ticket_message_id INT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NULL,
    size_bytes INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ticket_attachments_message_id (ticket_message_id),
    CONSTRAINT fk_ticket_attachments_message_id FOREIGN KEY (ticket_message_id) REFERENCES ticket_messages(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
