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
        $q = $_GET['q'] ?? '';
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 12;

        $result = $this->companyModel->findPaginated($page, $perPage, $q);

        $this->render('company/index.html.twig', [
            'companies' => $result['companies'],
            'q' => $q,
            'pagination' => [
                'totalPages'  => $result['totalPages'],
                'currentPage' => $result['currentPage'],
                'total'       => $result['total'],
            ],
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

        $offers      = $this->companyModel->getOffers($id);
        $ratings     = $this->companyModel->getRatings($id);
        $avgRating   = $this->companyModel->getAverageRating($id);
        $applicants  = $this->companyModel->countApplicants($id);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('company/show.html.twig', [
            'company'    => $company,
            'offers'     => $offers,
            'ratings'    => $ratings,
            'avgRating'  => $avgRating,
            'applicants' => $applicants,
            'flash'      => $flash,
        ]);
    }

    // POST /admin/company/create - SFx3
    public function create(): void
    {
        $this->requirePermission(Permission::COMPANY_CREATE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDashboard('companies');
        }

        $this->validateCsrfToken();

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $website     = trim($_POST['website'] ?? '');

        if (empty($name) || empty($email)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Le nom et l\'email sont obligatoires.'];
            $this->redirectToDashboard('companies');
        }

        $this->companyModel->create([
            'name'        => $name,
            'description' => $description,
            'email'       => $email,
            'phone'       => $phone,
            'website'     => $website,
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

        $this->validateCsrfToken();

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $website     = trim($_POST['website'] ?? '');

        if (empty($name) || empty($email)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Le nom et l\'email sont obligatoires.'];
            $this->redirectToDashboard('companies');
        }

        $statusCompany = isset($_POST['statusCompany']) ? (int) $_POST['statusCompany'] : 1;

        $this->companyModel->update($id, [
            'name'          => $name,
            'description'   => $description,
            'email'         => $email,
            'phone'         => $phone,
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

        $this->validateCsrfToken();

        $this->companyModel->delete($id);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Entreprise désactivée.'];
        $this->redirectToDashboard('companies');
    }

    // POST /admin/company/evaluate - SFx5
    public function evaluate(int $id): void
    {
        $this->requirePermission(Permission::COMPANY_EVALUATE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/company/' . $id);
        }

        $this->validateCsrfToken();

        $rate    = (int) ($_POST['rate'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($rate < 1 || $rate > 5) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'La note doit être entre 1 et 5.'];
            $this->redirect('/company/' . $id);
        }

        $idUser = $_SESSION['user']['id'];
        $this->companyModel->addRating($idUser, $id, $rate, $comment);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Évaluation enregistrée.'];
        $this->redirect('/company/' . $id);
    }
}
