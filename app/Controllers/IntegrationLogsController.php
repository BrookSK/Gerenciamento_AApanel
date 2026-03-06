<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\IntegrationLog;

final class IntegrationLogsController extends Controller
{
    private function requireAuth(): ?Response
    {
        $auth = Container::get('auth');
        if (!$auth->check()) {
            return Response::redirect('/login');
        }
        return null;
    }

    public function index(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 50);

        try {
            $data = IntegrationLog::paginate($page, $perPage);
        } catch (\Throwable $e) {
            $data = [];
        }

        if (!is_array($data)) {
            $data = [];
        }

        if (!isset($data['items']) || !is_array($data['items'])) {
            $data['items'] = [];
        }

        if (!isset($data['total'])) {
            $data['total'] = 0;
        }

        return $this->view('integration_logs/index', [
            'data' => $data,
        ]);
    }
}
