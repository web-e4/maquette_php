<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Session;

Session::start();

$config = require dirname(__DIR__) . '/config/config.php';

$router = new Router($config);

$router->get('/inscription',   [\App\Controller\AuthController::class, 'showRegister']);
$router->post('/inscription',  [\App\Controller\AuthController::class, 'register']);
$router->get('/connexion',     [\App\Controller\AuthController::class, 'showLogin']);
$router->post('/connexion',    [\App\Controller\AuthController::class, 'login']);
$router->get('/deconnexion',   [\App\Controller\AuthController::class, 'logout']);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);
