<?php
// src/Controllers/AbstractDashboardController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Role;
use Equipe4\Gigastage\Models\UserModel;
use Equipe4\Gigastage\Models\CompanyModel;
use Equipe4\Gigastage\Models\OfferModel;
use Equipe4\Gigastage\Models\ApplicationModel;

//on instancie des objets pour communiquer avec la BDD et on créé une classe abstraite pour les tableaux de bord Admin et Pilote qui partagent beaucoup de fonctionnalités communes (ex: afficher les entreprises, offres, étudiants, etc) et qui héritent de AbstractController pour bénéficier des fonctions d'affichage, redirection, auth, etc
abstract class AbstractDashboardController extends AbstractController
{
    protected UserModel $userModel;
    protected CompanyModel $companyModel;
    protected OfferModel $offerModel;
    protected ApplicationModel $applicationModel;
    //on attribue une valeur a ces objets instanciés plus haut pour eviter la repetition de code 
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

    /** Indique si ce tableau de bord doit charger la liste des pilotes Admincontroller aura besoin de la surchargé*/
    protected function loadPilots(): bool
    {
        return false;
    }

    public function dashboard(): void
    {
        // verifie les droits 
        $this->requirePermission($this->getDashboardPermission());

        $userRole = $this->getUserRole();
        $idUser = $_SESSION['user']['id'];

        // recherche dans les onglets
        $qStudent = trim($_GET['qStudent'] ?? '');
        $qPilot   = trim($_GET['qPilot'] ?? '');

        // données communes
        $companies = $this->companyModel->findAllForAdmin();
        // chargement des données depuis la BDD, equivalent à faire des requetes SQL pour récupérer les entreprises, offres, étudiants, etc
        $companies = $this->companyModel->findAll();
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
            ? $this->applicationModel->findAllWithDetails() // admin voit tout
            : $this->applicationModel->findByPilot($idUser); // pilote voit que les candidatures de son groupe

        // onglet actif
        $activeTab = $_GET['tab'] ?? 'companies';

        // Recupere le get via l'url si l'user veut modifier une données de la bdd
        $editType = $_GET['editType'] ?? null; // recupere le type de donnée a modifier
        $editId   = isset($_GET['edit']) ? (int) $_GET['edit'] : null; // converti en int pour eviter les injections SQL
        $editData = null;

        if ($editType && $editId) { // on entre seulement si le type et l'id sont présent pour trouver les données dans la bdd
            // en fonction du type de donnée a modifier, on recupere les données correspondantes dans la bdd pour les afficher dans le formulaire de modification (ex: si on veut modifier une entreprise, on recupere les données de cette entreprise pour les afficher dans le formulaire)
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
                    break;//break pour éviter d'executer le code des autres cases
                case 'pilot':
                    $editData = $this->userModel->findById($editId);
                    $activeTab = 'pilots';
                    break;
            }
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);//message flash : message temporaire qui s'affiche une fois après une action (ex: "Entreprise modifiée avec succès") et qui est stocké dans la session pour être affiché après une redirection, puis supprimé pour ne pas réapparaître
        //renvoie les données modifier
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
