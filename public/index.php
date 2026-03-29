<?php
// public/index.php

session_start();

require '../vendor/autoload.php';

use Equipe4\Gigastage\Core\Router;

// Chargement des variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Affichage des erreurs en développement uniquement
if ($_ENV['APP_ENV'] === 'dev') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Initialisation de Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../src/Views');
$twig   = new \Twig\Environment($loader, [
    'cache' => $_ENV['APP_ENV'] === 'dev' ? false : __DIR__ . '/../storage/twig-cache',
    'debug' => $_ENV['APP_ENV'] === 'dev',
]);

// Routeur
$router = new Router($twig);
$router->dispatch($_GET['uri'] ?? '/');