<?php

namespace App\Core;

final class LogEvent
{
    // Autenticação
    public const LOGIN_SUCCESS = "login_success";
    public const LOGIN_FAILED = "login_failed";
    public const LOGOUT = "logout";

    // Pedido
    public const ORDER_CREATED = "order_created";
    public const ORDER_UPDATED = "order_updated";
    public const ORDER_STATUS_CHANGED = "order_status_changed";
    public const ORDER_DELETED = "order_deleted";
    public const ORDER_MARKED_PENDING = "order_marked_pending";
    public const ORDER_RESUMED = "order_resumed";

    // Importação de planilha
    public const IMPORT_COMPLETED = "import_completed";

    public static function label(string $event): string
    {
        return match ($event) {
            // Autenticação
            self::LOGIN_SUCCESS => "Login realizado",
            self::LOGIN_FAILED => "Tentativa de login falhou",
            self::LOGOUT => "Logout realizado",

            // Pedido
            self::ORDER_CREATED => "Pedido cadastrado",
            self::ORDER_UPDATED => "Pedido atualizado",
            self::ORDER_STATUS_CHANGED => "Status do pedido alterado",
            self::ORDER_DELETED => "Pedido excluído",
            self::ORDER_MARKED_PENDING => "Pedido marcado como pendente",
            self::ORDER_RESUMED => "Pendência do pedido resolvida",

            // Importação de planilha
            self::IMPORT_COMPLETED => "Importação de planilha concluída",

            default => $event,
        };
    }
}