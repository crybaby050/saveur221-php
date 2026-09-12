-- ============================================================================
-- Script de données de test — Saveur221
-- À exécuter APRÈS le script de schéma. Suppose que roles (ADMIN, GERANT)
-- et le compte admin@saveur221.sn existent déjà.
-- Mot de passe de test pour tous les comptes ci-dessous : password123
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Utilisateur interne supplémentaire (Gérant)
-- ----------------------------------------------------------------------------
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, actif, role_id)
VALUES (
    'Sow',
    'Khadim',
    'gerant@saveur221.sn',
    '$2y$10$Ut5iDSErjKJWDptNBq1rsuZFIQmrsh4PNvbCqDVK8TV5wNhjaqyD.',
    TRUE,
    (SELECT id FROM roles WHERE nom = 'GERANT')
);

-- ----------------------------------------------------------------------------
-- Catégories
-- ----------------------------------------------------------------------------
INSERT INTO categories (nom, description, image, couleur) VALUES
('Entrées', 'Pour bien commencer le repas', 'https://images.unsplash.com/photo-1541014741259-de529411b96a?auto=format&fit=crop&w=800&q=80', '#E63946'),
('Plats principaux', 'Nos classiques sénégalais, mijotés avec passion', 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=800&q=80', '#F4A261'),
('Poissons & Fruits de mer', 'Fraîcheur de la mer, grillée ou mijotée', 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?auto=format&fit=crop&w=800&q=80', '#2A9D8F'),
('Grillades', 'Viandes braisées au feu de bois', 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?auto=format&fit=crop&w=800&q=80', '#E76F51'),
('Desserts', 'La touche sucrée pour finir en beauté', 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=800&q=80', '#E9C46A'),
('Boissons', 'Jus naturels et boissons locales', 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=800&q=80', '#264653');

-- ----------------------------------------------------------------------------
-- Produits
-- ----------------------------------------------------------------------------

-- Entrées
INSERT INTO produits (libelle, description, prix, quantite_stock, seuil_alerte, disponible, image, categorie_id) VALUES
('Pastels', 'Beignets croustillants farcis au poisson', 1500, 40, 8, TRUE, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Entrées')),
('Fataya viande', 'Chaussons frits farcis à la viande épicée', 1000, 35, 8, TRUE, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Entrées')),
('Salade Dakaroise', 'Salade fraîche, avocat, œuf et crevettes', 2000, 20, 5, TRUE, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Entrées'));

-- Plats principaux
INSERT INTO produits (libelle, description, prix, quantite_stock, seuil_alerte, disponible, image, categorie_id) VALUES
('Thiéboudienne rouge', 'Riz au poisson, légumes et sauce tomate, le plat national', 3500, 25, 5, TRUE, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Plats principaux')),
('Thiéboudienne blanc', 'Riz au poisson sans tomate, sauce claire aux légumes', 3500, 20, 5, TRUE, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Plats principaux')),
('Yassa poulet', 'Poulet mariné aux oignons et citron', 3000, 30, 6, TRUE, 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Plats principaux')),
('Mafé', 'Ragoût de viande à la pâte d''arachide', 3200, 25, 5, TRUE, 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Plats principaux')),
('Domoda', 'Ragoût de bœuf à la sauce arachide et tomate', 3000, 15, 5, TRUE, 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Plats principaux')),
('Soupou kandia', 'Sauce gombo, poisson fumé et viande', 3200, 10, 5, TRUE, 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Plats principaux'));

-- Poissons & Fruits de mer
INSERT INTO produits (libelle, description, prix, quantite_stock, seuil_alerte, disponible, image, categorie_id) VALUES
('Poisson braisé', 'Poisson entier grillé, sauce oignons', 4000, 18, 5, TRUE, 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Poissons & Fruits de mer')),
('Capitaine grillé', 'Filet de capitaine grillé, accompagné de légumes', 4500, 12, 5, TRUE, 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Poissons & Fruits de mer')),
('Crevettes sautées', 'Crevettes sautées à l''ail et au piment', 5000, 8, 10, TRUE, 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Poissons & Fruits de mer')),
('Yeté farci', 'Thiof farci, spécialité de la maison', 4800, 0, 5, FALSE, 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Poissons & Fruits de mer'));

-- Grillades
INSERT INTO produits (libelle, description, prix, quantite_stock, seuil_alerte, disponible, image, categorie_id) VALUES
('Poulet braisé', 'Demi-poulet braisé, sauce piquante', 3500, 22, 5, TRUE, 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Grillades')),
('Brochettes de bœuf', 'Brochettes marinées grillées au charbon', 3800, 16, 5, TRUE, 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Grillades')),
('Dibiterie', 'Mouton grillé à la sénégalaise, moutarde et oignons', 4200, 14, 5, TRUE, 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Grillades'));

-- Desserts
INSERT INTO produits (libelle, description, prix, quantite_stock, seuil_alerte, disponible, image, categorie_id) VALUES
('Thiakry', 'Couscous de mil sucré au yaourt et lait', 1500, 30, 6, TRUE, 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Desserts')),
('Sombi', 'Riz au lait parfumé à la vanille', 1200, 25, 5, TRUE, 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Desserts')),
('Beignets sucrés', 'Beignets moelleux sucrés, faits maison', 1000, 40, 8, TRUE, 'https://images.unsplash.com/photo-1509365465985-25d11c17e812?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Desserts'));

-- Boissons
INSERT INTO produits (libelle, description, prix, quantite_stock, seuil_alerte, disponible, image, categorie_id) VALUES
('Bissap', 'Jus d''hibiscus glacé, sucré à la menthe', 1000, 50, 10, TRUE, 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Boissons')),
('Bouye', 'Jus de pain de singe (baobab)', 1200, 40, 8, TRUE, 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Boissons')),
('Gingembre (Gnamakoudji)', 'Jus de gingembre frais et pimenté', 1000, 45, 8, TRUE, 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Boissons')),
('Café Touba', 'Café traditionnel épicé au poivre de Guinée', 800, 60, 10, TRUE, 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Boissons')),
('Eau minérale', 'Bouteille 50cl', 500, 100, 20, TRUE, 'https://images.unsplash.com/photo-1560023907-5f339617ea30?auto=format&fit=crop&w=800&q=80', (SELECT id FROM categories WHERE nom = 'Boissons'));

-- ----------------------------------------------------------------------------
-- Clients
-- ----------------------------------------------------------------------------
INSERT INTO clients (nom, prenom, email, mot_de_passe, telephone, adresse) VALUES
('Ndiaye', 'Fatou', 'fatou.ndiaye@example.sn', '$2y$10$Ut5iDSErjKJWDptNBq1rsuZFIQmrsh4PNvbCqDVK8TV5wNhjaqyD.', '77 123 45 67', 'Sacré-Cœur 3, Dakar'),
('Diallo', 'Moussa', 'moussa.diallo@example.sn', '$2y$10$Ut5iDSErjKJWDptNBq1rsuZFIQmrsh4PNvbCqDVK8TV5wNhjaqyD.', '76 234 56 78', 'Parcelles Assainies U15, Dakar'),
('Ba', 'Aïssatou', 'aissatou.ba@example.sn', '$2y$10$Ut5iDSErjKJWDptNBq1rsuZFIQmrsh4PNvbCqDVK8TV5wNhjaqyD.', '70 345 67 89', 'Mermoz, Dakar'),
('Sarr', 'Ibrahima', 'ibrahima.sarr@example.sn', '$2y$10$Ut5iDSErjKJWDptNBq1rsuZFIQmrsh4PNvbCqDVK8TV5wNhjaqyD.', '77 456 78 90', 'Ouakam, Dakar'),
('Fall', 'Mariama', 'mariama.fall@example.sn', '$2y$10$Ut5iDSErjKJWDptNBq1rsuZFIQmrsh4PNvbCqDVK8TV5wNhjaqyD.', '78 567 89 01', 'Yoff, Dakar'),
('Gueye', 'Cheikh', 'cheikh.gueye@example.sn', '$2y$10$Ut5iDSErjKJWDptNBq1rsuZFIQmrsh4PNvbCqDVK8TV5wNhjaqyD.', '76 678 90 12', 'Grand Yoff, Dakar'),
('Diouf', 'Astou', 'astou.diouf@example.sn', '$2y$10$Ut5iDSErjKJWDptNBq1rsuZFIQmrsh4PNvbCqDVK8TV5wNhjaqyD.', '70 789 01 23', 'Liberté 6, Dakar'),
('Seck', 'Modou', 'modou.seck@example.sn', '$2y$10$Ut5iDSErjKJWDptNBq1rsuZFIQmrsh4PNvbCqDVK8TV5wNhjaqyD.', '77 890 12 34', 'Point E, Dakar');

-- ----------------------------------------------------------------------------
-- Commandes (scénarios variés : statuts, paiements, dates)
-- ----------------------------------------------------------------------------
INSERT INTO commandes (numero_commande, client_id, date_commande, statut, statut_paiement, montant_total) VALUES
('CMD-2026-000001', (SELECT id FROM clients WHERE email = 'fatou.ndiaye@example.sn'), CURRENT_TIMESTAMP - INTERVAL '10 days', 'RETIREE', 'PAYEE', 9000.00),
('CMD-2026-000002', (SELECT id FROM clients WHERE email = 'moussa.diallo@example.sn'), CURRENT_TIMESTAMP - INTERVAL '3 days', 'EN_PREPARATION', 'PARTIEL', 7800.00),
('CMD-2026-000003', (SELECT id FROM clients WHERE email = 'aissatou.ba@example.sn'), CURRENT_TIMESTAMP - INTERVAL '1 day', 'EN_ATTENTE', 'IMPAYE', 5700.00),
('CMD-2026-000004', (SELECT id FROM clients WHERE email = 'ibrahima.sarr@example.sn'), CURRENT_TIMESTAMP - INTERVAL '3 hours', 'PRETE', 'IMPAYE', 4500.00),
('CMD-2026-000005', (SELECT id FROM clients WHERE email = 'mariama.fall@example.sn'), CURRENT_TIMESTAMP - INTERVAL '20 days', 'RETIREE', 'PAYEE', 7500.00),
('CMD-2026-000006', (SELECT id FROM clients WHERE email = 'cheikh.gueye@example.sn'), CURRENT_TIMESTAMP - INTERVAL '5 days', 'ANNULEE', 'IMPAYE', 4000.00),
('CMD-2026-000007', (SELECT id FROM clients WHERE email = 'astou.diouf@example.sn'), CURRENT_TIMESTAMP - INTERVAL '6 days', 'RETIREE', 'PARTIEL', 6700.00),
('CMD-2026-000008', (SELECT id FROM clients WHERE email = 'modou.seck@example.sn'), CURRENT_TIMESTAMP - INTERVAL '2 days', 'EN_ATTENTE', 'IMPAYE', 8600.00),
('CMD-2026-000009', (SELECT id FROM clients WHERE email = 'fatou.ndiaye@example.sn'), CURRENT_TIMESTAMP - INTERVAL '15 days', 'RETIREE', 'PAYEE', 5200.00),
('CMD-2026-000010', (SELECT id FROM clients WHERE email = 'mariama.fall@example.sn'), CURRENT_TIMESTAMP - INTERVAL '29 days', 'RETIREE', 'PAYEE', 6200.00);

-- ----------------------------------------------------------------------------
-- Lignes de commande
-- ----------------------------------------------------------------------------
INSERT INTO ligne_commandes (commande_id, produit_id, quantite, prix_unitaire) VALUES
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000001'), (SELECT id FROM produits WHERE libelle = 'Thiéboudienne rouge'), 2, 3500.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000001'), (SELECT id FROM produits WHERE libelle = 'Bissap'), 2, 1000.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000002'), (SELECT id FROM produits WHERE libelle = 'Yassa poulet'), 1, 3000.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000002'), (SELECT id FROM produits WHERE libelle = 'Mafé'), 1, 3200.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000002'), (SELECT id FROM produits WHERE libelle = 'Café Touba'), 2, 800.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000003'), (SELECT id FROM produits WHERE libelle = 'Pastels'), 3, 1500.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000003'), (SELECT id FROM produits WHERE libelle = 'Bouye'), 1, 1200.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000004'), (SELECT id FROM produits WHERE libelle = 'Poulet braisé'), 1, 3500.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000004'), (SELECT id FROM produits WHERE libelle = 'Eau minérale'), 2, 500.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000005'), (SELECT id FROM produits WHERE libelle = 'Capitaine grillé'), 1, 4500.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000005'), (SELECT id FROM produits WHERE libelle = 'Thiakry'), 2, 1500.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000006'), (SELECT id FROM produits WHERE libelle = 'Domoda'), 1, 3000.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000006'), (SELECT id FROM produits WHERE libelle = 'Bissap'), 1, 1000.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000007'), (SELECT id FROM produits WHERE libelle = 'Mafé'), 1, 3200.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000007'), (SELECT id FROM produits WHERE libelle = 'Thiéboudienne blanc'), 1, 3500.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000008'), (SELECT id FROM produits WHERE libelle = 'Brochettes de bœuf'), 2, 3800.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000008'), (SELECT id FROM produits WHERE libelle = 'Gingembre (Gnamakoudji)'), 1, 1000.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000009'), (SELECT id FROM produits WHERE libelle = 'Poisson braisé'), 1, 4000.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000009'), (SELECT id FROM produits WHERE libelle = 'Sombi'), 1, 1200.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000010'), (SELECT id FROM produits WHERE libelle = 'Dibiterie'), 1, 4200.00),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000010'), (SELECT id FROM produits WHERE libelle = 'Beignets sucrés'), 2, 1000.00);

-- ----------------------------------------------------------------------------
-- Factures (une par commande, y compris la commande annulée)
-- ----------------------------------------------------------------------------
INSERT INTO factures (commande_id, numero_facture, montant_total, date_emission) VALUES
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000001'), 'FAC-2026-000001', 9000.00, CURRENT_TIMESTAMP - INTERVAL '10 days'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000002'), 'FAC-2026-000002', 7800.00, CURRENT_TIMESTAMP - INTERVAL '3 days'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000003'), 'FAC-2026-000003', 5700.00, CURRENT_TIMESTAMP - INTERVAL '1 day'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000004'), 'FAC-2026-000004', 4500.00, CURRENT_TIMESTAMP - INTERVAL '3 hours'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000005'), 'FAC-2026-000005', 7500.00, CURRENT_TIMESTAMP - INTERVAL '20 days'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000006'), 'FAC-2026-000006', 4000.00, CURRENT_TIMESTAMP - INTERVAL '5 days'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000007'), 'FAC-2026-000007', 6700.00, CURRENT_TIMESTAMP - INTERVAL '6 days'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000008'), 'FAC-2026-000008', 8600.00, CURRENT_TIMESTAMP - INTERVAL '2 days'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000009'), 'FAC-2026-000009', 5200.00, CURRENT_TIMESTAMP - INTERVAL '15 days'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000010'), 'FAC-2026-000010', 6200.00, CURRENT_TIMESTAMP - INTERVAL '29 days');

-- ----------------------------------------------------------------------------
-- Paiements (commandes PAYEE ou PARTIEL uniquement)
-- ----------------------------------------------------------------------------
INSERT INTO paiements (commande_id, montant, date_paiement) VALUES
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000001'), 9000.00, CURRENT_TIMESTAMP - INTERVAL '10 days' + INTERVAL '20 minutes'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000002'), 4000.00, CURRENT_TIMESTAMP - INTERVAL '3 days' + INTERVAL '10 minutes'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000005'), 7500.00, CURRENT_TIMESTAMP - INTERVAL '20 days' + INTERVAL '15 minutes'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000007'), 4700.00, CURRENT_TIMESTAMP - INTERVAL '6 days' + INTERVAL '25 minutes'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000009'), 5200.00, CURRENT_TIMESTAMP - INTERVAL '15 days' + INTERVAL '10 minutes'),
((SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000010'), 6200.00, CURRENT_TIMESTAMP - INTERVAL '29 days' + INTERVAL '20 minutes');

-- ----------------------------------------------------------------------------
-- Reçus (un par paiement)
-- ----------------------------------------------------------------------------
INSERT INTO recus (paiement_id, numero_recu, type_paiement, montant, date_emission) VALUES
((SELECT id FROM paiements WHERE commande_id = (SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000001')), 'REC-2026-000001', 'TOTAL', 9000.00, CURRENT_TIMESTAMP - INTERVAL '10 days' + INTERVAL '20 minutes'),
((SELECT id FROM paiements WHERE commande_id = (SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000002')), 'REC-2026-000002', 'PARTIEL', 4000.00, CURRENT_TIMESTAMP - INTERVAL '3 days' + INTERVAL '10 minutes'),
((SELECT id FROM paiements WHERE commande_id = (SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000005')), 'REC-2026-000003', 'TOTAL', 7500.00, CURRENT_TIMESTAMP - INTERVAL '20 days' + INTERVAL '15 minutes'),
((SELECT id FROM paiements WHERE commande_id = (SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000007')), 'REC-2026-000004', 'PARTIEL', 4700.00, CURRENT_TIMESTAMP - INTERVAL '6 days' + INTERVAL '25 minutes'),
((SELECT id FROM paiements WHERE commande_id = (SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000009')), 'REC-2026-000005', 'TOTAL', 5200.00, CURRENT_TIMESTAMP - INTERVAL '15 days' + INTERVAL '10 minutes'),
((SELECT id FROM paiements WHERE commande_id = (SELECT id FROM commandes WHERE numero_commande = 'CMD-2026-000010')), 'REC-2026-000006', 'TOTAL', 6200.00, CURRENT_TIMESTAMP - INTERVAL '29 days' + INTERVAL '20 minutes');

-- ----------------------------------------------------------------------------
-- Avis (uniquement sur des produits commandés dans une commande RETIREE)
-- ----------------------------------------------------------------------------
INSERT INTO avis (produit_id, client_id, note, commentaire, date_avis) VALUES
((SELECT id FROM produits WHERE libelle = 'Thiéboudienne rouge'), (SELECT id FROM clients WHERE email = 'fatou.ndiaye@example.sn'), 5, 'Un délice, exactement comme à la maison !', CURRENT_TIMESTAMP - INTERVAL '9 days'),
((SELECT id FROM produits WHERE libelle = 'Poisson braisé'), (SELECT id FROM clients WHERE email = 'fatou.ndiaye@example.sn'), 4, 'Très bon poisson, bien assaisonné.', CURRENT_TIMESTAMP - INTERVAL '14 days'),
((SELECT id FROM produits WHERE libelle = 'Capitaine grillé'), (SELECT id FROM clients WHERE email = 'mariama.fall@example.sn'), 5, 'Le meilleur capitaine grillé de Dakar.', CURRENT_TIMESTAMP - INTERVAL '19 days'),
((SELECT id FROM produits WHERE libelle = 'Dibiterie'), (SELECT id FROM clients WHERE email = 'mariama.fall@example.sn'), 4, 'Bonne dibiterie, un peu épicée à mon goût.', CURRENT_TIMESTAMP - INTERVAL '28 days'),
((SELECT id FROM produits WHERE libelle = 'Mafé'), (SELECT id FROM clients WHERE email = 'astou.diouf@example.sn'), 5, 'Mafé onctueux, j''adore !', CURRENT_TIMESTAMP - INTERVAL '5 days'),
((SELECT id FROM produits WHERE libelle = 'Thiéboudienne blanc'), (SELECT id FROM clients WHERE email = 'astou.diouf@example.sn'), 3, 'Correct mais un peu fade pour moi.', CURRENT_TIMESTAMP - INTERVAL '5 days');
