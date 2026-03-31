<?php
// src/Models/UserModel.php

namespace Equipe4\Gigastage\Models;

use Equipe4\Gigastage\Core\Role;

class UserModel extends AbstractModel
{
    // Compte les utilisateurs d'un rôle donné
    public function countByRole(string $role): int
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) FROM User_ u
            JOIN Role r ON u.idRole = r.idRole
            WHERE r.role = :role
        ");
        $stmt->execute(['role' => $role]);
        return (int) $stmt->fetchColumn();
    }

    // Récupère tous les utilisateurs d'un rôle donné
    public function findByRole(string $role): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT u.idUser, u.email, u.statusUser, p.firstName, p.surname
            FROM User_ u
            JOIN Role r ON u.idRole = r.idRole
            LEFT JOIN Profile p ON u.idUser = p.idUser
            WHERE r.role = :role
            ORDER BY p.surname ASC, p.firstName ASC
        ");
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll();
    }

    // Récupère tous les étudiants avec le nom du pilote assigné
    public function findByRoleWithPilot(string $role): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT
                u.idUser, u.email, u.statusUser, u.idPilot,
                p.firstName, p.surname,
                CONCAT(pp.firstName, ' ', pp.surname) AS pilotName
            FROM User_ u
            JOIN Role r ON u.idRole = r.idRole
            LEFT JOIN Profile p  ON u.idUser    = p.idUser
            LEFT JOIN User_ pilot ON u.idPilot   = pilot.idUser
            LEFT JOIN Profile pp  ON pilot.idUser = pp.idUser
            WHERE r.role = :role
            ORDER BY p.surname ASC, p.firstName ASC
        ");
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll();
    }

    // Recherche dans les utilisateurs d'un rôle par nom ou email
    public function search(string $role, string $q): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT u.idUser, u.email, u.statusUser, p.firstName, p.surname
            FROM User_ u
            JOIN Role r ON u.idRole = r.idRole
            LEFT JOIN Profile p ON u.idUser = p.idUser
            WHERE r.role = :role
              AND (p.firstName LIKE :q OR p.surname LIKE :q OR u.email LIKE :q)
            ORDER BY p.surname ASC, p.firstName ASC
        ");
        $stmt->execute(['role' => $role, 'q' => '%' . $q . '%']);
        return $stmt->fetchAll();
    }

    // Récupère un utilisateur par son id (avec profil et rôle)
    public function findById(int $id): ?array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT u.idUser, u.email, u.statusUser, u.idPilot, r.role, p.firstName, p.surname
            FROM User_ u
            JOIN Role r ON u.idRole = r.idRole
            LEFT JOIN Profile p ON u.idUser = p.idUser
            WHERE u.idUser = :id
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Crée un utilisateur avec le rôle indiqué
    public function create(array $data): void
    {
        $pdo = $this->getConnection();

        // Récupère l'idRole depuis la table Role
        $stmtRole = $pdo->prepare("SELECT idRole FROM Role WHERE role = :role");
        $stmtRole->execute(['role' => $data['role']]);
        $idRole = (int) $stmtRole->fetchColumn();

        $pdo->beginTransaction();

        try {
            $stmtUser = $pdo->prepare("
                INSERT INTO User_ (email, password, statusUser, idRole)
                VALUES (:email, :password, 1, :idRole)
            ");
            $stmtUser->execute([
                'email'    => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_BCRYPT),
                'idRole'   => $idRole,
            ]);

            $idUser = (int) $pdo->lastInsertId();

            $stmtProfile = $pdo->prepare("
                INSERT INTO Profile (idUser, firstName, surname)
                VALUES (:idUser, :firstName, :surname)
            ");
            $stmtProfile->execute([
                'idUser'    => $idUser,
                'firstName' => $data['firstName'],
                'surname'   => $data['surname'],
            ]);

            $pdo->commit();

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Met à jour l'email et/ou le profil d'un utilisateur
    public function update(int $id, array $data): void
    {
        $pdo = $this->getConnection();

        $pdo->beginTransaction();

        try {
            $stmtUser = $pdo->prepare("
                UPDATE User_ SET email = :email, statusUser = :statusUser WHERE idUser = :id
            ");
            $stmtUser->execute(['email' => $data['email'], 'statusUser' => $data['statusUser'] ?? 1, 'id' => $id]);

            $stmtProfile = $pdo->prepare("
                UPDATE Profile SET firstName = :firstName, surname = :surname
                WHERE idUser = :id
            ");
            $stmtProfile->execute([
                'firstName' => $data['firstName'],
                'surname'   => $data['surname'],
                'id'        => $id,
            ]);

            $pdo->commit();

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Récupère les étudiants d'un pilote donné
    public function findStudentsByPilot(int $idPilot): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT u.idUser, u.email, u.statusUser, p.firstName, p.surname
            FROM User_ u
            LEFT JOIN Profile p ON u.idUser = p.idUser
            WHERE u.idPilot = :idPilot
            ORDER BY p.surname ASC, p.firstName ASC
        ");
        $stmt->execute(['idPilot' => $idPilot]);
        return $stmt->fetchAll();
    }

    // Assigne un pilote à un étudiant
    public function assignPilot(int $idStudent, ?int $idPilot): void
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE User_ SET idPilot = :idPilot WHERE idUser = :id
        ");
        $stmt->execute(['idPilot' => $idPilot, 'id' => $idStudent]);
    }

    // Désactive un utilisateur (statusUser = 0)
    public function delete(int $id): void
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE User_ SET statusUser = 0 WHERE idUser = :id
        ");
        $stmt->execute(['id' => $id]);
    }
}
