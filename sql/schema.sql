
CREATE DATABASE IF NOT EXISTS dorm_management;
USE dorm_management;

CREATE TABLE IF NOT EXISTS Room (
    Room_No VARCHAR(10) PRIMARY KEY,
    Dorm_name VARCHAR(50),
    Floor INT,
    Capacity INT,
    Status VARCHAR(20) DEFAULT 'Active'
); 

CREATE TABLE IF NOT EXISTS Student (
    Student_ID INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Phone_Number VARCHAR(20),
    Gender VARCHAR(10),
    Contributor_Points INT DEFAULT 0,
    Department VARCHAR(50),
    Room_No VARCHAR(10) NULL,

    FOREIGN KEY (Room_No)
        REFERENCES Room(Room_No)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);
CREATE TABLE IF NOT EXISTS room_transfer_request (
    Transfer_ID INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID INT NOT NULL,
    Current_Room VARCHAR(10) NOT NULL,
    Requested_Room VARCHAR(10) NOT NULL,
    Reason TEXT NOT NULL,
    Status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    DateRequested DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
        ON DELETE CASCADE,

    FOREIGN KEY (Current_Room)
        REFERENCES Room(Room_No),

    FOREIGN KEY (Requested_Room)
        REFERENCES Room(Room_No)
);
CREATE TABLE IF NOT EXISTS Staff (

    Staff_ID INT AUTO_INCREMENT PRIMARY KEY,

    Name VARCHAR(100) NOT NULL,

    Phone_Number VARCHAR(20),

    Email VARCHAR(100) UNIQUE,

    Role VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS Login (
    Login_ID INT AUTO_INCREMENT PRIMARY KEY,

    Student_ID INT NULL,
    Staff_ID INT NULL,

    PasswordHash VARCHAR(255) NOT NULL,
    LastLogin DATETIME NULL,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
        ON DELETE CASCADE,

    FOREIGN KEY (Staff_ID)
        REFERENCES Staff(Staff_ID)
        ON DELETE CASCADE,

    UNIQUE KEY uniq_student_login (Student_ID),
    UNIQUE KEY uniq_staff_login (Staff_ID)
);

CREATE TABLE IF NOT EXISTS Parcel (

    P_ID INT AUTO_INCREMENT PRIMARY KEY,

    Tracking_Number VARCHAR(100) NOT NULL,

    Status VARCHAR(50) NOT NULL,

    Locker_Number VARCHAR(50),

    Arrival_Date DATETIME,

    Receive_Time DATETIME NULL,

    OTP_Code VARCHAR(50),

    Student_ID INT,

    Handled_By INT,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS Maintenance_request (
    RequestID INT AUTO_INCREMENT PRIMARY KEY,
    DateSubmitted DATETIME DEFAULT CURRENT_TIMESTAMP,
    Description TEXT NOT NULL,
    Photo VARCHAR(255) NULL,
    Category VARCHAR(50) DEFAULT 'Other',
    Priority ENUM('High','Medium','Low') DEFAULT 'Low',
    Status ENUM('Submitted','In Progress','Resolved') DEFAULT 'Submitted',
    Student_ID INT NOT NULL,
    Room_No VARCHAR(10) NOT NULL,
    Dorm_name VARCHAR(50) NOT NULL,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (Room_No)
        REFERENCES Room(Room_No)
);
CREATE TABLE IF NOT EXISTS meal (
    token_no INT AUTO_INCREMENT PRIMARY KEY,
    meal_type VARCHAR(50), -- e.g., 'Breakfast', 'Lunch', 'Dinner'
    student_id INT NOT NULL, -- The original owner of the meal (who booked it)
    meal_serve_date DATE NOT NULL,
    is_released BOOLEAN DEFAULT FALSE,
    released_by INT NULL, -- The student releasing the meal
    released_status VARCHAR(20) DEFAULT 'Booked', -- 'Booked', 'Available', 'Claimed'
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

CREATE TABLE IF NOT EXISTS Leave_Request (

    Request_ID INT AUTO_INCREMENT PRIMARY KEY,

    Leave_Date DATE NOT NULL,

    Return_Date DATE NOT NULL,

    Reason TEXT,

    Status VARCHAR(20) DEFAULT 'Pending',

    Student_ID INT NOT NULL,

    Staff_ID INT NULL,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
        ON DELETE CASCADE,

    FOREIGN KEY (Staff_ID)
        REFERENCES Staff(Staff_ID)
        ON DELETE SET NULL

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

CREATE TABLE IF NOT EXISTS Medical_Record (

    R_ID INT AUTO_INCREMENT PRIMARY KEY,

    Visit_Date DATE NOT NULL,

    Diagnosis VARCHAR(255) NOT NULL,

    Treatment TEXT,

    Prescription TEXT,

    Student_ID INT NOT NULL,

    Staff_ID INT,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (Staff_ID)
        REFERENCES Staff(Staff_ID)

);
CREATE TABLE IF NOT EXISTS mood_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    log_date DATE NOT NULL,
    mood_score INT NOT NULL CHECK (mood_score BETWEEN 1 AND 5),
    
    FOREIGN KEY (student_id)
        REFERENCES Student(Student_ID)
        ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS Visitor (
    Visitor_ID INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID INT NOT NULL,
    Visitor_Name VARCHAR(100) NOT NULL,
    Visitor_Phone VARCHAR(20),
    Visit_Date DATE NOT NULL,
    QR_Code VARCHAR(100) NOT NULL UNIQUE,
    Status VARCHAR(20) DEFAULT 'Pending',
    Entry_Time DATETIME NULL,
    Exit_Time DATETIME NULL,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
        ON DELETE CASCADE
);



CREATE TABLE IF NOT EXISTS academic_resources (
    resource_id INT AUTO_INCREMENT PRIMARY KEY,
    resource_type VARCHAR(50) NOT NULL,
    submitted_by INT NOT NULL,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    course_reference VARCHAR(100) NOT NULL,
    approval_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    moderator_id INT,
    file_path VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (submitted_by)
        REFERENCES Student(Student_ID)
        ON DELETE CASCADE,

    FOREIGN KEY (moderator_id)
        REFERENCES Staff(Staff_ID)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS access_and_rates (
    rate_id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    student_id INT NOT NULL,
    rate_value INT NOT NULL CHECK (rate_value BETWEEN 1 AND 5),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (resource_id)
        REFERENCES academic_resources(resource_id)
        ON DELETE CASCADE,

    FOREIGN KEY (student_id)
        REFERENCES Student(Student_ID)
        ON DELETE CASCADE,

    UNIQUE KEY uniq_student_resource_rating (resource_id, student_id)
);

CREATE TABLE IF NOT EXISTS SOS_Request (
    SOS_ID INT AUTO_INCREMENT PRIMARY KEY,
    Emergency_Type VARCHAR(100) NOT NULL,
    Request_Time DATETIME DEFAULT CURRENT_TIMESTAMP,
    Status VARCHAR(20) DEFAULT 'Pending',
    Student_ID INT,
    Staff_ID INT,
    FOREIGN KEY (Student_ID) REFERENCES Student(Student_ID),
    FOREIGN KEY (Staff_ID) REFERENCES Staff(Staff_ID)
);

CREATE TABLE IF NOT EXISTS exit_clearance (
    clearance_id INT AUTO_INCREMENT PRIMARY KEY,
    clearance_status ENUM('Pending', 'Cleared') DEFAULT 'Pending',
    cleared_at DATETIME NULL
);

ALTER TABLE Student ADD COLUMN clearance_id INT NULL;
ALTER TABLE Student ADD FOREIGN KEY (clearance_id) REFERENCES exit_clearance(clearance_id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS laundry (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    laundry_status VARCHAR(50) DEFAULT 'Pending',
    item_type VARCHAR(50),
    owned_by INT NOT NULL,
    payment_status VARCHAR(20) DEFAULT 'Unpaid',
    clearance_id INT,
    FOREIGN KEY (owned_by) REFERENCES Student(Student_ID) ON DELETE CASCADE,
    FOREIGN KEY (clearance_id) REFERENCES exit_clearance(clearance_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS library (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    booked_status VARCHAR(50) DEFAULT 'Issued',
    item_type VARCHAR(50),
    booked_by INT NOT NULL,
    clearance_id INT,
    FOREIGN KEY (booked_by) REFERENCES Student(Student_ID) ON DELETE CASCADE,
    FOREIGN KEY (clearance_id) REFERENCES exit_clearance(clearance_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS accounts (
    invoice_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_type VARCHAR(50),
    payment_status VARCHAR(20) DEFAULT 'Unpaid',
    clearance_id INT,
    student_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES Student(Student_ID) ON DELETE CASCADE,
    FOREIGN KEY (clearance_id) REFERENCES exit_clearance(clearance_id) ON DELETE SET NULL
);

-- Room Data
INSERT INTO room (Room_No, Dorm_name, Floor, Capacity, Status)
VALUES
-- Maloncho
('M-101', 'Maloncho', 1, 4, 'Active'),
('M-102', 'Maloncho', 1, 4, 'Active'),
('M-103', 'Maloncho', 1, 4, 'Active'),
('M-104', 'Maloncho', 1, 4, 'Active'),
('M-105', 'Maloncho', 1, 4, 'Active'),
('M-106', 'Maloncho', 1, 4, 'Active'),
('M-107', 'Maloncho', 1, 4, 'Active'),
('M-108', 'Maloncho', 1, 4, 'Active'),
('M-109', 'Maloncho', 1, 4, 'Active'),

('M-201', 'Maloncho', 2, 4, 'Active'),
('M-202', 'Maloncho', 2, 4, 'Active'),
('M-203', 'Maloncho', 2, 4, 'Active'),
('M-204', 'Maloncho', 2, 4, 'Active'),
('M-205', 'Maloncho', 2, 4, 'Active'),
('M-206', 'Maloncho', 2, 4, 'Active'),
('M-207', 'Maloncho', 2, 4, 'Active'),
('M-208', 'Maloncho', 2, 4, 'Active'),
('M-209', 'Maloncho', 2, 4, 'Active'),

('M-301', 'Maloncho', 3, 4, 'Active'),
('M-302', 'Maloncho', 3, 4, 'Active'),
('M-303', 'Maloncho', 3, 4, 'Active'),
('M-304', 'Maloncho', 3, 4, 'Active'),
('M-305', 'Maloncho', 3, 4, 'Active'),
('M-306', 'Maloncho', 3, 4, 'Active'),
('M-307', 'Maloncho', 3, 4, 'Active'),
('M-308', 'Maloncho', 3, 4, 'Active'),
('M-309', 'Maloncho', 3, 4, 'Active'),

('M-401', 'Maloncho', 4, 4, 'Active'),
('M-402', 'Maloncho', 4, 4, 'Active'),
('M-403', 'Maloncho', 4, 4, 'Active'),
('M-404', 'Maloncho', 4, 4, 'Active'),
('M-405', 'Maloncho', 4, 4, 'Active'),
('M-406', 'Maloncho', 4, 4, 'Active'),
('M-407', 'Maloncho', 4, 4, 'Active'),
('M-408', 'Maloncho', 4, 4, 'Active'),
('M-409', 'Maloncho', 4, 4, 'Active'),

('M-501', 'Maloncho', 5, 4, 'Active'),
('M-502', 'Maloncho', 5, 4, 'Active'),
('M-503', 'Maloncho', 5, 4, 'Active'),
('M-504', 'Maloncho', 5, 4, 'Active'),
('M-505', 'Maloncho', 5, 4, 'Active'),
('M-506', 'Maloncho', 5, 4, 'Active'),
('M-507', 'Maloncho', 5, 4, 'Active'),
('M-508', 'Maloncho', 5, 4, 'Active'),
('M-509', 'Maloncho', 5, 4, 'Active'),

('M-601', 'Maloncho', 6, 4, 'Active'),
('M-602', 'Maloncho', 6, 4, 'Active'),
('M-603', 'Maloncho', 6, 4, 'Active'),
('M-604', 'Maloncho', 6, 4, 'Active'),
('M-605', 'Maloncho', 6, 4, 'Active'),
('M-606', 'Maloncho', 6, 4, 'Active'),
('M-607', 'Maloncho', 6, 4, 'Active'),
('M-608', 'Maloncho', 6, 4, 'Active'),
('M-609', 'Maloncho', 6, 4, 'Active'),

('M-701', 'Maloncho', 7, 4, 'Active'),
('M-702', 'Maloncho', 7, 4, 'Active'),
('M-703', 'Maloncho', 7, 4, 'Active'),
('M-704', 'Maloncho', 7, 4, 'Active'),
('M-705', 'Maloncho', 7, 4, 'Active'),
('M-706', 'Maloncho', 7, 4, 'Active'),
('M-707', 'Maloncho', 7, 4, 'Active'),
('M-708', 'Maloncho', 7, 4, 'Active'),
('M-709', 'Maloncho', 7, 4, 'Active'),

('M-801', 'Maloncho', 8, 4, 'Active'),
('M-802', 'Maloncho', 8, 4, 'Active'),
('M-803', 'Maloncho', 8, 4, 'Active'),
('M-804', 'Maloncho', 8, 4, 'Active'),
('M-805', 'Maloncho', 8, 4, 'Active'),
('M-806', 'Maloncho', 8, 4, 'Active'),
('M-807', 'Maloncho', 8, 4, 'Active'),
('M-808', 'Maloncho', 8, 4, 'Active'),
('M-809', 'Maloncho', 8, 4, 'Active'),

('M-901', 'Maloncho', 9, 4, 'Active'),
('M-902', 'Maloncho', 9, 4, 'Active'),
('M-903', 'Maloncho', 9, 4, 'Active'),
('M-904', 'Maloncho', 9, 4, 'Active'),
('M-905', 'Maloncho', 9, 4, 'Active'),
('M-906', 'Maloncho', 9, 4, 'Active'),
('M-907', 'Maloncho', 9, 4, 'Active'),
('M-908', 'Maloncho', 9, 4, 'Active'),
('M-909', 'Maloncho', 9, 4, 'Active'),

-- Nikunjo
('N-101', 'Nikunjo', 1, 4, 'Active'),
('N-102', 'Nikunjo', 1, 4, 'Active'),
('N-103', 'Nikunjo', 1, 4, 'Active'),
('N-104', 'Nikunjo', 1, 4, 'Active'),
('N-105', 'Nikunjo', 1, 4, 'Active'),
('N-106', 'Nikunjo', 1, 4, 'Active'),
('N-107', 'Nikunjo', 1, 4, 'Active'),
('N-108', 'Nikunjo', 1, 4, 'Active'),
('N-109', 'Nikunjo', 1, 4, 'Active'),

('N-201', 'Nikunjo', 2, 4, 'Active'),
('N-202', 'Nikunjo', 2, 4, 'Active'),
('N-203', 'Nikunjo', 2, 4, 'Active'),
('N-204', 'Nikunjo', 2, 4, 'Active'),
('N-205', 'Nikunjo', 2, 4, 'Active'),
('N-206', 'Nikunjo', 2, 4, 'Active'),
('N-207', 'Nikunjo', 2, 4, 'Active'),
('N-208', 'Nikunjo', 2, 4, 'Active'),
('N-209', 'Nikunjo', 2, 4, 'Active'),

('N-301', 'Nikunjo', 3, 4, 'Active'),
('N-302', 'Nikunjo', 3, 4, 'Active'),
('N-303', 'Nikunjo', 3, 4, 'Active'),
('N-304', 'Nikunjo', 3, 4, 'Active'),
('N-305', 'Nikunjo', 3, 4, 'Active'),
('N-306', 'Nikunjo', 3, 4, 'Active'),
('N-307', 'Nikunjo', 3, 4, 'Active'),
('N-308', 'Nikunjo', 3, 4, 'Active'),
('N-309', 'Nikunjo', 3, 4, 'Active'),

('N-401', 'Nikunjo', 4, 4, 'Active'),
('N-402', 'Nikunjo', 4, 4, 'Active'),
('N-403', 'Nikunjo', 4, 4, 'Active'),
('N-404', 'Nikunjo', 4, 4, 'Active'),
('N-405', 'Nikunjo', 4, 4, 'Active'),
('N-406', 'Nikunjo', 4, 4, 'Active'),
('N-407', 'Nikunjo', 4, 4, 'Active'),
('N-408', 'Nikunjo', 4, 4, 'Active'),
('N-409', 'Nikunjo', 4, 4, 'Active'),

('N-501', 'Nikunjo', 5, 4, 'Active'),
('N-502', 'Nikunjo', 5, 4, 'Active'),
('N-503', 'Nikunjo', 5, 4, 'Active'),
('N-504', 'Nikunjo', 5, 4, 'Active'),
('N-505', 'Nikunjo', 5, 4, 'Active'),
('N-506', 'Nikunjo', 5, 4, 'Active'),
('N-507', 'Nikunjo', 5, 4, 'Active'),
('N-508', 'Nikunjo', 5, 4, 'Active'),
('N-509', 'Nikunjo', 5, 4, 'Active'),

('N-601', 'Nikunjo', 6, 4, 'Active'),
('N-602', 'Nikunjo', 6, 4, 'Active'),
('N-603', 'Nikunjo', 6, 4, 'Active'),
('N-604', 'Nikunjo', 6, 4, 'Active'),
('N-605', 'Nikunjo', 6, 4, 'Active'),
('N-606', 'Nikunjo', 6, 4, 'Active'),
('N-607', 'Nikunjo', 6, 4, 'Active'),
('N-608', 'Nikunjo', 6, 4, 'Active'),
('N-609', 'Nikunjo', 6, 4, 'Active'),

('N-701', 'Nikunjo', 7, 4, 'Active'),
('N-702', 'Nikunjo', 7, 4, 'Active'),
('N-703', 'Nikunjo', 7, 4, 'Active'),
('N-704', 'Nikunjo', 7, 4, 'Active'),
('N-705', 'Nikunjo', 7, 4, 'Active'),
('N-706', 'Nikunjo', 7, 4, 'Active'),
('N-707', 'Nikunjo', 7, 4, 'Active'),
('N-708', 'Nikunjo', 7, 4, 'Active'),
('N-709', 'Nikunjo', 7, 4, 'Active'),

('N-801', 'Nikunjo', 8, 4, 'Active'),
('N-802', 'Nikunjo', 8, 4, 'Active'),
('N-803', 'Nikunjo', 8, 4, 'Active'),
('N-804', 'Nikunjo', 8, 4, 'Active'),
('N-805', 'Nikunjo', 8, 4, 'Active'),
('N-806', 'Nikunjo', 8, 4, 'Active'),
('N-807', 'Nikunjo', 8, 4, 'Active'),
('N-808', 'Nikunjo', 8, 4, 'Active'),
('N-809', 'Nikunjo', 8, 4, 'Active'),

('N-901', 'Nikunjo', 9, 4, 'Active'),
('N-902', 'Nikunjo', 9, 4, 'Active'),
('N-903', 'Nikunjo', 9, 4, 'Active'),
('N-904', 'Nikunjo', 9, 4, 'Active'),
('N-905', 'Nikunjo', 9, 4, 'Active'),
('N-906', 'Nikunjo', 9, 4, 'Active'),
('N-907', 'Nikunjo', 9, 4, 'Active'),
('N-908', 'Nikunjo', 9, 4, 'Active'),
('N-909', 'Nikunjo', 9, 4, 'Active');

-- Sample Data for Testing Well-Being & Homesickness Alerts

INSERT INTO Student (Student_ID, FirstName, LastName, Email, Room_No) 
VALUES (1, 'Rakib', 'Hasan', 'rakib.hasan@g.bracu.ac.bd', 'M-101');

INSERT INTO Student (Student_ID, FirstName, LastName, Email, Room_No) 
VALUES (2, 'Sadia', 'Rahman', 'sadia.rahman@g.bracu.ac.bd', 'M-101');

INSERT INTO Student (Student_ID, FirstName, LastName, Email, Room_No) 
VALUES (3, 'Nafis', 'Ahmed', 'nafis.ahmed@g.bracu.ac.bd', 'M-101');

INSERT INTO Student (Student_ID, FirstName, LastName, Email, Room_No) 
VALUES (4, 'Anika', 'Tabassum', 'anika.tabassum@g.bracu.ac.bd', 'M-101');

INSERT INTO Student (Student_ID, FirstName, LastName, Email, Room_No) 
VALUES (5, 'Tanvir', 'Hossain', 'tanvir.hossain@g.bracu.ac.bd', 'M-101');

INSERT INTO Login (Student_ID, PasswordHash, CreatedAt) 
VALUES (1, 'hashed_password', DATE_SUB(NOW(), INTERVAL 10 DAY));

INSERT INTO Login (Student_ID, PasswordHash, CreatedAt) 
VALUES (2, 'hashed_password', DATE_SUB(NOW(), INTERVAL 4 DAY));

INSERT INTO Login (Student_ID, PasswordHash, CreatedAt) 
VALUES (3, 'hashed_password', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO Login (Student_ID, PasswordHash, CreatedAt) 
VALUES (4, 'hashed_password', DATE_SUB(NOW(), INTERVAL 10 DAY));

INSERT INTO Login (Student_ID, PasswordHash, CreatedAt) 
VALUES (5, 'hashed_password', DATE_SUB(NOW(), INTERVAL 10 DAY));

INSERT INTO mood_log (student_id, log_date, mood_score) VALUES 
(1, CURDATE(), 2),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1),
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 2);

INSERT INTO mood_log (student_id, log_date, mood_score) VALUES 
(5, CURDATE(), 1),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1),
(5, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1);

INSERT INTO mood_log (student_id, log_date, mood_score) VALUES 
(4, CURDATE(), 5),
(4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 4);

INSERT INTO meal (student_id, meal_type, meal_serve_date) VALUES 
(1, 'Lunch', CURDATE());

INSERT INTO meal (student_id, meal_type, meal_serve_date) VALUES 
(4, 'Lunch', CURDATE());