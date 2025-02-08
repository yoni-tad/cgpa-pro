CREATE DATABASE IF NOT EXISTS yonitatd_cgpa;
USE yonitatd_cgpa;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telegram_id BIGINT UNIQUE NOT NULL,
    username VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telegram_id INT NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    credit_hours INT NOT NULL,
    grade VARCHAR(10) NOT NULL,
    grade_point DECIMAL(3,2) NOT NULL,
    status VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cgpa_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telegram_id INT NOT NULL,
    total_credit_hours INT NOT NULL,
    total_grade_points DECIMAL(5,2) NOT NULL,
    gpa DECIMAL(4,2) NOT NULL,
    cgpa DECIMAL(4,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);