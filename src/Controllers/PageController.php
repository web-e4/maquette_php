<?php
// src/Controllers/PageController.php

namespace Equipe4\Gigastage\Controllers;

class PageController extends AbstractController
{
    // GET /contact + POST /contact
    public function contact(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name    = trim($_POST['name']    ?? '');
            $email   = trim($_POST['email']   ?? '');
            $message = trim($_POST['message'] ?? '');

            $errors = [];
            if (empty($name))                      $errors['name']    = 'Le nom est obligatoire.';
            if (empty($email))                     $errors['email']   = 'L\'email est obligatoire.';
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'L\'email est invalide.';
            if (empty($message))                   $errors['message'] = 'Le message est obligatoire.';

            if (!empty($errors)) {
                $this->render('pages/contact.html.twig', [
                    'errors' => $errors,
                    'old'    => $_POST,
                ]);
                return;
            }

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Votre message a bien été envoyé.'];
            $this->redirect('/contact');
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('pages/contact.html.twig', [
            'flash' => $flash,
        ]);
    }

    // GET /terms
    public function terms(): void
    {
        $this->render('pages/terms.html.twig');
    }

    // GET /legal
    public function legal(): void
    {
        $this->render('pages/legal.html.twig');
    }
}
