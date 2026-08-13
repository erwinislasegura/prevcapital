<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->render('admin/dashboard', [
            'pageTitle' => 'Panel de control',
            'metrics' => [
                'users' => User::count(),
                'activeUsers' => User::count(true),
                'roles' => Role::count(),
            ],
            'recentUsers' => User::recent(),
            'recentActivity' => AuditLog::recent(),
        ]);
    }
}
