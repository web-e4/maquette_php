<?php

// permets d'afficher les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);


require '../vendor/autoload.php';

use Equipe4\Gigastage\Core\Router;


$loader = new \Twig\Loader\FilesystemLoader('../src/Views');
$twig = new \Twig\Environment($loader);


$router = new Router($twig);
$router->dispatch($_GET['uri'] ?? '/');

?>