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
}