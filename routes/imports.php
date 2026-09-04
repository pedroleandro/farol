<?php

$router->get("/importar", "ImportController@index");
$router->post("/importar", "ImportController@store");
