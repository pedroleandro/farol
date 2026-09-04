<?php

namespace App\Core;

final class LogEvent
{
    // Autenticação
    public const LOGIN_SUCCESS = "login_success";
    public const LOGIN_FAILED = "login_failed";
    public const LOGOUT = "logout";
    public const ACCOUNT_LOCKED = "account_locked";
    public const SESSION_REVOKED = "session_revoked";

    // Cadastro / pedido
    public const ORDER_CREATED = "order_created";
    public const ORDER_UPDATED = "order_updated";
    public const ORDER_STATUS_CHANGED = "order_status_changed";
    public const ORDER_DELETED = "order_deleted";
    public const ORDER_MARKED_PENDING = "order_marked_pending";
    public const ORDER_RESUMED = "order_resumed";

    // Cadastro / conta
    public const USER_REGISTERED = "user_registered";
    public const EMAIL_VERIFIED = "email_verified";
    public const EMAIL_CHANGED = "email_changed";
    public const PASSWORD_CHANGED = "password_changed";
    public const PASSWORD_RESET_REQUESTED = "password_reset_requested";
    public const PASSWORD_RESET_REQUESTED_UNKNOWN_EMAIL = "password_reset_requested_unknown_email";
    public const ACCOUNT_DELETED = "account_deleted";
    public const SUSPICIOUS_LOGIN_REPORTED = "suspicious_login_reported";

    // Perfil
    public const PROFILE_UPDATED = "profile_updated";

    public static function label(string $event): string
    {
        return match ($event) {
            // Autenticação
            self::LOGIN_SUCCESS => "Login realizado",
            self::LOGIN_FAILED => "Tentativa de login falhou",
            self::LOGOUT => "Logout realizado",
            self::ACCOUNT_LOCKED => "Conta bloqueada por tentativas excessivas",
            self::SESSION_REVOKED => "Sessão encerrada manualmente",

            // Cadastro / conta
            self::USER_REGISTERED => "Usuário cadastrado",
            self::EMAIL_VERIFIED => "E-mail verificado",
            self::EMAIL_CHANGED => "E-mail alterado",
            self::PASSWORD_CHANGED => "Senha alterada",
            self::PASSWORD_RESET_REQUESTED => "Redefinição de senha solicitada",
            self::PASSWORD_RESET_REQUESTED_UNKNOWN_EMAIL => "Redefinição de senha solicitada para e-mail não cadastrado",
            self::ACCOUNT_DELETED => "Conta excluída",

            // Perfil
            self::PROFILE_UPDATED => "Perfil atualizado",

            default => $event,
        };
    }
}