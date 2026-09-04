<?php

namespace App\Models;

use App\Core\AbstractModel;
use PDO;

class Client extends AbstractModel
{
    protected string $table = "clients";

    protected array $fillable = [
        "name",
        "city",
        "state",
    ];

    protected array $required = [
        "name" => "O nome do cliente é obrigatório.",
    ];

    protected int $id;
    protected string $name;
    protected ?string $city = null;
    protected ?string $state = null;

    public function getId(): ?int
    {
        return $this->attributes['id'] ?? null;
    }

    public function getName(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function getCity(): ?string
    {
        return $this->attributes['city'] ?? null;
    }

    public function getState(): ?string
    {
        return $this->attributes['state'] ?? null;
    }

    public function getLocation(): string
    {
        $city = $this->getCity();
        $state = $this->getState();

        if ($city && $state) {
            return "{$city}/{$state}";
        }

        return $city ?? $state ?? '—';
    }

    public static function findByNameAndCity(string $name, ?string $city): ?self
    {
        $instance = new static();

        $sql = "SELECT * FROM clients
                WHERE UPPER(TRIM(name)) = UPPER(TRIM(:name))
                  AND deleted_at IS NULL";

        $params = ["name" => $name];

        if ($city) {
            $sql .= " AND UPPER(TRIM(city)) = UPPER(TRIM(:city))";
            $params["city"] = $city;
        } else {
            $sql .= " AND (city IS NULL OR city = '')";
        }

        $sql .= " LIMIT 1";

        $statement = $instance->connection->prepare($sql);
        $statement->execute($params);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $data = $statement->fetch();

        return $data ? static::hydrate($data) : null;
    }
}