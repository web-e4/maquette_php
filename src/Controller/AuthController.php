<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\UserModel;
use App\Core\Database;

class AuthController{

    private UserModel $userModel;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $pdo             = Database::getInstance($config['db']);
        $this->userModel = new UserModel($pdo);
    }

    public function showRegister(): void
    {
        echo $this->twig->render('inscription.html.twig');
    }

    public function register(): void
    {
        $email    = filter_input(INPUT_POST, 'email',    FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
        $name     = filter_input(INPUT_POST, 'name',     FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$email || !$password || !$name) {
            $this->flash('error', 'Données invalides.');
            $this->redirect('/inscription');
        }

        if (strlen($password) < 8) {
            $this->flash('error', 'Mot de passe trop court (8 caractères minimum).');
            $this->redirect('/inscription');
        }

        if ($this->userModel->findByEmail($email)) {
            $this->flash('error', 'Cet email est déjà utilisé.');
            $this->redirect('/inscription');
        }

        $this->userModel->register($name, $email, $password);

        $this->flash('success', 'Compte créé. Vous pouvez vous connecter.');
        $this->redirect('/connexion');
    }

    public function showLogin(): void
    {
        $this->render('connexion.html.twig');
    }

    public function login(): void
    {
        $email    = filter_input(INPUT_POST, 'email',    FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);

        if (!$email || !$password) {
            $this->flash('error', 'Email ou mot de passe invalide.');
            $this->redirect('/connexion');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->flash('error', 'Identifiants incorrects.');
            $this->redirect('/connexion');
        }

        // Ne jamais stocker le hash en session
        unset($user['password']);
        \App\Core\Session::set('user', $user);

        $this->redirect('/');
    }

    public function logout(): void
    {
        \App\Core\Session::destroy();
        $this->redirect('/connexion');
    }
}
