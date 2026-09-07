<?php

namespace App\Models;

use App\Core\AbstractModel;

class Import extends AbstractModel
{
    protected string $table = "imports";

    protected bool $timestamps = false;
    protected bool $softDelete = false;

    protected array $fillable = [
        "user_id",
        "file_name",
        "total_rows",
        "created_count",
        "updated_count",
        "error_count",
    ];

    protected int $id;
    protected ?int $userId = null;
    protected string $fileName;
    protected int $totalRows = 0;
    protected int $createdCount = 0;
    protected int $updatedCount = 0;
    protected int $errorCount = 0;

    public function getId(): ?int
    {
        return $this->attributes['id'] ?? null;
    }

    public function getUserId(): ?int
    {
        return $this->attributes['user_id'] ?? null;
    }

    public function getFileName(): ?string
    {
        return $this->attributes['file_name'] ?? null;
    }

    public function getTotalRows(): int
    {
        return (int)($this->attributes['total_rows'] ?? 0);
    }

    public function getCreatedCount(): int
    {
        return (int)($this->attributes['created_count'] ?? 0);
    }

    public function getUpdatedCount(): int
    {
        return (int)($this->attributes['updated_count'] ?? 0);
    }

    public function getErrorCount(): int
    {
        return (int)($this->attributes['error_count'] ?? 0);
    }

    public function getCreatedAt(): ?string
    {
        return $this->attributes['created_at'] ?? null;
    }

    public static function latest(): ?self
    {
        return (new static())->orderBy("created_at", "DESC")->first();
    }
}