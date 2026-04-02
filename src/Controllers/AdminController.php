<?php
// src/Controllers/AdminController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Permission;
use Equipe4\Gigastage\Core\Role;

class AdminController extends AbstractDashboardController
{   //defini l'adresse du dashboard admin pour que la fonction redirection sache ou renvoyer l'user
    protected function getDashboardBase(): string
    {
        return '/admin';
    }
    //defini le titre de la page pour l'afficher dans le header
    protected function getPageTitle(): string
    {
        return 'Espace Admin';
    }
    //defini la permission requise pour acceder au dashboard admin, grace a la classe Permission qui gère les permissions de l'application
    protected function getDashboardPermission(): string
    {
        return Permission::PILOT_VIEW; // Admin uniquement
    }
    //on surchage la fonction loadPilots pour retourner true et que les admins puissent avoir la listes des pilotes
    protected function loadPilots(): bool
    {
        return true; // L'admin a besoin de la liste des pilotes
    }

    public function dashboard(): void
    {
        // Si un Pilote accède à /admin, le rediriger vers /pilot
        if ($this->isLoggedIn() && $this->getUserRole() === Role::PILOT) {
            $this->redirect('/pilot');
        }
        parent::dashboard();
    }
}
