<?php

namespace App\Core;

use League\Plates\Engine;

class Controller
{
    protected ?Engine $view = null;

    public function __construct(string $pathView = "Web")
    {
        $this->view = new Engine(__DIR__ . "/../Views/" . $pathView, "php");
    }

    protected function validateCsrfToken(array $data, string $route): void
    {
        if (!$data || !csrf_verify($data['_csrf'] ?? null)) {
            Message::error("Token de segurança inválido");
            redirect($route);
            return;
        }
    }

    /**
     * Renderiza uma view já injetando automaticamente os dados do
     * usuário autenticado (id, nome, papel). Toda tela que exige
     * login deve usar $this->render() em vez de $this->view->render()
     * diretamente, para nunca esquecer de repassar o papel do usuário
     * — o menu lateral depende dele para decidir o que exibir.
     */
    protected function render(string $template, array $data = []): string
    {
        $user = Auth::user();

        $data = array_merge([
            "userId" => $user->id ?? null,
            "userName" => $user->name ?? "",
            "userRole" => $user->role ?? "",
        ], $data);

        return $this->view->render($template, $data);
    }

    /**
     * Exige login e restringe o acesso a um conjunto de papéis.
     * Uso típico no construtor do controller:
     *   $this->requireRole(User::ROLES_CAN_MANAGE_ORDERS);
     */
    protected function requireRole(array $allowedRoles): void
    {
        Auth::requireLogin();

        $user = Auth::user();

        if (!in_array($user->role ?? null, $allowedRoles, true)) {
            Message::warning("Você não tem permissão para acessar esta página.");
            redirect("/dashboard");
        }
    }
}
