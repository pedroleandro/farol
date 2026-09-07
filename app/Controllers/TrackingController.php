<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use JetBrains\PhpStorm\NoReturn;

class TrackingController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    #[NoReturn]
    public function index(): void
    {
        $code = isset($_GET["codigo"]) ? trim($_GET["codigo"]) : null;

        $order = null;
        $client = null;
        $history = [];
        $notFound = false;

        if ($code) {
            $order = Order::findByTrackingCode(strtoupper($code));

            if ($order) {
                $client = Client::find($order->getClientId());
                $history = OrderStatusHistory::forOrder($order->getId());
            } else {
                $notFound = true;
            }
        }

        echo $this->view->render("tracking/index", [
            "title" => "Rastreio de Pedido | " . APP_NAME,
            "code" => $code,
            "order" => $order,
            "client" => $client,
            "history" => $history,
            "notFound" => $notFound,
        ]);
    }
}