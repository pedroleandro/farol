<?php

namespace App\Models;

use App\Core\AbstractModel;

class User extends AbstractModel
{
    public const ROLE_ADMIN = "admin";
    public const ROLE_MANAGER = "manager";
    public const ROLE_DISPATCHER = "dispatcher";
    public const ROLE_STAKEHOLDER = "stakeholder";

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_DISPATCHER,
        self::ROLE_STAKEHOLDER,
    ];

    public const ROLE_LABELS = [
        self::ROLE_ADMIN => "Administrador",
        self::ROLE_MANAGER => "Gerente",
        self::ROLE_DISPATCHER => "Despachante",
        self::ROLE_STAKEHOLDER => "Proprietário",
    ];

    /** Papéis que podem importar planilha. */
    public const ROLES_CAN_IMPORT = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
    ];

    /** Papéis que podem cadastrar/editar pedidos e clientes. */
    public const ROLES_CAN_MANAGE_ORDERS = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_DISPATCHER,
    ];

    /** Papéis que podem EXCLUIR pedidos — mais restrito que gerenciar. */
    public const ROLES_CAN_DELETE_ORDERS = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
    ];

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

    public function getRoleLabel(): string
    {
        return self::ROLE_LABELS[$this->getRole()] ?? $this->getRole() ?? '—';
    }

    public function isActive(): bool
    {
        return (bool)($this->attributes['is_active'] ?? false);
    }

    public function isAdmin(): bool
    {
        return $this->getRole() === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->getRole() === self::ROLE_MANAGER;
    }

    public function isDispatcher(): bool
    {
        return $this->getRole() === self::ROLE_DISPATCHER;
    }

    public function isStakeholder(): bool
    {
        return $this->getRole() === self::ROLE_STAKEHOLDER;
    }

    public function hasRole(array $allowedRoles): bool
    {
        return in_array($this->getRole(), $allowedRoles, true);
    }

    public function setPassword(string $plainPassword): self
    {
        $this->attributes['password'] = password_hash($plainPassword, PASSWORD_DEFAULT);
        return $this;
    }

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