<?php

namespace App\Models;

use App\Core\AbstractModel;
use PDO;

class Order extends AbstractModel
{
    public const STATUS_IN_PRODUCTION = "in_production";
    public const STATUS_AWAITING_LOADING = "awaiting_loading";
    public const STATUS_IN_TRANSIT = "in_transit";
    public const STATUS_DELIVERED = "delivered";

    public const STATUS_LABELS = [
        self::STATUS_IN_PRODUCTION => "Em produção",
        self::STATUS_AWAITING_LOADING => "Aguardando carregamento",
        self::STATUS_IN_TRANSIT => "Em viagem",
        self::STATUS_DELIVERED => "Entregue",
    ];

    public const STATUS_BADGE_CLASS = [
        self::STATUS_IN_PRODUCTION => "bg-info",
        self::STATUS_AWAITING_LOADING => "bg-warning",
        self::STATUS_IN_TRANSIT => "bg-primary",
        self::STATUS_DELIVERED => "bg-success",
    ];

    public const STATUS_FLOW = [
        self::STATUS_IN_PRODUCTION => self::STATUS_AWAITING_LOADING,
        self::STATUS_AWAITING_LOADING => self::STATUS_IN_TRANSIT,
        self::STATUS_IN_TRANSIT => self::STATUS_DELIVERED,
        self::STATUS_DELIVERED => null,
    ];

    public const FREIGHT_OWN_FLEET = "own_fleet";
    public const FREIGHT_CIF_CARRIER = "cif_carrier";
    public const FREIGHT_FOB_CLIENT = "fob_client";

    public const FREIGHT_TYPE_LABELS = [
        self::FREIGHT_OWN_FLEET => "Frota Própria",
        self::FREIGHT_CIF_CARRIER => "CIF Transportadora",
        self::FREIGHT_FOB_CLIENT => "FOB Cliente",
    ];

    public const SOURCE_SPREADSHEET = "spreadsheet";
    public const SOURCE_SYSTEM = "system";

    protected string $table = "orders";

    protected array $fillable = [
        "order_number",
        "tracking_code",
        "client_id",
        "product_qty",
        "item_qty",
        "invoice_number",
        "order_date",
        "freight_type",
        "vehicle_type",
        "driver_name",
        "freight_value",
        "loading_date",
        "delivery_date",
        "expected_delivery",
        "status",
        "is_pending",
        "source",
    ];

    protected array $required = [
        "client_id" => "O cliente é obrigatório.",
        "order_date" => "A data do pedido é obrigatória.",
        "freight_type" => "O tipo de frete é obrigatório.",
        "item_qty" => "A quantidade de itens é obrigatória.",
        "vehicle_type" => "O tipo de veículo é obrigatório.",
        "product_qty" => "A quantidade de produtos é obrigatória.",
        "loading_date" => "A data de carregamento é obrigatória.",
        "expected_delivery" => "A previsão de entrega é obrigatória.",
    ];

    protected int $id;
    protected string $orderNumber;
    protected ?string $trackingCode = null;
    protected int $clientId;
    protected ?int $productQty = null;
    protected ?int $itemQty = null;
    protected ?string $invoiceNumber = null;
    protected ?string $orderDate = null;
    protected ?string $freightType = null;
    protected ?string $vehicleType = null;
    protected ?float $freightValue = null;
    protected ?string $loadingDate = null;
    protected ?string $deliveryDate = null;
    protected ?string $expectedDelivery = null;
    protected string $status = self::STATUS_IN_PRODUCTION;
    protected bool $isPending = false;
    protected string $source = self::SOURCE_SYSTEM;

    public function getId(): ?int
    {
        return $this->attributes['id'] ?? null;
    }

    public function getOrderNumber(): ?string
    {
        return $this->attributes['order_number'] ?? null;
    }

    public function getTrackingCode(): ?string
    {
        return $this->attributes['tracking_code'] ?? null;
    }

    public function getClientId(): ?int
    {
        return $this->attributes['client_id'] ?? null;
    }

    public function getProductQty(): ?int
    {
        return $this->attributes['product_qty'] ?? null;
    }

    public function getItemQty(): ?int
    {
        return $this->attributes['item_qty'] ?? null;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->attributes['invoice_number'] ?? null;
    }

    public function getOrderDate(): ?string
    {
        return $this->attributes['order_date'] ?? null;
    }

    public function getFreightType(): ?string
    {
        return $this->attributes['freight_type'] ?? null;
    }

    public function getFreightTypeLabel(): string
    {
        return self::FREIGHT_TYPE_LABELS[$this->getFreightType()] ?? '—';
    }

    public function getVehicleType(): ?string
    {
        return $this->attributes['vehicle_type'] ?? null;
    }

    protected ?string $driverName = null;

    public function getDriverName(): ?string
    {
        return $this->attributes['driver_name'] ?? null;
    }

    public function getFreightPerProduct(): ?float
    {
        $value = $this->getFreightValue();
        $qty = $this->getProductQty();

        if ($value === null || !$qty) {
            return null;
        }

        return round($value / $qty, 2);
    }

    public function getFreightPerProductFormatted(): string
    {
        $value = $this->getFreightPerProduct();

        return $value !== null ? 'R$ ' . number_format($value, 2, ',', '.') : '—';
    }

    public function getFreightValue(): ?float
    {
        return isset($this->attributes['freight_value'])
            ? (float)$this->attributes['freight_value']
            : null;
    }

    public function getFreightValueFormatted(): string
    {
        $value = $this->getFreightValue();
        return $value !== null ? number_format($value, 2, ',', '.') : '';
    }

    public function getLoadingDate(): ?string
    {
        return $this->attributes['loading_date'] ?? null;
    }

    public function getDeliveryDate(): ?string
    {
        return $this->attributes['delivery_date'] ?? null;
    }

    public function getExpectedDelivery(): ?string
    {
        return $this->attributes['expected_delivery'] ?? null;
    }

    public function getStatus(): string
    {
        return $this->attributes['status'] ?? self::STATUS_IN_PRODUCTION;
    }

    public function getStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->getStatus()] ?? $this->getStatus();
    }

    public function getStatusBadgeClass(): string
    {
        return self::STATUS_BADGE_CLASS[$this->getStatus()] ?? "bg-secondary";
    }

    public function isPending(): bool
    {
        return (bool)($this->attributes['is_pending'] ?? false);
    }

    public function getSource(): string
    {
        return $this->attributes['source'] ?? self::SOURCE_SYSTEM;
    }

    public function canMarkPending(): bool
    {
        return $this->getStatus() !== self::STATUS_DELIVERED && !$this->isPending();
    }

    public function canResolvePending(): bool
    {
        return $this->isPending();
    }

    public function getNextStatus(): ?string
    {
        return self::STATUS_FLOW[$this->getStatus()] ?? null;
    }

    public function canAdvanceStatus(): bool
    {
        return $this->getNextStatus() !== null && !$this->isPending();
    }

    public function getTotalDays(): ?int
    {
        $end = $this->getDeliveryDate() ?? date('Y-m-d');
        return $this->daysBetween($this->getOrderDate(), $end);
    }

    public function isOnTime(): ?bool
    {
        $expected = $this->getExpectedDelivery();

        if (!$expected) {
            return null;
        }

        $reference = $this->getDeliveryDate() ?? date('Y-m-d');

        return strtotime($reference) <= strtotime($expected);
    }

    private function daysBetween(?string $start, ?string $end): ?int
    {
        if (!$start || !$end) {
            return null;
        }

        $diff = (strtotime($end) - strtotime($start)) / 86400;

        return (int)round($diff);
    }

    public static function generateOrderNumber(): string
    {
        do {
            $candidate = "PED" . date("y") . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $exists = (new static())
                ->where("order_number", "=", $candidate)
                ->first();
        } while ($exists !== null);

        return $candidate;
    }

    public static function generateTrackingCode(): string
    {
        do {
            $candidate = "FR-" . strtoupper(bin2hex(random_bytes(4)));
            $exists = (new static())
                ->where("tracking_code", "=", $candidate)
                ->first();
        } while ($exists !== null);

        return $candidate;
    }

    public static function existsForClient(int $clientId): bool
    {
        return (new static())
                ->where("client_id", "=", $clientId)
                ->count() > 0;
    }

    public static function clientIdsWithOrders(array $clientIds): array
    {
        if (empty($clientIds)) {
            return [];
        }

        $rows = (new static())
            ->whereIn("client_id", $clientIds)
            ->countGroupBy("client_id");

        return array_map(static fn($row) => (int)$row["client_id"], $rows);
    }

    public static function countCreatedToday(): int
    {
        return (new static())
            ->where("created_at", ">=", date("Y-m-d 00:00:00"))
            ->count();
    }

    public static function countAwaitingStatusUpdate(): int
    {
        $instance = new static();

        $sql = "SELECT COUNT(*) FROM orders
            WHERE deleted_at IS NULL
              AND is_pending = 0
              AND status IN ('in_production', 'awaiting_loading')
              AND loading_date IS NOT NULL
              AND loading_date <= CURDATE()";

        return (int)$instance->connection->query($sql)->fetchColumn();
    }

    public static function countLate(): int
    {
        $instance = new static();

        $sql = "SELECT COUNT(*) FROM orders
            WHERE deleted_at IS NULL
              AND status <> 'delivered'
              AND expected_delivery IS NOT NULL
              AND expected_delivery < CURDATE()";

        return (int)$instance->connection->query($sql)->fetchColumn();
    }

    public static function countPending(): int
    {
        return (new static())->where("is_pending", "=", 1)->count();
    }

    /**
     * @return Order[]
     */
    public static function recent(int $limit = 5): array
    {
        return (new static())->orderBy("created_at", "DESC")->limit($limit)->get();
    }

    /**
     * Métodos para o Despachante
     */
    public static function availableOrderMonths(): array
    {
        $instance = new static();

        $sql = "SELECT DISTINCT DATE_FORMAT(order_date, '%Y-%m') AS ym
            FROM orders
            WHERE deleted_at IS NULL AND order_date IS NOT NULL
            ORDER BY ym DESC";

        return $instance->connection->query($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function countInMonth(string $yearMonth): int
    {
        [$start, $end] = self::monthRange($yearMonth);

        return (new static())
            ->where("order_date", ">=", $start)
            ->where("order_date", "<=", $end)
            ->count();
    }

    public static function countAwaitingStatusUpdateInMonth(string $yearMonth): int
    {
        [$start, $end] = self::monthRange($yearMonth);
        $instance = new static();

        $sql = "SELECT COUNT(*) FROM orders
            WHERE deleted_at IS NULL
              AND is_pending = 0
              AND status IN ('in_production', 'awaiting_loading')
              AND loading_date IS NOT NULL
              AND loading_date <= CURDATE()
              AND order_date BETWEEN :start AND :end";

        $statement = $instance->connection->prepare($sql);
        $statement->execute(["start" => $start, "end" => $end]);

        return (int)$statement->fetchColumn();
    }

    public static function countLateInMonth(string $yearMonth): int
    {
        [$start, $end] = self::monthRange($yearMonth);
        $instance = new static();

        $sql = "SELECT COUNT(*) FROM orders
            WHERE deleted_at IS NULL
              AND status <> 'delivered'
              AND expected_delivery IS NOT NULL
              AND expected_delivery < CURDATE()
              AND order_date BETWEEN :start AND :end";

        $statement = $instance->connection->prepare($sql);
        $statement->execute(["start" => $start, "end" => $end]);

        return (int)$statement->fetchColumn();
    }

    public static function countPendingInMonth(string $yearMonth): int
    {
        [$start, $end] = self::monthRange($yearMonth);

        return (new static())
            ->where("is_pending", "=", 1)
            ->where("order_date", ">=", $start)
            ->where("order_date", "<=", $end)
            ->count();
    }

    /**
     * @return Order[]
     */
    public static function recentInMonth(string $yearMonth, int $limit = 10): array
    {
        [$start, $end] = self::monthRange($yearMonth);

        return (new static())
            ->where("order_date", ">=", $start)
            ->where("order_date", "<=", $end)
            ->orderBy("order_date", "DESC")
            ->limit($limit)
            ->get();
    }

    private static function monthRange(string $yearMonth): array
    {
        $start = $yearMonth . "-01";
        $end = date("Y-m-t", strtotime($start));

        return [$start, $end];
    }

    /**
     * Métodos do Gerente
     */
    public static function sumFreightValueInMonth(string $yearMonth): float
    {
        [$start, $end] = self::monthRange($yearMonth);
        $instance = new static();

        $sql = "SELECT COALESCE(SUM(freight_value), 0) AS total
            FROM orders
            WHERE deleted_at IS NULL AND order_date BETWEEN :start AND :end";

        $statement = $instance->connection->prepare($sql);
        $statement->execute(["start" => $start, "end" => $end]);

        return (float)$statement->fetchColumn();
    }

    public static function onTimeRateInMonth(string $yearMonth): ?float
    {
        [$start, $end] = self::monthRange($yearMonth);

        $delivered = (new static())
            ->where("status", "=", self::STATUS_DELIVERED)
            ->where("order_date", ">=", $start)
            ->where("order_date", "<=", $end)
            ->get();

        if (empty($delivered)) {
            return null;
        }

        $onTime = 0;
        foreach ($delivered as $order) {
            if ($order->isOnTime()) {
                $onTime++;
            }
        }

        return round(($onTime / count($delivered)) * 100, 1);
    }

    public static function averageDeliveryDaysInMonth(string $yearMonth): ?float
    {
        [$start, $end] = self::monthRange($yearMonth);

        $delivered = (new static())
            ->where("status", "=", self::STATUS_DELIVERED)
            ->where("order_date", ">=", $start)
            ->where("order_date", "<=", $end)
            ->get();

        if (empty($delivered)) {
            return null;
        }

        $total = 0;
        $count = 0;

        foreach ($delivered as $order) {
            $days = $order->getTotalDays();
            if ($days !== null) {
                $total += $days;
                $count++;
            }
        }

        return $count ? round($total / $count, 1) : null;
    }

    public static function statusBreakdownInMonth(string $yearMonth): array
    {
        [$start, $end] = self::monthRange($yearMonth);
        $instance = new static();

        $sql = "SELECT status, COUNT(*) AS total
            FROM orders
            WHERE deleted_at IS NULL AND order_date BETWEEN :start AND :end
            GROUP BY status";

        $statement = $instance->connection->prepare($sql);
        $statement->execute(["start" => $start, "end" => $end]);

        $map = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $map[$row["status"]] = (int)$row["total"];
        }

        return $map;
    }

    public static function freightValueByTypeInMonth(string $yearMonth): array
    {
        [$start, $end] = self::monthRange($yearMonth);
        $instance = new static();

        $sql = "SELECT freight_type, COALESCE(SUM(freight_value), 0) AS total
            FROM orders
            WHERE deleted_at IS NULL AND freight_type IS NOT NULL
              AND order_date BETWEEN :start AND :end
            GROUP BY freight_type";

        $statement = $instance->connection->prepare($sql);
        $statement->execute(["start" => $start, "end" => $end]);

        $map = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $map[$row["freight_type"]] = (float)$row["total"];
        }

        return $map;
    }

    /**
     * @return array<int, int> [dia => quantidade]
     */
    public static function ordersByDayInMonth(string $yearMonth): array
    {
        [$start, $end] = self::monthRange($yearMonth);
        $daysInMonth = (int)date("t", strtotime($start));
        $counts = array_fill(1, $daysInMonth, 0);

        $instance = new static();

        $sql = "SELECT DAY(order_date) AS d, COUNT(*) AS total
            FROM orders
            WHERE deleted_at IS NULL AND order_date BETWEEN :start AND :end
            GROUP BY DAY(order_date)";

        $statement = $instance->connection->prepare($sql);
        $statement->execute(["start" => $start, "end" => $end]);

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $counts[(int)$row["d"]] = (int)$row["total"];
        }

        return $counts;
    }

    public static function countDueSoon(int $daysAhead = 3): int
    {
        $instance = new static();

        $sql = "SELECT COUNT(*) FROM orders
            WHERE deleted_at IS NULL
              AND status <> 'delivered'
              AND expected_delivery IS NOT NULL
              AND expected_delivery BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)";

        $statement = $instance->connection->prepare($sql);
        $statement->execute(["days" => $daysAhead]);

        return (int)$statement->fetchColumn();
    }

    /**
     * Métodos do Proprietário
     */

    public static function availableOrderYears(): array
    {
        $instance = new static();

        $sql = "SELECT DISTINCT YEAR(order_date) AS y
            FROM orders
            WHERE deleted_at IS NULL AND order_date IS NOT NULL
            ORDER BY y DESC";

        return array_map("intval", $instance->connection->query($sql)->fetchAll(\PDO::FETCH_COLUMN));
    }

    public static function sumFreightValueInYear(int $year): float
    {
        $instance = new static();

        $sql = "SELECT COALESCE(SUM(freight_value), 0) AS total
            FROM orders
            WHERE deleted_at IS NULL AND YEAR(order_date) = :year";

        $statement = $instance->connection->prepare($sql);
        $statement->execute(["year" => $year]);

        return (float)$statement->fetchColumn();
    }

    public static function freightValueByTypeInYear(int $year): array
    {
        $instance = new static();

        $sql = "SELECT freight_type, COALESCE(SUM(freight_value), 0) AS total
            FROM orders
            WHERE deleted_at IS NULL AND freight_type IS NOT NULL AND YEAR(order_date) = :year
            GROUP BY freight_type";

        $statement = $instance->connection->prepare($sql);
        $statement->execute(["year" => $year]);

        $map = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $map[$row["freight_type"]] = (float)$row["total"];
        }

        return $map;
    }

    /**
     * @return array<int, float> [mês 1-12 => valor]
     */
    public static function freightValueByMonthInYear(int $year): array
    {
        $counts = array_fill(1, 12, 0.0);
        $instance = new static();

        $sql = "SELECT MONTH(order_date) AS m, COALESCE(SUM(freight_value), 0) AS total
            FROM orders
            WHERE deleted_at IS NULL AND YEAR(order_date) = :year
            GROUP BY MONTH(order_date)";

        $statement = $instance->connection->prepare($sql);
        $statement->execute(["year" => $year]);

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $counts[(int)$row["m"]] = (float)$row["total"];
        }

        return $counts;
    }

    /**
     * Métodos do Cliente
     */
    public static function findByTrackingCode(string $code): ?self
    {
        return (new static())->where("tracking_code", "=", $code)->first();
    }
}