-- Comptes existants : empreintes SHA-1, sans sel (l'ancien code).
-- Mots de passe : a.martin / Genou-42 · s.leroy / Kineo2026
--                 c.petit / Kineo2026 · m.dalmeida / Epaule-17
INSERT INTO Utilisateur (login, nom, motDePasse) VALUES
  ('a.martin',   'Anne Martin',      'e04fa90cd577b80f4d0e368893156752cdee8bd1'),
  ('s.leroy',    'Sophie Leroy',     '21b3d3d50ef71cf7376dceddfd8c1a92f15f2ac6'),
  ('c.petit',    'Camille Petit',    '21b3d3d50ef71cf7376dceddfd8c1a92f15f2ac6'),
  ('m.dalmeida', 'Manuel D''Almeida', '0e0ab80b9942f61ce32adad5eb5e51a2dfaef0f7');
