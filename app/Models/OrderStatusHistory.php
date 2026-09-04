<?php

namespace App\Models;

use App\Core\AbstractModel;

class OrderStatusHistory extends AbstractModel
{
    protected string $table = "order_status_history";

    protected bool $timestamps = false;
    protected bool $softDelete = false;

    protected array $fillable = [
        "order_id",
        "user_id",
        "from_status",
        "to_status",
    ];

    protected int $id;
    protected int $orderId;
    protected ?int $userId = null;
    protected ?string $fromStatus = null;
    protected string $toStatus;

    public function getId(): ?int
    {
        return $this->attributes['id'] ?? null;
    }

    public function getOrderId(): ?int
    {
        return $this->attributes['order_id'] ?? null;
    }

    public function getUserId(): ?int
    {
        return $this->attributes['user_id'] ?? null;
    }

    public function getFromStatus(): ?string
    {
        return $this->attributes['from_status'] ?? null;
    }

    public function getToStatus(): ?string
    {
        return $this->attributes['to_status'] ?? null;
    }

    public function getFromStatusLabel(): string
    {
        $from = $this->getFromStatus();
        return $from ? (Order::STATUS_LABELS[$from] ?? $from) : '—';
    }

    public function getToStatusLabel(): string
    {
        return Order::STATUS_LABELS[$this->getToStatus()] ?? $this->getToStatus();
    }

    public function getCreatedAt(): ?string
    {
        return $this->attributes['created_at'] ?? null;
    }

    public static function logTransition(int $orderId, ?int $userId, ?string $from, string $to): void
    {
        $history = new self();
        $history->fill([
            "order_id" => $orderId,
            "user_id" => $userId,
            "from_status" => $from,
            "to_status" => $to,
        ]);
        $history->save();
    }

    /**
     * @return OrderStatusHistory[]
     */
    public static function forOrder(int $orderId): array
    {
        return (new self())
            ->where("order_id", "=", $orderId)
            ->orderBy("created_at", "ASC")
            ->get();
    }
}