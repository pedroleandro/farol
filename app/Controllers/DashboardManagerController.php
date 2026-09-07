<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Import;
use App\Models\Order;
use App\Models\User;
use JetBrains\PhpStorm\NoReturn;

class DashboardManagerController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
    }

    #[NoReturn]
    public function index(): void
    {
        Auth::requireLogin();

        $currentMonth = date("Y-m");
        $availableMonths = Order::availableOrderMonths();

        if (!in_array($currentMonth, $availableMonths, true)) {
            array_unshift($availableMonths, $currentMonth);
        }

        $selectedMonth = $_GET["mes"] ?? $currentMonth;

        if (!in_array($selectedMonth, $availableMonths, true)) {
            $selectedMonth = $currentMonth;
        }

        $totalOrdersMonth = Order::countInMonth($selectedMonth);
        $totalFreightMonth = Order::sumFreightValueInMonth($selectedMonth);

        $daysElapsed = $selectedMonth === $currentMonth
            ? (int)date("j")
            : (int)date("t", strtotime($selectedMonth . "-01"));
        $daysElapsed = max($daysElapsed, 1);

        $avgDailyFreight = round($totalFreightMonth / $daysElapsed, 2);

        $statusBreakdown = Order::statusBreakdownInMonth($selectedMonth);
        $freightByType = Order::freightValueByTypeInMonth($selectedMonth);
        $ordersByDay = Order::ordersByDayInMonth($selectedMonth);

        echo $this->render("dashboard/manager/index", [
            "title" => "Dashboard | " . APP_NAME,

            "availableMonths" => $availableMonths,
            "selectedMonth" => $selectedMonth,
            "currentMonth" => $currentMonth,
            "totalOrders" => $totalOrdersMonth,
            "totalFreightValue" => $totalFreightMonth,
            "onTimeRate" => Order::onTimeRateInMonth($selectedMonth),
            "avgDeliveryDays" => Order::averageDeliveryDaysInMonth($selectedMonth),
            "avgFreightPerOrder" => $totalOrdersMonth > 0
                ? round($totalFreightMonth / $totalOrdersMonth, 2)
                : null,
            "avgDailyFreight" => $avgDailyFreight,
            "dueSoon" => Order::countDueSoon(),
            "awaitingStatusUpdate" => Order::countAwaitingStatusUpdate(),
            "lateOrders" => Order::countLate(),
            "pendingOrders" => Order::countPending(),
            "statusLabels" => array_map(
                static fn($s) => Order::STATUS_LABELS[$s] ?? $s,
                array_keys($statusBreakdown)
            ),
            "statusValues" => array_values($statusBreakdown),
            "freightTypeLabels" => array_map(
                static fn($t) => Order::FREIGHT_TYPE_LABELS[$t] ?? $t,
                array_keys($freightByType)
            ),
            "freightTypeValues" => array_values($freightByType),
            "ordersByDayLabels" => array_keys($ordersByDay),
            "ordersByDayValues" => array_values($ordersByDay),
            "lastImport" => $this->formatLastImport(),
        ]);
    }

    private function formatLastImport(): ?array
    {
        $import = Import::latest();

        if (!$import) {
            return null;
        }

        $userName = "—";
        if ($import->getUserId()) {
            $user = User::find($import->getUserId());
            $userName = $user?->getName() ?? "—";
        }

        return [
            "user_name" => $userName,
            "created_at" => $import->getCreatedAt()
                ? date("d/m/Y H:i", strtotime($import->getCreatedAt()))
                : "—",
            "created_count" => $import->getCreatedCount(),
            "updated_count" => $import->getUpdatedCount(),
            "error_count" => $import->getErrorCount(),
        ];
    }
}