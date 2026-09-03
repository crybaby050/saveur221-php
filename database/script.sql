/* ============================================================================
   Script SQL — Plateforme de gestion du restaurant Saveur221 (PostgreSQL)
   ============================================================================
   Base de données partagée entre le module Java Console (personnel interne)
   et le module PHP Web (visiteurs, clients, gérants, administrateurs).

   Conventions :
     - Clés primaires en GENERATED ALWAYS AS IDENTITY.
     - Les statuts (commande, paiement, reçu) sont modélisés en types ENUM
       PostgreSQL, alignés avec les enums applicatifs des deux modules.
     - PostgreSQL n'a pas d'équivalent natif à ON UPDATE CURRENT_TIMESTAMP :
       une fonction trigger générique set_updated_at() est définie une fois
       puis attachée à chaque table possédant une colonne updated_at.
     - Les colonnes "image" (produits, categories) et "couleur" (categories)
       ne sont jamais renseignées côté Java : leur gestion (upload Cloudinary,
       choix de colorimétrie) est entièrement portée par le module PHP Web.
   ============================================================================ */

/* ----------------------------------------------------------------------------
   Nettoyage préalable (permet de rejouer le script à volonté en développement)
   ---------------------------------------------------------------------------- */
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
DROP TYPE IF EXISTS statut_paiement_commande;
DROP TYPE IF EXISTS type_paiement_recu;

DROP FUNCTION IF EXISTS set_updated_at() CASCADE;

/* ----------------------------------------------------------------------------
   Types énumérés
   ---------------------------------------------------------------------------- */

/* Doit rester strictement synchronisé avec l'enum StatutCommande des deux
   modules applicatifs. */
CREATE TYPE statut_commande AS ENUM (
    'EN_ATTENTE',
    'EN_PREPARATION',
    'PRETE',
    'RETIREE',
    'ANNULEE'
);

/* Indépendant du statut de préparation : suit uniquement l'avancement du
   règlement financier d'une commande. */
CREATE TYPE statut_paiement_commande AS ENUM (
    'IMPAYE',
    'PARTIEL',
    'PAYEE'
);

/* PARTIEL si un solde reste après le paiement associé, TOTAL sinon. */
CREATE TYPE type_paiement_recu AS ENUM (
    'PARTIEL',
    'TOTAL'
);

/* ----------------------------------------------------------------------------
   Fonction trigger générique : maintient updated_at à jour automatiquement
   ---------------------------------------------------------------------------- */
CREATE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

/* ============================================================================
   Table : roles
   ============================================================================
   Référentiel des rôles internes (ADMIN, GERANT). Modélisée en table plutôt
   qu'en simple colonne ENUM pour rester alignée avec le diagramme de classes,
   où Role est une entité à part entière.
   ============================================================================ */
CREATE TABLE roles (
    id  INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nom VARCHAR(20) NOT NULL UNIQUE
);

COMMENT ON COLUMN roles.nom IS 'ADMIN ou GERANT — doit rester synchronisé avec l''enum applicatif';

/* ============================================================================
   Table : utilisateurs
   ============================================================================
   Personnel interne du restaurant (Gérant, Administrateur). Distincte de la
   table "clients" : deux populations d'acteurs différentes, avec des cycles
   de vie et des droits différents.
   ============================================================================ */
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
COMMENT ON COLUMN utilisateurs.mot_de_passe IS 'Haché en BCrypt côté PHP et côté Java — jamais stocké en clair';
COMMENT ON COLUMN utilisateurs.actif IS 'Un compte désactivé ne peut pas se connecter';

CREATE TRIGGER trg_utilisateurs_updated_at
    BEFORE UPDATE ON utilisateurs
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

/* ============================================================================
   Table : clients
   ============================================================================
   Visiteurs ayant créé un compte sur la plateforme PHP Web. Le Java Console
   ne crée jamais de client (hors périmètre), mais lit et référence cette
   table pour les commandes, paiements et avis.
   ============================================================================ */
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

/* ============================================================================
   Table : categories
   ============================================================================
   image et couleur sont exclusivement gérées côté PHP Web (illustration
   Cloudinary et identité visuelle de la catégorie dans l'interface) — le
   Java Console les lit mais ne les écrit jamais.
   ============================================================================ */
CREATE TABLE categories (
    id          INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nom         VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    image       VARCHAR(500) NULL,
    couleur     VARCHAR(7)   NULL UNIQUE,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN categories.image IS 'URL Cloudinary — gérée uniquement côté PHP, toujours NULL côté Java';
COMMENT ON COLUMN categories.couleur IS 'Code hexadécimal (ex: #8B1424) — une couleur ne peut être attribuée qu''à une seule catégorie à la fois';

CREATE TRIGGER trg_categories_updated_at
    BEFORE UPDATE ON categories
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

/* ============================================================================
   Table : produits
   ============================================================================
   La colonne "image" reste NULL tant qu'un gérant ne l'a pas renseignée
   depuis l'espace PHP. Le Java Console ne l'affiche pas et ne l'écrit jamais.
   ============================================================================ */
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
COMMENT ON COLUMN produits.image IS 'URL Cloudinary — gérée uniquement côté PHP, toujours NULL côté Java';

/* ON DELETE RESTRICT est un filet de sécurité au niveau base ; la règle
   métier "une catégorie contenant des produits ne peut pas être supprimée"
   est de toute façon vérifiée en amont dans la couche service. */

CREATE TRIGGER trg_produits_updated_at
    BEFORE UPDATE ON produits
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

/* ============================================================================
   Table : commandes
   ============================================================================
   numero_commande et statut_paiement ont été ajoutés après la conception
   initiale : le premier pour disposer d'une référence lisible (utilisée
   par les factures et par la recherche), le second pour suivre le règlement
   financier indépendamment de l'avancement de la préparation.
   ============================================================================ */
CREATE TABLE commandes (
    id                INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    numero_commande   VARCHAR(50) NOT NULL UNIQUE,
    client_id         INT NOT NULL,
    date_commande     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut            statut_commande NOT NULL DEFAULT 'EN_ATTENTE',
    statut_paiement   statut_paiement_commande NOT NULL DEFAULT 'IMPAYE',
    montant_total     DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_commandes_client
        FOREIGN KEY (client_id) REFERENCES clients(id)
        ON DELETE RESTRICT
);

COMMENT ON COLUMN commandes.numero_commande IS 'Référence lisible générée en couche service, ex: CMD-2026-000231';
COMMENT ON COLUMN commandes.statut_paiement IS 'IMPAYE à la création, mis à jour à chaque paiement enregistré';

CREATE INDEX idx_commandes_statut ON commandes(statut);
CREATE INDEX idx_commandes_statut_paiement ON commandes(statut_paiement);

CREATE TRIGGER trg_commandes_updated_at
    BEFORE UPDATE ON commandes
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

/* ============================================================================
   Table : factures
   ============================================================================
   Une facture est émise une seule fois par commande (relation 1-1), à la
   création de celle-ci. Le montant est dupliqué depuis commandes.montant_total
   au moment de l'émission, pour figer sa valeur dans le temps.
   Portée actuelle : générée uniquement pour les commandes créées côté Java
   Console (vente au comptoir).
   ============================================================================ */
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

COMMENT ON COLUMN factures.numero_facture IS 'Référence lisible générée en couche service, ex: FAC-2026-000104';
COMMENT ON COLUMN factures.montant_total IS 'Montant de la commande figé au moment de l''émission';

/* ============================================================================
   Table : ligne_commandes
   ============================================================================
   prix_unitaire est dupliqué depuis produits.prix au moment de la commande
   (historisation) : une modification ultérieure du prix du produit ne doit
   jamais altérer le montant des commandes déjà passées.
   ============================================================================ */
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

/* ============================================================================
   Table : paiements
   ============================================================================ */
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

/* La règle "un paiement ne peut jamais dépasser le montant restant" dépend
   de la somme des paiements déjà enregistrés : elle ne peut pas être
   exprimée par un simple CHECK et est vérifiée en couche service. */

/* ============================================================================
   Table : recus
   ============================================================================
   Un reçu est émis systématiquement à chaque paiement, partiel ou total
   (relation 1-1 stricte avec paiements). type_paiement est déterminé en
   couche service en comparant le total déjà payé au montant de la commande.
   ============================================================================ */
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

COMMENT ON COLUMN recus.numero_recu IS 'Référence lisible générée en couche service, ex: REC-2026-000088';
COMMENT ON COLUMN recus.type_paiement IS 'PARTIEL si un solde reste après ce paiement, TOTAL sinon';

/* ============================================================================
   Table : avis
   ============================================================================
   La contrainte UNIQUE sur commande_id fait respecter directement au niveau
   base la règle "un client ne peut donner qu'un seul avis par commande".
   ============================================================================ */
CREATE TABLE avis (
    id              INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    commande_id     INT NOT NULL UNIQUE,
    client_id       INT NOT NULL,
    note            SMALLINT NOT NULL,
    commentaire     TEXT NULL,
    date_avis       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_avis_commande
        FOREIGN KEY (commande_id) REFERENCES commandes(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_avis_client
        FOREIGN KEY (client_id) REFERENCES clients(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_avis_note_valide CHECK (note BETWEEN 1 AND 5)
);

/* ============================================================================
   Données de référence minimales
   ============================================================================
   Seuls les rôles sont indispensables au fonctionnement de l'application.
   Le premier compte ADMIN doit être créé séparément (script de seed dédié
   ou insertion manuelle), avec un mot de passe haché en BCrypt — jamais
   codé en dur dans ce script de structure.
   ============================================================================ */

INSERT INTO roles (nom) VALUES ('ADMIN'), ('GERANT');