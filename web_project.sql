-- 1. Création de la base de données
CREATE DATABASE IF NOT EXISTS italie_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE italie_db;

-- 2. Création de la table des Actualités (News)
CREATE TABLE IF NOT EXISTS News (
    news_id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    resume TEXT NOT NULL,
    contenu TEXT NOT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Création de la table des Inscrits à la Newsletter (Internaute)
CREATE TABLE IF NOT EXISTS Internaute (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 4. Création de la table Administrateur (Pour se connecter)
CREATE TABLE IF NOT EXISTS Admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- 5. INSERTION DES DONNÉES DE TEST

-- Création du compte Admin
-- Login : admin
-- Mot de passe : admin123
INSERT INTO Admin (login, password) VALUES ('admin', 'admin123');
INSERT INTO admin (login, password) VALUES ('user3', 'user123');
-- Ajout de quelques news pour que le site ne soit pas vide au début
INSERT INTO News (titre, resume, contenu, date_publication) VALUES 
('Ouverture du Carnaval de Venise', 'Le célèbre carnaval a débuté ce matin avec le vol de l\'ange.', 'Le Carnaval de Venise est une fête traditionnelle italienne remontant au Moyen Âge. Les couleurs, les masques et les gondoles sont au rendez-vous pour cette nouvelle édition qui promet d\'être spectaculaire.', NOW()),

('Match décisif : Juventus vs Milan', 'Un choc au sommet de la Serie A ce week-end.', 'La Juventus de Turin recevra l\'AC Milan ce samedi soir. Un match crucial pour la course au titre. Les supporters sont attendus en nombre au stade.', NOW()),

('Découverte culinaire : La vraie Pizza', 'Naples célèbre la fête de la pizza Margherita.', 'Saviez-vous que la pizza Margherita a été créée en l\'honneur de la reine Marguerite de Savoie ? Elle reprend les couleurs du drapeau italien : basilic (vert), mozzarella (blanc) et tomate (rouge).', NOW());

CREATE TABLE Contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP
);
