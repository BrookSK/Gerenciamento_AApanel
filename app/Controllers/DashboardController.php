<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;

final class DashboardController extends Controller
{
    public function index(): Response
    {
        $auth = Container::get('auth');
        if (!$auth->check()) {
            return Response::redirect('/login');
        }

        return $this->view('dashboard/index', []);
    }
}
