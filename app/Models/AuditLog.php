<?php

namespace App\Models;

use App\Core\LogEvent;

class AuditLog extends \App\Core\AbstractModel
{
    protected string $table = "audit_logs";

    protected bool $timestamps = false;
    protected bool $softDelete = false;

    protected int $id;
    protected ?int $userId = null;
    protected string $event;
    protected ?string $description = null;
    protected ?string $ipAddress = null;
    protected ?string $userAgent = null;
    protected ?string $metadata = null;
    protected string $createdAt;

    public function getId(): ?int
    {
        return $this->attributes['id'] ?? null;
    }

    public function getUserId(): ?int
    {
        return $this->attributes['user_id'] ?? null;
    }

    public function getEvent(): ?string
    {
        return $this->attributes['event'] ?? null;
    }

    public function getEventLabel(): string
    {
        return LogEvent::label($this->getEvent() ?? '');
    }

    public function getCreatedAt(): ?string
    {
        return $this->attributes['created_at'] ?? null;
    }

    private function getMetadataArray(): array
    {
        $raw = $this->attributes['metadata'] ?? null;

        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function getDetail(): string
    {
        $meta = $this->getMetadataArray();

        return match ($this->getEvent()) {
            LogEvent::ORDER_CREATED,
            LogEvent::ORDER_UPDATED,
            LogEvent::ORDER_DELETED,
            LogEvent::ORDER_MARKED_PENDING,
            LogEvent::ORDER_RESUMED => isset($meta['order_number'])
                ? "Pedido #" . $meta['order_number']
                : '—',

            LogEvent::ORDER_STATUS_CHANGED => isset($meta['order_number'])
                ? "Pedido #" . $meta['order_number']
                . " (" . $this->statusLabel($meta['from'] ?? null)
                . " → " . $this->statusLabel($meta['to'] ?? null) . ")"
                : '—',

            LogEvent::CLIENT_CREATED,
            LogEvent::CLIENT_UPDATED,
            LogEvent::CLIENT_DELETED => isset($meta['client_name'])
                ? "Cliente: " . $meta['client_name']
                : '—',

            LogEvent::IMPORT_COMPLETED => isset($meta['file_name'])
                ? "Arquivo: " . $meta['file_name']
                : '—',

            default => '—',
        };
    }

    private function statusLabel(?string $status): string
    {
        if (!$status) {
            return '—';
        }

        return Order::STATUS_LABELS[$status] ?? $status;
    }

    /**
     * @return AuditLog[]
     */
    public static function recent(int $limit = 10, ?int $excludeUserId = null): array
    {
        $query = (new static())->orderBy("created_at", "DESC");

        if ($excludeUserId !== null) {
            $query->where("user_id", "<>", $excludeUserId);
        }

        return $query->limit($limit)->get();
    }

    public static function countEventToday(string $event, ?int $excludeUserId = null): int
    {
        $query = (new static())
            ->where("event", "=", $event)
            ->where("created_at", ">=", date("Y-m-d 00:00:00"));

        if ($excludeUserId !== null) {
            $query->where("user_id", "<>", $excludeUserId);
        }

        return $query->count();
    }

    public static function lastLogin(?int $excludeUserId = null): ?self
    {
        $query = (new static())
            ->where("event", "=", LogEvent::LOGIN_SUCCESS)
            ->orderBy("created_at", "DESC");

        if ($excludeUserId !== null) {
            $query->where("user_id", "<>", $excludeUserId);
        }

        return $query->first();
    }
}