<?php
// src/Controllers/CompanyController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Permission;
use Equipe4\Gigastage\Models\CompanyModel;

class CompanyController extends AbstractController
{
    private CompanyModel $companyModel;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->companyModel = new CompanyModel();
    }

    // GET /companies - SFx2
    public function index(): void
    {
        $companies = $this->companyModel->findAll();

        $this->render('company/index.html.twig', [
            'companies' => $companies,
        ]);
    }

    // GET /company?id=x - SFx2
    public function show(int $id): void
    {
        $company = $this->companyModel->findById($id);

        if (!$company) {
            http_response_code(404);
            echo '404 - Entreprise introuvable';
            return;
        }

        $offers    = $this->companyModel->getOffers($id);
        $ratings   = $this->companyModel->getRatings($id);
        $avgRating = $this->companyModel->getAverageRating($id);

        $this->render('company/show.html.twig', [
            'company'   => $company,
            'offers'    => $offers,
            'ratings'   => $ratings,
            'avgRating' => $avgRating,
        ]);
    }

    // POST /admin/company/create - SFx3
    public function create(): void
    {
        $this->requirePermission(Permission::COMPANY_CREATE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('companies');
        }

        $name    = trim($_POST['name']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $website = trim($_POST['website'] ?? '');

        if (empty($name) || empty($email)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Le nom et l\'email sont obligatoires.'];
            $this->redirectToDashboard('companies');
        }

        $this->companyModel->create([
            'name'    => $name,
            'email'   => $email,
            'website' => $website,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Entreprise créée avec succès.'];
        $this->redirectToDashboard('companies');
    }

    // POST /admin/company/update - SFx4
    public function update(int $id): void
    {
        $this->requirePermission(Permission::COMPANY_EDIT);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('companies');
        }

        $name    = trim($_POST['name']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $website = trim($_POST['website'] ?? '');

        if (empty($name) || empty($email)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Le nom et l\'email sont obligatoires.'];
            $this->redirectToDashboard('companies');
        }

        $statusCompany = isset($_POST['statusCompany']) ? (int) $_POST['statusCompany'] : 1;

        $this->companyModel->update($id, [
            'name'          => $name,
            'email'         => $email,
            'website'       => $website,
            'statusCompany' => $statusCompany,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Entreprise mise à jour.'];
        $this->redirectToDashboard('companies');
    }

    // POST /admin/company/delete - SFx6
    public function delete(int $id): void
    {
        $this->requirePermission(Permission::COMPANY_DELETE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('companies');
        }

        $this->companyModel->delete($id);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Entreprise supprimée.'];
        $this->redirectToDashboard('companies');
    }

    // POST /admin/company/evaluate - SFx5
    public function evaluate(int $id): void
    {
        $this->requirePermission(Permission::COMPANY_EVALUATE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('companies');
        }

        $rate    = (int) ($_POST['rate']    ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($rate < 1 || $rate > 5) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'La note doit être entre 1 et 5.'];
            $this->redirect('/company?id=' . $id);
        }

        $idUser = $_SESSION['user']['id'];
        $this->companyModel->addRating($idUser, $id, $rate, $comment);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Évaluation enregistrée.'];
        $this->redirect('/company?id=' . $id);
    }
}
