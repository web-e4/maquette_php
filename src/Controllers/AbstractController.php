<?php
// src/Controllers/AbstractController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\AccessControl;
use Equipe4\Gigastage\Core\RedirectException;
use Equipe4\Gigastage\Core\Role;
// permet de simplifier le code des autres controleurs en centralisant les fonctions communes
abstract class AbstractController
{
    private $twig;
    // le constructeur reçoit twig et le stocke pour que les autres fonctions puissent l'utiliser (this->twig)
    public function __construct($twig)
    {
        $this->twig = $twig;
    }
    // affiche une page via un template twig
    protected function render(string $template, array $data = []): void
    {
        // permet de gerer l'affichage dynamique pour afficher les infos de l'utilisateur connecté (ex: son nom dans le header)
        $data['app_user']    = $_SESSION['user'] ?? null;
        // + mets en surbrillance le lien URL utilisé pour le confort utilisateur (ex: si on est sur /admin, le lien "Admin" dans le menu sera surligné)
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

    //permet de rediriger vers une URL donnée (ex: après une connexion réussie)
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        throw new RedirectException($url);
    }
    // permet de verif si l'utilisateur est connecté (isset renvoie un booléen)
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }
    // return les données de l'utilisateur si elles sont présentes dans la session, sinon null
    protected function getUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
    // vérifie si l'utilisateur est connecté, sinon redirige vers la page de connexion
    protected function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    // renvoie le role de l'utilisateur
    protected function getUserRole(): string
    {
        return $_SESSION['user']['role'] ?? Role::ANONYMOUS;
    }

    
    //Vérifie si l'utilisateur courant a une permission donnée. grace a la classe AccessControl 
    //qui gère les permissions et la fonction getuserrole qui verifie le role de l'user
    protected function userCan(string $permission): bool
    {
        return AccessControl::can($this->getUserRole(), $permission);
    }

    // on créé l'url en fonction du role et tab redirige vers le bon dashboard (page qui permet de naviguer) en fonction du role 
    protected function redirectToDashboard(string $tab = ''): void
    {
        $role = $_SESSION['user']['role'] ?? '';
        $base = $role === Role::ADMIN ? '/admin' : '/pilot';
        $this->redirect($tab ? $base . '?tab=' . $tab : $base);
    }

    // verifie si l'user est connecté et s'il a les perm via les fonctions
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