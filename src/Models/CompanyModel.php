<?php
// src/Models/CompanyModel.php

namespace Equipe4\Gigastage\Models;

class CompanyModel extends AbstractModel
{
    // Récupère toutes les entreprises actives avec note moyenne et nb d'offres
    public function findAll(): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT
                c.idCompany, c.name, c.email, c.website,
                ROUND(AVG(r.rate), 1) AS avgRating,
                COUNT(DISTINCT r.idUser) AS ratingCount,
                COUNT(DISTINCT o.idOffer) AS offerCount
            FROM Company c
            LEFT JOIN Rating r ON c.idCompany = r.idCompany
            LEFT JOIN Offer  o ON c.idCompany = o.idCompany AND o.statusOffer = 1
            WHERE c.statusCompany = 1
            GROUP BY c.idCompany
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Récupère toutes les entreprises (actives et inactives) pour l'admin
    public function findAllForAdmin(): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT
                c.idCompany, c.name, c.email, c.website, c.statusCompany,
                ROUND(AVG(r.rate), 1) AS avgRating
            FROM Company c
            LEFT JOIN Rating r ON c.idCompany = r.idCompany
            GROUP BY c.idCompany
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Récupère une entreprise par son id
    public function findById(int $id): ?array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT idCompany, name, email, website, statusCompany
            FROM Company
            WHERE idCompany = :id
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Crée une nouvelle entreprise
    public function create(array $data): void
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO Company (name, email, website, statusCompany)
            VALUES (:name, :email, :website, 1)
        ");
        $stmt->execute([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'website' => $data['website'] ?: null,
        ]);
    }

    // Met à jour une entreprise
    public function update(int $id, array $data): void
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE Company
            SET name = :name, email = :email, website = :website, statusCompany = :statusCompany
            WHERE idCompany = :id
        ");
        $stmt->execute([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'website'       => $data['website'] ?: null,
            'statusCompany' => $data['statusCompany'] ?? 1,
            'id'            => $id,
        ]);
    }

    // Supprime une entreprise (désactivation)
    public function delete(int $id): void
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE Company SET statusCompany = 0 WHERE idCompany = :id
        ");
        $stmt->execute(['id' => $id]);
    }

    // Ajoute ou met à jour une évaluation
    public function addRating(int $idUser, int $idCompany, int $rate, string $comment): void
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO Rating (idUser, idCompany, rate, comment)
            VALUES (:idUser, :idCompany, :rate, :comment)
            ON DUPLICATE KEY UPDATE rate = :rate, comment = :comment
        ");
        $stmt->execute([
            'idUser'    => $idUser,
            'idCompany' => $idCompany,
            'rate'      => $rate,
            'comment'   => $comment,
        ]);
    }

    // Récupère toutes les évaluations d'une entreprise avec le nom de l'auteur
    public function getRatings(int $idCompany): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT r.rate, r.comment, p.firstName, p.surname
            FROM Rating r
            JOIN Profile p ON r.idUser = p.idUser
            WHERE r.idCompany = :idCompany
            ORDER BY r.rate DESC
        ");
        $stmt->execute(['idCompany' => $idCompany]);
        return $stmt->fetchAll();
    }

    // Calcule la note moyenne d'une entreprise
    public function getAverageRating(int $idCompany): ?float
    {
        $stmt = $this->getConnection()->prepare("
            SELECT AVG(rate) FROM Rating WHERE idCompany = :idCompany
        ");
        $stmt->execute(['idCompany' => $idCompany]);
        $avg = $stmt->fetchColumn();
        return $avg !== false ? round((float) $avg, 1) : null;
    }

    // Récupère les offres actives d'une entreprise
    public function getOffers(int $idCompany): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT idOffer, title, location, durationInWeeks, startDate
            FROM Offer
            WHERE idCompany = :idCompany AND statusOffer = 1
            ORDER BY idOffer DESC
        ");
        $stmt->execute(['idCompany' => $idCompany]);
        return $stmt->fetchAll();
    }
}
