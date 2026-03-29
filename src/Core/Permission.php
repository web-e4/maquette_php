<?php
// src/Core/Permission.php

namespace Equipe4\Gigastage\Core;

class Permission
{
    const AUTH = 'auth'; // SFx1 — Authentification et gestion des accès

    const COMPANY_VIEW     = 'company.view';     // SFx2 — Rechercher et afficher une entreprise
    const COMPANY_CREATE   = 'company.create';   // SFx3 — Créer une entreprise
    const COMPANY_EDIT     = 'company.edit';     // SFx4 — Modifier une entreprise
    const COMPANY_EVALUATE = 'company.evaluate'; // SFx5 — Évaluer une entreprise
    const COMPANY_DELETE   = 'company.delete';   // SFx6 — Supprimer une entreprise

    const OFFER_VIEW   = 'offer.view';   // SFx7  — Rechercher et afficher une offre
    const OFFER_CREATE = 'offer.create'; // SFx8  — Créer une offre
    const OFFER_EDIT   = 'offer.edit';   // SFx9  — Modifier une offre
    const OFFER_DELETE = 'offer.delete'; // SFx10 — Supprimer une offre
    const OFFER_STATS  = 'offer.stats';  // SFx11 — Consulter les statistiques des offres

    const PILOT_VIEW   = 'pilot.view';   // SFx12 — Rechercher et afficher un compte Pilote
    const PILOT_CREATE = 'pilot.create'; // SFx13 — Créer un compte Pilote
    const PILOT_EDIT   = 'pilot.edit';   // SFx14 — Modifier un compte Pilote
    const PILOT_DELETE = 'pilot.delete'; // SFx15 — Supprimer un compte Pilote

    const STUDENT_VIEW   = 'student.view';   // SFx16 — Rechercher et afficher un compte Étudiant
    const STUDENT_CREATE = 'student.create'; // SFx17 — Créer un compte Étudiant
    const STUDENT_EDIT   = 'student.edit';   // SFx18 — Modifier un compte Étudiant
    const STUDENT_DELETE = 'student.delete'; // SFx19 — Supprimer un compte Étudiant

    const APPLICATION_APPLY      = 'application.apply';       // SFx20 — Postuler à une offre
    const APPLICATION_OWN_LIST   = 'application.own_list';    // SFx21 — Afficher ses propres candidatures
    const APPLICATION_PILOT_LIST = 'application.pilot_list';  // SFx22 — Afficher les candidatures des élèves du pilote

    const WISHLIST_VIEW   = 'wishlist.view';   // SFx23 — Afficher la wish-list
    const WISHLIST_ADD    = 'wishlist.add';    // SFx24 — Ajouter une offre à la wish-list
    const WISHLIST_REMOVE = 'wishlist.remove'; // SFx25 — Retirer une offre de la wish-list
}
