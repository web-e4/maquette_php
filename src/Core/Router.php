<?php
// src/Core/Router.php

namespace Equipe4\Gigastage\Core;

use Equipe4\Gigastage\Controllers\HomeController;
use Equipe4\Gigastage\Controllers\OfferController;
use Equipe4\Gigastage\Controllers\ApplicationController;
use Equipe4\Gigastage\Controllers\AuthController;
use Equipe4\Gigastage\Controllers\PageController;
use Equipe4\Gigastage\Controllers\AdminController;
use Equipe4\Gigastage\Controllers\CompanyController;
use Equipe4\Gigastage\Controllers\PilotController;
use Equipe4\Gigastage\Controllers\StudentController;
use Equipe4\Gigastage\Controllers\WishlistController;

class Router
{
    private $twig;

    private array $routes = [
        '/'                    => [HomeController::class,        'index'],
        '/offers'              => [OfferController::class,       'index'],
        '/offer/create'        => [OfferController::class,       'create'],
        '/offer/create/submit' => [OfferController::class,       'createSubmit'],
        '/offer/edit'          => [OfferController::class,       'edit'],
        '/offer/delete'        => [OfferController::class,       'delete'],
        '/offers/stats'        => [OfferController::class,       'stats'],
        '/offer'               => [OfferController::class,       'show'],
        '/apply/submit'        => [ApplicationController::class, 'submit'],
        '/apply/update'        => [ApplicationController::class, 'update'],
        '/apply'               => [ApplicationController::class, 'show'],
        '/login'               => [AuthController::class,        'index'],
        '/login/submit'        => [AuthController::class,        'login'],
        '/register'            => [AuthController::class,        'registerIndex'],
        '/register/submit'     => [AuthController::class,        'register'],
        '/profile'             => [AuthController::class,        'profile'],
        '/logout'              => [AuthController::class,        'logout'],
        '/contact'             => [PageController::class,        'contact'],
        '/terms'               => [PageController::class,        'terms'],
        '/legal'               => [PageController::class,        'legal'],
        '/admin'                    => [AdminController::class,    'dashboard'],
        '/pilot'                    => [PilotController::class,    'dashboard'],
        '/companies'                => [CompanyController::class,  'index'],
        '/company'                  => [CompanyController::class,  'show'],
        '/admin/company/create'     => [CompanyController::class,  'create'],
        '/admin/company/update'     => [CompanyController::class,  'update'],
        '/admin/company/delete'     => [CompanyController::class,  'delete'],
        '/admin/company/evaluate'   => [CompanyController::class,  'evaluate'],
        '/admin/pilot/create'       => [PilotController::class,    'create'],
        '/admin/pilot/update'       => [PilotController::class,    'update'],
        '/admin/pilot/delete'       => [PilotController::class,    'delete'],
        '/admin/student/create'       => [StudentController::class,  'create'],
        '/admin/student/update'       => [StudentController::class,  'update'],
        '/admin/student/delete'       => [StudentController::class,  'delete'],
        '/admin/student/assign-pilot' => [StudentController::class,  'assignPilot'],
        '/wishlist/add'             => [WishlistController::class, 'add'],
        '/wishlist/remove'          => [WishlistController::class, 'remove'],
    ];

    // Routes avec segment d'ID dans le chemin : /ressource/{id}
    private array $patternRoutes = [
        '#^/offer/(\d+)$#'   => [OfferController::class,       'show'],
        '#^/company/(\d+)$#' => [CompanyController::class,     'show'],
        '#^/apply/(\d+)$#'   => [ApplicationController::class, 'show'],
    ];

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    public function dispatch(string $uri): void
    {
        // Retire la query string du chemin pour le matching des routes
        $path = parse_url($uri, PHP_URL_PATH) ?? $uri;

        // 1. Matching exact (routes statiques)
        if (array_key_exists($path, $this->routes)) {
            [$controllerClass, $method] = $this->routes[$path];
            $controller = new $controllerClass($this->twig);
            // Privilégie GET à POST pour l'id
            $id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : null);

            if ($id !== null) {
                $controller->$method($id);
            } else {
                $controller->$method();
            }
            return;
        }

        // 2. Matching par pattern (routes avec ID dans le chemin)
        foreach ($this->patternRoutes as $pattern => [$controllerClass, $method]) {
            if (preg_match($pattern, $path, $matches)) {
                $id = (int) $matches[1];
                $controller = new $controllerClass($this->twig);
                $controller->$method($id);
                return;
            }
        }

        http_response_code(404);
        echo '404 - Page not found';
    }
}
