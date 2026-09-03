<?php

namespace App\Models;

use App\Core\AbstractModel;

class User extends AbstractModel
{
    protected string $table = "users";

    protected array $fillable = [
        "name",
        "email",
        "password",
        "role",
        "avatar",
        "is_active",
    ];

    protected array $required = [
        "name" => "O nome é obrigatório.",
        "email" => "O e-mail é obrigatório.",
        "password" => "A senha é obrigatória.",
        "role" => "O papel do usuário é obrigatório.",
    ];

    protected int $id;
    protected string $name;
    protected string $email;
    protected string $password;
    protected string $role;
    protected ?string $avatar = null;
    protected bool $isActive = true;

    public function getId(): ?int
    {
        return $this->attributes['id'] ?? null;
    }

    public function getName(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function getEmail(): ?string
    {
        return $this->attributes['email'] ?? null;
    }

    public function getPassword(): ?string
    {
        return $this->attributes['password'] ?? null;
    }

    public function getRole(): ?string
    {
        return $this->attributes['role'] ?? null;
    }

    public function isActive(): bool
    {
        return (bool)($this->attributes['is_active'] ?? false);
    }

    public function setPassword(string $plainPassword): self
    {
        $this->attributes['password'] = password_hash($plainPassword, PASSWORD_DEFAULT);
        return $this;
    }

    /**
     * Busca um usuário ativo pelo e-mail. Retorna null se não existir
     * ou se a conta estiver desativada/excluída.
     */
    public static function findByEmail(string $email): ?self
    {
        /** @var self|null $user */
        $user = (new static())
            ->where("email", "=", $email)
            ->where("is_active", "=", 1)
            ->first();

        return $user;
    }
}
