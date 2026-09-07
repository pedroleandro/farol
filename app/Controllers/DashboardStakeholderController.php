<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;
use JetBrains\PhpStorm\NoReturn;

class DashboardStakeholderController extends Controller
{
    private const MONTH_NAMES_SHORT = [
        1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun",
        7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez",
    ];

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

        $statusBreakdown = Order::statusBreakdownInMonth($selectedMonth);
        $freightByTypeMonth = Order::freightValueByTypeInMonth($selectedMonth);

        $currentYear = (int)date("Y");
        $availableYears = Order::availableOrderYears();
        if (!in_array($currentYear, $availableYears, true)) {
            array_unshift($availableYears, $currentYear);
        }
        $selectedYear = isset($_GET["ano"]) ? (int)$_GET["ano"] : $currentYear;
        if (!in_array($selectedYear, $availableYears, true)) {
            $selectedYear = $currentYear;
        }

        $freightByTypeYear = Order::freightValueByTypeInYear($selectedYear);
        $freightByMonthYear = Order::freightValueByMonthInYear($selectedYear);

        echo $this->render("dashboard/stakeholder/index", [
            "title" => "Dashboard | " . APP_NAME,
            "availableMonths" => $availableMonths,
            "selectedMonth" => $selectedMonth,
            "currentMonth" => $currentMonth,
            "availableYears" => $availableYears,
            "selectedYear" => $selectedYear,
            "currentYear" => $currentYear,
            "totalOrders" => Order::countInMonth($selectedMonth),
            "totalFreightValue" => Order::sumFreightValueInMonth($selectedMonth),
            "onTimeRate" => Order::onTimeRateInMonth($selectedMonth),
            "avgDeliveryDays" => Order::averageDeliveryDaysInMonth($selectedMonth),
            "statusLabels" => array_map(
                static fn($s) => Order::STATUS_LABELS[$s] ?? $s,
                array_keys($statusBreakdown)
            ),
            "statusValues" => array_values($statusBreakdown),
            "freightTypeLabels" => array_map(
                static fn($t) => Order::FREIGHT_TYPE_LABELS[$t] ?? $t,
                array_keys($freightByTypeMonth)
            ),
            "freightTypeValues" => array_values($freightByTypeMonth),
            "totalFreightYear" => Order::sumFreightValueInYear($selectedYear),
            "freightShareLabels" => array_map(
                static fn($t) => Order::FREIGHT_TYPE_LABELS[$t] ?? $t,
                array_keys($freightByTypeYear)
            ),
            "freightShareValues" => array_values($freightByTypeYear),
            "freightByMonthLabels" => array_map(
                static fn($m) => self::MONTH_NAMES_SHORT[$m],
                array_keys($freightByMonthYear)
            ),
            "freightByMonthValues" => array_values($freightByMonthYear),
        ]);
    }
}