<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Message;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use JetBrains\PhpStorm\NoReturn;

class OrderController extends Controller
{
    private const ROLES_CAN_VIEW_ONLY = [
        User::ROLE_STAKEHOLDER,
    ];

    public function __construct()
    {
        parent::__construct("App");
        $this->requireRole([...User::ROLES_CAN_MANAGE_ORDERS, ...self::ROLES_CAN_VIEW_ONLY]);
    }

    #[NoReturn]
    public function index(): void
    {
        $orders = (new Order())->orderBy("created_at", "DESC")->get();
        $clients = $this->clientsById($orders);

        echo $this->render("orders/index", [
            "title" => "Pedidos | " . APP_NAME,
            "orders" => $orders,
            "clients" => $clients,
        ]);

        clear_old();
    }

    #[NoReturn]
    public function create(): void
    {
        $this->blockViewOnly();

        echo $this->render("orders/create", [
            "title" => "Novo Pedido | " . APP_NAME,
            "clients" => Client::all(),
        ]);

        clear_old();
    }

    #[NoReturn]
    public function store(?array $data): void
    {
        $this->blockViewOnly();
        $this->validateCsrfToken($data ?? [], "/pedidos/cadastrar");

        $order = new Order();

        try {
            $order->fill([
                "order_number" => Order::generateOrderNumber(),
                "tracking_code" => Order::generateTrackingCode(),
                "client_id" => $data["client_id"] ?? null,
                "product_qty" => !empty($data["product_qty"]) ? (int)$data["product_qty"] : null,
                "item_qty" => !empty($data["item_qty"]) ? (int)$data["item_qty"] : null,
                "invoice_number" => !empty($data["invoice_number"]) ? $data["invoice_number"] : null,
                "order_date" => !empty($data["order_date"]) ? $data["order_date"] : null,
                "freight_type" => !empty($data["freight_type"]) ? $data["freight_type"] : null,
                "vehicle_type" => !empty($data["vehicle_type"]) ? $data["vehicle_type"] : null,
                "freight_value" => $this->parseCurrency($data["freight_value"] ?? null),
                "loading_date" => !empty($data["loading_date"]) ? $data["loading_date"] : null,
                "expected_delivery" => !empty($data["expected_delivery"]) ? $data["expected_delivery"] : null,
                "status" => Order::STATUS_IN_PRODUCTION,
                "is_pending" => 0,
                "source" => Order::SOURCE_SYSTEM,
            ]);

            $errors = $order->validate($data);

            if ($errors) {
                flash_old($data);
                foreach ($errors as $error) {
                    Message::warning($error);
                }
                redirect("/pedidos/cadastrar");
                return;
            }

            $order->save();

            $userId = Auth::user()->id ?? null;

            AuditLog::record(LogEvent::ORDER_CREATED, $userId, [
                "order_id" => $order->getId(),
                "order_number" => $order->getOrderNumber(),
            ]);

            OrderStatusHistory::logTransition(
                $order->getId(),
                $userId,
                null,
                Order::STATUS_IN_PRODUCTION
            );
        } catch (\InvalidArgumentException $e) {
            Message::error($e->getMessage());
            redirect("/pedidos/cadastrar");
            return;
        }

        Message::success("Pedido cadastrado com sucesso.");
        redirect("/pedidos");
    }

    #[NoReturn]
    public function edit(?array $data): void
    {
        $this->blockViewOnly();

        $order = Order::find((int)($data["id"] ?? 0));

        if (!$order) {
            Message::warning("Pedido não encontrado ou não existe.");
            redirect("/pedidos");
            return;
        }

        echo $this->render("orders/edit", [
            "title" => "Editar Pedido | " . APP_NAME,
            "order" => $order,
            "clients" => Client::all(),
        ]);

        clear_old();
    }

    #[NoReturn]
    public function update(?array $data): void
    {
        $this->blockViewOnly();
        $this->validateCsrfToken($data ?? [], "/pedidos/editar/" . ($data["id"] ?? ''));

        $order = Order::find((int)($data["id"] ?? 0));

        if (!$order) {
            Message::warning("Pedido não encontrado ou não existe.");
            redirect("/pedidos");
            return;
        }

        try {
            $order->fill([
                "client_id" => $data["client_id"] ?? null,
                "product_qty" => !empty($data["product_qty"]) ? (int)$data["product_qty"] : null,
                "item_qty" => !empty($data["item_qty"]) ? (int)$data["item_qty"] : null,
                "invoice_number" => !empty($data["invoice_number"]) ? $data["invoice_number"] : null,
                "order_date" => !empty($data["order_date"]) ? $data["order_date"] : null,
                "freight_type" => !empty($data["freight_type"]) ? $data["freight_type"] : null,
                "vehicle_type" => !empty($data["vehicle_type"]) ? $data["vehicle_type"] : null,
                "freight_value" => $this->parseCurrency($data["freight_value"] ?? null),
                "loading_date" => !empty($data["loading_date"]) ? $data["loading_date"] : null,
                "expected_delivery" => !empty($data["expected_delivery"]) ? $data["expected_delivery"] : null,
            ]);

            $errors = $order->validate($data);

            if ($errors) {
                flash_old($data);
                foreach ($errors as $error) {
                    Message::warning($error);
                }
                redirect("/pedidos/editar/" . $order->getId());
                return;
            }

            $order->save();

            AuditLog::record(LogEvent::ORDER_UPDATED, Auth::user()->id ?? null, [
                "order_id" => $order->getId(),
                "order_number" => $order->getOrderNumber(),
            ]);
        } catch (\InvalidArgumentException $e) {
            Message::error($e->getMessage());
            redirect("/pedidos/editar/" . $order->getId());
            return;
        }

        Message::success("Pedido atualizado com sucesso.");
        redirect("/pedidos/editar/" . $order->getId());
    }

    #[NoReturn]
    public function advanceStatus(?array $data): void
    {
        $this->blockViewOnly();
        $this->validateCsrfToken($data ?? [], "/pedidos");

        $order = Order::find((int)($data["id"] ?? 0));

        if (!$order) {
            Message::error("Pedido não encontrado ou não existe.");
            redirect("/pedidos");
            return;
        }

        if ($order->isPending()) {
            Message::warning("Resolva a pendência antes de avançar o status.");
            redirect("/pedidos");
            return;
        }

        $previousStatus = $order->getStatus();
        $nextStatus = $order->getNextStatus();

        if (!$nextStatus) {
            Message::warning("Este pedido já está na última etapa do fluxo.");
            redirect("/pedidos");
            return;
        }

        $updates = ["status" => $nextStatus];

        if ($nextStatus === Order::STATUS_DELIVERED) {
            $updates["delivery_date"] = date("Y-m-d");
        }

        $order->fill($updates);
        $order->save();

        $userId = Auth::user()->id ?? null;

        AuditLog::record(LogEvent::ORDER_STATUS_CHANGED, $userId, [
            "order_id" => $order->getId(),
            "order_number" => $order->getOrderNumber(),
            "from" => $previousStatus,
            "to" => $nextStatus,
        ]);

        OrderStatusHistory::logTransition($order->getId(), $userId, $previousStatus, $nextStatus);

        Message::success("Pedido atualizado para \"" . Order::STATUS_LABELS[$nextStatus] . "\".");
        redirect("/pedidos");
    }

    #[NoReturn]
    public function markAsPending(?array $data): void
    {
        $this->blockViewOnly();
        $this->validateCsrfToken($data ?? [], "/pedidos");

        $order = Order::find((int)($data["id"] ?? 0));

        if (!$order) {
            Message::error("Pedido não encontrado ou não existe.");
            redirect("/pedidos");
            return;
        }

        if (!$order->canMarkPending()) {
            Message::warning("Este pedido não pode ser marcado como pendente.");
            redirect("/pedidos");
            return;
        }

        $order->fill(["is_pending" => 1]);
        $order->save();

        AuditLog::record(LogEvent::ORDER_MARKED_PENDING, Auth::user()->id ?? null, [
            "order_id" => $order->getId(),
            "order_number" => $order->getOrderNumber(),
            "status_at_the_time" => $order->getStatus(),
        ]);

        Message::success("Pedido marcado como pendente.");
        redirect("/pedidos");
    }

    #[NoReturn]
    public function resolvePending(?array $data): void
    {
        $this->blockViewOnly();
        $this->validateCsrfToken($data ?? [], "/pedidos");

        $order = Order::find((int)($data["id"] ?? 0));

        if (!$order) {
            Message::error("Pedido não encontrado ou não existe.");
            redirect("/pedidos");
            return;
        }

        if (!$order->canResolvePending()) {
            Message::warning("Este pedido não está marcado como pendente.");
            redirect("/pedidos");
            return;
        }

        $order->fill(["is_pending" => 0]);
        $order->save();

        AuditLog::record(LogEvent::ORDER_RESUMED, Auth::user()->id ?? null, [
            "order_id" => $order->getId(),
            "order_number" => $order->getOrderNumber(),
            "status_at_the_time" => $order->getStatus(),
        ]);

        Message::success("Pendência resolvida. O pedido segue em \"" . $order->getStatusLabel() . "\".");
        redirect("/pedidos");
    }

    #[NoReturn]
    public function destroy(?array $data): void
    {
        $this->blockViewOnly();
        $this->validateCsrfToken($data ?? [], "/pedidos");

        $user = Auth::user();

        if (!in_array($user->role ?? null, User::ROLES_CAN_DELETE_ORDERS, true)) {
            Message::warning("Você não tem permissão para excluir pedidos.");
            redirect("/pedidos");
            return;
        }

        $order = Order::find((int)($data["id"] ?? 0));

        if (!$order) {
            Message::error("Pedido não encontrado ou não existe.");
            redirect("/pedidos");
            return;
        }

        try {
            $orderId = $order->getId();
            $orderNumber = $order->getOrderNumber();

            $order->delete();

            AuditLog::record(LogEvent::ORDER_DELETED, $user->id ?? null, [
                "order_id" => $orderId,
                "order_number" => $orderNumber,
            ]);
        } catch (\InvalidArgumentException $e) {
            Message::error($e->getMessage());
            redirect("/pedidos");
            return;
        }

        Message::success("Pedido excluído com sucesso.");
        redirect("/pedidos");
    }

    private function blockViewOnly(): void
    {
        $role = Auth::user()->role ?? null;

        if (in_array($role, self::ROLES_CAN_VIEW_ONLY, true)) {
            Message::warning("Seu perfil tem acesso somente para visualização.");
            redirect("/pedidos");
        }
    }

    private function parseCurrency(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $clean = str_replace(array('.', ','), array('', '.'), $value);

        return is_numeric($clean) ? $clean : null;
    }

    /**
     * @param Order[] $orders
     * @return array<int, Client>
     */
    private function clientsById(array $orders): array
    {
        $ids = array_unique(array_map(fn(Order $o) => $o->getClientId(), $orders));

        if (empty($ids)) {
            return [];
        }

        $clients = (new Client())->whereIn("id", $ids)->get();

        $map = [];
        foreach ($clients as $client) {
            $map[$client->getId()] = $client;
        }

        return $map;
    }
}