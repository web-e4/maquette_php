<?php
// src/Models/ProfileModel.php

namespace Equipe4\Gigastage\Models;

use Equipe4\Gigastage\Core\Role;

class ProfileModel extends AbstractModel
{
    // Récupère un profil complet par id utilisateur
    public function findById(int $idUser): ?array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT u.idUser, u.email, p.firstName, p.surname
            FROM User_ u
            JOIN Profile p ON u.idUser = p.idUser
            WHERE u.idUser = :idUser
        ");
        $stmt->execute(['idUser' => $idUser]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Récupère un utilisateur par son email (login + vérif doublon inscription)
    // Inclut le nom du rôle via jointure avec la table Role
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT u.idUser, u.email, u.password, r.role
            FROM User_ u
            JOIN Role r ON u.idRole = r.idRole
            WHERE u.email = :email
        ");
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Crée un nouvel utilisateur et son profil associé
    public function createUser(array $data): void
    {
        $pdo = $this->getConnection();

        // Récupère l'idRole correspondant au rôle Etudiant depuis la BDD
        $stmtRole = $pdo->prepare("SELECT idRole FROM Role WHERE role = :role");
        $stmtRole->execute(['role' => Role::STUDENT]);
        $idRole = (int) $stmtRole->fetchColumn();

        $pdo->beginTransaction();

        try {
            $stmtUser = $pdo->prepare("
                INSERT INTO User_ (email, password, statusUser, idRole)
                VALUES (:email, :password, 1, :idRole)
            ");
            $stmtUser->execute([
                'email'    => $data['email'],
                'password' => $data['password'],
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
}