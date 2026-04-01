<?php
// src/Controllers/AuthController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Models\ProfileModel;
use Equipe4\Gigastage\Models\ApplicationModel;
use Equipe4\Gigastage\Models\WishlistModel;

class AuthController extends AbstractController
{
    private ProfileModel $profileModel;
    private ApplicationModel $applicationModel;
    private WishlistModel $wishlistModel;

    public function __construct($twig, ?ProfileModel $profileModel = null, ?ApplicationModel $applicationModel = null, ?WishlistModel $wishlistModel = null)
    {
        parent::__construct($twig);
        $this->profileModel     = $profileModel     ?? new ProfileModel();
        $this->applicationModel = $applicationModel ?? new ApplicationModel();
        $this->wishlistModel    = $wishlistModel    ?? new WishlistModel();
    }

    // GET /login
    public function index(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/profile');
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('pages/login.html.twig', [
            'flash' => $flash,
        ]);
    }

    // POST /login/submit
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }

        $this->validateCsrfToken();

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please fill in all fields.'];
            $this->redirect('/login');
        }

        $user = $this->profileModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid email or password.'];
            $this->redirect('/login');
        }

        $_SESSION['user'] = [
            'id'        => $user['idUser'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'firstName' => $user['firstName'] ?? '',
            'surname'   => $user['surname']   ?? '',
        ];

        $this->redirect('/profile');
    }

    // GET /register
    public function registerIndex(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/profile');
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('pages/register.html.twig', [
            'flash' => $flash,
        ]);
    }

    // POST /register/submit
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
        }

        $this->validateCsrfToken();

        $email     = trim($_POST['email']     ?? '');
        $password  = trim($_POST['password']  ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $surname   = trim($_POST['surname']   ?? '');

        if (empty($email) || empty($password) || empty($firstName) || empty($surname)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please fill in all fields.'];
            $this->redirect('/register');
        }

        if ($this->profileModel->findByEmail($email)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'This email address is already in use.'];
            $this->redirect('/register');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->profileModel->createUser([
            'email'     => $email,
            'password'  => $hashedPassword,
            'firstName' => $firstName,
            'surname'   => $surname,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Account created successfully! You can now log in.'];
        $this->redirect('/login');
    }

    // GET /profile
    public function profile(): void
    {
        $this->requireAuth();

        $idUser = $_SESSION['user']['id'];
        $role   = $_SESSION['user']['role'];
        $user   = $this->profileModel->findById($idUser);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if ($role === \Equipe4\Gigastage\Core\Role::STUDENT) {
            $applications = $this->applicationModel->findByUser($idUser);
            $wishlist     = $this->wishlistModel->findByUser($idUser);

            $this->render('pages/profile-student.html.twig', [
                'student'      => $user,
                'applications' => $applications,
                'favorites'    => $wishlist,
                'flash'        => $flash,
            ]);
        } elseif ($role === \Equipe4\Gigastage\Core\Role::PILOT) {
            $this->render('pages/profile-pilot.html.twig', [
                'user'  => $user,
                'flash' => $flash,
            ]);
        } else {
            // Admin (et tout autre rôle non étudiant/pilote)
            $this->render('pages/profile-admin.html.twig', [
                'user'  => $user,
                'flash' => $flash,
            ]);
        }
    }

    // GET /logout
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }
}