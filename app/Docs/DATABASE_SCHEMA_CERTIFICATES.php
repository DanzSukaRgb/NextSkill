<?php

/**
 * STUDENT COURSES + CERTIFICATES SYSTEM - DATABASE SCHEMA
 * 
 * This file documents the database structure required for the student courses
 * and certificate system. Ensure these tables and columns exist.
 * 
 * Run migrations if needed:
 * - php artisan migrate
 */

/**
 * TABLE: enrollments
 * 
 * id              - BIGINT PRIMARY KEY
 * user_id         - BIGINT (references users.id)
 * course_id       - BIGINT (references courses.id)
 * enrolled_at     - TIMESTAMP (when student enrolled)
 * status          - ENUM ('active', 'completed', 'dropped') - updated by Observer
 * progress_percentage - INT (0-100) - updated when lessons progress
 * created_at      - TIMESTAMP
 * updated_at      - TIMESTAMP
 * 
 * When progress_percentage reaches 100 on update:
 * - EnrollmentObserver triggers CourseCompletionService
 * - Certificate is auto-created
 * - Status changes to 'completed'
 * - Data cleanup occurs (LessonProgress, TaskSubmission records deleted)
 */

/**
 * TABLE: certificates
 * 
 * id                           - BIGINT PRIMARY KEY
 * user_id                      - BIGINT (references users.id)
 * course_id                    - BIGINT (references courses.id)
 * certificate_number           - VARCHAR (unique identifier) - Format: CERT-RANDOM8-COURSEID
 * issued_at                    - TIMESTAMP (auto-filled with now() when creating)
 * created_at                   - TIMESTAMP
 * updated_at                   - TIMESTAMP
 * 
 * One certificate per user per course (unique constraint: user_id + course_id)
 * Certificate auto-creates when Enrollment.progress_percentage >= 100
 */

/**
 * TABLE: lesson_progress
 * 
 * id              - BIGINT PRIMARY KEY
 * user_id         - BIGINT (references users.id)
 * lesson_id       - BIGINT (references lessons.id)
 * is_completed    - BOOLEAN (false by default)
 * completed_at    - TIMESTAMP (auto-filled when completed)
 * created_at      - TIMESTAMP
 * updated_at      - TIMESTAMP
 * 
 * When lesson marked complete:
 * - Update Enrollment.progress_percentage
 * - Observer detects change and triggers CourseCompletionService
 * - If >= 100%, Certificate created and this data is deleted (cleanup)
 */

/**
 * TABLE: task_submissions
 * 
 * id              - BIGINT PRIMARY KEY
 * user_id         - BIGINT (references users.id)
 * task_id         - BIGINT (references tasks.id)
 * content         - LONGTEXT (submission content)
 * status          - ENUM ('submitted', 'graded')
 * grade           - INT (0-100, nullable until graded)
 * graded_at       - TIMESTAMP (null until graded)
 * graded_by       - BIGINT (references users.id, mentor)
 * created_at      - TIMESTAMP
 * updated_at      - TIMESTAMP
 * 
 * When course completed:
 * - CourseCompletionService deletes submissions (cleanup only)
 * - Grade data preserved in history before deletion (optional)
 */

?>