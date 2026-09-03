<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Message;
use App\Core\Session;
use App\Core\SessionTimeoutMiddleware;
use JetBrains\PhpStorm\NoReturn;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
    }

    #[NoReturn]
    public function index(): void
    {

        if (Auth::check()) {
            redirect("/dashboard");
            return;
        }

        echo $this->view->render("auth/auth-login", [
            "title" => "Login | " . APP_NAME,
        ]);
    }

    #[NoReturn]
    public function login(?array $data): void
    {
        $this->validateCsrfToken($data ?? [], "/entrar");

        $email = trim($data["email"] ?? "");
        $password = (string)($data["password"] ?? "");

        if ($email === "" || $password === "") {
            Message::error("Informe e-mail e senha para continuar.");
            redirect("/entrar");
            return;
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user->getPassword())) {
            AuditLog::record(LogEvent::LOGIN_FAILED, $user?->getId(), [
                "email" => $email,
            ]);

            flash_old(["email" => $email]);
            Message::error("E-mail ou senha inválidos.");
            redirect("/entrar");
            return;
        }

        clear_old();

        $session = new Session();
        $session->set("auth", [
            "id" => $user->getId(),
            "name" => $user->getName(),
            "email" => $user->getEmail(),
            "role" => $user->getRole(),
        ]);

        SessionTimeoutMiddleware::start(rememberMe: false);
        $session->regenerate();

        AuditLog::record(LogEvent::LOGIN_SUCCESS, $user->getId());

        redirect("/dashboard");
    }

    #[NoReturn]
    public function logout(?array $data): void
    {
        $this->validateCsrfToken($data ?? [], "/dashboard");

        $user = Auth::user();

        if ($user) {
            AuditLog::record(LogEvent::LOGOUT, $user->id ?? null);
        }

        Auth::logout();

        Message::success("Sessão encerrada com sucesso.");
        redirect("/entrar");
    }
}
