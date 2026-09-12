-- ============================================================================
-- Script SQL — Plateforme de gestion du restaurant Saveur221 (PostgreSQL)
-- ============================================================================
-- Base de données partagée entre le module Java Console (personnel interne)
-- et le module PHP Web (visiteurs, clients, gérants, administrateurs).
--
-- Conventions :
--   - Clés primaires en GENERATED ALWAYS AS IDENTITY (norme SQL moderne,
--     préférée à SERIAL).
--   - Les statuts (commande, paiement, reçu) sont modélisés en types ENUM
--     PostgreSQL, pour rester alignés avec les enums applicatifs
--     (StatutCommande, StatutPaiement côté Java et PHP).
--   - PostgreSQL n'a pas d'équivalent natif à ON UPDATE CURRENT_TIMESTAMP :
--     une fonction trigger générique set_updated_at() est définie une fois
--     puis attachée à chaque table possédant une colonne updated_at.
--   - Les colonnes "image" ne sont JAMAIS renseignées côté Java : l'upload
--     et le stockage des images sont entièrement gérés côté PHP via
--     Cloudinary. Le Java Console se contente de lire/écrire NULL dessus.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Nettoyage préalable (permet de rejouer le script à volonté en développement)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS avis CASCADE;
DROP TABLE IF EXISTS recus CASCADE;
DROP TABLE IF EXISTS paiements CASCADE;
DROP TABLE IF EXISTS factures CASCADE;
DROP TABLE IF EXISTS ligne_commandes CASCADE;
DROP TABLE IF EXISTS commandes CASCADE;
DROP TABLE IF EXISTS produits CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS clients CASCADE;
DROP TABLE IF EXISTS utilisateurs CASCADE;
DROP TABLE IF EXISTS roles CASCADE;

DROP TYPE IF EXISTS statut_commande;
DROP TYPE IF EXISTS statut_paiement;
DROP TYPE IF EXISTS type_paiement_recu;

DROP FUNCTION IF EXISTS set_updated_at() CASCADE;

-- ----------------------------------------------------------------------------
-- Types énumérés
-- ----------------------------------------------------------------------------
-- Doit rester strictement synchronisé avec l'enum StatutCommande
-- (Java : com.saveur221.enums.StatutCommande / PHP : App\Enums\StatutCommande).
CREATE TYPE statut_commande AS ENUM (
    'EN_ATTENTE',
    'EN_PREPARATION',
    'PRETE',
    'RETIREE',
    'ANNULEE'
);

-- Ajouté : manquait dans la version précédente alors que Commande,
-- CommandeRepository et App\Enums\StatutPaiement l'utilisent tous.
-- Indépendant de statut_commande : une commande RETIREE peut rester
-- IMPAYE si le client règle plus tard.
CREATE TYPE statut_paiement AS ENUM (
    'IMPAYE',
    'PARTIEL',
    'PAYEE'
);

-- PARTIEL si un solde reste après le paiement associé, TOTAL sinon.
CREATE TYPE type_paiement_recu AS ENUM (
    'PARTIEL',
    'TOTAL'
);

-- ----------------------------------------------------------------------------
-- Fonction trigger générique : maintient updated_at à jour automatiquement
-- ----------------------------------------------------------------------------
CREATE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ============================================================================
-- Table : roles
-- ============================================================================
CREATE TABLE roles (
    id  INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nom VARCHAR(20) NOT NULL UNIQUE
);

COMMENT ON COLUMN roles.nom IS 'ADMIN ou GERANT — doit rester synchronisé avec l''enum applicatif';

-- ============================================================================
-- Table : utilisateurs
-- ============================================================================
CREATE TABLE utilisateurs (
    id            INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nom           VARCHAR(100) NOT NULL,
    prenom        VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe  VARCHAR(255) NOT NULL,
    actif         BOOLEAN NOT NULL DEFAULT TRUE,
    role_id       INT NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_utilisateurs_role
        FOREIGN KEY (role_id) REFERENCES roles(id)
        ON DELETE RESTRICT
);

COMMENT ON COLUMN utilisateurs.email IS 'Règle métier : unicité obligatoire';
COMMENT ON COLUMN utilisateurs.mot_de_passe IS 'Haché (BCrypt côté PHP et côté Java) — jamais stocké en clair';
COMMENT ON COLUMN utilisateurs.actif IS 'Un compte désactivé ne peut pas se connecter';

CREATE TRIGGER trg_utilisateurs_updated_at
    BEFORE UPDATE ON utilisateurs
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

-- ============================================================================
-- Table : clients
-- ============================================================================
CREATE TABLE clients (
    id            INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nom           VARCHAR(100) NOT NULL,
    prenom        VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe  VARCHAR(255) NOT NULL,
    telephone     VARCHAR(20)  NULL,
    adresse       VARCHAR(255) NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN clients.email IS 'Règle métier : unicité obligatoire';
COMMENT ON COLUMN clients.mot_de_passe IS 'Haché — jamais stocké en clair';

CREATE TRIGGER trg_clients_updated_at
    BEFORE UPDATE ON clients
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

-- ============================================================================
-- Table : categories
-- ============================================================================
-- image et couleur ajoutées : manquaient dans la version précédente alors
-- que Categorie (modèle), CategorieRepository et CategorieService (génération
-- de couleur aléatoire avec vérification d'unicité) en dépendent tous les
-- trois. couleur porte une contrainte UNIQUE, qui sert de filet de sécurité
-- final derrière la vérification applicative CategorieRepository::
-- couleurDejaUtilisee().
-- ============================================================================
CREATE TABLE categories (
    id          INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nom         VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    image       VARCHAR(500) NULL,
    couleur     VARCHAR(7)   NULL UNIQUE,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN categories.image IS 'URL Cloudinary — renseignée uniquement côté PHP, toujours NULL côté Java';
COMMENT ON COLUMN categories.couleur IS 'Code hexadécimal unique (ex: #8B1424), généré aléatoirement à la création si non fourni';

CREATE TRIGGER trg_categories_updated_at
    BEFORE UPDATE ON categories
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

-- ============================================================================
-- Table : produits
-- ============================================================================
CREATE TABLE produits (
    id              INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    libelle         VARCHAR(150) NOT NULL,
    description     VARCHAR(500) NULL,
    prix            DECIMAL(10, 2) NOT NULL,
    quantite_stock  INT NOT NULL DEFAULT 0,
    seuil_alerte    INT NOT NULL DEFAULT 5,
    disponible      BOOLEAN NOT NULL DEFAULT TRUE,
    image           VARCHAR(500) NULL,
    categorie_id    INT NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_produits_categorie
        FOREIGN KEY (categorie_id) REFERENCES categories(id)
        ON DELETE RESTRICT
);

COMMENT ON COLUMN produits.seuil_alerte IS 'En dessous de ce seuil : produit signalé "stock faible"';
COMMENT ON COLUMN produits.disponible IS 'Recalculé automatiquement : false si quantite_stock = 0';
COMMENT ON COLUMN produits.image IS 'URL Cloudinary — renseignée uniquement côté PHP, toujours NULL côté Java';

CREATE TRIGGER trg_produits_updated_at
    BEFORE UPDATE ON produits
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

-- ============================================================================
-- Table : commandes
-- ============================================================================
-- numero_commande et statut_paiement ajoutés : manquaient dans la version
-- précédente alors que Commande, CommandeRepository et CommandeService
-- (génération du numéro type CMD-2026-000231, gestion du statut de
-- paiement indépendant du statut de préparation) en dépendent tous les
-- trois.
-- ============================================================================
CREATE TABLE commandes (
    id                INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    numero_commande   VARCHAR(50) NOT NULL UNIQUE,
    client_id         INT NOT NULL,
    date_commande     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut            statut_commande NOT NULL DEFAULT 'EN_ATTENTE',
    statut_paiement   statut_paiement NOT NULL DEFAULT 'IMPAYE',
    montant_total     DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_commandes_client
        FOREIGN KEY (client_id) REFERENCES clients(id)
        ON DELETE RESTRICT
);

COMMENT ON COLUMN commandes.numero_commande IS 'Référence lisible générée en couche service, ex: CMD-2026-000231';
COMMENT ON COLUMN commandes.statut_paiement IS 'Indépendant de statut : une commande RETIREE peut rester IMPAYE';

CREATE INDEX idx_commandes_statut ON commandes(statut);
CREATE INDEX idx_commandes_statut_paiement ON commandes(statut_paiement);

CREATE TRIGGER trg_commandes_updated_at
    BEFORE UPDATE ON commandes
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

-- ============================================================================
-- Table : factures
-- ============================================================================
CREATE TABLE factures (
    id              INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    commande_id     INT NOT NULL UNIQUE,
    numero_facture  VARCHAR(50) NOT NULL UNIQUE,
    montant_total   DECIMAL(10, 2) NOT NULL,
    date_emission   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_factures_commande
        FOREIGN KEY (commande_id) REFERENCES commandes(id)
        ON DELETE CASCADE
);

COMMENT ON COLUMN factures.commande_id IS 'Une facture par commande (1-1)';
COMMENT ON COLUMN factures.numero_facture IS 'Référence lisible générée en couche service, ex: FAC-2026-000104';
COMMENT ON COLUMN factures.montant_total IS 'Montant de la commande figé au moment de l''émission';

-- ============================================================================
-- Table : ligne_commandes
-- ============================================================================
CREATE TABLE ligne_commandes (
    id              INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    commande_id     INT NOT NULL,
    produit_id      INT NOT NULL,
    quantite        INT NOT NULL,
    prix_unitaire   DECIMAL(10, 2) NOT NULL,

    CONSTRAINT fk_lignes_commande
        FOREIGN KEY (commande_id) REFERENCES commandes(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_lignes_produit
        FOREIGN KEY (produit_id) REFERENCES produits(id)
        ON DELETE RESTRICT,

    CONSTRAINT chk_lignes_quantite_positive CHECK (quantite > 0)
);

COMMENT ON COLUMN ligne_commandes.prix_unitaire IS 'Prix du produit figé au moment de la commande';

-- ============================================================================
-- Table : paiements
-- ============================================================================
CREATE TABLE paiements (
    id              INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    commande_id     INT NOT NULL,
    montant         DECIMAL(10, 2) NOT NULL,
    date_paiement   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_paiements_commande
        FOREIGN KEY (commande_id) REFERENCES commandes(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_paiements_montant_positif CHECK (montant > 0)
);

-- Remarque : la règle "un paiement ne peut jamais dépasser le montant
-- restant" ne peut pas être exprimée par un simple CHECK (elle dépend de
-- la somme des paiements déjà enregistrés) — elle est vérifiée en amont
-- dans PaiementService avant l'insertion.

-- ============================================================================
-- Table : recus
-- ============================================================================
CREATE TABLE recus (
    id              INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    paiement_id     INT NOT NULL UNIQUE,
    numero_recu     VARCHAR(50) NOT NULL UNIQUE,
    type_paiement   type_paiement_recu NOT NULL,
    montant         DECIMAL(10, 2) NOT NULL,
    date_emission   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_recus_paiement
        FOREIGN KEY (paiement_id) REFERENCES paiements(id)
        ON DELETE CASCADE
);

COMMENT ON COLUMN recus.paiement_id IS 'Un reçu par paiement, sans exception (1-1)';
COMMENT ON COLUMN recus.numero_recu IS 'Référence lisible générée en couche service, ex: REC-2026-000088';
COMMENT ON COLUMN recus.type_paiement IS 'PARTIEL si un solde reste après ce paiement, TOTAL sinon';
COMMENT ON COLUMN recus.montant IS 'Copie du montant du paiement associé';

-- ============================================================================
-- Table : avis
-- ============================================================================
-- Modifiée : rattachée à produit_id plutôt qu'à commande_id. Un avis porte
-- désormais sur un produit du catalogue, pas sur une commande précise — un
-- client ayant commandé le même produit plusieurs fois ne peut toujours
-- laisser qu'un seul avis dessus (contrainte UNIQUE sur (client_id,
-- produit_id), vérifiée aussi en amont par AvisRepository::
-- existeParClientEtProduit()). L'éligibilité au dépôt (le produit doit
-- avoir été commandé dans une commande RETIREE) est vérifiée séparément
-- par AvisRepository::aCommandeEtRetireProduit(), via une jointure sur
-- ligne_commandes — pas besoin de clé étrangère directe vers commandes ici.
-- ============================================================================
CREATE TABLE avis (
    id              INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    produit_id      INT NOT NULL,
    client_id       INT NOT NULL,
    note            SMALLINT NOT NULL,
    commentaire     TEXT NULL,
    date_avis       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_avis_produit
        FOREIGN KEY (produit_id) REFERENCES produits(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_avis_client
        FOREIGN KEY (client_id) REFERENCES clients(id)
        ON DELETE CASCADE,

    CONSTRAINT uq_avis_client_produit UNIQUE (client_id, produit_id),

    CONSTRAINT chk_avis_note_valide CHECK (note BETWEEN 1 AND 5)
);

COMMENT ON COLUMN avis.produit_id IS 'Un seul avis autorisé par couple client/produit (voir uq_avis_client_produit)';

CREATE INDEX idx_avis_produit ON avis(produit_id);

-- ============================================================================
-- Données de référence minimales
-- ============================================================================
INSERT INTO roles (nom) VALUES ('ADMIN'), ('GERANT');

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, actif, role_id)
VALUES (
    'Diop',
    'Awa',
    'admin@saveur221.sn',
    '$2y$10$Ut5iDSErjKJWDptNBq1rsuZFIQmrsh4PNvbCqDVK8TV5wNhjaqyD.', -- bcrypt("password123")
    TRUE,
    (SELECT id FROM roles WHERE nom = 'ADMIN')
);
