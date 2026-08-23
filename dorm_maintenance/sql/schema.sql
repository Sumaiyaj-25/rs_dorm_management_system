

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


CREATE TABLE IF NOT EXISTS Room (
    Room_No     VARCHAR(10) PRIMARY KEY,
    Dorm_name   VARCHAR(50),
    Floor       INT,
    Capacity    INT,
    Status      VARCHAR(20) DEFAULT 'Active'
);

CREATE TABLE IF NOT EXISTS Maintenance_request (
    RequestID      INT AUTO_INCREMENT PRIMARY KEY,
    DateSubmitted  DATETIME DEFAULT CURRENT_TIMESTAMP,
    Description    TEXT NOT NULL,
    Photo          VARCHAR(255) NULL,          -- stores file path, not BLOB
    Category       VARCHAR(50)  DEFAULT 'Other',
    Priority       ENUM('High','Medium','Low') DEFAULT 'Low',
    Status         ENUM('Submitted','In Progress','Resolved') DEFAULT 'Submitted',
    Student_ID     INT NOT NULL,
    Room_No        VARCHAR(10) NOT NULL,
	Dorm_name      VARCHAR(10) NOT NULL,
    FOREIGN KEY (Student_ID) REFERENCES Student(Student_ID),
    FOREIGN KEY (Room_No) REFERENCES Room(Room_No)
);


CREATE TABLE IF NOT EXISTS Staff (
    Staff_ID      INT AUTO_INCREMENT PRIMARY KEY,
    FirstName     VARCHAR(50) NOT NULL,
    LastName      VARCHAR(50) NOT NULL,
    Email         VARCHAR(100) NOT NULL UNIQUE,
    Phone_Number  VARCHAR(20),
	Role VARCHAR(50)
);



CREATE TABLE IF NOT EXISTS Attendance (
    AttendanceID  INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID    INT NOT NULL,
    Date          DATE NOT NULL,
    Entry_time    DATETIME NULL,
    Exit_time     DATETIME NULL,
    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
);



CREATE TABLE IF NOT EXISTS Visitor (
    Visitor_NID   VARCHAR(30) PRIMARY KEY,
    Name          VARCHAR(100) NOT NULL,
    Phone         VARCHAR(20),
    QRCode        VARCHAR(255),
    EntryTime     DATETIME NULL,
    ExitTime      DATETIME NULL,
	Relationship  VARCHAR(50),
    Registered_By INT NOT NULL,
    FOREIGN KEY (Registered_By)
        REFERENCES Student(Student_ID)
);


CREATE TABLE IF NOT EXISTS Room_Transfer (
    TransferID          INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID          INT NOT NULL,
    Reason               TEXT,
	CurrentRoomNo        VARCHAR(10),
    RecommendedRoomNo    VARCHAR(10),
    ApprovalDate         DATETIME NULL,
    Status               ENUM('Pending','Approved','Rejected')
                         DEFAULT 'Pending',

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (RecommendedRoomNo)
        REFERENCES Room(Room_No),
		
	FOREIGN KEY (CurrentRoomNo)
    REFERENCES Room(Room_No)
);



CREATE TABLE IF NOT EXISTS Roommate_Preference (
    PreferenceID    INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID      INT NOT NULL,
    SleepingHabit   VARCHAR(50),
    Cleanliness     VARCHAR(50),
    NoiseTolerance  VARCHAR(50),
    StudyHabit      VARCHAR(50),
	Others VARCHAR(255),
	
    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID),

    UNIQUE KEY uniq_student_pref (Student_ID)
);


CREATE TABLE IF NOT EXISTS Roommate_Match (
    Match_ID              INT AUTO_INCREMENT PRIMARY KEY,
    Requesting_Student_ID INT NOT NULL,
    Potential_Roommate_ID INT NOT NULL,
    CompatibleScore       DECIMAL(5,2),
    Status                ENUM('Pending','Accepted','Rejected')
                          DEFAULT 'Pending',

    FOREIGN KEY (Requesting_Student_ID)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (Potential_Roommate_ID)
        REFERENCES Student(Student_ID)
);



CREATE TABLE IF NOT EXISTS Mood_Log (
    Log_ID       INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID   INT NOT NULL,
    Mood_Score   INT NOT NULL,
    Log_Date     DATE NOT NULL,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
);



CREATE TABLE IF NOT EXISTS Counselor (
    Counselor_ID  INT AUTO_INCREMENT PRIMARY KEY,
    FirstName     VARCHAR(50),
    LastName      VARCHAR(50),
    Email         VARCHAR(100)
);



CREATE TABLE IF NOT EXISTS Counselor_Alert (
    Alert_ID      INT AUTO_INCREMENT PRIMARY KEY,
    Counselor_ID  INT NOT NULL,
    Student_ID    INT NOT NULL,
    Log_ID        INT NULL,
    Alert_Date    DATETIME DEFAULT CURRENT_TIMESTAMP,
    Status        VARCHAR(30) DEFAULT 'Open',

    FOREIGN KEY (Counselor_ID)
        REFERENCES Counselor(Counselor_ID),

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (Log_ID)
        REFERENCES Mood_Log(Log_ID)
);



CREATE TABLE IF NOT EXISTS Moderator (
    Moderator_ID  INT AUTO_INCREMENT PRIMARY KEY,
    FirstName     VARCHAR(50),
    LastName      VARCHAR(50)
);


CREATE TABLE IF NOT EXISTS Meal (
    Meal_ID           INT AUTO_INCREMENT PRIMARY KEY,
    Meal_Serve_Date   DATE,
    Meal_Type         VARCHAR(50),
    Token_No          VARCHAR(30),
    Submitted_By      INT NOT NULL,
    Course_Reference  VARCHAR(100),

    Approval_Status   ENUM('Pending','Approved','Rejected')
                      DEFAULT 'Pending',

    Approved_By       INT NULL,
    Rating            DECIMAL(3,2),
    Is_Released       BOOLEAN DEFAULT FALSE,

    Claimed_By        INT NULL,
    Claim_Status      VARCHAR(30) DEFAULT 'Unclaimed',

    FOREIGN KEY (Submitted_By)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (Approved_By)
        REFERENCES Moderator(Moderator_ID),

    FOREIGN KEY (Claimed_By)
        REFERENCES Student(Student_ID)
);


CREATE TABLE IF NOT EXISTS Academic_Resource (
    Resource_ID      INT AUTO_INCREMENT PRIMARY KEY,
    Resource_Type    VARCHAR(50),
    Released_By      INT NULL,
    Released_Status  ENUM('Pending','Released') DEFAULT 'Pending',
    Course_Reference VARCHAR(100),
    Approval_Status  ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    Rating           DECIMAL(3,2),
    Submitted_By     INT NULL,

    FOREIGN KEY (Released_By)
        REFERENCES Staff(Staff_ID),

    FOREIGN KEY (Submitted_By)
        REFERENCES Student(Student_ID)
);

CREATE TABLE IF NOT EXISTS Academic_Resource_Access (
    Access_ID    INT AUTO_INCREMENT PRIMARY KEY,
    Resource_ID  INT NOT NULL,
    Student_ID   INT NOT NULL,
    Claim_Status VARCHAR(30),
    Rating       DECIMAL(3,2),

    FOREIGN KEY (Resource_ID)
        REFERENCES Academic_Resource(Resource_ID),

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
);


CREATE TABLE IF NOT EXISTS Laundry_Machine (
    Machine_ID    INT AUTO_INCREMENT PRIMARY KEY,
    Machine_Type  VARCHAR(50),
    Status        VARCHAR(30) DEFAULT 'Available',
    Assigned_To   INT NULL,

    FOREIGN KEY (Assigned_To)
        REFERENCES Staff(Staff_ID)
);



CREATE TABLE IF NOT EXISTS Laundry_Booking (
    B_ID          INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID    INT NOT NULL,
    Machine_ID    INT NOT NULL,
    Start_Time    DATETIME,
    End_Time      DATETIME,
    Status        VARCHAR(30) DEFAULT 'Booked',
    Booking_Time  DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (Machine_ID)
        REFERENCES Laundry_Machine(Machine_ID)
);



CREATE TABLE IF NOT EXISTS Laundry_Item (
    Item_ID         INT AUTO_INCREMENT PRIMARY KEY,
    Item_Type       VARCHAR(50),
    Owned_By        INT NOT NULL,
    Laundry_Status  VARCHAR(30) DEFAULT 'Received',
    Payment_Status  VARCHAR(30) DEFAULT 'Unpaid',

    FOREIGN KEY (Owned_By)
        REFERENCES Student(Student_ID)
);



CREATE TABLE IF NOT EXISTS Parcel (
    P_ID             INT AUTO_INCREMENT PRIMARY KEY,
    Tracking_Number  VARCHAR(100),
    Status           VARCHAR(30) DEFAULT 'Arrived',
    Locker_Number    VARCHAR(20),
    Arrival_Date     DATETIME DEFAULT CURRENT_TIMESTAMP,
    Receive_Time     DATETIME NULL,
    Student_ID       INT NOT NULL,
    Handled_By       INT NULL,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (Handled_By)
        REFERENCES Staff(Staff_ID)
);



CREATE TABLE IF NOT EXISTS Leave_Request (
    Request_ID   INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID   INT NOT NULL,
    Reason       TEXT,
    Leave_Date   DATE,
    Return_Date  DATE,

    Status       ENUM('Pending','Approved','Rejected')
                 DEFAULT 'Pending',

    Approved_By  INT NULL,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (Approved_By)
        REFERENCES Staff(Staff_ID)
);



CREATE TABLE IF NOT EXISTS SOS_Request (
    SOS_ID          INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID      INT NOT NULL,
    Emergency_Type  VARCHAR(50),
    Request_Time    DATETIME DEFAULT CURRENT_TIMESTAMP,
    Receive_Time    DATETIME NULL,

    Status          ENUM('Open','Acknowledged','Resolved')
                    DEFAULT 'Open',

    Received_By     INT NULL,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID),

    FOREIGN KEY (Received_By)
        REFERENCES Staff(Staff_ID)
);



CREATE TABLE IF NOT EXISTS Medical_Record (
    R_ID          INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID    INT NOT NULL,
    Diagnosis     TEXT,
    Treatment     TEXT,
    Prescription  TEXT,
    Visit_Date    DATE,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
);



CREATE TABLE IF NOT EXISTS Medicine_Order (
    Order_ID      INT AUTO_INCREMENT PRIMARY KEY,
    R_ID          INT NOT NULL,
    Medicine_Name VARCHAR(100),
    Quantity      INT,
    Cost          DECIMAL(10,2),
    Status        VARCHAR(30) DEFAULT 'Pending',

    FOREIGN KEY (R_ID)
        REFERENCES Medical_Record(R_ID)
);


CREATE TABLE IF NOT EXISTS Exit_Clearance (
    Clearance_ID      INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID        INT NOT NULL,

    Clearance_Status  ENUM('Pending','Cleared','Blocked')
                      DEFAULT 'Pending',

    Cleared_At        DATETIME NULL,

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
);


CREATE TABLE IF NOT EXISTS Library_Item (
    Item_ID       INT AUTO_INCREMENT PRIMARY KEY,
    Item_Type     VARCHAR(50),
    Booked_By     INT NOT NULL,
    Booked_Status VARCHAR(30) DEFAULT 'Available',

    FOREIGN KEY (Booked_By)
        REFERENCES Student(Student_ID)
);


CREATE TABLE IF NOT EXISTS Account (
    Account_ID      INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID      INT NOT NULL,
    Invoice_ID      VARCHAR(50),
    Payment_Type    VARCHAR(50),
    Payment_Status  VARCHAR(30) DEFAULT 'Unpaid',

    FOREIGN KEY (Student_ID)
        REFERENCES Student(Student_ID)
);


CREATE TABLE IF NOT EXISTS Room_Quality (
    UsageID          INT AUTO_INCREMENT PRIMARY KEY,
    Room_No          VARCHAR(10) NOT NULL,
    Month            VARCHAR(10),
    WaterUsage       DECIMAL(10,2),
    ElectricityUsage DECIMAL(10,2),
    Points           INT,

    FOREIGN KEY (Room_No)
        REFERENCES Room(Room_No)
);