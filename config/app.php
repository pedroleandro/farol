<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

define("APP_URL", $_ENV['APP_URL'] ?? "http://localhost:8010");
define("APP_NAME", $_ENV['APP_NAME'] ?? "FAROL - Sistema Web de Gestão de Logística e Fretes");

define("APP_TIMEZONE", $_ENV['APP_TIMEZONE'] ?? "America/Sao_Paulo");
date_default_timezone_set(APP_TIMEZONE);

define("DB_CONNECTION", $_ENV['DB_CONNECTION'] ?? "mysql");
define("DB_HOST", $_ENV['DB_HOST'] ?? "localhost");
define("DB_PORT", $_ENV['DB_PORT'] ?? "3306");
define("DB_DATABASE", $_ENV['DB_DATABASE'] ?? "db");
define("DB_USERNAME", $_ENV['DB_USERNAME'] ?? "user");
define("DB_PASSWORD", $_ENV['DB_PASSWORD'] ?? "password");
define("DB_CHARSET", $_ENV['DB_CHARSET'] ?? "utf8mb4");

define("APP_ENV", $_ENV['APP_ENV'] ?? "production");

define("APP_DEVELOPER", $_ENV['APP_DEVELOPER'] ?? "Pedro Leandro");
const UPLOAD_PATH = __DIR__ . "/../storage/uploads";
