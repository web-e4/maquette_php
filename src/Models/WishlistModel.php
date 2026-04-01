<?php
// src/Models/WishlistModel.php

namespace Equipe4\Gigastage\Models;

class WishlistModel extends AbstractModel
{
    // SFx23 - Récupère toutes les offres en wish-list d'un étudiant
    public function findByUser(int $idUser): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT
                o.idOffer,
                o.title,
                o.location,
                o.durationInWeeks,
                c.name AS companyName,
                w.startDate AS dateAdded
            FROM Wishlist w
            JOIN Offer   o ON w.idOffer    = o.idOffer
            JOIN Company c ON o.idCompany  = c.idCompany
            WHERE w.idUser = :idUser
            ORDER BY w.startDate DESC
        ");
        $stmt->execute(['idUser' => $idUser]);
        return $stmt->fetchAll();
    }

    // Retourne les IDs des offres en wish-list (pour vérification en masse dans les listes)
    public function findIdsByUser(int $idUser): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT idOffer FROM Wishlist WHERE idUser = :idUser
        ");
        $stmt->execute(['idUser' => $idUser]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    // Vérifie si une offre est déjà dans la wish-list d'un étudiant
    public function has(int $idUser, int $idOffer): bool
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) FROM Wishlist
            WHERE idUser = :idUser AND idOffer = :idOffer
        ");
        $stmt->execute(['idUser' => $idUser, 'idOffer' => $idOffer]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // SFx24 - Ajoute une offre à la wish-list
    public function add(int $idUser, int $idOffer): void
    {
        if ($this->has($idUser, $idOffer)) {
            return;
        }

        $stmt = $this->getConnection()->prepare("
            INSERT INTO Wishlist (idUser, idOffer, startDate)
            VALUES (:idUser, :idOffer, :startDate)
        ");
        $stmt->execute([
            'idUser'    => $idUser,
            'idOffer'   => $idOffer,
            'startDate' => date('Y-m-d'),
        ]);
    }

    // SFx25 - Retire une offre de la wish-list
    public function remove(int $idUser, int $idOffer): void
    {
        $stmt = $this->getConnection()->prepare("
            DELETE FROM Wishlist
            WHERE idUser = :idUser AND idOffer = :idOffer
        ");
        $stmt->execute(['idUser' => $idUser, 'idOffer' => $idOffer]);
    }
}
