
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
CREATE TABLE IF NOT EXISTS Login (
    Login_ID INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID INT NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    LastLogin DATETIME NULL,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
        ON DELETE CASCADE,

    UNIQUE KEY uniq_student_login (Student_ID)
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
    released_status VARCHAR(20) DEFAULT 'Pending', -- 'Pending', 'Available', 'Claimed'
    claim_status BOOLEAN DEFAULT FALSE,
    claimed_by INT NULL, -- The student who claims it
    FOREIGN KEY (student_id) REFERENCES Student(Student_ID) ON DELETE CASCADE,
    FOREIGN KEY (released_by) REFERENCES Student(Student_ID) ON DELETE SET NULL,
    FOREIGN KEY (claimed_by) REFERENCES Student(Student_ID) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS Staff (

    Staff_ID INT AUTO_INCREMENT PRIMARY KEY,

    Name VARCHAR(100) NOT NULL,

    Phone_Number VARCHAR(20),

    Email VARCHAR(100) UNIQUE,

    Role VARCHAR(50)
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

CREATE TABLE IF NOT EXISTS moderator (
    moderator_id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,

    FOREIGN KEY (staff_id)
        REFERENCES Staff(Staff_ID)
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
        REFERENCES moderator(moderator_id)
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