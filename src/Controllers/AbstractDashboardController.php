<?php
// src/Controllers/AbstractDashboardController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Role;
use Equipe4\Gigastage\Models\UserModel;
use Equipe4\Gigastage\Models\CompanyModel;
use Equipe4\Gigastage\Models\OfferModel;
use Equipe4\Gigastage\Models\ApplicationModel;

/**
 * Contrôleur abstrait partagé entre AdminController (/admin) et PilotController (/pilot).
 * Chaque sous-classe définit sa route de base, son titre et sa permission d'accès.
 */
abstract class AbstractDashboardController extends AbstractController
{
    protected UserModel $userModel;
    protected CompanyModel $companyModel;
    protected OfferModel $offerModel;
    protected ApplicationModel $applicationModel;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->userModel = new UserModel();
        $this->companyModel = new CompanyModel();
        $this->offerModel = new OfferModel();
        $this->applicationModel = new ApplicationModel();
    }

    /** URL de base du tableau de bord : '/admin' ou '/pilot' */
    abstract protected function getDashboardBase(): string;

    /** Titre affiché dans le header de la page */
    abstract protected function getPageTitle(): string;

    /** Permission requise pour accéder à ce tableau de bord */
    abstract protected function getDashboardPermission(): string;

    /** Indique si ce tableau de bord doit charger la liste des pilotes */
    protected function loadPilots(): bool
    {
        return false;
    }

    public function dashboard(): void
    {
        $this->requirePermission($this->getDashboardPermission());

        $userRole = $this->getUserRole();
        $idUser = $_SESSION['user']['id'];

        // recherche dans les onglets
        $qStudent = trim($_GET['qStudent'] ?? '');
        $qPilot   = trim($_GET['qPilot'] ?? '');

        // données communes
        $companies = $this->companyModel->findAllForAdmin();
        $offers    = $this->offerModel->findAll();

        // Pour un pilote : seulement ses propres étudiants ; pour un admin : tous les étudiants
        if ($userRole === Role::ADMIN) {
            $students = $qStudent !== ''
                ? $this->userModel->search(Role::STUDENT, $qStudent)
                : $this->userModel->findByRoleWithPilot(Role::STUDENT);
        } else {
            $allStudents = $this->userModel->findStudentsByPilot($idUser);
            if ($qStudent !== '') {
                $qLower = mb_strtolower($qStudent);
                $students = array_filter($allStudents, function ($s) use ($qLower) {
                    return str_contains(mb_strtolower($s['firstName'] ?? ''), $qLower)
                        || str_contains(mb_strtolower($s['surname'] ?? ''), $qLower)
                        || str_contains(mb_strtolower($s['email'] ?? ''), $qLower);
                });
                $students = array_values($students);
            } else {
                $students = $allStudents;
            }
        }

        // pilotes : uniquement si le dashboard Admin l'active
        $pilots = [];
        if ($this->loadPilots()) {
            $pilots = $qPilot !== ''
                ? $this->userModel->search(Role::PILOT, $qPilot)
                : $this->userModel->findByRole(Role::PILOT);
        }

        // candidatures : toutes pour Admin, groupe pour Pilote
        $applications = ($userRole === Role::ADMIN)
            ? $this->applicationModel->findAllWithDetails()
            : $this->applicationModel->findByPilot($idUser);

        // onglet actif
        $activeTab = $_GET['tab'] ?? 'companies';

        // formulaire d'édition inline via ?editType=X&edit=Y
        $editType = $_GET['editType'] ?? null;
        $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
        $editData = null;

        if ($editType && $editId) {
            switch ($editType) {
                case 'company':
                    $editData = $this->companyModel->findById($editId);
                    $activeTab = 'companies';
                    break;
                case 'offer':
                    $editData = $this->offerModel->findByIdAdmin($editId);
                    $activeTab = 'offers';
                    break;
                case 'student':
                    $editData = $this->userModel->findById($editId);
                    $activeTab = 'students';
                    break;
                case 'pilot':
                    $editData = $this->userModel->findById($editId);
                    $activeTab = 'pilots';
                    break;
            }
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('admin/dashboard.html.twig', [
            'companies'     => $companies,
            'offers'        => $offers,
            'students'      => $students,
            'pilots'        => $pilots,
            'applications'  => $applications,
            'activeTab'     => $activeTab,
            'editType'      => $editType,
            'editData'      => $editData,
            'editId'        => $editId,
            'flash'         => $flash,
            'pageTitle'     => $this->getPageTitle(),
            'dashboardBase' => $this->getDashboardBase(),
            'qStudent'      => $qStudent,
            'qPilot'        => $qPilot,
        ]);
    }
}
