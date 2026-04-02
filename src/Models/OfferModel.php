<?php
// src/Models/OfferModel.php

namespace Equipe4\Gigastage\Models;

class OfferModel extends AbstractModel
{
    // Récupère les X dernières offres pour la home
    public function findXLast(int $x): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT idOffer, title, location, durationInWeeks, createdAt
            FROM Offer
            WHERE statusOffer = 1
            ORDER BY createdAt DESC, idOffer DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $x, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Récupère une offre par son id (offres actives uniquement)
    public function findById(int $id): ?array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT o.*, c.name AS companyName
            FROM Offer o
            JOIN Company c ON o.idCompany = c.idCompany
            WHERE o.idOffer = :id AND o.statusOffer = 1
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Récupère une offre par son id sans filtre de statut (pour l'admin)
    public function findByIdAdmin(int $id): ?array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT o.*, c.name AS companyName
            FROM Offer o
            JOIN Company c ON o.idCompany = c.idCompany
            WHERE o.idOffer = :id
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Récupère les offres paginées avec recherche par titre, ville et compétence
    public function findPaginated(int $page, int $perPage, string $q = '', string $city = '', string $skill = ''): array
    {
        $pdo = $this->getConnection();
        $offset = ($page - 1) * $perPage;

        $where = 'WHERE statusOffer = 1';
        $params = [];

        if ($q !== '') {
            $where .= ' AND title LIKE :q';
            $params['q'] = '%' . $q . '%';
        }

        if ($city !== '') {
            $where .= ' AND location LIKE :city';
            $params['city'] = '%' . $city . '%';
        }

        if ($skill !== '') {
            $where .= ' AND skills LIKE :skill';
            $params['skill'] = '%' . $skill . '%';
        }

        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Offer $where");
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $stmt = $pdo->prepare("
            SELECT idOffer, title, location, durationInWeeks, skills, remuneration
            FROM Offer
            $where
            ORDER BY idOffer DESC
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
            'offers'      => $stmt->fetchAll(),
            'total'       => $total,
            'totalPages'  => (int) ceil($total / $perPage),
            'currentPage' => $page,
        ];
    }

    // Compte le nombre de candidatures pour une offre
    public function countApplications(int $idOffer): int
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) FROM Application WHERE idOffer = :idOffer
        ");
        $stmt->execute(['idOffer' => $idOffer]);
        return (int) $stmt->fetchColumn();
    }

    // Crée une nouvelle offre
    public function createOffer(array $data): void
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO Offer (title, description, missions, skills, remuneration, location, durationInWeeks, startDate, statusOffer, idCompany, createdAt)
            VALUES (:title, :description, :missions, :skills, :remuneration, :location, :durationInWeeks, :startDate, 1, :idCompany, CURRENT_DATE)
        ");
        $stmt->execute([
            'title'           => $data['title'],
            'description'     => $data['description'],
            'missions'        => $data['missions'],
            'skills'          => $data['skills'] ?: null,
            'remuneration'    => $data['remuneration'] !== '' ? (float) $data['remuneration'] : null,
            'location'        => $data['location'],
            'durationInWeeks' => $data['durationInWeeks'],
            'startDate'       => $data['startDate'],
            'idCompany'       => $data['idCompany'],
        ]);
    }

    // Met à jour une offre existante
    public function updateOffer(int $id, array $data): void
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE Offer
            SET title = :title,
                description = :description,
                missions = :missions,
                skills = :skills,
                remuneration = :remuneration,
                location = :location,
                durationInWeeks = :durationInWeeks,
                startDate = :startDate,
                statusOffer = :statusOffer
            WHERE idOffer = :id
        ");
        $stmt->execute([
            'title'           => $data['title'],
            'description'     => $data['description'],
            'missions'        => $data['missions'],
            'skills'          => $data['skills'] ?: null,
            'remuneration'    => isset($data['remuneration']) && $data['remuneration'] !== '' ? (float) $data['remuneration'] : null,
            'location'        => $data['location'],
            'durationInWeeks' => $data['durationInWeeks'],
            'startDate'       => $data['startDate'],
            'statusOffer'     => $data['statusOffer'] ?? 1,
            'id'              => $id,
        ]);
    }

    // Supprime une offre par son id
    public function deleteOffer(int $id): void
    {
        $stmt = $this->getConnection()->prepare("
            DELETE FROM Offer WHERE idOffer = :id
        ");
        $stmt->execute(['id' => $id]);
    }

    // Récupère toutes les offres avec le nom de l'entreprise (pour l'admin)
    public function findAll(): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT o.idOffer, o.title, o.location, o.durationInWeeks, o.startDate, o.statusOffer,
                   o.skills, o.remuneration,
                   c.name AS companyName, c.idCompany
            FROM Offer o
            JOIN Company c ON o.idCompany = c.idCompany
            ORDER BY o.idOffer DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Compte le nombre de lignes d'une table pour les stats de la home
    public function countRows(string $table): int
    {
        $allowed = ['Offer', 'Company', 'User_'];

        if (!in_array($table, $allowed)) {
            throw new \InvalidArgumentException("Unauthorized table: $table");
        }

        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) FROM $table");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // SFx11 - Statistiques des offres

    // Répartition des offres par durée (en semaines)
    public function statsByDuration(): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT durationInWeeks, COUNT(*) AS total
            FROM Offer
            WHERE statusOffer = 1
            GROUP BY durationInWeeks
            ORDER BY durationInWeeks ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Top offres les plus ajoutées en wish-list
    public function topWishlisted(int $limit = 5): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT o.idOffer, o.title, c.name AS companyName, COUNT(w.idUser) AS wishlistCount
            FROM Offer o
            JOIN Company c ON o.idCompany = c.idCompany
            LEFT JOIN Wishlist w ON o.idOffer = w.idOffer
            WHERE o.statusOffer = 1
            GROUP BY o.idOffer, o.title, c.name
            ORDER BY wishlistCount DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Nombre total d'offres actives
    public function countActive(): int
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) FROM Offer WHERE statusOffer = 1
        ");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // Nombre moyen de candidatures par offre
    public function avgApplicationsPerOffer(): float
    {
        $stmt = $this->getConnection()->prepare("
            SELECT AVG(cnt) FROM (
                SELECT COUNT(*) AS cnt FROM Application GROUP BY idOffer
            ) AS sub
        ");
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result !== false ? round((float) $result, 1) : 0.0;
    }
}
