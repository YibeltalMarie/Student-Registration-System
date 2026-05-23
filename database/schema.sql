-- ============================================================
-- Student Registration System — Full Schema
-- Roles: admin | student  (only two roles)
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS student_registration_system
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_registration_system;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id                       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username                 VARCHAR(50)  NOT NULL UNIQUE,
    email                    VARCHAR(150) NOT NULL UNIQUE,
    password                 VARCHAR(255) NOT NULL,
    role                     ENUM('admin','student') NOT NULL DEFAULT 'student',
    must_change_password     TINYINT(1)   NOT NULL DEFAULT 0,
    email_verified_at        DATETIME     DEFAULT NULL,
    email_verification_token VARCHAR(100) DEFAULT NULL,
    reset_token              VARCHAR(100) DEFAULT NULL,
    reset_token_expires      DATETIME     DEFAULT NULL,
    remember_token           VARCHAR(100) DEFAULT NULL,
    failed_attempts          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until             DATETIME     DEFAULT NULL,
    last_login               DATETIME     DEFAULT NULL,
    created_at               DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin: password = "password" (plain bcrypt, no pepper — run fix_admin.php to set Admin@123)
INSERT IGNORE INTO users (username, email, password, role, must_change_password, email_verified_at)
VALUES ('admin', 'admin@example.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin', 0, NOW());

-- ============================================================
-- DEPARTMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS departments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    code        VARCHAR(20)  NOT NULL UNIQUE,
    description TEXT         DEFAULT NULL,
    parent_id   INT UNSIGNED DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO departments (id,name,code,description,parent_id) VALUES
(1,'Faculty of Engineering','ENG','Engineering faculty',NULL),
(2,'Computer Science','CS','CS department',1),
(3,'Electrical Engineering','EE','EE department',1),
(4,'Faculty of Business','BUS','Business faculty',NULL),
(5,'Accounting','ACC','Accounting dept',4),
(6,'Management','MGT','Management dept',4),
(7,'Faculty of Sciences','SCI','Sciences faculty',NULL),
(8,'Physics','PHY','Physics dept',7),
(9,'Mathematics','MATH','Mathematics dept',7);

-- ============================================================
-- STUDENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS students (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id       VARCHAR(20)  NOT NULL UNIQUE,
    first_name       VARCHAR(100) NOT NULL,
    last_name        VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NOT NULL UNIQUE,
    phone            VARCHAR(20)  DEFAULT NULL,
    date_of_birth    DATE         DEFAULT NULL,
    gender           ENUM('male','female','other') DEFAULT NULL,
    address          TEXT         DEFAULT NULL,
    department_id    INT UNSIGNED DEFAULT NULL,
    enrollment_year  YEAR         NOT NULL,
    status           ENUM('active','inactive','graduated','suspended') NOT NULL DEFAULT 'active',
    profile_image    VARCHAR(255) DEFAULT NULL,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO students
(student_id,first_name,last_name,email,phone,date_of_birth,gender,department_id,enrollment_year,status)
VALUES
('STU20240001','Abebe','Girma','abebe.girma@example.com','+251911000001','2000-03-15','male',2,2024,'active'),
('STU20240002','Tigist','Haile','tigist.haile@example.com','+251911000002','2001-06-20','female',2,2024,'active'),
('STU20240003','Dawit','Tadesse','dawit.tadesse@example.com','+251911000003','1999-09-10','male',3,2023,'active'),
('STU20240004','Meron','Bekele','meron.bekele@example.com','+251911000004','2000-12-01','female',5,2024,'active'),
('STU20240005','Samuel','Alemu','samuel.alemu@example.com','+251911000005','2001-02-28','male',8,2022,'graduated');

-- Sample student user accounts (password = "12345678" plain bcrypt — run fix_admin.php to also fix these)
-- For sample data we use the same placeholder hash; fix_admin.php sets them properly
INSERT IGNORE INTO users (username,email,password,role,must_change_password,email_verified_at) VALUES
('STU20240001','abebe.girma@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student',1,NOW()),
('STU20240002','tigist.haile@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student',1,NOW()),
('STU20240003','dawit.tadesse@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student',1,NOW()),
('STU20240004','meron.bekele@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student',1,NOW()),
('STU20240005','samuel.alemu@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student',1,NOW());

-- ============================================================
-- COURSES
-- ============================================================
CREATE TABLE IF NOT EXISTS courses (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code          VARCHAR(20)  NOT NULL UNIQUE,
    name          VARCHAR(150) NOT NULL,
    description   TEXT         DEFAULT NULL,
    credits       TINYINT UNSIGNED NOT NULL DEFAULT 3,
    department_id INT UNSIGNED DEFAULT NULL,
    max_students  SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO courses (code,name,credits,department_id,max_students) VALUES
('CS101','Introduction to Programming',3,2,40),
('CS201','Data Structures & Algorithms',3,2,35),
('CS301','Database Systems',3,2,30),
('EE101','Circuit Theory',3,3,35),
('EE201','Digital Electronics',3,3,30),
('ACC101','Principles of Accounting',3,5,40),
('MGT101','Principles of Management',3,6,40),
('MATH101','Calculus I',4,9,50),
('PHY101','General Physics I',4,8,45);

-- ============================================================
-- ENROLLMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS enrollments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id  INT UNSIGNED NOT NULL,
    course_id   INT UNSIGNED NOT NULL,
    status      ENUM('enrolled','dropped','completed') NOT NULL DEFAULT 'enrolled',
    grade       DECIMAL(5,2) DEFAULT NULL,
    enrolled_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_enrollment (student_id,course_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id)  REFERENCES courses(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO enrollments (student_id,course_id,status,grade) VALUES
(1,1,'enrolled',88.5),(1,2,'enrolled',76.0),
(2,1,'enrolled',95.0),(2,3,'enrolled',82.5),
(3,4,'enrolled',79.0),(4,5,'enrolled',90.0),
(5,7,'completed',97.5),(5,8,'completed',94.0);

-- ============================================================
-- ACTIVITY LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED DEFAULT NULL,
    action      VARCHAR(50)  NOT NULL,
    entity_type VARCHAR(50)  NOT NULL,
    entity_id   INT UNSIGNED NOT NULL,
    old_data    LONGTEXT     DEFAULT NULL,
    new_data    LONGTEXT     DEFAULT NULL,
    ip_address  VARCHAR(45)  DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- VIEW
-- ============================================================
CREATE OR REPLACE VIEW student_enrollment_summary AS
SELECT s.id, s.student_id,
       CONCAT(s.first_name,' ',s.last_name) AS full_name,
       d.name AS department_name,
       s.enrollment_year, s.status,
       COUNT(e.id) AS total_courses,
       ROUND(AVG(e.grade),2) AS average_grade
FROM students s
LEFT JOIN departments d ON s.department_id=d.id
LEFT JOIN enrollments e ON e.student_id=s.id
GROUP BY s.id,s.student_id,s.first_name,s.last_name,d.name,s.enrollment_year,s.status;

-- ============================================================
-- STORED PROCEDURE
-- ============================================================
DROP PROCEDURE IF EXISTS calculate_letter_grade;
DELIMITER $$
CREATE PROCEDURE calculate_letter_grade(IN p_grade DECIMAL(5,2), OUT p_letter CHAR(2))
BEGIN
    SET p_letter = CASE
        WHEN p_grade >= 90 THEN 'A'
        WHEN p_grade >= 80 THEN 'B'
        WHEN p_grade >= 70 THEN 'C'
        WHEN p_grade >= 60 THEN 'D'
        ELSE 'F'
    END;
END$$
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
