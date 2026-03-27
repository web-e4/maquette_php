<?php
// src\Core\Router.php

namespace Equipe4\Gigastage\Core;

use Equipe4\Gigastage\Controllers\OffreController;
use Equipe4\Gigastage\Controllers\PageController;
use Equipe4\Gigastage\Controllers\AuthController;

class Router {


    private $twig;

    private array $routes = [
        '/'                => [OffreController::class, 'home'],
        '/offres'          => [OffreController::class, 'index'],
        '/offre'           => [OffreController::class, 'detail'],
        '/publier-offre'   => [OffreController::class, 'create'],
        '/postuler'        => [OffreController::class, 'apply'],
        '/connexion'       => [AuthController::class,  'loginPage'],
        '/login'           => [AuthController::class,  'login'],
        '/inscription'     => [AuthController::class,  'registerPage'],
        '/register'        => [AuthController::class,  'register'],
        '/profil'          => [AuthController::class,  'profil'],
        '/contact'         => [PageController::class,  'contact'],
        '/cgu'             => [PageController::class,  'cgu'],
        '/mentions-legales'=> [PageController::class,  'mentionsLegales'],
    ];
    

    public function __construct($twig) {
        $this->twig = $twig;
    }

    public function dispatch(string $uri) {
        if (array_key_exists($uri, $this->routes)) {
            [$controllerClass, $method] = $this->routes[$uri];
            $controller = new $controllerClass($this->twig);

          
            $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

            if ($id !== null) {
                $controller->$method($id);
            } else {
                $controller->$method();
            }

        } else {
            echo "404 - Page non trouvée";
        }
    }


}
 
?>