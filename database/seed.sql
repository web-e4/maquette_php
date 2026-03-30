-- =============================================================
-- SEED — Gigastage
-- Mot de passe de tous les comptes créés : password
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- ENTREPRISES (8 nouvelles, 10 au total)
-- -------------------------------------------------------------
INSERT INTO Company (name, email, website, statusCompany) VALUES
  ('Nexio Digital',     'contact@nexiodigital.fr',   'https://nexiodigital.fr',   1),
  ('Atelier Créatif',   'hello@ateliercrea.com',      NULL,                        1),
  ('DataSphere',        'rh@datasphere.io',           'https://datasphere.io',     1),
  ('Horizon Web',       'jobs@horizonweb.fr',         'https://horizonweb.fr',     1),
  ('Softeam Rouen',     'recrutement@softeam.fr',     'https://softeam.fr',        1),
  ('CyberSec France',   'contact@cybersecfr.com',     'https://cybersecfr.com',    1),
  ('GreenTech Labs',    'rh@greentechlabs.fr',        'https://greentechlabs.fr',  1),
  ('MediaPulse',        'stages@mediapulse.fr',       NULL,                        1);

-- -------------------------------------------------------------
-- UTILISATEURS — Pilotes (3 nouveaux, idRole=2)
-- -------------------------------------------------------------
INSERT INTO User_ (email, password, statusUser, idRole) VALUES
  ('sophie.martin@gigastage.fr',  '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 2),
  ('lucas.bernard@gigastage.fr',  '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 2),
  ('clara.dupont@gigastage.fr',   '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 2);

INSERT INTO Profile (surname, firstName, idUser) VALUES
  ('Martin',  'Sophie', (SELECT idUser FROM User_ WHERE email = 'sophie.martin@gigastage.fr')),
  ('Bernard', 'Lucas',  (SELECT idUser FROM User_ WHERE email = 'lucas.bernard@gigastage.fr')),
  ('Dupont',  'Clara',  (SELECT idUser FROM User_ WHERE email = 'clara.dupont@gigastage.fr'));

-- -------------------------------------------------------------
-- UTILISATEURS — Étudiants (15 nouveaux, idRole=3)
-- Assignés aux pilotes idUser 4, 5 et 6
-- -------------------------------------------------------------
INSERT INTO User_ (email, password, statusUser, idRole, idPilot) VALUES
  ('theo.leroy@etudiant.fr',       '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 4),
  ('emma.roux@etudiant.fr',        '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 4),
  ('hugo.moreau@etudiant.fr',      '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 4),
  ('chloe.simon@etudiant.fr',      '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 4),
  ('nathan.girard@etudiant.fr',    '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 4),
  ('lea.fontaine@etudiant.fr',     '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 5),
  ('maxime.lambert@etudiant.fr',   '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 5),
  ('julie.petit@etudiant.fr',      '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 5),
  ('antoine.chevalier@etudiant.fr','$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 5),
  ('manon.robin@etudiant.fr',      '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 5),
  ('pierre.blanc@etudiant.fr',     '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 6),
  ('alice.henry@etudiant.fr',      '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 6),
  ('romain.garnier@etudiant.fr',   '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 6),
  ('lucie.faure@etudiant.fr',      '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 6),
  ('baptiste.michaud@etudiant.fr', '$2y$10$SZAGM0rpshxllXTmlQKDjOoRsWI1kcfrziX9VY.fji3KTUFyzVNyq', 1, 3, 6);

INSERT INTO Profile (surname, firstName, idUser) VALUES
  ('Leroy',     'Théo',     (SELECT idUser FROM User_ WHERE email = 'theo.leroy@etudiant.fr')),
  ('Roux',      'Emma',     (SELECT idUser FROM User_ WHERE email = 'emma.roux@etudiant.fr')),
  ('Moreau',    'Hugo',     (SELECT idUser FROM User_ WHERE email = 'hugo.moreau@etudiant.fr')),
  ('Simon',     'Chloé',    (SELECT idUser FROM User_ WHERE email = 'chloe.simon@etudiant.fr')),
  ('Girard',    'Nathan',   (SELECT idUser FROM User_ WHERE email = 'nathan.girard@etudiant.fr')),
  ('Fontaine',  'Léa',      (SELECT idUser FROM User_ WHERE email = 'lea.fontaine@etudiant.fr')),
  ('Lambert',   'Maxime',   (SELECT idUser FROM User_ WHERE email = 'maxime.lambert@etudiant.fr')),
  ('Petit',     'Julie',    (SELECT idUser FROM User_ WHERE email = 'julie.petit@etudiant.fr')),
  ('Chevalier', 'Antoine',  (SELECT idUser FROM User_ WHERE email = 'antoine.chevalier@etudiant.fr')),
  ('Robin',     'Manon',    (SELECT idUser FROM User_ WHERE email = 'manon.robin@etudiant.fr')),
  ('Blanc',     'Pierre',   (SELECT idUser FROM User_ WHERE email = 'pierre.blanc@etudiant.fr')),
  ('Henry',     'Alice',    (SELECT idUser FROM User_ WHERE email = 'alice.henry@etudiant.fr')),
  ('Garnier',   'Romain',   (SELECT idUser FROM User_ WHERE email = 'romain.garnier@etudiant.fr')),
  ('Faure',     'Lucie',    (SELECT idUser FROM User_ WHERE email = 'lucie.faure@etudiant.fr')),
  ('Michaud',   'Baptiste', (SELECT idUser FROM User_ WHERE email = 'baptiste.michaud@etudiant.fr'));

-- -------------------------------------------------------------
-- OFFRES (25 offres réparties sur les 10 entreprises)
-- -------------------------------------------------------------
INSERT INTO Offer (title, description, missions, location, durationInWeeks, startDate, statusOffer, idCompany) VALUES
  -- SuperTech (1)
  ('Stage Développeur Full-Stack',
   'Intégrez l\'équipe produit de SuperTech pour contribuer au développement de notre plateforme SaaS B2B utilisée par plus de 500 entreprises.',
   'Développer de nouvelles fonctionnalités front et back,Corriger les bugs remontés par les clients,Participer aux revues de code',
   'Paris (75)', 24, '2025-09-01', 1, 1),
  ('Stage DevOps',
   'Au sein de l\'équipe infrastructure, vous automatiserez le déploiement et améliorerez la résilience de nos services cloud.',
   'Mettre en place des pipelines CI/CD,Surveiller les métriques systèmes,Documenter les procédures d\'exploitation',
   'Paris (75)', 16, '2025-10-01', 1, 1),

  -- PetiteBoite (2)
  ('Stage UX Designer',
   'Rejoignez notre studio de design pour repenser l\'expérience utilisateur de notre application mobile grand public.',
   'Réaliser des interviews utilisateurs,Concevoir des wireframes et prototypes,Conduire des tests d\'utilisabilité',
   'Lyon (69)', 20, '2025-09-15', 1, 2),

  -- Nexio Digital (3)
  ('Stage Développeur React',
   'Participez à la refonte de notre portail client en React 18, en étroite collaboration avec notre équipe produit.',
   'Développer des composants React réutilisables,Intégrer des API REST,Écrire des tests unitaires avec Jest',
   'Rouen (76)', 16, '2025-09-01', 1, 3),
  ('Stage Chef de projet digital',
   'Gérez le suivi de projets web pour nos clients grands comptes dans un environnement agile.',
   'Animer les cérémonies Scrum,Rédiger les spécifications fonctionnelles,Assurer le lien entre clients et développeurs',
   'Rouen (76)', 20, '2025-10-01', 1, 3),
  ('Stage Développeur mobile Flutter',
   'Contribuez au développement de notre application mobile cross-platform basée sur Flutter.',
   'Développer des écrans en Flutter/Dart,Intégrer les services Firebase,Optimiser les performances',
   'Rouen (76)', 24, '2026-01-05', 1, 3),

  -- Atelier Créatif (4)
  ('Stage Graphiste / Motion Design',
   'Participez à la création de contenus visuels animés pour les réseaux sociaux de nos clients.',
   'Concevoir des visuels selon les chartes graphiques,Produire des animations After Effects,Préparer les fichiers pour l\'impression et le web',
   'Lille (59)', 12, '2025-09-01', 1, 4),
  ('Stage Community Manager',
   'Gérez la présence sur les réseaux sociaux de nos clients et analysez les performances des campagnes.',
   'Créer et planifier les publications,Répondre aux commentaires et messages,Produire des rapports mensuels de performance',
   'Lille (59)', 16, '2025-10-15', 1, 4),

  -- DataSphere (5)
  ('Stage Data Analyst',
   'Analysez les données de nos clients e-commerce pour identifier des leviers de croissance et optimiser les performances.',
   'Extraire et nettoyer des jeux de données,Construire des tableaux de bord Power BI,Présenter les insights aux clients',
   'Paris (75)', 20, '2025-09-01', 1, 5),
  ('Stage Machine Learning',
   'Participez à des projets de prédiction et de classification appliqués aux données clients.',
   'Préparer et explorer des datasets,Entraîner et évaluer des modèles ML,Déployer des modèles en API Flask',
   'Paris (75)', 24, '2026-02-02', 1, 5),
  ('Stage Ingénieur Data',
   'Contribuez à la construction de nos pipelines de données en Python et au maintien de notre data warehouse.',
   'Développer des pipelines ETL avec Airflow,Optimiser les requêtes SQL,Documenter les flux de données',
   'Paris (75)', 20, '2025-09-01', 0, 5),

  -- Horizon Web (6)
  ('Stage Développeur WordPress',
   'Créez et personnalisez des sites WordPress pour les PME de notre portefeuille clients.',
   'Développer des thèmes et plugins sur mesure,Intégrer des maquettes Figma,Assurer la maintenance et les mises à jour',
   'Marseille (13)', 12, '2025-09-01', 1, 6),
  ('Stage Référenceur SEO',
   'Optimisez le référencement naturel des sites de nos clients et suivez les positions sur les moteurs de recherche.',
   'Réaliser des audits SEO techniques,Rédiger des contenus optimisés,Construire des plans de netlinking',
   'Marseille (13)', 16, '2025-10-01', 1, 6),

  -- Softeam Rouen (7)
  ('Stage Développeur Java Spring',
   'Développez des microservices Java Spring Boot pour notre plateforme de gestion documentaire.',
   'Concevoir et implémenter des API REST,Écrire des tests unitaires et d\'intégration,Participer aux daily Scrum',
   'Rouen (76)', 24, '2025-09-01', 1, 7),
  ('Stage Testeur QA',
   'Assurez la qualité de nos livrables en rédigeant et exécutant des plans de test fonctionnels et automatisés.',
   'Rédiger des cas de test,Automatiser les tests avec Selenium,Remonter et suivre les anomalies dans Jira',
   'Rouen (76)', 16, '2026-01-05', 1, 7),
  ('Stage Administrateur Système',
   'Gérez et faites évoluer notre parc de serveurs Linux en garantissant disponibilité et sécurité.',
   'Administrer des serveurs Linux,Mettre en place des sauvegardes,Traiter les incidents de niveau 2',
   'Rouen (76)', 20, '2025-10-01', 1, 7),

  -- CyberSec France (8)
  ('Stage Analyste SOC',
   'Intégrez notre centre opérationnel de sécurité et participez à la surveillance des systèmes d\'information de nos clients.',
   'Analyser les alertes SIEM,Qualifier les incidents de sécurité,Rédiger des rapports d\'incidents',
   'Paris (75)', 20, '2025-09-15', 1, 8),
  ('Stage Pentester Junior',
   'Réalisez des tests d\'intrusion sur des périmètres web et réseau pour nos clients sous contrat.',
   'Conduire des tests de pénétration web,Rédiger des rapports de vulnérabilités,Présenter les résultats aux clients',
   'Paris (75)', 24, '2026-02-02', 1, 8),

  -- GreenTech Labs (9)
  ('Stage Développeur IoT',
   'Participez au développement de solutions IoT pour la gestion de l\'énergie dans les bâtiments connectés.',
   'Programmer des capteurs embarqués,Développer un dashboard de monitoring,Intégrer des protocoles MQTT et HTTP',
   'Lyon (69)', 24, '2025-09-01', 1, 9),
  ('Stage Ingénieur R&D',
   'Contribuez à nos travaux de recherche sur l\'efficacité énergétique et les énergies renouvelables.',
   'Analyser des données de consommation,Modéliser des scénarios énergétiques,Rédiger des livrables de recherche',
   'Lyon (69)', 20, '2026-01-05', 1, 9),

  -- MediaPulse (10)
  ('Stage Journaliste Web',
   'Rédigez des articles de fond sur l\'actualité tech et startup pour nos portails d\'information.',
   'Rédiger des articles et reportages,Réaliser des interviews,Optimiser les contenus pour le SEO',
   'Paris (75)', 12, '2025-09-01', 1, 10),
  ('Stage Vidéaste / Monteur',
   'Produisez des contenus vidéo pour nos clients médias, du tournage au montage final.',
   'Filmer des reportages et interviews,Monter des vidéos avec Premiere Pro,Gérer le planning de production',
   'Paris (75)', 16, '2025-10-01', 1, 10),
  ('Stage Développeur Back-end PHP',
   'Développez de nouvelles fonctionnalités pour notre CMS maison basé sur PHP/Symfony.',
   'Développer des modules Symfony,Écrire des tests PHPUnit,Optimiser les performances SQL',
   'Paris (75)', 20, '2026-01-05', 1, 10),
  ('Stage Designer UI',
   'Créez des interfaces modernes et accessibles pour nos clients en vous basant sur un design system existant.',
   'Décliner le design system sur de nouveaux écrans,Produire des prototypes Figma interactifs,Collaborer avec les développeurs front',
   'Rouen (76)', 16, '2025-09-15', 1, 10),
  ('Stage Développeur Node.js',
   'Rejoignez l\'équipe technique pour développer des API performantes en Node.js/Express.',
   'Concevoir des routes REST sécurisées,Gérer l\'authentification JWT,Mettre en place la documentation Swagger',
   'Lille (59)', 20, '2026-02-02', 1, 3);

-- -------------------------------------------------------------
-- CANDIDATURES
-- Étudiants : idUser 7 à 21  |  Offres : idOffer 2 à 26
-- -------------------------------------------------------------
INSERT INTO Application (idUser, idOffer, resume, motivationLetter, applicationDate) VALUES
  (7,  2,  'uploads/resumes/cv_theo_leroy.pdf',       'uploads/letters/lm_theo_leroy_2.pdf',       '2025-03-10'),
  (7,  4,  'uploads/resumes/cv_theo_leroy.pdf',       NULL,                                         '2025-03-15'),
  (8,  4,  'uploads/resumes/cv_emma_roux.pdf',        'uploads/letters/lm_emma_roux_4.pdf',         '2025-03-12'),
  (8,  9,  'uploads/resumes/cv_emma_roux.pdf',        NULL,                                         '2025-03-20'),
  (9,  14, 'uploads/resumes/cv_hugo_moreau.pdf',      'uploads/letters/lm_hugo_moreau_14.pdf',      '2025-03-08'),
  (9,  15, 'uploads/resumes/cv_hugo_moreau.pdf',      NULL,                                         '2025-03-22'),
  (10, 7,  'uploads/resumes/cv_chloe_simon.pdf',      'uploads/letters/lm_chloe_simon_7.pdf',       '2025-03-05'),
  (10, 21, 'uploads/resumes/cv_chloe_simon.pdf',      NULL,                                         '2025-03-18'),
  (11, 17, 'uploads/resumes/cv_nathan_girard.pdf',    'uploads/letters/lm_nathan_girard_17.pdf',    '2025-03-11'),
  (11, 18, 'uploads/resumes/cv_nathan_girard.pdf',    'uploads/letters/lm_nathan_girard_18.pdf',    '2025-03-14'),
  (12, 4,  'uploads/resumes/cv_lea_fontaine.pdf',     NULL,                                         '2025-03-09'),
  (12, 6,  'uploads/resumes/cv_lea_fontaine.pdf',     'uploads/letters/lm_lea_fontaine_6.pdf',      '2025-03-17'),
  (13, 5,  'uploads/resumes/cv_maxime_lambert.pdf',   'uploads/letters/lm_maxime_lambert_5.pdf',    '2025-03-06'),
  (13, 26, 'uploads/resumes/cv_maxime_lambert.pdf',   NULL,                                         '2025-03-21'),
  (14, 3,  'uploads/resumes/cv_julie_petit.pdf',      'uploads/letters/lm_julie_petit_3.pdf',       '2025-03-13'),
  (14, 8,  'uploads/resumes/cv_julie_petit.pdf',      NULL,                                         '2025-03-19'),
  (15, 19, 'uploads/resumes/cv_antoine_chev.pdf',     'uploads/letters/lm_antoine_chev_19.pdf',     '2025-03-07'),
  (15, 20, 'uploads/resumes/cv_antoine_chev.pdf',     NULL,                                         '2025-03-16'),
  (16, 22, 'uploads/resumes/cv_manon_robin.pdf',      'uploads/letters/lm_manon_robin_22.pdf',      '2025-03-10'),
  (17, 14, 'uploads/resumes/cv_pierre_blanc.pdf',     NULL,                                         '2025-03-23'),
  (17, 16, 'uploads/resumes/cv_pierre_blanc.pdf',     'uploads/letters/lm_pierre_blanc_16.pdf',     '2025-03-24'),
  (18, 25, 'uploads/resumes/cv_alice_henry.pdf',      'uploads/letters/lm_alice_henry_25.pdf',      '2025-03-11'),
  (19, 10, 'uploads/resumes/cv_romain_garnier.pdf',   NULL,                                         '2025-03-18'),
  (20, 24, 'uploads/resumes/cv_lucie_faure.pdf',      'uploads/letters/lm_lucie_faure_24.pdf',      '2025-03-09'),
  (21, 23, 'uploads/resumes/cv_baptiste_mich.pdf',    'uploads/letters/lm_baptiste_mich_23.pdf',    '2025-03-20');

-- -------------------------------------------------------------
-- ÉVALUATIONS (étudiants notent les entreprises)
-- -------------------------------------------------------------
INSERT INTO Rating (idUser, idCompany, rate, comment) VALUES
  (7,  1, 5, 'Super ambiance, équipe très accueillante et projets stimulants.'),
  (8,  3, 4, 'Bonne expérience, encadrement sérieux. Locaux modernes.'),
  (9,  7, 5, 'Excellent tuteur, j\'ai beaucoup appris sur les méthodes agiles.'),
  (10, 4, 3, 'Stage correct mais peu de responsabilités données au stagiaire.'),
  (11, 8, 5, 'Immersion totale dans la cybersécurité, contacts passionnants.'),
  (12, 3, 4, 'Projets intéressants, bonne montée en compétences en data.'),
  (13, 3, 5, 'Cadre idéal pour apprendre. Missions variées et équipe sympa.'),
  (14, 4, 2, 'Tâches répétitives, peu de suivi de la part du maître de stage.'),
  (15, 9, 4, 'Belle découverte de l\'IoT. Technologies récentes et équipe dynamique.'),
  (16, 10, 3, 'Stage agréable mais court. Les missions correspondent à l\'annonce.'),
  (17, 7, 5, 'Environnement professionnel top. J\'ai pu travailler sur des projets réels.'),
  (18, 10, 4, 'Bonne expérience dans les médias, équipe créative.'),
  (3,  1, 4, 'Équipe sympa et projet varié. Je recommande.'),
  (3,  2, 5, 'Petite structure très agréable, forte autonomie et vrai impact.');

-- -------------------------------------------------------------
-- WISHLIST
-- -------------------------------------------------------------
INSERT INTO Wishlist (idUser, idOffer) VALUES
  (7,  5),
  (7,  19),
  (8,  6),
  (8,  14),
  (9,  4),
  (9,  17),
  (10, 9),
  (10, 20),
  (11, 10),
  (12, 7),
  (12, 22),
  (13, 2),
  (13, 16),
  (14, 5),
  (15, 18),
  (16, 23),
  (17, 15),
  (18, 24),
  (19, 11),
  (20, 26),
  (3,  4),
  (3,  14);

SET FOREIGN_KEY_CHECKS = 1;
