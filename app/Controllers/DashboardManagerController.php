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

        // ---------------------------------------------------------
        // Filtro de mês
        // ---------------------------------------------------------
        $currentMonth = date("Y-m");
        $availableMonths = Order::availableOrderMonths();

        if (!in_array($currentMonth, $availableMonths, true)) {
            array_unshift($availableMonths, $currentMonth);
        }

        $selectedMonth = $_GET["mes"] ?? $currentMonth;

        if (!in_array($selectedMonth, $availableMonths, true)) {
            $selectedMonth = $currentMonth;
        }

        // ---------------------------------------------------------
        // Filtro de ano
        // ---------------------------------------------------------
        $currentYear = (int)date("Y");
        $availableYears = Order::availableOrderYears();

        if (!in_array($currentYear, $availableYears, true)) {
            array_unshift($availableYears, $currentYear);
        }

        $selectedYear = isset($_GET["ano"]) ? (int)$_GET["ano"] : $currentYear;

        if (!in_array($selectedYear, $availableYears, true)) {
            $selectedYear = $currentYear;
        }

        // ---------------------------------------------------------
        // KPIs e gráficos do mês
        // ---------------------------------------------------------
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
        $freightByVehicle = Order::freightValueByVehicleTypeInMonth($selectedMonth);
        $routeAverages = Order::avgFreightByRouteInMonth($selectedMonth);

        // ---------------------------------------------------------
        // Visão anual
        // ---------------------------------------------------------
        $freightByTypeYear = Order::freightValueByTypeInYear($selectedYear);
        $freightByMonthYear = Order::freightValueByMonthInYear($selectedYear);

        echo $this->render("dashboard/manager/index", [
            "title" => "Dashboard | " . APP_NAME,

            // Filtros
            "availableMonths" => $availableMonths,
            "selectedMonth" => $selectedMonth,
            "currentMonth" => $currentMonth,
            "availableYears" => $availableYears,
            "selectedYear" => $selectedYear,
            "currentYear" => $currentYear,

            // KPIs de negócio (mês)
            "totalOrders" => $totalOrdersMonth,
            "totalFreightValue" => $totalFreightMonth,
            "onTimeRate" => Order::onTimeRateInMonth($selectedMonth),
            "avgDeliveryDays" => Order::averageDeliveryDaysInMonth($selectedMonth),
            "avgFreightPerOrder" => $totalOrdersMonth > 0
                ? round($totalFreightMonth / $totalOrdersMonth, 2)
                : null,
            "avgDailyFreight" => $avgDailyFreight,

            // Pontos de atenção — agora escopados pelo mês selecionado
            "dueSoon" => Order::countDueSoonInMonth($selectedMonth),
            "awaitingStatusUpdate" => Order::countAwaitingStatusUpdateInMonth($selectedMonth),
            "lateOrders" => Order::countLateInMonth($selectedMonth),
            "pendingOrders" => Order::countPendingInMonth($selectedMonth),

            // Gráficos do mês
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
            "vehicleTypeLabels" => array_keys($freightByVehicle),
            "vehicleTypeValues" => array_values($freightByVehicle),
            "routeAverages" => $routeAverages,

            // Visão anual
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