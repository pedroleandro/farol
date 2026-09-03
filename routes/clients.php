<?php

$router->get("/clientes", "ClientController@index");
$router->get("/clientes/cadastrar", "ClientController@create");
$router->post("/clientes/cadastrar", "ClientController@store");
$router->get("/clientes/editar/{id}", "ClientController@edit");
$router->put("/clientes/editar/{id}", "ClientController@update");
$router->delete("/clientes/excluir/{id}", "ClientController@destroy");
