<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class UpgradeRequest
{
    public static function create(int $clientId, int $subscriptionId, ?int $currentPlanId, int $targetPlanId): int
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('INSERT INTO upgrade_requests (client_id, subscription_id, current_plan_id, target_plan_id, status) VALUES (:client_id, :subscription_id, :current_plan_id, :target_plan_id, "pending")');
        $stmt->execute([
            'client_id' => $clientId,
            'subscription_id' => $subscriptionId,
            'current_plan_id' => $currentPlanId,
            'target_plan_id' => $targetPlanId,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function pendingBySubscriptionId(int $subscriptionId): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, client_id, subscription_id, current_plan_id, target_plan_id, status, asaas_payment_id, asaas_invoice_url, amount, external_reference, last_asaas_status, created_at FROM upgrade_requests WHERE subscription_id = :sid AND status = "pending" ORDER BY id DESC');
        $stmt->execute(['sid' => $subscriptionId]);
        return $stmt->fetchAll();
    }

    public static function apply(int $id): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE upgrade_requests SET status = "applied", applied_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function cancel(int $id): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE upgrade_requests SET status = "canceled", canceled_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function setPaymentInfo(int $id, string $asaasPaymentId, ?string $invoiceUrl, ?string $amount, ?string $externalReference, ?string $lastAsaasStatus): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE upgrade_requests SET asaas_payment_id = :asaas_payment_id, asaas_invoice_url = :asaas_invoice_url, amount = :amount, external_reference = :external_reference, last_asaas_status = :last_asaas_status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'asaas_payment_id' => $asaasPaymentId,
            'asaas_invoice_url' => $invoiceUrl,
            'amount' => $amount,
            'external_reference' => $externalReference,
            'last_asaas_status' => $lastAsaasStatus,
        ]);
    }

    public static function updateLastAsaasStatus(int $id, ?string $lastAsaasStatus): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE upgrade_requests SET last_asaas_status = :last_asaas_status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'last_asaas_status' => $lastAsaasStatus,
        ]);
    }

    public static function paginate(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $offset = ($page - 1) * $perPage;

        $db = Container::get('db')->pdo();
        $totalStmt = $db->query('SELECT COUNT(*) AS cnt FROM upgrade_requests');
        $totalRow = $totalStmt->fetch();
        $total = (int)($totalRow['cnt'] ?? 0);

        $stmt = $db->prepare('SELECT ur.id, ur.client_id, c.name AS client_name, ur.subscription_id, ur.current_plan_id, ur.target_plan_id, p.name AS target_plan_name, ur.status, ur.asaas_payment_id, ur.asaas_invoice_url, ur.amount, ur.last_asaas_status, ur.created_at FROM upgrade_requests ur INNER JOIN clients c ON c.id = ur.client_id INNER JOIN plans p ON p.id = ur.target_plan_id ORDER BY ur.id DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => (int)ceil($total / $perPage),
        ];
    }

    public static function find(int $id): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, client_id, subscription_id, current_plan_id, target_plan_id, status, asaas_payment_id, asaas_invoice_url, amount, external_reference, last_asaas_status FROM upgrade_requests WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findWithDetails(int $id): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT ur.id, ur.client_id, ur.subscription_id, ur.current_plan_id, ur.target_plan_id, ur.status, ur.asaas_payment_id, ur.asaas_invoice_url, ur.amount, ur.external_reference, ur.last_asaas_status, p.name AS target_plan_name, p.price AS target_plan_price FROM upgrade_requests ur INNER JOIN plans p ON p.id = ur.target_plan_id WHERE ur.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findByExternalReference(string $externalReference): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, client_id, subscription_id, target_plan_id, status, last_asaas_status FROM upgrade_requests WHERE external_reference = :er LIMIT 1');
        $stmt->execute(['er' => $externalReference]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
