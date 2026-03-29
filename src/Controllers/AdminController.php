<?php
// src/Controllers/AdminController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Permission;
use Equipe4\Gigastage\Core\Role;

class AdminController extends AbstractDashboardController
{
    protected function getDashboardBase(): string
    {
        return '/admin';
    }

    protected function getPageTitle(): string
    {
        return 'Espace Admin';
    }

    protected function getDashboardPermission(): string
    {
        return Permission::PILOT_VIEW; // Admin uniquement
    }

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
