-- Active: 1722854869653@@127.0.0.1@3306@eFSMS
CREATE DATABASE FieldOpportunities;
USE FieldOpportunities;

-- Create Student Table
CREATE TABLE Student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    privilege ENUM('student', 'admin') NOT NULL
);

-- Create Staff Table
CREATE TABLE Staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    privilege ENUM('staff', 'admin') NOT NULL
);

-- Create Field Chance Table
CREATE TABLE FieldChance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    available_chance INT NOT NULL
);

-- Create Request Table
CREATE TABLE Request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    staff_id INT, -- Added to track which staff member approved the request
    registration_no VARCHAR(100) NOT NULL,
    university_name VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_of_study INT NOT NULL,
    start_field DATE NOT NULL,
    end_field DATE NOT NULL,
    area_specialization VARCHAR (100) NOT NULL,
    upload_request_letter VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES Student(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES Staff(id) ON DELETE SET NULL -- If the staff is deleted, the field is set to NULL
);

-- Create Feedback Table
CREATE TABLE Feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    staff_id INT NOT NULL,
    field_id INT NOT NULL,
    upload_document VARCHAR(255),
    description VARCHAR (1000) NOT NULL,
    status ENUM('Pending', 'Reviewed') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES Student(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES Staff(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES FieldChance(id) ON DELETE CASCADE
);

-- Create Opinion Table
CREATE TABLE Opinion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    staff_id INT, -- Added to track which staff member viewed the opinion
    email VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15),
    description VARCHAR (1000) NOT NULL,
    FOREIGN KEY (student_id) REFERENCES Student(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES Staff(id) ON DELETE SET NULL -- If the staff is deleted, the field is set to NULL
);
