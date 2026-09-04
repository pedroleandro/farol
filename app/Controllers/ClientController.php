<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Message;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use JetBrains\PhpStorm\NoReturn;

class ClientController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        $this->requireRole(User::ROLES_CAN_MANAGE_ORDERS);
    }

    #[NoReturn]
    public function index(): void
    {
        $clients = (new Client())->orderBy("name")->get();

        $clientIds = array_map(fn(Client $c) => $c->getId(), $clients);
        $clientsWithOrders = Order::clientIdsWithOrders($clientIds);

        echo $this->render("clients/index", [
            "title" => "Clientes | " . APP_NAME,
            "clients" => $clients,
            "clientsWithOrders" => $clientsWithOrders,
        ]);

        clear_old();
    }

    #[NoReturn]
    public function create(): void
    {
        echo $this->render("clients/create", [
            "title" => "Novo Cliente | " . APP_NAME,
        ]);

        clear_old();
    }

    #[NoReturn]
    public function store(?array $data): void
    {
        $this->validateCsrfToken($data ?? [], "/clientes/cadastrar");

        $client = new Client();

        try {
            $client->fill([
                "name" => $data["name"] ?? null,
                "city" => !empty($data["city"]) ? $data["city"] : null,
                "state" => !empty($data["state"]) ? strtoupper($data["state"]) : null,
            ]);

            $errors = $client->validate($data);

            if ($errors) {
                flash_old($data);
                foreach ($errors as $error) {
                    Message::warning($error);
                }
                redirect("/clientes/cadastrar");
                return;
            }

            $client->save();
        } catch (\InvalidArgumentException $e) {
            Message::error($e->getMessage());
            redirect("/clientes/cadastrar");
            return;
        }

        Message::success("Cliente cadastrado com sucesso.");
        redirect("/clientes");
    }

    #[NoReturn]
    public function edit(?array $data): void
    {
        $client = Client::find((int)($data["id"] ?? 0));

        if (!$client) {
            Message::warning("Cliente não encontrado ou não existe.");
            redirect("/clientes");
            return;
        }

        echo $this->render("clients/edit", [
            "title" => "Editar Cliente | " . APP_NAME,
            "client" => $client,
        ]);

        clear_old();
    }

    #[NoReturn]
    public function update(?array $data): void
    {
        $this->validateCsrfToken($data ?? [], "/clientes/editar/" . ($data["id"] ?? ''));

        $client = Client::find((int)($data["id"] ?? 0));

        if (!$client) {
            Message::warning("Cliente não encontrado ou não existe.");
            redirect("/clientes");
            return;
        }

        try {
            $client->fill([
                "name" => $data["name"] ?? null,
                "city" => !empty($data["city"]) ? $data["city"] : null,
                "state" => !empty($data["state"]) ? strtoupper($data["state"]) : null,
            ]);

            $errors = $client->validate($data);

            if ($errors) {
                flash_old($data);
                foreach ($errors as $error) {
                    Message::warning($error);
                }
                redirect("/clientes/editar/" . $client->getId());
                return;
            }

            $client->save();
        } catch (\InvalidArgumentException $e) {
            Message::error($e->getMessage());
            redirect("/clientes/editar/" . $client->getId());
            return;
        }

        Message::success("Cliente atualizado com sucesso.");
        redirect("/clientes/editar/" . $client->getId());
    }

    #[NoReturn]
    public function destroy(?array $data): void
    {
        $this->validateCsrfToken($data ?? [], "/clientes");

        $client = Client::find((int)($data["id"] ?? 0));

        if (!$client) {
            Message::error("Cliente não encontrado ou não existe.");
            redirect("/clientes");
            return;
        }

        if (Order::existsForClient($client->getId())) {
            Message::warning(
                "Este cliente possui pedidos vinculados e não pode ser excluído. " .
                "Exclua ou reatribua os pedidos antes de remover o cliente."
            );
            redirect("/clientes");
            return;
        }

        try {
            $client->delete();
        } catch (\InvalidArgumentException $e) {
            Message::error($e->getMessage());
            redirect("/clientes");
            return;
        }

        Message::success("Cliente excluído com sucesso.");
        redirect("/clientes");
    }
}