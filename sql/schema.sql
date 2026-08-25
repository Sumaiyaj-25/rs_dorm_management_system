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

CREATE TABLE IF NOT EXISTS Parcel (

    P_ID INT AUTO_INCREMENT PRIMARY KEY,

    Tracking_Number VARCHAR(100) NOT NULL,

    Status VARCHAR(50) NOT NULL,

    Locker_Number VARCHAR(50),

    Arrival_Date DATETIME,

    Receive_Time DATETIME NULL,

    Student_ID INT,

    Handled_By INT,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
        ON DELETE SET NULL

);