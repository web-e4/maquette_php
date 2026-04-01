<?php
// src/Core/AccessControl.php

namespace Equipe4\Gigastage\Core;

class AccessControl
{
    /**
     * Matrice des permissions : permission => rôles autorisés
     */
    private static array $matrix = [

        // SFx1 - Gestion d'accès
        Permission::AUTH => [Role::ADMIN, Role::PILOT, Role::STUDENT, Role::ANONYMOUS],

        // SFx2-6 - Gestion des entreprises
        Permission::COMPANY_VIEW     => [Role::ADMIN, Role::PILOT, Role::STUDENT, Role::ANONYMOUS],
        Permission::COMPANY_CREATE   => [Role::ADMIN, Role::PILOT],
        Permission::COMPANY_EDIT     => [Role::ADMIN, Role::PILOT],
        Permission::COMPANY_EVALUATE => [Role::ADMIN, Role::PILOT],
        Permission::COMPANY_DELETE   => [Role::ADMIN, Role::PILOT],

        // SFx7-11 - Gestion des offres de stages
        Permission::OFFER_VIEW   => [Role::ADMIN, Role::PILOT, Role::STUDENT, Role::ANONYMOUS],
        Permission::OFFER_CREATE => [Role::ADMIN, Role::PILOT],
        Permission::OFFER_EDIT   => [Role::ADMIN, Role::PILOT],
        Permission::OFFER_DELETE => [Role::ADMIN, Role::PILOT],
        Permission::OFFER_STATS  => [Role::ADMIN, Role::PILOT, Role::STUDENT, Role::ANONYMOUS],

        // SFx12-15 - Gestion des pilotes
        Permission::PILOT_VIEW   => [Role::ADMIN],
        Permission::PILOT_CREATE => [Role::ADMIN],
        Permission::PILOT_EDIT   => [Role::ADMIN],
        Permission::PILOT_DELETE => [Role::ADMIN],

        // SFx16-19 - Gestion des étudiants
        Permission::STUDENT_VIEW   => [Role::ADMIN, Role::PILOT],
        Permission::STUDENT_CREATE => [Role::ADMIN, Role::PILOT],
        Permission::STUDENT_EDIT   => [Role::ADMIN, Role::PILOT],
        Permission::STUDENT_DELETE => [Role::ADMIN, Role::PILOT],

        // SFx20-22 - Gestion des candidatures
        Permission::APPLICATION_APPLY      => [Role::STUDENT],
        Permission::APPLICATION_OWN_LIST   => [Role::STUDENT],
        Permission::APPLICATION_PILOT_LIST => [Role::PILOT],

        // SFx23-25 - Gestion des wish-list
        Permission::WISHLIST_VIEW   => [Role::STUDENT],
        Permission::WISHLIST_ADD    => [Role::STUDENT],
        Permission::WISHLIST_REMOVE => [Role::STUDENT],
    ];

    /**
     * Vérifie si un rôle a accès à une permission donnée.
     *
     * @param string $role       Le rôle de l'utilisateur (ex: Role::STUDENT)
     * @param string $permission La permission à vérifier (ex: Permission::APPLY)
     */
    public static function can(string $role, string $permission): bool
    {
        return in_array($role, self::$matrix[$permission] ?? []);
    }
}
