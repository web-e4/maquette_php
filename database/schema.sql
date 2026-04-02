-- =============================================================
-- SCHEMA — Gigastage
-- Creates all tables required by src/Models/*.php
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS Wishlist;
DROP TABLE IF EXISTS Rating;
DROP TABLE IF EXISTS Application;
DROP TABLE IF EXISTS Offer;
DROP TABLE IF EXISTS Profile;
DROP TABLE IF EXISTS User_;
DROP TABLE IF EXISTS Company;
DROP TABLE IF EXISTS Role;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE Role (
    idRole INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE User_ (
    idUser INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    statusUser TINYINT(1) NOT NULL DEFAULT 1,
    idRole INT NOT NULL,
    idPilot INT NULL,
    CONSTRAINT fk_user_role
        FOREIGN KEY (idRole) REFERENCES Role(idRole)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_user_pilot
        FOREIGN KEY (idPilot) REFERENCES User_(idUser)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Profile (
    idProfile INT AUTO_INCREMENT PRIMARY KEY,
    surname VARCHAR(100) NOT NULL,
    firstName VARCHAR(100) NOT NULL,
    idUser INT NOT NULL UNIQUE,
    CONSTRAINT fk_profile_user
        FOREIGN KEY (idUser) REFERENCES User_(idUser)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Company (
    idCompany INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NULL,
    website VARCHAR(255) NULL,
    statusCompany TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Offer (
    idOffer INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    missions TEXT NOT NULL,
    location VARCHAR(150) NOT NULL,
    durationInWeeks INT NOT NULL,
    startDate DATE NOT NULL,
    statusOffer TINYINT(1) NOT NULL DEFAULT 1,
    idCompany INT NOT NULL,
    createdAt DATE NOT NULL DEFAULT (CURRENT_DATE),
    CONSTRAINT fk_offer_company
        FOREIGN KEY (idCompany) REFERENCES Company(idCompany)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Application (
    idApplication INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT NOT NULL,
    idOffer INT NOT NULL,
    resume VARCHAR(255) NULL,
    motivationLetter VARCHAR(255) NULL,
    applicationDate DATE NOT NULL DEFAULT (CURRENT_DATE),
    UNIQUE KEY uq_application_user_offer (idUser, idOffer),
    CONSTRAINT fk_application_user
        FOREIGN KEY (idUser) REFERENCES User_(idUser)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_application_offer
        FOREIGN KEY (idOffer) REFERENCES Offer(idOffer)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Rating (
    idRating INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT NOT NULL,
    idCompany INT NOT NULL,
    rate TINYINT NOT NULL,
    comment TEXT NULL,
    UNIQUE KEY uq_rating_user_company (idUser, idCompany),
    CONSTRAINT chk_rating_rate CHECK (rate BETWEEN 1 AND 5),
    CONSTRAINT fk_rating_user
        FOREIGN KEY (idUser) REFERENCES User_(idUser)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_rating_company
        FOREIGN KEY (idCompany) REFERENCES Company(idCompany)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Wishlist (
    idWishlist INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT NOT NULL,
    idOffer INT NOT NULL,
    startDate DATE NOT NULL DEFAULT (CURRENT_DATE),
    UNIQUE KEY uq_wishlist_user_offer (idUser, idOffer),
    CONSTRAINT fk_wishlist_user
        FOREIGN KEY (idUser) REFERENCES User_(idUser)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_offer
        FOREIGN KEY (idOffer) REFERENCES Offer(idOffer)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO Role (role) VALUES
('Admin'),
('Pilote'),
('Etudiant');
