ALTER TABLE clients
    ADD COLUMN portal_email VARCHAR(190) NULL AFTER email,
    ADD COLUMN portal_password_hash VARCHAR(255) NULL AFTER portal_email,
    ADD COLUMN portal_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER portal_password_hash,
    ADD COLUMN portal_last_login_at TIMESTAMP NULL DEFAULT NULL AFTER portal_enabled;

CREATE INDEX idx_clients_portal_email ON clients (portal_email);
