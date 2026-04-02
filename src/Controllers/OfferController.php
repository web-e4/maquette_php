<?php
// src/Controllers/OfferController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Permission;
use Equipe4\Gigastage\Core\Role;
use Equipe4\Gigastage\Models\OfferModel;
use Equipe4\Gigastage\Models\WishlistModel;
use Equipe4\Gigastage\Models\CompanyModel;

class OfferController extends AbstractController
{
    private OfferModel $offerModel;
    private WishlistModel $wishlistModel;
    private CompanyModel $companyModel;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->offerModel = new OfferModel();
        $this->wishlistModel = new WishlistModel();
        $this->companyModel = new CompanyModel();
    }

    // GET /offers
    public function index(): void
    {
        $q     = $_GET['q'] ?? '';
        $city  = $_GET['city'] ?? '';
        $skill = $_GET['skill'] ?? '';
        $page  = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 15;

        $result = $this->offerModel->findPaginated($page, $perPage, $q, $city, $skill);

        $wishlistIds = [];
        if ($this->isLoggedIn() && $_SESSION['user']['role'] === Role::STUDENT) {
            $wishlistIds = $this->wishlistModel->findIdsByUser($_SESSION['user']['id']);
        }

        $this->render('pages/offers.html.twig', [
            'offers'      => $result['offers'],
            'wishlistIds' => $wishlistIds,
            'q'           => $q,
            'city'        => $city,
            'skill'       => $skill,
            'pagination'  => [
                'totalPages'  => $result['totalPages'],
                'currentPage' => $result['currentPage'],
                'total'       => $result['total'],
            ],
        ]);
    }

    // GET /offer?id=x
    public function show(int $id): void
    {
        $offer = $this->offerModel->findById($id);

        if (!$offer) {
            http_response_code(404);
            echo '404 - Offer not found';
            return;
        }

        $applicationCount = $this->offerModel->countApplications($id);

        $inWishlist = false;
        if ($this->isLoggedIn() && $_SESSION['user']['role'] === Role::STUDENT) {
            $inWishlist = $this->wishlistModel->has($_SESSION['user']['id'], $id);
        }

        $this->render('pages/offer.html.twig', [
            'offer'            => $offer,
            'applicationCount' => $applicationCount,
            'inWishlist'       => $inWishlist,
        ]);
    }

    // GET /offer/create
    public function create(): void
    {
        $this->requirePermission(Permission::OFFER_CREATE);
        $companies = $this->companyModel->findAll();
        $from = $_GET['from'] ?? '';
        $role = $_SESSION['user']['role'] ?? '';
        $base = $role === Role::ADMIN ? '/admin' : '/pilot';
        $redirectTo = in_array($from, ['admin', 'pilot']) ? $base . '?tab=offers' : '/offers';
        $this->render('pages/offer-create.html.twig', [
            'companies'  => $companies,
            'redirectTo' => $redirectTo,
        ]);
    }

    // POST /offer/create/submit
    public function createSubmit(): void
    {
        $this->requirePermission(Permission::OFFER_CREATE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/offer/create');
        }

        $this->validateCsrfToken();

        $title        = trim($_POST['title'] ?? '');
        $location     = trim($_POST['location'] ?? '');
        $duration     = trim($_POST['duration'] ?? '');
        $startDate    = trim($_POST['startDate'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $missions     = trim($_POST['missions'] ?? '');
        $skills       = trim($_POST['skills'] ?? '');
        $remuneration = trim($_POST['remuneration'] ?? '');

        $errors = [];
        if (empty($title))       $errors[] = 'L\'intitulé est obligatoire.';
        if (empty($location))    $errors[] = 'La localisation est obligatoire.';
        if (empty($duration))    $errors[] = 'La durée est obligatoire.';
        if (empty($startDate))   $errors[] = 'La date de début est obligatoire.';
        if (empty($description)) $errors[] = 'La description est obligatoire.';
        if (empty($missions))    $errors[] = 'Les missions sont obligatoires.';

        if (!empty($errors)) {
            $companies = $this->companyModel->findAll();
            $this->render('pages/offer-create.html.twig', [
                'errors'     => $errors,
                'formData'   => $_POST,
                'companies'  => $companies,
                'redirectTo' => $_POST['redirectTo'] ?? '/offers',
            ]);
            return;
        }

        $idCompany  = (int) ($_POST['idCompany'] ?? 0);
        $redirectTo = trim($_POST['redirectTo'] ?? '/offers');

        $this->offerModel->createOffer([
            'title'           => $title,
            'location'        => $location,
            'durationInWeeks' => (int) $duration,
            'startDate'       => $startDate,
            'description'     => $description,
            'missions'        => $missions,
            'skills'          => $skills,
            'remuneration'    => $remuneration,
            'idCompany'       => $idCompany,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Offre publiée avec succès.'];
        $this->redirect($redirectTo);
    }

    // POST /offer/edit - traite la mise à jour (soumission depuis le dashboard)
    public function edit(?int $id = null): void
    {
        $this->requirePermission(Permission::OFFER_EDIT);

        $role = $_SESSION['user']['role'] ?? '';
        $base = $role === Role::ADMIN ? '/admin' : '/pilot';

        if ($id === null || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($base . '?tab=offers');
        }

        $this->validateCsrfToken();

        $title        = trim($_POST['title'] ?? '');
        $location     = trim($_POST['location'] ?? '');
        $duration     = trim($_POST['duration'] ?? '');
        $startDate    = trim($_POST['startDate'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $missions     = trim($_POST['missions'] ?? '');
        $skills       = trim($_POST['skills'] ?? '');
        $remuneration = trim($_POST['remuneration'] ?? '');
        $statusOffer  = isset($_POST['statusOffer']) ? (int) $_POST['statusOffer'] : 1;

        if (empty($title) || empty($location) || empty($duration) || empty($startDate) || empty($description) || empty($missions)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tous les champs obligatoires doivent être remplis.'];
            $redirectTo = trim($_POST['redirectTo'] ?? $base . '?tab=offers');
            $this->redirect($redirectTo);
        }

        $this->offerModel->updateOffer($id, [
            'title'           => $title,
            'location'        => $location,
            'durationInWeeks' => (int) $duration,
            'startDate'       => $startDate,
            'description'     => $description,
            'missions'        => $missions,
            'skills'          => $skills,
            'remuneration'    => $remuneration,
            'statusOffer'     => $statusOffer,
        ]);

        $redirectTo = trim($_POST['redirectTo'] ?? $base . '?tab=offers');
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Offre mise à jour avec succès.'];
        $this->redirect($redirectTo);
    }

    // POST /offer/delete?id=x
    public function delete(int $id): void
    {
        $this->requirePermission(Permission::OFFER_DELETE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/offers');
        }

        $this->validateCsrfToken();

        $this->offerModel->deleteOffer($id);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Offre supprimée.'];
        $this->redirectToDashboard('offers');
    }

    // GET /offer/stats - SFx11
    public function stats(): void
    {
        $this->requirePermission(Permission::OFFER_STATS);

        $this->render('pages/offer-stats.html.twig', [
            'statsByDuration' => $this->offerModel->statsByDuration(),
            'topWishlisted'   => $this->offerModel->topWishlisted(5),
            'totalActive'     => $this->offerModel->countActive(),
            'avgApplications' => $this->offerModel->avgApplicationsPerOffer(),
        ]);
    }
}
