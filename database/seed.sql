USE diplomatiki;
SET NAMES utf8mb4;

-- =======================
-- USERS
-- =======================
-- ΦΟΙΤΗΤΕΣ (15)
INSERT INTO Users (email, password, first_name, last_name, role, am, phone) VALUES
('student1@uni.gr', '1234', 'Alex', 'Kostas', 'student', 'AM1001', '6971110001'),
('student2@uni.gr', '1234', 'Maria', 'Papadopoulou', 'student', 'AM1002', '6971110002'),
('student3@uni.gr', '1234', 'Giorgos', 'Nikolaou', 'student', 'AM1003', '6971110003'),
('student4@uni.gr', '1234', 'Eleni', 'Papadaki', 'student', 'AM1004', '6971110004'),
('student5@uni.gr', '1234', 'Kostas', 'Georgiou', 'student', 'AM1005', '6971110005'),
('student6@uni.gr', '1234', 'Sofia', 'Karagianni', 'student', 'AM1006', '6971110006'),
('student7@uni.gr', '1234', 'Nikos', 'Mylonas', 'student', 'AM1007', '6971110007'),
('student8@uni.gr', '1234', 'Anna', 'Tsoukalas', 'student', 'AM1008', '6971110008'),
('student9@uni.gr', '1234', 'Panos', 'Laskaris', 'student', 'AM1009', '6971110009'),
('student10@uni.gr', '1234', 'Eleni', 'Vlachou', 'student', 'AM1010', '6971110010'),
('student11@uni.gr', '1234', 'Christos', 'Makris', 'student', 'AM1011', '6971110011'),
('student12@uni.gr', '1234', 'Olga', 'Spanou', 'student', 'AM1012', '6971110012'),
('student13@uni.gr', '1234', 'Petros', 'Giannakis', 'student', 'AM1013', '6971110013'),
('student14@uni.gr', '1234', 'Dimitra', 'Zerva', 'student', 'AM1014', '6971110014'),
('student15@uni.gr', '1234', 'Stavros', 'Alexiou', 'student', 'AM1015', '6971110015');

-- ΚΑΘΗΓΗΤΕΣ (6)
INSERT INTO Users (email, password, first_name, last_name, role, phone) VALUES
('prof1@uni.gr', '1234', 'Dimitris', 'Karalis', 'professor', '6972220001'),
('prof2@uni.gr', '1234', 'Eleni', 'Koukou', 'professor', '6972220002'),
('prof3@uni.gr', '1234', 'Nikos', 'Papadakis', 'professor', '6972220003'),
('prof4@uni.gr', '1234', 'Sofia', 'Georgiou', 'professor', '6972220004'),
('prof5@uni.gr', '1234', 'Giorgos', 'Kotsakis', 'professor', '6972220005'),
('prof6@uni.gr', '1234', 'Maria', 'Andreadou', 'professor', '6972220006');

-- ΓΡΑΜΜΑΤΕΙΑ (1)
INSERT INTO Users (email, password, first_name, last_name, role) VALUES
('secretary@uni.gr', '1234', 'Admin', 'Secretary', 'secretary');

-- =======================
-- TOPICS (12)
-- =======================
INSERT INTO Topics (id, title, summary, pdf_path, creator_id) VALUES
(1, 'Σύστημα Διαχείρισης Βιβλιοθήκης', 'Ανάπτυξη πληροφοριακού συστήματος για πανεπιστημιακή βιβλιοθήκη.', '/pdfs/topic1.pdf', 11),
(2, 'Εφαρμογή Κρατήσεων Αιθουσών', 'Υλοποίηση web app για κρατήσεις πανεπιστημιακών αιθουσών.', '/pdfs/topic2.pdf', 12),
(3, 'Αποθετήριο Διπλωματικών', 'Δημιουργία online συστήματος για αρχειοθέτηση διπλωματικών.', '/pdfs/topic3.pdf', 13),
(4, 'Ανάπτυξη Chatbot με AI', 'Κατασκευή chatbot με χρήση NLP.', '/pdfs/topic4.pdf', 14),
(5, 'Σύστημα Online Μαθημάτων', 'E-learning πλατφόρμα για τηλεκπαίδευση.', '/pdfs/topic5.pdf', 15),
(6, 'Σύστημα Σημειώσεων', 'Εφαρμογή για καθηγητές/φοιτητές με δυνατότητα διαμοιρασμού.', '/pdfs/topic6.pdf', 16),
(7, 'Διαχείριση Ερευνητικών Προγραμμάτων', 'Σύστημα για παρακολούθηση έργων.', '/pdfs/topic7.pdf', 11),
(8, 'Web Εφαρμογή με AJAX', 'Frontend/Backend project.', '/pdfs/topic8.pdf', 12),
(9, 'Σύστημα Ανακοινώσεων', 'Πλατφόρμα ανακοινώσεων για τμήμα.', '/pdfs/topic9.pdf', 13),
(10, 'Ανάλυση Δεδομένων με Python', 'Data mining και visualization.', '/pdfs/topic10.pdf', 14),
(11, 'Blockchain Εφαρμογή', 'Έργο σε Hyperledger.', '/pdfs/topic11.pdf', 15),
(12, 'Σύστημα Εξετάσεων Online', 'Διαδικτυακές εξετάσεις με ασφάλεια.', '/pdfs/topic12.pdf', 16);

-- =======================
-- THESES (12)
-- =======================
INSERT INTO Theses (id, topic_id, student_id, supervisor_id, status, assignment_date) VALUES
(1, 1, 1, 11, 'active', '2025-01-15'),
(2, 2, 2, 12, 'pending', '2025-01-16'),
(3, 3, 3, 13, 'under_review', '2025-01-17'),
(4, 4, 4, 14, 'completed', '2025-01-10'),
(5, 5, 5, 15, 'cancelled', '2025-01-20'),
(6, 6, 6, 16, 'active', '2025-02-01'),
(7, 7, 7, 11, 'pending', '2025-02-05'),
(8, 8, 8, 12, 'under_review', '2025-02-10'),
(9, 9, 9, 13, 'completed', '2025-02-15'),
(10, 10, 10, 14, 'active', '2025-02-20'),
(11, 11, 11, 15, 'pending', '2025-02-25'),
(12, 12, 12, 16, 'completed', '2025-03-01');

-- =======================
-- COMMITTEE MEMBERS
-- =======================
-- supervisors
INSERT INTO CommitteeMembers (thesis_id, professor_id, role, status) VALUES
(1, 11, 'supervisor', 'accepted'),
(2, 12, 'supervisor', 'accepted'),
(3, 13, 'supervisor', 'accepted'),
(4, 14, 'supervisor', 'accepted'),
(5, 15, 'supervisor', 'accepted'),
(6, 16, 'supervisor', 'accepted'),
(7, 11, 'supervisor', 'accepted'),
(8, 12, 'supervisor', 'accepted'),
(9, 13, 'supervisor', 'accepted'),
(10, 14, 'supervisor', 'accepted'),
(11, 15, 'supervisor', 'accepted'),
(12, 16, 'supervisor', 'accepted');

-- μέλη με διάφορα status
INSERT INTO CommitteeMembers (thesis_id, professor_id, role, status) VALUES
(1, 12, 'member', 'invited'),
(1, 13, 'member', 'accepted'),
(2, 14, 'member', 'accepted'),
(2, 15, 'member', 'rejected'),
(3, 16, 'member', 'accepted'),
(4, 11, 'member', 'accepted'),
(4, 12, 'member', 'accepted'),
(5, 13, 'member', 'invited'),
(6, 14, 'member', 'accepted'),
(6, 15, 'member', 'accepted'),
(7, 16, 'member', 'rejected'),
(8, 11, 'member', 'invited'),
(9, 12, 'member', 'accepted'),
(9, 14, 'member', 'accepted'),
(10, 13, 'member', 'accepted'),
(11, 11, 'member', 'invited'),
(12, 12, 'member', 'accepted'),
(12, 13, 'member', 'accepted');

-- =======================
-- PRESENTATIONS
-- =======================
INSERT INTO Presentations (thesis_id, date, time, mode, location) VALUES
(3, '2025-06-01', '10:00:00', 'in_person', 'Room 101'),
(4, '2025-06-05', '12:00:00', 'online', 'Zoom link'),
(9, '2025-06-10', '11:00:00', 'in_person', 'Room 102'),
(12, '2025-06-15', '09:30:00', 'online', 'Teams link');

-- =======================
-- GRADES
-- =======================
INSERT INTO Grades (thesis_id, professor_id, content_score, organization_score, presentation_score) VALUES
(4, 14, 9.0, 9.5, 9.0),
(4, 11, 8.5, 8.0, 9.0),
(9, 13, 8.0, 7.5, 8.5),
(9, 12, 7.5, 8.0, 7.0),
(12, 16, 9.5, 9.0, 9.0),
(12, 15, 9.0, 9.0, 9.5);

-- =======================
-- ANNOUNCEMENTS
-- =======================
INSERT INTO Announcements (thesis_id, title, text, start_date, end_date) VALUES
(3, 'Presentation Scheduled', 'Η παρουσίαση θα γίνει στις 2025-06-01.', '2025-05-20', '2025-06-02'),
(4, 'Final Presentation', 'Online παρουσίαση μέσω Zoom.', '2025-05-25', '2025-06-06'),
(9, 'Thesis Defense', 'Αίθουσα 102, ώρα 11:00.', '2025-05-28', '2025-06-11'),
(12, 'Online Defense', 'Η παρουσίαση θα γίνει μέσω Teams.', '2025-06-01', '2025-06-16');


