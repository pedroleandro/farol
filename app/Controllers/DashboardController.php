<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
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

        $user = Auth::user();

        echo $this->view->render("dashboard/dashboard", [
            "title" => "Visão geral | " . APP_NAME,
            "userName" => $user->name ?? "",
            "userRole" => $user->role ?? "",
        ]);
    }
}
