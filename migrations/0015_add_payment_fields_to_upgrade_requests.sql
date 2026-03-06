ALTER TABLE upgrade_requests
    ADD COLUMN asaas_payment_id VARCHAR(100) NULL,
    ADD COLUMN asaas_invoice_url VARCHAR(255) NULL,
    ADD COLUMN amount DECIMAL(10,2) NULL,
    ADD COLUMN external_reference VARCHAR(120) NULL,
    ADD COLUMN last_asaas_status VARCHAR(30) NULL,
    ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL;

CREATE INDEX idx_upgrade_requests_external_reference ON upgrade_requests(external_reference);
CREATE INDEX idx_upgrade_requests_asaas_payment_id ON upgrade_requests(asaas_payment_id);
