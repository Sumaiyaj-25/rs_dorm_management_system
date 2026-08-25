
CREATE DATABASE IF NOT EXISTS dorm_management;
USE dorm_management;

CREATE TABLE IF NOT EXISTS Student (
    Student_ID   INT AUTO_INCREMENT PRIMARY KEY,
    FirstName    VARCHAR(50)  NOT NULL,
    LastName     VARCHAR(50)  NOT NULL,
    Email        VARCHAR(100) NOT NULL UNIQUE,
	Phone_Number VARCHAR(20),
    Gender       VARCHAR(10),
	Contributor_Points INT DEFAULT 0,
    Department   VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS Login (
    Login_ID      INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID    INT NOT NULL,
    PasswordHash  VARCHAR(255) NOT NULL,
    LastLogin     DATETIME NULL,
    CreatedAt     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Student_ID) REFERENCES Student(Student_ID) ON DELETE CASCADE,
    UNIQUE KEY uniq_student_login (Student_ID)
);

CREATE TABLE IF NOT EXISTS meal (
    token_no INT AUTO_INCREMENT PRIMARY KEY,
    meal_type VARCHAR(50), -- e.g., 'Breakfast', 'Lunch', 'Dinner'
    student_id INT NOT NULL, -- The original owner of the meal (who booked it)
    meal_serve_date DATE NOT NULL,
    is_released BOOLEAN DEFAULT FALSE,
    released_by INT NULL, -- The student releasing the meal
    released_status VARCHAR(20) DEFAULT 'Pending', -- 'Pending', 'Available', 'Claimed'
    claim_status BOOLEAN DEFAULT FALSE,
    claimed_by INT NULL, -- The student who claims it
    FOREIGN KEY (student_id) REFERENCES Student(Student_ID) ON DELETE CASCADE,
    FOREIGN KEY (released_by) REFERENCES Student(Student_ID) ON DELETE SET NULL,
    FOREIGN KEY (claimed_by) REFERENCES Student(Student_ID) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS Preferences (
    PreferenceID INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID INT NOT NULL,
    Cleanliness VARCHAR(50),
    NoiseTolerance VARCHAR(50),
    StudyHabit VARCHAR(50),
    SleepingHabit VARCHAR(50),
    Others VARCHAR(255),

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
        ON DELETE CASCADE,

    UNIQUE KEY uniq_student_pref (Student_ID)
);

CREATE TABLE IF NOT EXISTS Compatible (
    Requesting_Student_ID INT NOT NULL,
    Potential_Roommate_ID INT NOT NULL,
    Score DECIMAL(5,2),
    Status ENUM('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending',

    PRIMARY KEY (Requesting_Student_ID, Potential_Roommate_ID),

    FOREIGN KEY (Requesting_Student_ID)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (Potential_Roommate_ID)
        REFERENCES Student(Student_ID)
);