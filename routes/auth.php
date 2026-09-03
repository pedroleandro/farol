<?php

$router->get("/entrar", "AuthController@index");
$router->post("/entrar", "AuthController@login");
$router->post("/sair", "AuthController@logout");

$router->get("/dashboard", "DashboardController@index");
