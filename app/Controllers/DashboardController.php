<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;
use JetBrains\PhpStorm\NoReturn;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
    }

    #[NoReturn]
    public function index(): void
    {
        Auth::requireLogin();

        $role = Auth::user()->role ?? '';

        $controller = match ($role) {
            User::ROLE_DISPATCHER => new DashboardDispatcherController(),
            User::ROLE_MANAGER, User::ROLE_STAKEHOLDER => new DashboardManagerController(),
            User::ROLE_ADMIN => new DashboardAdminController(),
            default => null,
        };

        $controller?->index();
    }
}