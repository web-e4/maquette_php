<?php
// src/Controllers/HomeController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Role;
use Equipe4\Gigastage\Models\OfferModel;
use Equipe4\Gigastage\Models\UserModel;
use Equipe4\Gigastage\Models\WishlistModel;

class HomeController extends AbstractController
{
    private OfferModel $offerModel;
    private UserModel $userModel;
    private WishlistModel $wishlistModel;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->offerModel    = new OfferModel();
        $this->userModel     = new UserModel();
        $this->wishlistModel = new WishlistModel();
    }

    // GET /
    public function index(): void
    {
        $offers = $this->offerModel->findXLast(6);

        $stats = [
            'offers'    => $this->offerModel->countRows('Offer'),
            'companies' => $this->offerModel->countRows('Company'),
            'students'  => $this->userModel->countByRole(Role::STUDENT),
        ];

        $wishlistIds = [];
        if ($this->isLoggedIn() && $_SESSION['user']['role'] === Role::STUDENT) {
            $wishlistIds = $this->wishlistModel->findIdsByUser($_SESSION['user']['id']);
        }

        $this->render('pages/home.html.twig', [
            'offers'      => $offers,
            'stats'       => $stats,
            'wishlistIds' => $wishlistIds,
        ]);
    }
}