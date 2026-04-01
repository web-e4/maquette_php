<?php
// src/Controllers/PilotController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Permission;
use Equipe4\Gigastage\Core\Role;
use Equipe4\Gigastage\Models\UserModel;

class PilotController extends AbstractDashboardController
{
    protected function getDashboardBase(): string
    {
        return '/pilot';
    }

    protected function getPageTitle(): string
    {
        return 'Espace Pilote';
    }

    protected function getDashboardPermission(): string
    {
        return Permission::STUDENT_VIEW; // Admin + Pilote
    }

    protected function loadPilots(): bool
    {
        return false; // Le pilote n'a pas accès à la gestion des pilotes
    }

    public function dashboard(): void
    {
        // Si un Admin accède à /pilot, le rediriger vers /admin
        if ($this->isLoggedIn() && $this->getUserRole() === Role::ADMIN) {
            $this->redirect('/admin');
        }
        parent::dashboard();
    }

    // POST /admin/pilot/create - SFx13
    public function create(): void
    {
        $this->requirePermission(Permission::PILOT_CREATE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('pilots');
        }

        $email     = trim($_POST['email']     ?? '');
        $password  = trim($_POST['password']  ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $surname   = trim($_POST['surname']   ?? '');

        if (empty($email) || empty($password) || empty($firstName) || empty($surname)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tous les champs sont obligatoires.'];
            $this->redirectToDashboard('pilots');
        }

        $this->userModel->create([
            'email'     => $email,
            'password'  => $password,
            'firstName' => $firstName,
            'surname'   => $surname,
            'role'      => Role::PILOT,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Compte pilote créé avec succès.'];
        $this->redirectToDashboard('pilots');
    }

    // POST /admin/pilot/update - SFx14
    public function update(int $id): void
    {
        $this->requirePermission(Permission::PILOT_EDIT);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('pilots');
        }

        $email     = trim($_POST['email']     ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $surname   = trim($_POST['surname']   ?? '');

        if (empty($email) || empty($firstName) || empty($surname)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tous les champs sont obligatoires.'];
            $this->redirectToDashboard('pilots');
        }

        $statusUser = isset($_POST['statusUser']) ? (int) $_POST['statusUser'] : 1;

        $this->userModel->update($id, [
            'email'      => $email,
            'firstName'  => $firstName,
            'surname'    => $surname,
            'statusUser' => $statusUser,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Compte pilote mis à jour.'];
        $this->redirectToDashboard('pilots');
    }

    // POST /admin/pilot/delete - SFx15
    public function delete(int $id): void
    {
        $this->requirePermission(Permission::PILOT_DELETE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('pilots');
        }

        $this->userModel->delete($id);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Compte pilote désactivé.'];
        $this->redirectToDashboard('pilots');
    }
}
