<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use JetBrains\PhpStorm\NoReturn;

class DashboardAdminController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
    }

    #[NoReturn]
    public function index(): void
    {
        Auth::requireLogin();

        $currentUserId = Auth::user()->id ?? null;

        $recentLogs = AuditLog::recent(10, $currentUserId);

        $userIds = array_unique(array_filter(array_map(
            static fn(AuditLog $log) => $log->getUserId(),
            $recentLogs
        )));

        $users = [];
        if ($userIds) {
            foreach ((new User())->whereIn("id", $userIds)->get() as $user) {
                $users[$user->getId()] = $user;
            }
        }

        $lastLogin = AuditLog::lastLogin($currentUserId);
        $lastLoginUser = null;
        if ($lastLogin && $lastLogin->getUserId()) {
            $lastLoginUser = $users[$lastLogin->getUserId()] ?? User::find($lastLogin->getUserId());
        }

        $roleRows = (new User())->where("is_active", "=", 1)->countGroupBy("role");
        $usersByRole = [];
        foreach ($roleRows as $row) {
            $usersByRole[] = [
                "label" => User::ROLE_LABELS[$row["role"]] ?? $row["role"],
                "total" => (int)$row["total"],
            ];
        }

        echo $this->render("dashboard/admin/index", [
            "title" => "Dashboard | " . APP_NAME,

            "activeUsers" => (new User())->where("is_active", "=", 1)->count(),
            "lastLoginUserName" => $lastLoginUser?->getName() ?? "—",
            "lastLoginAt" => $lastLogin?->getCreatedAt()
                ? date("d/m/Y H:i", strtotime($lastLogin->getCreatedAt()))
                : "—",
            "totalLoginsToday" => AuditLog::countEventToday(LogEvent::LOGIN_SUCCESS, $currentUserId),
            "ordersCreatedToday" => Order::countCreatedToday(),

            "usersByRole" => $usersByRole,
            "recentLogs" => $recentLogs,
            "recentLogsUsers" => $users,
        ]);
    }
}