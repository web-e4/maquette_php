<?php
// src/Controllers/WishlistController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Permission;
use Equipe4\Gigastage\Models\WishlistModel;

class WishlistController extends AbstractController
{
    private WishlistModel $wishlistModel;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->wishlistModel = new WishlistModel();
    }

    // POST /wishlist/add?id=x — SFx24
    public function add(int $idOffer): void
    {
        $this->requirePermission(Permission::WISHLIST_ADD);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/offers');
        }

        $idUser   = $_SESSION['user']['id'];
        $redirect = trim($_POST['redirect'] ?? '');
        $this->wishlistModel->add($idUser, $idOffer);

        $this->redirect($redirect ?: '/offer?id=' . $idOffer);
    }

    // POST /wishlist/remove?id=x — SFx25
    public function remove(int $idOffer): void
    {
        $this->requirePermission(Permission::WISHLIST_REMOVE);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile');
        }

        $idUser   = $_SESSION['user']['id'];
        $redirect = trim($_POST['redirect'] ?? '');
        $this->wishlistModel->remove($idUser, $idOffer);

        $this->redirect($redirect ?: '/profile');
    }
}
