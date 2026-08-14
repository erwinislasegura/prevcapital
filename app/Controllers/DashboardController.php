<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\ContactInquiry;
use App\Models\Client;
use App\Models\Quote;
use App\Models\Role;
use App\Models\User;
use App\Support\Schema;

final class DashboardController extends Controller
{
    public function index(): void
    {
        Schema::install();
        $this->render('admin/dashboard', [
            'pageTitle' => 'Panel de control',
            'metrics' => [
                'users' => User::count(),
                'activeUsers' => User::count(true),
                'roles' => Role::count(),
                'newContacts' => ContactInquiry::count('new'),
                'quotes' => Quote::count(),
                'acceptedQuotes' => Quote::count('accepted'),
                'activeClients' => Client::count(true),
            ],
            'recentContacts' => ContactInquiry::recent(),
            'recentQuotes' => Quote::recent(),
            'recentActivity' => AuditLog::recent(),
        ]);
    }
}
