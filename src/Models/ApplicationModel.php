<?php
// src/Models/ApplicationModel.php

namespace Equipe4\Gigastage\Models;

class ApplicationModel extends AbstractModel
{
    // Vérifie si un utilisateur a déjà postulé à une offre
    public function hasAlreadyApplied(int $idUser, int $idOffer): bool
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*)
            FROM Application
            WHERE idUser = :idUser AND idOffer = :idOffer
        ");
        $stmt->execute(['idUser' => $idUser, 'idOffer' => $idOffer]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // Récupère une candidature existante avec les infos utilisateur
    public function findApplication(int $idUser, int $idOffer): ?array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT
                a.*,
                u.email,
                p.surname,
                p.firstName
            FROM Application a
            JOIN User_   u ON a.idUser = u.idUser
            JOIN Profile p ON a.idUser = p.idUser
            WHERE a.idUser = :idUser AND a.idOffer = :idOffer
        ");
        $stmt->execute(['idUser' => $idUser, 'idOffer' => $idOffer]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Récupère l'offre liée à la candidature
    public function findOfferById(int $id): ?array
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

    // Crée une nouvelle candidature
    public function createApplication(array $data): void
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO Application (idUser, idOffer, resume, motivationLetter, applicationDate)
            VALUES (:idUser, :idOffer, :resume, :motivationLetter, :applicationDate)
        ");
        $stmt->execute([
            'idUser'           => $data['idUser'],
            'idOffer'          => $data['idOffer'],
            'resume'           => $data['resume'],
            'motivationLetter' => $data['motivationLetter'],
            'applicationDate'  => $data['applicationDate'],
        ]);
    }

    // SFx21 — Récupère toutes les candidatures d'un étudiant avec le détail des offres
    public function findByUser(int $idUser): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT
                a.applicationDate,
                a.resume,
                a.motivationLetter,
                o.idOffer,
                o.title,
                o.location,
                o.durationInWeeks,
                c.name AS companyName
            FROM Application a
            JOIN Offer   o ON a.idOffer    = o.idOffer
            JOIN Company c ON o.idCompany  = c.idCompany
            WHERE a.idUser = :idUser
            ORDER BY a.applicationDate DESC
        ");
        $stmt->execute(['idUser' => $idUser]);
        return $stmt->fetchAll();
    }

    // SFx22 — Récupère toutes les candidatures (vue pilote) avec nom de l'étudiant
    public function findAllWithDetails(): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT
                a.applicationDate,
                p.firstName,
                p.surname,
                u.email,
                o.idOffer,
                o.title,
                c.name AS companyName
            FROM Application a
            JOIN User_   u ON a.idUser    = u.idUser
            JOIN Profile p ON a.idUser    = p.idUser
            JOIN Offer   o ON a.idOffer   = o.idOffer
            JOIN Company c ON o.idCompany = c.idCompany
            ORDER BY a.applicationDate DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // SFx22 (pilote) — Candidatures des étudiants d'un pilote donné
    public function findByPilot(int $idPilot): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT
                a.applicationDate,
                p.firstName,
                p.surname,
                u.email,
                o.idOffer,
                o.title,
                c.name AS companyName
            FROM Application a
            JOIN User_   u ON a.idUser    = u.idUser
            JOIN Profile p ON a.idUser    = p.idUser
            JOIN Offer   o ON a.idOffer   = o.idOffer
            JOIN Company c ON o.idCompany = c.idCompany
            WHERE u.idPilot = :idPilot
            ORDER BY a.applicationDate DESC
        ");
        $stmt->execute(['idPilot' => $idPilot]);
        return $stmt->fetchAll();
    }

    // Met à jour le CV ou la lettre de motivation
    public function updateApplication(int $idUser, int $idOffer, string $column, ?string $value): void
    {
        $allowed = ['resume', 'motivationLetter'];
        if (!in_array($column, $allowed)) return;

        $stmt = $this->getConnection()->prepare("
            UPDATE Application
            SET $column = :value
            WHERE idUser = :idUser AND idOffer = :idOffer
        ");
        $stmt->execute([
            'value'   => $value,
            'idUser'  => $idUser,
            'idOffer' => $idOffer,
        ]);
    }
}