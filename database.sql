DROP DATABASE IF EXISTS diplomatiki;
CREATE DATABASE diplomatiki;
USE diplomatiki;

CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    role ENUM('student', 'professor', 'secretary') NOT NULL,
    am VARCHAR(20), -- μόνο για φοιτητές
    contact_info TEXT
);

CREATE TABLE Topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    summary TEXT,
    pdf_path VARCHAR(255),
    creator_id INT NOT NULL,
    FOREIGN KEY (creator_id) REFERENCES Users(id)
        ON DELETE CASCADE
);

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
);

CREATE TABLE CommitteeMembers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    supervisor_id INT NOT NULL,
    professor_id1 INT NOT NULL,
    professor_id2 INT NOT NULL,
    status ENUM('invited', 'accepted', 'rejected') DEFAULT 'invited',
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES Users(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id1) REFERENCES Users(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id2) REFERENCES Users(id) ON DELETE CASCADE
);

CREATE TABLE Notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    creator_id INT NOT NULL,
    content VARCHAR(300) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE,
    FOREIGN KEY (creator_id) REFERENCES Users(id) ON DELETE CASCADE
);

CREATE TABLE StatusHistory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    status ENUM('pending', 'active', 'under_review', 'completed', 'cancelled') NOT NULL,
    changed_by INT,
    change_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES Users(id) ON DELETE SET NULL
);

CREATE TABLE Presentations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    mode ENUM('in_person', 'online') NOT NULL,
    location VARCHAR(255) NOT NULL,
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE
);

CREATE TABLE Grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    professor_id INT NOT NULL,
    grade DECIMAL(4,2) NOT NULL,
    criteria TEXT,
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES Users(id) ON DELETE CASCADE
);

CREATE TABLE Announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    text TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thesis_id) REFERENCES Theses(id) ON DELETE CASCADE
);
