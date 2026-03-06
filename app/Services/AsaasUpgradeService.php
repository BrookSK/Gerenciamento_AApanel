<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AsaasCustomer;
use App\Models\Client;
use App\Models\IntegrationLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\UpgradeRequest;

final class AsaasUpgradeService
{
    private SettingsService $settings;

    public function __construct(?SettingsService $settings = null)
    {
        $this->settings = $settings ?? new SettingsService();
    }

    private function api(): ?AsaasApiClient
    {
        $env = (string)($this->settings->safeGet('asaas_environment') ?? 'sandbox');
        $token = null;
        if ($env === 'prod') {
            $token = $this->settings->safeGet('asaas_token_prod');
        } else {
            $token = $this->settings->safeGet('asaas_token_sandbox');
        }

        if (!is_string($token) || trim($token) === '') {
            return null;
        }

        return new AsaasApiClient(trim($token));
    }

    public function createPaymentForUpgradeRequest(int $upgradeRequestId): ?array
    {
        $req = UpgradeRequest::findWithDetails($upgradeRequestId);
        if ($req === null) {
            return null;
        }

        $api = $this->api();
        if ($api === null) {
            IntegrationLog::add('asaas', 'upgrade.create_payment', 'upgrade_request', (string)$upgradeRequestId, 'error', 'ASAAS token not configured', null, null);
            return null;
        }

        $client = Client::find((int)$req['client_id']);
        $subscription = Subscription::find((int)$req['subscription_id']);
        $plan = Plan::find((int)$req['target_plan_id']);

        if ($client === null || $subscription === null || $plan === null) {
            IntegrationLog::add('asaas', 'upgrade.create_payment', 'upgrade_request', (string)$upgradeRequestId, 'error', 'Missing client/subscription/plan', null, ['client' => $client, 'subscription' => $subscription, 'plan' => $plan]);
            return null;
        }

        $customerId = $this->ensureCustomer($api, (int)$client['id'], $client);
        if ($customerId === null) {
            return null;
        }

        $externalReference = 'upgrade_request:' . $upgradeRequestId;
        $dueDate = date('Y-m-d', strtotime('+1 day'));
        $amount = (string)($plan['price'] ?? '0.00');

        $billingType = (string)($this->settings->safeGet('asaas_billing_type') ?? 'PIX');
        $description = (string)($this->settings->safeGet('asaas_payment_description') ?? 'Upgrade de plano');

        $payload = [
            'customer' => $customerId,
            'billingType' => $billingType,
            'value' => $amount,
            'dueDate' => $dueDate,
            'description' => $description,
            'externalReference' => $externalReference,
        ];

        $resp = $api->postJson('/payments', $payload);
        IntegrationLog::add('asaas', 'payments.create', 'upgrade_request', (string)$upgradeRequestId, ($resp['http_status'] ?? 0) >= 200 && ($resp['http_status'] ?? 0) < 300 ? 'ok' : 'error', null, $payload, $resp);

        if (!isset($resp['id']) || !is_string($resp['id'])) {
            return null;
        }

        $invoiceUrl = null;
        if (isset($resp['invoiceUrl']) && is_string($resp['invoiceUrl'])) {
            $invoiceUrl = $resp['invoiceUrl'];
        }

        $status = null;
        if (isset($resp['status']) && is_string($resp['status'])) {
            $status = $resp['status'];
        }

        UpgradeRequest::setPaymentInfo($upgradeRequestId, $resp['id'], $invoiceUrl, $amount, $externalReference, $status);

        return $resp;
    }

    private function ensureCustomer(AsaasApiClient $api, int $clientId, array $clientRow): ?string
    {
        $existing = AsaasCustomer::findByClientId($clientId);
        if ($existing !== null) {
            return (string)$existing['asaas_customer_id'];
        }

        $payload = [
            'name' => (string)($clientRow['name'] ?? ''),
            'email' => (string)($clientRow['email'] ?? ''),
        ];

        if (isset($clientRow['document']) && is_string($clientRow['document']) && trim($clientRow['document']) !== '') {
            $payload['cpfCnpj'] = trim($clientRow['document']);
        }

        if (isset($clientRow['phone']) && is_string($clientRow['phone']) && trim($clientRow['phone']) !== '') {
            $payload['phone'] = trim($clientRow['phone']);
        }

        $resp = $api->postJson('/customers', $payload);
        IntegrationLog::add('asaas', 'customers.create', 'client', (string)$clientId, ($resp['http_status'] ?? 0) >= 200 && ($resp['http_status'] ?? 0) < 300 ? 'ok' : 'error', null, $payload, $resp);

        if (!isset($resp['id']) || !is_string($resp['id'])) {
            return null;
        }

        AsaasCustomer::upsert($clientId, $resp['id']);
        return $resp['id'];
    }
}
