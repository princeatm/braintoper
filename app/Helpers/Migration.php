<?php
/**
 * Database Migration Runner
 */

namespace App\Helpers;

use PDO;

class Migration
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Run all migrations
     */
    public function runAll(): void
    {
        $this->createTables();
        Logger::info('Database migration completed successfully');
    }

    /**
     * Create all tables
     */
    private function createTables(): void
    {
        // Create users table - base table for all user types
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                login_code VARCHAR(50) UNIQUE NOT NULL,
                role ENUM('student', 'teacher', 'admin', 'superadmin') NOT NULL,
                pin_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                is_active BOOLEAN DEFAULT TRUE,
                
                INDEX idx_login_code (login_code),
                INDEX idx_role (role),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create classes table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS classes (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL UNIQUE,
                code VARCHAR(50) UNIQUE NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                INDEX idx_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create grades table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS grades (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL,
                code VARCHAR(50) UNIQUE NOT NULL,
                level INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                UNIQUE KEY unique_grade_level (name, level),
                INDEX idx_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create arms table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS arms (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL UNIQUE,
                code VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                INDEX idx_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create academic_groups table (JSS1A, JSS1B, etc.)
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS academic_groups (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                class_id INT UNSIGNED NOT NULL,
                grade_id INT UNSIGNED NOT NULL,
                arm_id INT UNSIGNED NOT NULL,
                code VARCHAR(100) UNIQUE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
                FOREIGN KEY (grade_id) REFERENCES grades(id) ON DELETE CASCADE,
                FOREIGN KEY (arm_id) REFERENCES arms(id) ON DELETE CASCADE,
                UNIQUE KEY unique_group (class_id, grade_id, arm_id),
                INDEX idx_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create students table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS students (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                user_id INT UNSIGNED NOT NULL UNIQUE,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255),
                phone VARCHAR(20),
                class_id INT UNSIGNED NOT NULL,
                grade_id INT UNSIGNED NOT NULL,
                arm_id INT UNSIGNED NOT NULL,
                academic_group_id INT UNSIGNED NOT NULL,
                registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_active BOOLEAN DEFAULT TRUE,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
                FOREIGN KEY (grade_id) REFERENCES grades(id) ON DELETE RESTRICT,
                FOREIGN KEY (arm_id) REFERENCES arms(id) ON DELETE RESTRICT,
                FOREIGN KEY (academic_group_id) REFERENCES academic_groups(id) ON DELETE RESTRICT,
                INDEX idx_user_id (user_id),
                INDEX idx_academic_group_id (academic_group_id),
                FULLTEXT idx_name (first_name, last_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create teachers table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS teachers (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                user_id INT UNSIGNED NOT NULL UNIQUE,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                specialization VARCHAR(255),
                registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_active BOOLEAN DEFAULT TRUE,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create subjects table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS subjects (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(100) UNIQUE NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_active BOOLEAN DEFAULT TRUE,
                
                INDEX idx_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create teacher_subjects table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS teacher_subjects (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                teacher_id INT UNSIGNED NOT NULL,
                subject_id INT UNSIGNED NOT NULL,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
                FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
                UNIQUE KEY unique_assignment (teacher_id, subject_id),
                INDEX idx_teacher_id (teacher_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create exams table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS exams (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                teacher_id INT UNSIGNED NOT NULL,
                subject_id INT UNSIGNED NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                exam_code VARCHAR(50) UNIQUE NOT NULL,
                class_id INT UNSIGNED NOT NULL,
                grade_id INT UNSIGNED NOT NULL,
                arm_id INT UNSIGNED,
                academic_group_id INT UNSIGNED,
                duration_minutes INT NOT NULL DEFAULT 60,
                total_marks INT NOT NULL DEFAULT 100,
                passing_marks INT NOT NULL DEFAULT 50,
                status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
                show_results BOOLEAN DEFAULT FALSE,
                randomize_questions BOOLEAN DEFAULT TRUE,
                randomize_options BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                published_at TIMESTAMP NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
                FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
                FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
                FOREIGN KEY (grade_id) REFERENCES grades(id) ON DELETE RESTRICT,
                FOREIGN KEY (academic_group_id) REFERENCES academic_groups(id) ON DELETE SET NULL,
                INDEX idx_exam_code (exam_code),
                INDEX idx_teacher_id (teacher_id),
                INDEX idx_status (status),
                INDEX idx_published_at (published_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create questions table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS questions (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                exam_id INT UNSIGNED NOT NULL,
                subject_id INT UNSIGNED NOT NULL,
                question_text TEXT NOT NULL,
                question_image VARCHAR(255),
                marks INT NOT NULL DEFAULT 1,
                difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
                order_position INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
                FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
                INDEX idx_exam_id (exam_id),
                INDEX idx_order (order_position)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create options table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS options (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                question_id INT UNSIGNED NOT NULL,
                option_letter VARCHAR(1) NOT NULL COMMENT 'A, B, C, D',
                option_text TEXT NOT NULL,
                is_correct BOOLEAN NOT NULL DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
                UNIQUE KEY unique_option (question_id, option_letter),
                INDEX idx_question_id (question_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create exam_attempts table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS exam_attempts (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                exam_id INT UNSIGNED NOT NULL,
                student_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                submitted_at TIMESTAMP NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                browser_tabs_switched INT DEFAULT 0,
                window_minimized_count INT DEFAULT 0,
                focus_lost_count INT DEFAULT 0,
                is_completed BOOLEAN DEFAULT FALSE,
                auto_submitted BOOLEAN DEFAULT FALSE,
                auto_submit_reason VARCHAR(255),
                
                FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
                FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_attempt (exam_id, student_id),
                INDEX idx_exam_id (exam_id),
                INDEX idx_student_id (student_id),
                INDEX idx_started_at (started_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create exam_answers table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS exam_answers (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                exam_attempt_id INT UNSIGNED NOT NULL,
                question_id INT UNSIGNED NOT NULL,
                selected_option_id INT UNSIGNED,
                is_skipped BOOLEAN DEFAULT FALSE,
                answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (exam_attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
                FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
                FOREIGN KEY (selected_option_id) REFERENCES options(id) ON DELETE SET NULL,
                UNIQUE KEY unique_answer (exam_attempt_id, question_id),
                INDEX idx_exam_attempt_id (exam_attempt_id),
                INDEX idx_question_id (question_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create exam_results table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS exam_results (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                exam_attempt_id INT UNSIGNED NOT NULL UNIQUE,
                exam_id INT UNSIGNED NOT NULL,
                student_id INT UNSIGNED NOT NULL,
                total_questions INT NOT NULL,
                correct_answers INT NOT NULL,
                skipped INT NOT NULL,
                total_marks INT NOT NULL,
                obtained_marks INT NOT NULL,
                percentage DECIMAL(5, 2),
                is_passed BOOLEAN,
                grade VARCHAR(5),
                graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (exam_attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
                FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
                FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
                INDEX idx_exam_id (exam_id),
                INDEX idx_student_id (student_id),
                INDEX idx_percentage (percentage)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create practice_questions table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS practice_questions (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                subject_id INT UNSIGNED NOT NULL,
                question_text TEXT NOT NULL,
                question_image VARCHAR(255),
                marks INT NOT NULL DEFAULT 1,
                difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
                INDEX idx_subject_id (subject_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create practice_options table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS practice_options (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                practice_question_id INT UNSIGNED NOT NULL,
                option_letter VARCHAR(1) NOT NULL,
                option_text TEXT NOT NULL,
                is_correct BOOLEAN NOT NULL DEFAULT FALSE,
                
                FOREIGN KEY (practice_question_id) REFERENCES practice_questions(id) ON DELETE CASCADE,
                UNIQUE KEY unique_practice_option (practice_question_id, option_letter),
                INDEX idx_practice_question_id (practice_question_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create practice_results table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS practice_results (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                student_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                subject_id INT UNSIGNED NOT NULL,
                total_questions INT NOT NULL,
                correct_answers INT NOT NULL,
                total_marks INT NOT NULL,
                obtained_marks INT NOT NULL,
                percentage DECIMAL(5, 2),
                attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
                INDEX idx_student_id (student_id),
                INDEX idx_subject_id (subject_id),
                INDEX idx_attempted_at (attempted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create super_admins table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS super_admins (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                user_id INT UNSIGNED NOT NULL UNIQUE,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                phone VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create admins table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                user_id INT UNSIGNED NOT NULL UNIQUE,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                phone VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_active BOOLEAN DEFAULT TRUE,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create audit_logs table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                user_id INT UNSIGNED,
                action VARCHAR(100) NOT NULL,
                resource VARCHAR(100) NOT NULL,
                details TEXT,
                success BOOLEAN DEFAULT TRUE,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_user_id (user_id),
                INDEX idx_action (action),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create notifications table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS notifications (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                user_id INT UNSIGNED NOT NULL,
                exam_attempt_id INT UNSIGNED,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                phone_number VARCHAR(20),
                email_address VARCHAR(255),
                notification_method VARCHAR(50),
                status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
                sent_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (exam_attempt_id) REFERENCES exam_attempts(id) ON DELETE SET NULL,
                INDEX idx_user_id (user_id),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create settings table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                setting_key VARCHAR(255) UNIQUE NOT NULL,
                setting_value LONGTEXT,
                data_type VARCHAR(50),
                is_sensitive BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                INDEX idx_key (setting_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
