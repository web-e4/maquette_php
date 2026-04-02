<?php
// src/Controllers/AbstractController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\AccessControl;
use Equipe4\Gigastage\Core\RedirectException;
use Equipe4\Gigastage\Core\Role;

abstract class AbstractController
{
    private $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    protected function render(string $template, array $data = []): void
    {
        // injecte l'utilisateur connecté dans tous les templates
        $data['app_user'] = $_SESSION['user'] ?? null;
        $data['current_url'] = $_SERVER['REQUEST_URI'] ?? '/';
        $data['csrf_token'] = $this->getCsrfToken();
        echo $this->twig->render($template, $data);
    }

    protected function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function validateCsrfToken(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo '403 - Token CSRF invalide.';
            exit;
        }
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        throw new RedirectException($url);
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    protected function getUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    /**
     * Retourne le rôle de l'utilisateur connecté, ou Anonyme si non connecté.
     */
    protected function getUserRole(): string
    {
        return $_SESSION['user']['role'] ?? Role::ANONYMOUS;
    }

    /**
     * Vérifie si l'utilisateur courant a une permission donnée.
     */
    protected function userCan(string $permission): bool
    {
        return AccessControl::can($this->getUserRole(), $permission);
    }

    /**
     * Redirige vers le tableau de bord correspondant au rôle (/admin ou /pilot).
     * Accepte un nom d'onglet optionnel : redirectToDashboard('companies') → /admin?tab=companies
     */
    protected function redirectToDashboard(string $tab = ''): void
    {
        $role = $_SESSION['user']['role'] ?? '';
        $base = $role === Role::ADMIN ? '/admin' : '/pilot';
        $this->redirect($tab ? $base . '?tab=' . $tab : $base);
    }

    /**
     * Bloque l'accès si l'utilisateur n'a pas la permission.
     * Redirige vers /login si non connecté, renvoie 403 sinon.
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }

        if (!$this->userCan($permission)) {
            http_response_code(403);
            echo '403 - Accès refusé';
            exit;
        }
    }
}