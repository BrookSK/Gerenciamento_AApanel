<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    private array $config;
    private Router $router;

    public function __construct(array $config)
    {
        $this->config = $config;

        if (($this->config['app']['display_errors'] ?? false) === true) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        }

        $sessionName = (string)($this->config['app']['session_name'] ?? 'app_session');
        if ($sessionName !== '') {
            session_name($sessionName);
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->router = new Router();
        $this->registerRoutes($this->router);

        Container::set('config', $this->config);
        Container::set('db', new Database($this->config['db'] ?? []));
        Container::set('auth', new Auth());
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        $response = $this->router->dispatch($method, $path);

        http_response_code($response->statusCode);
        foreach ($response->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $response->body;
    }

    private function registerRoutes(Router $router): void
    {
        $router->get('/', ['App\\Controllers\\DashboardController', 'index']);

        $router->get('/login', ['App\\Controllers\\AuthController', 'showLogin']);
        $router->post('/login', ['App\\Controllers\\AuthController', 'login']);
        $router->post('/logout', ['App\\Controllers\\AuthController', 'logout']);

        $router->get('/clients', ['App\\Controllers\\ClientsController', 'index']);
        $router->get('/clients/create', ['App\\Controllers\\ClientsController', 'create']);
        $router->post('/clients/store', ['App\\Controllers\\ClientsController', 'store']);
        $router->get('/clients/edit', ['App\\Controllers\\ClientsController', 'edit']);
        $router->post('/clients/update', ['App\\Controllers\\ClientsController', 'update']);
        $router->post('/clients/delete', ['App\\Controllers\\ClientsController', 'delete']);

        $router->get('/subscriptions', ['App\\Controllers\\SubscriptionsController', 'index']);
        $router->get('/subscriptions/create', ['App\\Controllers\\SubscriptionsController', 'create']);
        $router->post('/subscriptions/store', ['App\\Controllers\\SubscriptionsController', 'store']);
        $router->get('/subscriptions/edit', ['App\\Controllers\\SubscriptionsController', 'edit']);
        $router->post('/subscriptions/update', ['App\\Controllers\\SubscriptionsController', 'update']);
        $router->post('/subscriptions/delete', ['App\\Controllers\\SubscriptionsController', 'delete']);

        $router->get('/aapanel-servers', ['App\\Controllers\\AapanelServersController', 'index']);
        $router->get('/aapanel-servers/create', ['App\\Controllers\\AapanelServersController', 'create']);
        $router->post('/aapanel-servers/store', ['App\\Controllers\\AapanelServersController', 'store']);
        $router->get('/aapanel-servers/edit', ['App\\Controllers\\AapanelServersController', 'edit']);
        $router->post('/aapanel-servers/update', ['App\\Controllers\\AapanelServersController', 'update']);
        $router->post('/aapanel-servers/delete', ['App\\Controllers\\AapanelServersController', 'delete']);

        $router->get('/settings', ['App\\Controllers\\SettingsController', 'index']);
        $router->post('/settings/save', ['App\\Controllers\\SettingsController', 'save']);

        $router->get('/portal/login', ['App\\Controllers\\PortalAuthController', 'showLogin']);
        $router->post('/portal/login', ['App\\Controllers\\PortalAuthController', 'login']);
        $router->post('/portal/logout', ['App\\Controllers\\PortalAuthController', 'logout']);
        $router->get('/portal', ['App\\Controllers\\PortalDashboardController', 'index']);

        $router->get('/portal/emails', ['App\\Controllers\\PortalEmailsController', 'index']);
        $router->get('/portal/emails/create', ['App\\Controllers\\PortalEmailsController', 'create']);
        $router->post('/portal/emails/store', ['App\\Controllers\\PortalEmailsController', 'store']);
        $router->post('/portal/emails/delete', ['App\\Controllers\\PortalEmailsController', 'delete']);
        $router->post('/portal/emails/password', ['App\\Controllers\\PortalEmailsController', 'changePassword']);

        $router->get('/portal/tickets', ['App\\Controllers\\PortalTicketsController', 'index']);
        $router->get('/portal/tickets/create', ['App\\Controllers\\PortalTicketsController', 'create']);
        $router->post('/portal/tickets/store', ['App\\Controllers\\PortalTicketsController', 'store']);
        $router->get('/portal/tickets/view', ['App\\Controllers\\PortalTicketsController', 'show']);
        $router->post('/portal/tickets/reply', ['App\\Controllers\\PortalTicketsController', 'reply']);
        $router->post('/portal/tickets/upload', ['App\\Controllers\\PortalTicketsController', 'upload']);
        $router->get('/portal/tickets/attachment', ['App\\Controllers\\PortalTicketsController', 'downloadAttachment']);

        $router->get('/portal/plans', ['App\\Controllers\\PortalPlansController', 'index']);
        $router->post('/portal/plans/upgrade', ['App\\Controllers\\PortalPlansController', 'requestUpgrade']);
        $router->post('/portal/plans/regenerate-payment', ['App\\Controllers\\PortalPlansController', 'regeneratePayment']);

        $router->post('/webhooks/asaas', ['App\\Controllers\\AsaasWebhookController', 'handle']);

        $router->get('/provisioning/wordpress', ['App\\Controllers\\ProvisioningController', 'wordpressForm']);
        $router->post('/provisioning/wordpress', ['App\\Controllers\\ProvisioningController', 'wordpressProvision']);

        $router->get('/integration-logs', ['App\\Controllers\\IntegrationLogsController', 'index']);

        $router->get('/plans', ['App\\Controllers\\PlansController', 'index']);
        $router->get('/plans/create', ['App\\Controllers\\PlansController', 'create']);
        $router->post('/plans/store', ['App\\Controllers\\PlansController', 'store']);
        $router->get('/plans/edit', ['App\\Controllers\\PlansController', 'edit']);
        $router->post('/plans/update', ['App\\Controllers\\PlansController', 'update']);
        $router->post('/plans/delete', ['App\\Controllers\\PlansController', 'delete']);

        $router->get('/upgrade-requests', ['App\\Controllers\\UpgradeRequestsController', 'index']);
        $router->post('/upgrade-requests/apply', ['App\\Controllers\\UpgradeRequestsController', 'apply']);
        $router->post('/upgrade-requests/cancel', ['App\\Controllers\\UpgradeRequestsController', 'cancel']);
        $router->post('/upgrade-requests/regenerate-payment', ['App\\Controllers\\UpgradeRequestsController', 'regeneratePayment']);

        $router->get('/tickets', ['App\\Controllers\\TicketsController', 'index']);
        $router->get('/tickets/view', ['App\\Controllers\\TicketsController', 'show']);
        $router->post('/tickets/assign', ['App\\Controllers\\TicketsController', 'assignToMe']);
        $router->post('/tickets/reply', ['App\\Controllers\\TicketsController', 'reply']);
        $router->post('/tickets/close', ['App\\Controllers\\TicketsController', 'close']);
        $router->post('/tickets/upload', ['App\\Controllers\\TicketsController', 'upload']);
        $router->get('/tickets/attachment', ['App\\Controllers\\TicketsController', 'downloadAttachment']);
    }
}
