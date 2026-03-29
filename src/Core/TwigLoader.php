<?php
declare(strict_types=1);

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;

class TwigLoader
{
    private static ?Environment $instance = null;

    public static function getInstance(array $config): Environment
    {
        if (self::$instance === null) {
            $loader = new FilesystemLoader($config['templates_path']);

            self::$instance = new Environment($loader, [
                'cache' => $config['debug'] ? false : $config['cache_path'],
                'debug' => $config['debug'],
            ]);

            if ($config['debug']) {
                self::$instance->addExtension(new DebugExtension());
            }

            // Globale Twig : flash messages accessibles dans tous les templates
            self::$instance->addGlobal('flash', [
                'success' => Session::getFlash('success'),
                'error'   => Session::getFlash('error'),
            ]);

            // Utilisateur courant accessible dans tous les templates
            self::$instance->addGlobal('user', Session::get('user'));
        }
        return self::$instance;
    }
}
