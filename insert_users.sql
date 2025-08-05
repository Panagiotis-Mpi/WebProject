-- Καθηγητές
INSERT INTO Users (email, password, first_name, last_name, role)
VALUES
('prof1@university.gr', '1234', 'Νίκος', 'Παπαδόπουλος', 'professor'),
('prof2@university.gr', '1234', 'Αθηνά', 'Κωνσταντίνου', 'professor'),
('prof3@university.gr', '1234', 'Γιώργος', 'Μαντάς', 'professor'),
('prof4@university.gr', '1234', 'Μαρία', 'Χριστοδούλου', 'professor'),
('prof5@university.gr', '1234', 'Σωτήρης', 'Καραγιάννης', 'professor');

-- Φοιτητές
INSERT INTO Users (email, password, first_name, last_name, role, am)
VALUES
('student1@university.gr', 'abcd', 'Δημήτρης', 'Αναστασίου', 'student', 'P2024001'),
('student2@university.gr', 'abcd', 'Ελένη', 'Ζαφειρίου', 'student', 'P2024002'),
('student3@university.gr', 'abcd', 'Χρήστος', 'Πανούσης', 'student', 'P2024003'),
('student4@university.gr', 'abcd', 'Ιωάννα', 'Λυμπεροπούλου', 'student', 'P2024004'),
('student5@university.gr', 'abcd', 'Μιχάλης', 'Κατσαντώνης', 'student', 'P2024005');

-- Γραμματεία
INSERT INTO Users (email, password, first_name, last_name, role, am)
VALUES
('secretary1@university.gr', 'admin123', 'Σοφία', 'Παπανικολάου', 'secretary');
