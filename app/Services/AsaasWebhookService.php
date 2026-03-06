<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AsaasEvent;
use App\Models\IntegrationLog;
use App\Models\Subscription;
use App\Models\UpgradeRequest;

final class AsaasWebhookService
{
    public function handle(array $payload): void
    {
        $eventId = (string)($payload['id'] ?? ($payload['eventId'] ?? ''));
        $type = (string)($payload['event'] ?? ($payload['type'] ?? ''));

        if ($eventId === '') {
            $eventId = 'no-id-' . time();
        }

        $stored = AsaasEvent::store($eventId, $type !== '' ? $type : null, $payload);
        if (!$stored) {
            IntegrationLog::add('asaas', 'webhook.store', 'event', $eventId, 'skipped', 'Duplicate or store failed', $payload, null);
            return;
        }

        $paymentExternalRef = $this->extractExternalReference($payload);
        if ($paymentExternalRef !== null) {
            $this->tryHandleUpgradeRequestByExternalReference($paymentExternalRef, $payload);
        }

        $subId = $this->extractSubscriptionId($payload);
        if ($subId === null) {
            IntegrationLog::add('asaas', 'webhook.parse', 'event', $eventId, 'skipped', 'No subscription id found in payload', $payload, null);
            return;
        }

        $subscription = Subscription::findByAsaasSubscriptionId($subId);
        if ($subscription === null) {
            IntegrationLog::add('asaas', 'webhook.link', 'asaas_subscription', $subId, 'error', 'Subscription not found in local DB', $payload, null);
            return;
        }

        $newStatus = $this->mapStatus($payload);
        if ($newStatus === null) {
            IntegrationLog::add('asaas', 'webhook.mapStatus', 'subscription', (string)$subscription['id'], 'skipped', 'Unmapped event/status', $payload, null);
            return;
        }

        Subscription::setStatus((int)$subscription['id'], $newStatus);
        IntegrationLog::add('asaas', 'subscription.status', 'subscription', (string)$subscription['id'], 'ok', 'Updated status to ' . $newStatus, $payload, ['status' => $newStatus]);

        if ($newStatus === 'active') {
            $pending = UpgradeRequest::pendingBySubscriptionId((int)$subscription['id']);
            if (count($pending) === 1) {
                $req = $pending[0];
                Subscription::setPlanId((int)$subscription['id'], (int)$req['target_plan_id']);
                UpgradeRequest::apply((int)$req['id']);
                IntegrationLog::add('asaas', 'upgrade.apply_auto', 'subscription', (string)$subscription['id'], 'ok', 'Auto applied upgrade request ' . (int)$req['id'], $payload, ['target_plan_id' => (int)$req['target_plan_id']]);
            }

            if (count($pending) > 1) {
                IntegrationLog::add('asaas', 'upgrade.apply_auto', 'subscription', (string)$subscription['id'], 'error', 'More than one pending upgrade request', $payload, ['pending_count' => count($pending)]);
            }
        }

        $provisioning = new ProvisioningService();
        if ($newStatus === 'active') {
            $provisioning->activateSubscription((int)$subscription['id']);
        }
        if ($newStatus === 'overdue' || $newStatus === 'inactive') {
            $provisioning->suspendSubscription((int)$subscription['id']);
        }
    }

    private function extractExternalReference(array $payload): ?string
    {
        $candidates = [
            $payload['externalReference'] ?? null,
            $payload['payment']['externalReference'] ?? null,
        ];

        foreach ($candidates as $c) {
            if (is_string($c) && trim($c) !== '') {
                return trim($c);
            }
        }

        return null;
    }

    private function extractPaymentStatus(array $payload): ?string
    {
        if (!isset($payload['payment']) || !is_array($payload['payment'])) {
            return null;
        }

        $status = $payload['payment']['status'] ?? null;
        if (!is_string($status) || trim($status) === '') {
            return null;
        }

        return strtoupper(trim($status));
    }

    private function tryHandleUpgradeRequestByExternalReference(string $externalReference, array $payload): void
    {
        if (!str_starts_with($externalReference, 'upgrade_request:')) {
            return;
        }

        $req = UpgradeRequest::findByExternalReference($externalReference);
        if ($req === null) {
            IntegrationLog::add('asaas', 'upgrade.external_reference', 'upgrade_request', null, 'error', 'Upgrade request not found by externalReference', $payload, ['externalReference' => $externalReference]);
            return;
        }

        $paymentStatus = $this->extractPaymentStatus($payload);
        UpgradeRequest::updateLastAsaasStatus((int)$req['id'], $paymentStatus);

        if ($paymentStatus === 'RECEIVED' || $paymentStatus === 'CONFIRMED') {
            if ((string)$req['status'] === 'pending') {
                Subscription::setPlanId((int)$req['subscription_id'], (int)$req['target_plan_id']);
                UpgradeRequest::apply((int)$req['id']);
                IntegrationLog::add('asaas', 'upgrade.apply_external_reference', 'upgrade_request', (string)$req['id'], 'ok', 'Applied upgrade by externalReference', $payload, ['subscription_id' => (int)$req['subscription_id'], 'target_plan_id' => (int)$req['target_plan_id']]);
            }
        }
    }

    private function extractSubscriptionId(array $payload): ?string
    {
        $candidates = [
            $payload['subscription'] ?? null,
            $payload['subscriptionId'] ?? null,
            $payload['payment']['subscription'] ?? null,
            $payload['payment']['subscriptionId'] ?? null,
        ];

        foreach ($candidates as $c) {
            if (is_string($c) && $c !== '') {
                return $c;
            }
        }

        return null;
    }

    private function mapStatus(array $payload): ?string
    {
        $event = (string)($payload['event'] ?? ($payload['type'] ?? ''));

        $status = null;
        if (isset($payload['payment']) && is_array($payload['payment'])) {
            $status = (string)($payload['payment']['status'] ?? '');
        }

        $event = strtoupper($event);
        $status = strtoupper((string)$status);

        if ($status === 'RECEIVED' || $status === 'CONFIRMED') {
            return 'active';
        }

        if ($status === 'OVERDUE') {
            return 'overdue';
        }

        if ($status === 'CANCELED' || $status === 'REFUNDED') {
            return 'inactive';
        }

        if (str_contains($event, 'PAYMENT_') && ($status === '')) {
            return null;
        }

        return null;
    }
}
