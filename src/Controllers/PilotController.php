<?php
// src/Controllers/PilotController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Permission;
use Equipe4\Gigastage\Core\Role;
use Equipe4\Gigastage\Models\UserModel;

class PilotController extends AbstractDashboardController
{
    //defini l'adresse du dashboard pilote pour que la fonction redirection sache ou renvoyer l'user
    protected function getDashboardBase(): string
    {
        return '/pilot';
    }
    //defini le titre de la page pour l'afficher dans le header
    protected function getPageTitle(): string
    {
        return 'Espace Pilote';
    }
    //defini la permission requise pour acceder au dashboard pilote, grace a la classe Permission qui gère les permissions de l'application
    protected function getDashboardPermission(): string
    {
        return Permission::STUDENT_VIEW; // Admin + Pilote
    }
    //on surchage la fonction loadPilots pour retourner false et que les pilotes n'aient pas accès à la gestion des pilotes
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

        // verif que c'est bien POST pour eviter les erreurs si quelqu'un accede a cette URL directement via GET
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('pilots');
        }

        $this->validateCsrfToken();

        // on recup les données que l'user a taper dans le formulaire (trim retire les espaces)
        $email     = trim($_POST['email']     ?? '');
        $password  = trim($_POST['password']  ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $surname   = trim($_POST['surname']   ?? '');
        // verifie aucun champ vide si champ vide message flash puis redirection
        if (empty($email) || empty($password) || empty($firstName) || empty($surname)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tous les champs sont obligatoires.'];
            $this->redirectToDashboard('pilots');
        }
        // crée un compte piloe dans la bdd role et mdp hashé
        $this->userModel->create([
            'email'     => $email,
            'password'  => $password,
            'firstName' => $firstName,
            'surname'   => $surname,
            'role'      => Role::PILOT,
        ]);
        //message flashsuccès puis redirection vers la liste des pilotes
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Compte pilote créé avec succès.'];
        $this->redirectToDashboard('pilots');
    }

    // POST /admin/pilot/update — SFx14
    public function update(int $id): void // id du pilote a modifier qui est recup via l'url
    {   //verif le droit d'edit
        $this->requirePermission(Permission::PILOT_EDIT);
        // toujours que POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('pilots');
        }

        $this->validateCsrfToken();

        // recup les nouvelles données du formulaire (mdp absent pour eviter l'ecrasement du mdp actuel si le champ est laissé vide)
        $email     = trim($_POST['email']     ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $surname   = trim($_POST['surname']   ?? '');
        // si champ vide message flash puis redirection
        if (empty($email) || empty($firstName) || empty($surname)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tous les champs sont obligatoires.'];
            $this->redirectToDashboard('pilots');
        }

        $statusUser = isset($_POST['statusUser']) ? (int) $_POST['statusUser'] : 1;

        // update dans la bdd (equivalent a une requete SQL UPDATE) avec les nouvelles données
        $this->userModel->update($id, [
            'email'      => $email,
            'firstName'  => $firstName,
            'surname'    => $surname,
            'statusUser' => $statusUser,
        ]);
        //signale de reussite puis redirection vers la liste des pilotes
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

        $this->validateCsrfToken();

        $this->userModel->delete($id);
        $this->userModel->delete($id);// envoie de l'odre a la bdd

        //message flash de succès puis redirection vers la liste des pilotes
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Compte pilote désactivé.'];
        $this->redirectToDashboard('pilots');
    }
}
