<?php
// src/Controllers/StudentController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Permission;
use Equipe4\Gigastage\Core\Role;
use Equipe4\Gigastage\Models\UserModel;

class StudentController extends AbstractController
{
    private UserModel $userModel;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->userModel = new UserModel();
    }

    // POST /admin/student/create - SFx17
    public function create(): void
    {
        $this->requirePermission(Permission::STUDENT_CREATE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('students');
        }

        $this->validateCsrfToken();

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $surname = trim($_POST['surname'] ?? '');

        if (empty($email) || empty($password) || empty($firstName) || empty($surname)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tous les champs sont obligatoires.'];
            $this->redirectToDashboard('students');
        }

        $this->userModel->create([
            'email' => $email,
            'password' => $password,
            'firstName' => $firstName,
            'surname' => $surname,
            'role' => Role::STUDENT,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Compte étudiant créé avec succès.'];
        $this->redirectToDashboard('students');
    }

    // POST /admin/student/update - SFx18
    public function update(int $id): void
    {
        $this->requirePermission(Permission::STUDENT_EDIT);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('students');
        }

        $this->validateCsrfToken();

        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $surname = trim($_POST['surname'] ?? '');

        if (empty($email) || empty($firstName) || empty($surname)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tous les champs sont obligatoires.'];
            $this->redirectToDashboard('students');
        }

        $statusUser = isset($_POST['statusUser']) ? (int) $_POST['statusUser'] : 1;

        $this->userModel->update($id, [
            'email' => $email,
            'firstName' => $firstName,
            'surname' => $surname,
            'statusUser' => $statusUser,
        ]);

        // assigne un pilote si le champ a été soumis
        if (array_key_exists('idPilot', $_POST)) {
            $idPilot = $_POST['idPilot'] !== '' ? (int) $_POST['idPilot'] : null;
            $this->userModel->assignPilot($id, $idPilot);
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Compte étudiant mis à jour.'];
        $this->redirectToDashboard('students');
    }

    // POST /admin/student/assign-pilot
    public function assignPilot(int $id): void
    {
        $this->requirePermission(Permission::STUDENT_EDIT);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('students');
        }

        $this->validateCsrfToken();

        $idPilot = isset($_POST['idPilot']) && $_POST['idPilot'] !== ''
            ? (int) $_POST['idPilot']
            : null;

        $this->userModel->assignPilot($id, $idPilot);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pilote assigné avec succès.'];
        $this->redirectToDashboard('students');
    }

    // POST /admin/student/delete - SFx19
    public function delete(int $id): void
    {
        $this->requirePermission(Permission::STUDENT_DELETE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('students');
        }

        $this->validateCsrfToken();

        $this->userModel->delete($id);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Compte étudiant désactivé.'];
        $this->redirectToDashboard('students');
    }
}
