# Student Courses & Certificate System - Complete Implementation Guide

## System Overview

This document explains the complete student courses and certificate system built for NextSkill platform.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│ Student completes lesson → LessonProgress marked as 'completed' │
└────────────────────────────┬──────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│ Controller updates Enrollment.progress_percentage (0-100%)      │
└────────────────────────────┬──────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│ EnrollmentObserver@updated() detects change                     │
└────────────────────────────┬──────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│ CourseCompletionService::checkAndCompleteCourse()               │
│ • If progress >= 100%:                                          │
│   - Create Certificate record                                   │
│   - Set Enrollment.status = 'completed'                         │
│   - Delete LessonProgress data (cleanup)                        │
│   - Delete TaskSubmission data (cleanup)                        │
└────────────────────────────┬──────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│ Student can now:                                                │
│ • View completed courses via /my-courses (is_completed: true)   │
│ • List certificates via /my-certificates                        │
│ • Download certificate PDF via /certificates/{id}/download      │
└─────────────────────────────────────────────────────────────────┘
```

## Components

### 1. Service: CourseCompletionService

**File:** `app/Services/CourseCompletionService.php`

**Responsibilities:**

- Check if enrollment has reached 100% completion
- Create Certificate records with unique numbers
- Cleanup database records after course completion
- Prevent database bloat from old data

**Key Methods:**

```php
checkAndCompleteCourse(Enrollment $enrollment)
  └─ Called by EnrollmentObserver
  └─ Creates certificate if progress >= 100%
  └─ Triggers cleanup operations

createCertificate(Enrollment $enrollment)
  └─ Generates unique certificate number
  └─ Creates Certificate model record

cleanupStudentData(Enrollment $enrollment)
  └─ Deletes LessonProgress records
  └─ Deletes TaskSubmission records
  └─ Prevents database bloat

updateCourseProgress($userId, $courseId)
  └─ Helper to trigger updates from external services
```

### 2. Observer: EnrollmentObserver

**File:** `app/Observers/EnrollmentObserver.php`

**Responsibilities:**

- Listen for Enrollment model changes
- Detect when progress_percentage is updated
- Trigger CourseCompletionService automatically

**Lifecycle:**

```php
Enrollment::observe(EnrollmentObserver::class)  // Registered in AppServiceProvider
├─ updated event fires when Enrollment is saved
├─ Checks isDirty('progress_percentage')
└─ Calls CourseCompletionService::checkAndCompleteCourse()
```

### 3. Controller: StudentCourseController

**File:** `app/Http/Controllers/Api/StudentCourseController.php`

**Endpoints:**

```
GET  /my-courses                           - List enrolled courses
GET  /courses/{courseId}/detail            - Get course details
GET  /my-certificates                      - List issued certificates
GET  /certificates/{certificateId}/download - Download certificate PDF
```

**Methods:**

#### getMyCourses(Request $request)

Returns all courses student is enrolled in with progress tracking.

**Query Parameters:**

- `status` (optional) - Filter by 'active' or 'completed'

**Response:**

```json
{
    "success": true,
    "message": "Daftar kursus berhasil diambil",
    "data": {
        "courses": [
            {
                "id": 1,
                "course_id": 5,
                "course_title": "Advanced Laravel",
                "mentor_name": "John Doe",
                "category_name": "Backend",
                "thumbnail": "https://...",
                "progress_percentage": 85,
                "status": "active",
                "enrolled_at": "2026-04-01",
                "is_completed": false,
                "has_certificate": false
            },
            {
                "id": 2,
                "course_id": 6,
                "course_title": "React Basics",
                "mentor_name": "Jane Smith",
                "category_name": "Frontend",
                "thumbnail": "https://...",
                "progress_percentage": 100,
                "status": "completed",
                "enrolled_at": "2026-03-15",
                "is_completed": true,
                "has_certificate": true
            }
        ]
    }
}
```

#### getCourseDetail($courseId)

Returns detailed information for a specific enrolled course.

**Path Parameters:**

- `courseId` (required) - Course ID from enrollment

**Response:**

```json
{
    "success": true,
    "message": "Detail kursus berhasil diambil",
    "data": {
        "id": 6,
        "title": "React Basics",
        "description": "Learn React from fundamentals...",
        "thumbnail": "https://...",
        "mentor_name": "Jane Smith",
        "category_name": "Frontend",
        "level": "Beginner",
        "progress_percentage": 100,
        "status": "completed",
        "enrolled_at": "2026-03-15",
        "is_completed": true,
        "total_lessons": 10,
        "lessons_completed": 10
    }
}
```

#### getMyCertificates(Request $request)

Returns all issued certificates for completed courses.

**Response:**

```json
{
    "success": true,
    "message": "Daftar sertifikat berhasil diambil",
    "data": {
        "certificates": [
            {
                "id": 1,
                "certificate_number": "CERT-ABC12345-6",
                "course_id": 6,
                "course_title": "React Basics",
                "mentor_name": "Jane Smith",
                "category_name": "Frontend",
                "issued_at": "2026-04-15T10:30:00",
                "issued_at_formatted": "15 Apr 2026"
            }
        ]
    }
}
```

#### downloadCertificatePDF($certificateId)

Generates and downloads certificate as PDF file.

**Path Parameters:**

- `certificateId` (required) - Certificate ID

**Response:**

- File download: `Certificate_CERT-ABC12345-6.pdf`
- Content-Type: `application/pdf`

### 4. Template: Certificate PDF

**File:** `resources/views/certificates/template.blade.php`

**Design Features:**

- Professional certificate layout
- Gold borders with decorative corners
- Watermark text
- Student name, course title, mentor name
- Unique certificate number
- Issue date
- Signature area placeholder

**Data Variables:**

```php
$student_name      - Full name of student
$course_title      - Name of completed course
$certificate_number - Unique cert ID (e.g., CERT-ABC12345-6)
$issued_at         - Formatted date (e.g., "15 Apr 2026")
```

## Database Schema

### enrollments table

```sql
CREATE TABLE enrollments (
  id BIGINT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  course_id BIGINT NOT NULL,
  enrolled_at TIMESTAMP,
  status ENUM('active', 'completed', 'dropped'),
  progress_percentage INT DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (course_id) REFERENCES courses(id)
)
```

### certificates table

```sql
CREATE TABLE certificates (
  id BIGINT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  course_id BIGINT NOT NULL,
  certificate_number VARCHAR(100) UNIQUE,
  issued_at TIMESTAMP,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (course_id) REFERENCES courses(id),
  UNIQUE KEY unique_cert (user_id, course_id)
)
```

### lesson_progress table

```sql
CREATE TABLE lesson_progress (
  id BIGINT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  lesson_id BIGINT NOT NULL,
  is_completed BOOLEAN DEFAULT FALSE,
  completed_at TIMESTAMP,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (lesson_id) REFERENCES lessons(id)
)
```

**Note:** LessonProgress records are deleted after course completion

### task_submissions table

```sql
CREATE TABLE task_submissions (
  id BIGINT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  task_id BIGINT NOT NULL,
  content LONGTEXT,
  status ENUM('submitted', 'graded'),
  grade INT(3),
  graded_at TIMESTAMP,
  graded_by BIGINT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (task_id) REFERENCES tasks(id),
  FOREIGN KEY (graded_by) REFERENCES users(id)
)
```

**Note:** TaskSubmission records are deleted after course completion

## Flow Examples

### Example 1: Student Completes Course

```
1. Student marks last lesson as complete
2. LessonProgress updated: is_completed = true
3. Controller calculates progress: 10 lessons, 10 completed = 100%
4. Enrollment.progress_percentage = 100 (UPDATE triggered)
5. EnrollmentObserver detects change via isDirty()
6. CourseCompletionService::checkAndCompleteCourse() called
7. Since 100% >= 100%:
   - Certificate created: CERT-XYZ789-5
   - Enrollment.status = 'completed'
   - LessonProgress rows deleted (cleanup)
   - TaskSubmission rows deleted (cleanup)
8. Event ends, database cleaned up
```

### Example 2: Student Views Completed Course

```
GET /my-courses?status=completed
Response includes:
- Courses with status = 'completed'
- is_completed = true
- has_certificate = true
- Progress percentage = 100
```

### Example 3: Student Downloads Certificate

```
1. Student views /my-certificates
2. Selects certificate, clicks download
3. GET /certificates/{id}/download
4. System loads Certificate record + related data
5. Blade template rendered with certificate data
6. PDF generated using laravel-dompdf
7. File downloaded as: Certificate_CERT-XYZ789-5.pdf
```

## Integration Points

### With Task System

- When course completes, TaskSubmission records deleted
- Task grades preserved in history if needed (optional enhancement)

### With Quiz System

- Quiz attempts preserved (optional: delete in cleanup)
- Quiz results available for admin reporting

### With Progress Tracking

- LessonProgress drives progress_percentage
- Progress_percentage update triggers certificate generation

### With Mentor System

- Mentors see enrolled student progress
- Can view student completion status
- Cannot delete certificates (student only)

## Security Considerations

1. **Authorization:**
    - Only authenticated students can access their own courses
    - StudentCourseController checks `auth()->id()` matches user_id
    - Certificates only accessible to their owner

2. **Data Cleanup:**
    - Error handling prevents certificate failures
    - Cleanup errors logged but don't block completion
    - No cascade deletes that could cause issues

3. **Certificate Numbers:**
    - Unique per user per course
    - Random 8-character codes prevent guessing
    - Format: CERT-{RANDOM8}-{COURSE_ID}

4. **PDF Generation:**
    - Only authenticated students can download
    - Ownership verified before PDF generation
    - File download is controlled by framework

## Performance Considerations

1. **Database Cleanup:**
    - Deletes run in background during course completion
    - Prevents unbounded table growth
    - Reduces memory footprint

2. **PDF Generation:**
    - Generated on-demand (not cached)
    - Consider caching if high traffic
    - DomPDF integration handled by Laravel

3. **Query Optimization:**
    - Enrollments eager load relationships (course, user)
    - Certificates eager load relationships
    - No N+1 queries in list endpoints

## Configuration

### Required Environment

- PHP 8.1+
- Laravel 11
- barryvdh/laravel-dompdf installed
- MySQL 5.7+

### Optional Enhancements

- Cache certificate PDFs after generation
- Add certificate templates per mentor
- Email certificate to student
- Archive historical records instead of delete
- Add certificate verification endpoint

## Troubleshooting

### Certificate Not Creating

1. Check EnrollmentObserver is registered in AppServiceProvider
2. Verify progress_percentage exactly reaches 100%
3. Check CourseCompletionService for errors in log
4. Verify Certificate model fillable includes all columns

### PDF Download Fails

1. Ensure barryvdh/laravel-dompdf is installed
2. Check template file exists at resources/views/certificates/template.blade.php
3. Verify Certificate record has user and course relationships
4. Check storage permissions for PDF generation

### Data Cleanup Not Working

1. Verify lesson count correct in course
2. Check LessonProgress deletion no foreign key constraints
3. Ensure TaskSubmission deletion doesn't have dependencies
4. Review logs for cleanup error messages

### Progress Shows Wrong Percentage

1. Verify LessonProgress records exist
2. Check lesson count in course
3. Ensure Enrollment.progress_percentage updated correctly
4. Trigger observer manually: `$enrollment->touch()`

## Testing

### Unit Tests

```php
// Test certificate creation
$enrollment->progress_percentage = 100;
$enrollment->save();
$this->assertDatabaseHas('certificates', [
    'user_id' => $enrollment->user_id,
    'course_id' => $enrollment->course_id
]);

// Test API endpoint
$response = $this->getJson('/my-courses');
$response->assertStatus(200)
         ->assertJsonStructure(['data' => ['courses']]);
```

### Integration Tests

```php
// Test complete flow
$this->completeAllLessons($student, $course);
$this->assertTrue(Certificate::where('user_id', $student->id)->exists());
$response = $this->downloadCertificatePDF($certificateId);
$response->assertDownload('Certificate_*.pdf');
```

## API Reference

| Method | Endpoint                    | Protected | Purpose                         |
| ------ | --------------------------- | --------- | ------------------------------- |
| GET    | /my-courses                 | Yes       | List student's enrolled courses |
| GET    | /courses/{courseId}/detail  | Yes       | Get single course details       |
| GET    | /my-certificates            | Yes       | List issued certificates        |
| GET    | /certificates/{id}/download | Yes       | Download certificate PDF        |

## Code Examples

### Manually Trigger Course Completion

```php
$enrollment = Enrollment::find(1);
$enrollment->progress_percentage = 100;
$enrollment->save(); // Observer triggers automatically
```

### Check Certificate Creation

```php
$cert = Certificate::where('user_id', 5)
                    ->where('course_id', 10)
                    ->first();

if ($cert) {
    echo "Certificate: " . $cert->certificate_number;
}
```

### Cleanup Manually (if needed)

```php
$enrollment = Enrollment::find(1);
CourseCompletionService::cleanupStudentData($enrollment);
```

## Summary

✅ Complete system for course completion tracking  
✅ Automatic certificate generation at 100%  
✅ Professional PDF certificate download  
✅ Database cleanup prevents bloat  
✅ Student APIs for viewing courses and certificates  
✅ Observer pattern for automatic triggers  
✅ Secure authorization checks  
✅ Error handling and logging included
