<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use JetBrains\PhpStorm\NoReturn;

class ErrorController extends Controller
{
    public function __construct()
    {
        parent::__construct("Error");
    }

    #[NoReturn]
    public function index(?array $data): void
    {
        $errorCode = $data['errorCode'];

        echo $this->view->render("error", [
            "title" => ($errorCode ?? 404) . ' - Erro | Técnico - ' . APP_NAME,
            "errorCode" => $errorCode,
        ]);
    }
}