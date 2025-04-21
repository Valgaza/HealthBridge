-- Create database
CREATE DATABASE IF NOT EXISTS healthbridge;
USE healthbridge;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('patient', 'doctor') NOT NULL,
    location VARCHAR(100) NOT NULL,
    specialization VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Symptoms table
CREATE TABLE IF NOT EXISTS symptoms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    symptom_name VARCHAR(100) NOT NULL,
    symptom_date DATE NOT NULL,
    symptom_time TIME NOT NULL,
    symptom_severity ENUM('low', 'medium', 'high') NOT NULL,
    doctor_type VARCHAR(50),
    symptom_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Consultations table
CREATE TABLE IF NOT EXISTS consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT,
    symptom_id INT,
    consultation_date DATE NOT NULL,
    consultation_time TIME,
    consultation_type ENUM('online', 'in-person'),
    consultation_status ENUM('pending', 'accepted', 'completed', 'cancelled') NOT NULL,
    diagnosis VARCHAR(255),
    consultation_notes TEXT,
    follow_up ENUM('yes', 'no') DEFAULT 'no',
    follow_up_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (symptom_id) REFERENCES symptoms(id) ON DELETE SET NULL
);

-- Prescriptions table
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    consultation_id INT,
    medication VARCHAR(100) NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    duration_value INT NOT NULL,
    duration_unit ENUM('days', 'weeks', 'months') NOT NULL,
    instructions TEXT,
    prescription_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL
);

-- Health Tips table
CREATE TABLE IF NOT EXISTS health_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    patient_id INT,
    consultation_id INT,
    tip_title VARCHAR(100) NOT NULL,
    tip_category VARCHAR(50) NOT NULL,
    tip_content TEXT NOT NULL,
    tip_date DATE NOT NULL,
    visibility ENUM('patient', 'public') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL
);

-- Fitness Goals table
CREATE TABLE IF NOT EXISTS fitness_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    goal_title VARCHAR(100) NOT NULL,
    goal_description TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Diet Plans table
CREATE TABLE IF NOT EXISTS diet_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    plan_title VARCHAR(100) NOT NULL,
    plan_description TEXT NOT NULL,
    meal_suggestions TEXT NOT NULL,
    restrictions TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data for testing
-- Sample users (password is 'password123' hashed)
INSERT INTO users (full_name, email, password, user_type, location, specialization) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'New York', NULL),
('Dr. Sarah Johnson', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', 'New York', 'neurology');

-- Sample symptoms
INSERT INTO symptoms (user_id, symptom_name, symptom_date, symptom_time, symptom_severity, doctor_type, symptom_notes) VALUES
(1, 'Headache', '2023-05-15', '09:30:00', 'high', 'neurology', 'Severe pain on the right side of the head, accompanied by sensitivity to light and sound.'),
(1, 'Fever', '2023-05-10', '14:15:00', 'medium', 'general', 'Temperature of 101°F, accompanied by chills and fatigue.'),
(1, 'Cough', '2023-05-05', '11:45:00', 'low', 'general', 'Dry cough, mostly in the morning.');

-- Sample consultations
INSERT INTO consultations (user_id, doctor_id, symptom_id, consultation_date, consultation_time, consultation_type, consultation_status) VALUES
(1, 2, 1, '2023-05-20', '10:30:00', 'online', 'pending');

-- Sample prescriptions
INSERT INTO prescriptions (patient_id, doctor_id, consultation_id, medication, dosage, frequency, duration_value, duration_unit, instructions, prescription_date) VALUES
(1, 2, 1, 'Ibuprofen', '400mg', 'three', 7, 'days', 'Take after meals', '2023-05-16');

-- Sample health tips
INSERT INTO health_tips (doctor_id, patient_id, tip_title, tip_category, tip_content, tip_date, visibility) VALUES
(2, 1, 'Stay Hydrated', 'general', 'Drink at least 8 glasses of water daily to maintain proper hydration, especially during summer months.', '2023-05-16', 'patient'),
(2, 1, 'Improve Sleep Quality', 'sleep', 'Maintain a consistent sleep schedule and avoid screens at least 1 hour before bedtime.', '2023-05-16', 'patient');
