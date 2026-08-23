CREATE DATABASE IF NOT EXISTS dorm_management;
USE dorm_management;

CREATE TABLE IF NOT EXISTS Student (
    Student_ID INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Phone_Number VARCHAR(20),
    Gender VARCHAR(10),
    Contributor_Points INT DEFAULT 0,
    Department VARCHAR(50)
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

CREATE TABLE IF NOT EXISTS Room (
    Room_No VARCHAR(10) PRIMARY KEY,
    Dorm_name VARCHAR(50),
    Floor INT,
    Capacity INT,
    Status VARCHAR(20) DEFAULT 'Active'
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