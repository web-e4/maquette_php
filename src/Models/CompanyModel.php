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
                c.idCompany, c.name, c.description, c.email, c.phone, c.website,
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

    // Récupère les entreprises actives paginées avec recherche par nom
    public function findPaginated(int $page, int $perPage, string $q = ''): array
    {
        $pdo = $this->getConnection();
        $offset = ($page - 1) * $perPage;

        $where = 'WHERE c.statusCompany = 1';
        $params = [];

        if ($q !== '') {
            $where .= ' AND c.name LIKE :q';
            $params['q'] = '%' . $q . '%';
        }

        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Company c $where");
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $stmt = $pdo->prepare("
            SELECT
                c.idCompany, c.name, c.description, c.email, c.phone, c.website,
                ROUND(AVG(r.rate), 1) AS avgRating,
                COUNT(DISTINCT r.idUser) AS ratingCount,
                COUNT(DISTINCT o.idOffer) AS offerCount
            FROM Company c
            LEFT JOIN Rating r ON c.idCompany = r.idCompany
            LEFT JOIN Offer  o ON c.idCompany = o.idCompany AND o.statusOffer = 1
            $where
            GROUP BY c.idCompany
            ORDER BY c.name ASC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue(":$key", $value, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }

        $stmt->execute();

        return [
            'companies'   => $stmt->fetchAll(),
            'total'       => $total,
            'totalPages'  => (int) ceil($total / $perPage),
            'currentPage' => $page,
        ];
    }

    // Récupère toutes les entreprises (actives et inactives) pour l'admin
    public function findAllForAdmin(): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT
                c.idCompany, c.name, c.description, c.email, c.phone, c.website, c.statusCompany,
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
            SELECT idCompany, name, description, email, phone, website, statusCompany
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
            INSERT INTO Company (name, description, email, phone, website, statusCompany)
            VALUES (:name, :description, :email, :phone, :website, 1)
        ");
        $stmt->execute([
            'name'        => $data['name'],
            'description' => $data['description'] ?: null,
            'email'       => $data['email'],
            'phone'       => $data['phone'] ?: null,
            'website'     => $data['website'] ?: null,
        ]);
    }

    // Met à jour une entreprise
    public function update(int $id, array $data): void
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE Company
            SET name = :name, description = :description, email = :email,
                phone = :phone, website = :website, statusCompany = :statusCompany
            WHERE idCompany = :id
        ");
        $stmt->execute([
            'name'          => $data['name'],
            'description'   => $data['description'] ?: null,
            'email'         => $data['email'],
            'phone'         => $data['phone'] ?: null,
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

    // Compte le nombre de candidatures déposées sur les offres d'une entreprise
    public function countApplicants(int $idCompany): int
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) FROM Application a
            JOIN Offer o ON a.idOffer = o.idOffer
            WHERE o.idCompany = :idCompany
        ");
        $stmt->execute(['idCompany' => $idCompany]);
        return (int) $stmt->fetchColumn();
    }
}
