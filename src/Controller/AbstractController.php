<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\TwigLoader;
use App\Core\Session;
use Twig\Environment;

abstract class AbstractController
{
    protected Environment $twig;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->twig   = TwigLoader::getInstance($config['twig']);
    }

    protected function render(string $template, array $data = []): void
    {
        echo $this->twig->render($template, $data);
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    protected function requireAuth(): void
    {
        if (!Session::has('user')) {
            $this->redirect('/connexion');
        }
    }
}
