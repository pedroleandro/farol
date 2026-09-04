<?php

$router->get("/pedidos", "OrderController@index");
$router->get("/pedidos/cadastrar", "OrderController@create");
$router->post("/pedidos/cadastrar", "OrderController@store");
$router->get("/pedidos/editar/{id}", "OrderController@edit");
$router->put("/pedidos/editar/{id}", "OrderController@update");
$router->post("/pedidos/{id}/avancar-status", "OrderController@advanceStatus");
$router->post("/pedidos/{id}/marcar-pendente", "OrderController@markAsPending");
$router->post("/pedidos/{id}/resolver-pendencia", "OrderController@resolvePending");
$router->delete("/pedidos/excluir/{id}", "OrderController@destroy");
