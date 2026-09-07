<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Client;
use App\Models\Order;
use JetBrains\PhpStorm\NoReturn;

class DashboardDispatcherController extends Controller
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

        $recentOrders = Order::recentInMonth($selectedMonth, 10);

        $clientIds = array_unique(array_map(static fn(Order $o) => $o->getClientId(), $recentOrders));
        $clients = [];
        if ($clientIds) {
            foreach ((new Client())->whereIn("id", $clientIds)->get() as $client) {
                $clients[$client->getId()] = $client;
            }
        }

        echo $this->render("dashboard/dispatcher/index", [
            "title" => "Dashboard | " . APP_NAME,
            "availableMonths" => $availableMonths,
            "selectedMonth" => $selectedMonth,
            "currentMonth" => $currentMonth,
            "ordersInMonth" => Order::countInMonth($selectedMonth),
            "awaitingStatusUpdate" => Order::countAwaitingStatusUpdateInMonth($selectedMonth),
            "lateOrders" => Order::countLateInMonth($selectedMonth),
            "pendingOrders" => Order::countPendingInMonth($selectedMonth),
            "recentOrders" => $recentOrders,
            "recentOrdersClients" => $clients,
        ]);
    }
}