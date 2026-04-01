    <?php
    // public/index.php

<<<<<<< HEAD
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_start();
=======
    session_start();
>>>>>>> cd9c0b4 (test uni com et control)

    require '../vendor/autoload.php';

<<<<<<< HEAD
use Equipe4\Gigastage\Core\RedirectException;
use Equipe4\Gigastage\Core\Router;
=======
    use Equipe4\Gigastage\Core\Router;
>>>>>>> cd9c0b4 (test uni com et control)

    // Chargement des variables d'environnement
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    // en fonction de l'environnement (dev ou prod), on affiche ou non les erreurs PHP dans le navigateur
    if ($_ENV['APP_ENV'] === 'dev') {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', 0);
        error_reporting(0);
    }

    // Initialisation de Twig
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../src/Views');
    // en mode dev, on désactive le cache de Twig pour faciliter le développement, sinon on l'active pour améliorer les performances en production
    $twig   = new \Twig\Environment($loader, [
        'cache' => $_ENV['APP_ENV'] === 'dev' ? false : __DIR__ . '/../storage/twig-cache',
        'debug' => $_ENV['APP_ENV'] === 'dev',
    ]);

<<<<<<< HEAD
// Injecte l'URL du serveur de contenu statique dans tous les templates
$twig->addGlobal('static_url', rtrim($_ENV['STATIC_URL'] ?? '', '/'));

// Routeur
$router = new Router($twig);

try {
    $router->dispatch($_GET['uri'] ?? '/');
} catch (RedirectException) {
    // La redirection a déjà été envoyée via header() dans AbstractController::redirect()
    exit;
}
=======
    // Initialisation du routeur et dispatch (analyse l'URL et redirige vers le bon contrôleur) de la requête
    $router = new Router($twig);
    $router->dispatch($_GET['uri'] ?? '/');
>>>>>>> cd9c0b4 (test uni com et control)
