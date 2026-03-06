ALTER TABLE subscriptions
    ADD COLUMN plan_id INT UNSIGNED NULL AFTER client_id;

ALTER TABLE subscriptions
    ADD CONSTRAINT fk_subscriptions_plan_id FOREIGN KEY (plan_id) REFERENCES plans(id)
        ON DELETE SET NULL ON UPDATE CASCADE;

CREATE INDEX idx_subscriptions_plan_id ON subscriptions (plan_id);
