DROP DATABASE IF EXISTS diplomatiki;
CREATE DATABASE diplomatiki CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE diplomatiki;


CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    role ENUM('student', 'professor', 'secretary') NOT NULL,
    am VARCHAR(20) DEFAULT NULL, -- μόνο για φοιτητές
    phone VARCHAR(20) DEFAULT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX idx_users_role ON Users(role);
CREATE INDEX idx_users_am ON Users(am);


CREATE TABLE Topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    summary TEXT,
    pdf_path VARCHAR(255),
    creator_id INT NOT NULL,
    FOREIGN KEY (creator_id) REFERENCES Users(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX idx_topics_creator ON Topics(creator_id);


CREATE TABLE Theses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    student_id INT NOT NULL,
    supervisor_id INT NOT NULL,
    status ENUM('pending', 'active', 'under_review', 'completed', 'cancelled') DEFAULT 'pending',
    assignment_date DATE,
    library_link VARCHAR(255),
    review_doc_path VARCHAR(255),
    FOREIGN KEY (topic_id) REFERENCES Topics(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES Users(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES Users(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX idx_theses_student ON Theses(student_id);
CREATE INDEX idx_theses_supervisor ON Theses(supervisor_id);
CREATE INDEX idx_theses_status ON Theses(status);


CREATE TABLE CommitteeMembers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    professor_id INT NOT NULL,
    role ENUM('supervisor', 'member') NOT NULL DEFAULT 'member',
    status ENUM('invited','accepted','rejected') DEFAULT 'invited',
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES Users(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX idx_committeemembers_thesis ON CommitteeMembers(thesis_id);


CREATE TABLE Notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    creator_id INT NOT NULL,
    content VARCHAR(300) NOT NULL,
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE,
    FOREIGN KEY (creator_id) REFERENCES Users(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX idx_notes_thesis ON Notes(thesis_id);


CREATE TABLE StatusHistory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    status ENUM('pending', 'active', 'under_review', 'completed', 'cancelled') NOT NULL,
    changed_by INT,
    change_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES Users(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX idx_statushistory_thesis ON StatusHistory(thesis_id);


CREATE TABLE Presentations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    mode ENUM('in_person', 'online') NOT NULL,
    location VARCHAR(255) NOT NULL,
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX idx_presentations_thesis ON Presentations(thesis_id);


CREATE TABLE Grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    professor_id INT NOT NULL,
    content_score DECIMAL(4,2) NOT NULL,
    organization_score DECIMAL(4,2) NOT NULL,
    presentation_score DECIMAL(4,2) NOT NULL,
    final_grade DECIMAL(4,2) GENERATED ALWAYS AS (
        (content_score + organization_score + presentation_score) / 3
    ) STORED,
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES Users(id) ON DELETE CASCADE,
    UNIQUE KEY idx_thesis_professor (thesis_id, professor_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX idx_grades_thesis ON Grades(thesis_id);


CREATE TABLE Announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    text TEXT,
    start_date DATE,
    end_date DATE,
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX idx_announcements_thesis ON Announcements(thesis_id);
CREATE INDEX idx_announcements_start ON Announcements(start_date);
