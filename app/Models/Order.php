<?php

namespace App\Models;

use App\Core\AbstractModel;

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

    /** Fluxo padrão de avanço: cada status aponta para o próximo. */
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

    public function getDaysToLoading(): ?int
    {
        return $this->daysBetween($this->getOrderDate(), $this->getLoadingDate());
    }

    public function getTravelDays(): ?int
    {
        $end = $this->getDeliveryDate() ?? date('Y-m-d');
        return $this->daysBetween($this->getLoadingDate(), $end);
    }

    public function getTotalDays(): ?int
    {
        $end = $this->getDeliveryDate() ?? date('Y-m-d');
        return $this->daysBetween($this->getOrderDate(), $end);
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
}