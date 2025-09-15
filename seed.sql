USE diplomatiki;
SET NAMES utf8mb4;

-- =======================
-- USERS
-- =======================
-- ΦΟΙΤΗΤΕΣ
INSERT INTO Users (email, password, first_name, last_name, role, am, phone) VALUES
('student1@university.gr', '1234', 'Alex', 'Kostas', 'student', 'AM1001', '6971110001'),
('student2@university.gr', '1234', 'Maria', 'Papadopoulou', 'student', 'AM1002', '6971110002'),
('student3@university.gr', '1234', 'Giorgos', 'Nikolaou', 'student', 'AM1003', '6971110003'),
('student4@university.gr', '1234', 'Eleni', 'Papadaki', 'student', 'AM1004', '6971110004'),
('student5@university.gr', '1234', 'Kostas', 'Georgiou', 'student', 'AM1005', '6971110005'),
('student6@university.gr', '1234', 'Sofia', 'Karagianni', 'student', 'AM1006', '6971110006'),
('student7@university.gr', '1234', 'Nikos', 'Mylonas', 'student', 'AM1007', '6971110007'),
('student8@university.gr', '1234', 'Anna', 'Tsoukalas', 'student', 'AM1008', '6971110008'),
('student9@university.gr', '1234', 'Panos', 'Laskaris', 'student', 'AM1009', '6971110009'),
('student10@university.gr', '1234', 'Eleni', 'Vlachou', 'student', 'AM1010', '6971110010');

-- ΚΑΘΗΓΗΤΕΣ
INSERT INTO Users (email, password, first_name, last_name, role, phone) VALUES
('prof1@university.gr', '1234', 'Dimitris', 'Karalis', 'professor', '6972220001'),
('prof2@university.gr', '1234', 'Eleni', 'Koukou', 'professor', '6972220002'),
('prof3@university.gr', '1234', 'Nikos', 'Papadakis', 'professor', '6972220003'),
('prof4@university.gr', '1234', 'Sofia', 'Georgiou', 'professor', '6972220004'),
('prof5@university.gr', '1234', 'Giorgos', 'Kotsakis', 'professor', '6972220005');

-- ΓΡΑΜΜΑΤΕΙΑ
INSERT INTO Users (email, password, first_name, last_name, role) VALUES
('secretary1@university.gr', '1234', 'Admin', 'Secretary', 'secretary');

-- =======================
-- TOPICS
-- =======================
INSERT INTO Topics (id, title, summary, pdf_path, creator_id) VALUES
(1, 'Ανάπτυξη Εφαρμογής Διαχείρισης Βάσεων Δεδομένων', 'Σχεδίαση και υλοποίηση εφαρμογής με MySQL και PHP.', '/pdfs/topic1.pdf', 11),
(2, 'Ανάπτυξη Web Εφαρμογής με AJAX', 'Δημιουργία web εφαρμογής με χρήση AJAX για φόρτωση δεδομένων.', '/pdfs/topic2.pdf', 12),
(3, 'Σύστημα Διαχείρισης Φοιτητικών Δεδομένων', 'Υλοποίηση συστήματος για διαχείριση φοιτητικών δεδομένων.', '/pdfs/topic3.pdf', 13),
(4, 'Ανάπτυξη Εφαρμογής Τριμελών Επιτροπών', 'Σχεδίαση εφαρμογής για διαχείριση τριμελών επιτροπών.', '/pdfs/topic4.pdf', 14),
(5, 'Διαχείριση Διπλωματικών Εργασιών', 'Σχεδίαση συστήματος για παρακολούθηση διπλωματικών εργασιών.', '/pdfs/topic5.pdf', 15),
(6, 'Διασύνδεση Βάσεων Δεδομένων με PHP', 'Σύνδεση MySQL με PHP και AJAX για δυναμική εμφάνιση δεδομένων.', '/pdfs/topic6.pdf', 11),
(7, 'Web Σύστημα Αξιολόγησης Φοιτητών', 'Υλοποίηση συστήματος αξιολόγησης με καταγραφή βαθμολογίας και στατιστικά.', '/pdfs/topic7.pdf', 12),
(8, 'Ανάπτυξη Αποθετηρίου Διπλωματικών', 'Δημιουργία αποθετηρίου για αποθήκευση και διαχείριση διπλωματικών.', '/pdfs/topic8.pdf', 13),
(9, 'Σύστημα Σημειώσεων για Διδάσκοντες', 'Υλοποίηση λειτουργίας καταχώρησης και διαχείρισης σημειώσεων.', '/pdfs/topic9.pdf', 14),
(10, 'Διαχείριση Παρουσιάσεων και Ανακοινώσεων', 'Σχεδίαση συστήματος για καταχώρηση παρουσιάσεων και ανακοινώσεων.', '/pdfs/topic10.pdf', 15);

-- =======================
-- THESES
-- =======================
INSERT INTO Theses (id, topic_id, student_id, supervisor_id, status, assignment_date) VALUES
(1, 1, 1, 11, 'active', '2025-01-15'),
(2, 2, 2, 12, 'active', '2025-01-16'),
(3, 3, 3, 13, 'pending', '2025-01-17'),
(4, 4, 4, 14, 'under_review', '2025-01-18'),
(5, 5, 5, 15, 'completed', '2025-01-10'),
(6, 6, 6, 11, 'active', '2025-02-01'),
(7, 7, 7, 12, 'pending', '2025-02-02'),
(8, 8, 8, 13, 'under_review', '2025-02-03'),
(9, 9, 9, 14, 'completed', '2025-02-04'),
(10, 10, 10, 15, 'active', '2025-02-05');

-- =======================
-- COMMITTEE MEMBERS
-- =======================
INSERT INTO CommitteeMembers (thesis_id, professor_id, role, status) VALUES
(1, 11, 'supervisor', 'accepted'),
(2, 12, 'supervisor', 'accepted'),
(3, 13, 'supervisor', 'accepted'),
(4, 14, 'supervisor', 'accepted'),
(5, 15, 'supervisor', 'accepted'),
(6, 11, 'supervisor', 'accepted'),
(7, 12, 'supervisor', 'accepted'),
(8, 13, 'supervisor', 'accepted'),
(9, 14, 'supervisor', 'accepted'),
(10, 15, 'supervisor', 'accepted');

INSERT INTO CommitteeMembers (thesis_id, professor_id, role, status) VALUES
(1, 12, 'member', 'invited'),
(1, 13, 'member', 'invited'),
(2, 13, 'member', 'accepted'),
(2, 14, 'member', 'accepted'),
(4, 11, 'member', 'accepted'),
(4, 15, 'member', 'invited'),
(8, 11, 'member', 'rejected');

-- =======================
-- NOTES
-- =======================
INSERT INTO Notes (thesis_id, creator_id, content) VALUES
(1, 11, 'Initial meeting done.'),
(1, 12, 'Reviewed first draft.'),
(2, 12, 'Student progressing well.'),
(4, 14, 'Final draft almost ready.');

-- =======================
-- PRESENTATIONS
-- =======================
INSERT INTO Presentations (thesis_id, date, time, mode, location) VALUES
(4, '2025-06-01', '10:00:00', 'in_person', 'Room 101'),
(5, '2025-06-05', '12:00:00', 'online', 'Zoom link'),
(9, '2025-06-10', '11:00:00', 'in_person', 'Room 102');

-- =======================
-- GRADES
-- =======================
INSERT INTO Grades (thesis_id, professor_id, content_score, organization_score, presentation_score) VALUES
(4, 14, 8.5, 9.0, 8.0),
(4, 11, 9.0, 8.5, 8.5),
(5, 15, 9.5, 9.0, 9.0),
(5, 12, 9.0, 8.5, 9.0),
(9, 14, 8.0, 8.5, 8.0),
(9, 15, 7.5, 7.0, 7.5);

-- =======================
-- ANNOUNCEMENTS
-- =======================
INSERT INTO Announcements (thesis_id, title, text) VALUES
(4, 'Presentation of Thesis 4', 'The presentation will take place on 2025-06-01 at Room 101.'),
(5, 'Presentation of Thesis 5', 'Online presentation via Zoom.'),
(9, 'Presentation of Thesis 9', 'Presentation scheduled for 2025-06-10 in Room 102.');
