-- Create database
CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Users table (students and admins)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) UNIQUE,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    course VARCHAR(100),
    year_level VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_student_id (student_id)
);

-- Courses table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    description TEXT,
    instructor VARCHAR(100),
    schedule_day VARCHAR(20),
    schedule_time VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attendance table
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'absent',
    check_in_time TIME,
    notes TEXT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (student_id, course_id, date)
);

-- Enrollment table (students to courses)
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, role) 
VALUES ('admin', 'admin@school.edu', '$2y$10$YourHashedPasswordHere', 'System Administrator', 'admin');

-- Insert sample courses
INSERT INTO courses (course_code, course_name, description, instructor, schedule_day, schedule_time) VALUES
('CS101', 'Introduction to Programming', 'Basic programming concepts', 'Dr. Smith', 'Monday, Wednesday', '9:00 AM - 10:30 AM'),
('MATH201', 'Calculus I', 'Differential calculus', 'Prof. Johnson', 'Tuesday, Thursday', '11:00 AM - 12:30 PM'),
('ENG102', 'English Composition', 'Writing and communication skills', 'Dr. Williams', 'Monday, Friday', '2:00 PM - 3:30 PM');

-- Create indexes for performance
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_attendance_student_course ON attendance(student_id, course_id);
CREATE INDEX idx_enrollment_student ON enrollments(student_id);