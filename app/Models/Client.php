<?php

namespace App\Models;

use App\Core\AbstractModel;

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

    /**
     * "Cidade/UF" prontos para exibição, com fallback caso algum
     * dos dois esteja vazio.
     */
    public function getLocation(): string
    {
        $city = $this->getCity();
        $state = $this->getState();

        if ($city && $state) {
            return "{$city}/{$state}";
        }

        return $city ?? $state ?? '—';
    }
}
