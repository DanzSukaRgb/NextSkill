<!-- STUDENT COURSES & CERTIFICATE SYSTEM - INTEGRATION CHECKLIST -->

## Pre-Deployment Checklist

### ✅ Files Created

- [x] app/Services/CourseCompletionService.php
- [x] resources/views/certificates/template.blade.php
- [x] app/Observers/EnrollmentObserver.php
- [x] app/Docs/STUDENT_COURSES_CERTIFICATE_GUIDE.md
- [x] app/Docs/DATABASE_SCHEMA_CERTIFICATES.php

### ✅ Files Modified

- [x] app/Http/Controllers/Api/StudentCourseController.php (already exists - verified)
- [x] app/Providers/AppServiceProvider.php (added observer registration)
- [x] routes/api.php (added StudentCourseController import + 4 routes)
- [x] app/Models/Enrollment.php (verified - has relationships needed)
- [x] app/Models/Certificate.php (verified - has relationships needed)

### ✅ Dependencies Verified

- [x] BaseResponse helper exists (used in StudentCourseController)
- [x] Laravel Sanctum middleware available (auth:sanctum)
- [x] PDF library (barryvdh/laravel-dompdf) should be installed
- [x] Certificate model exists and is fillable
- [x] Enrollment model has user() and course() relationships

### ⚠️ Pre-Deployment Tasks (Manual)

1. **Install PDF Library (if not installed)**

    ```bash
    composer require barryvdh/laravel-dompdf
    php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
    ```

2. **Run Database Migrations (if needed)**

    ```bash
    php artisan migrate
    ```

    Note: Tables should already exist (enrollments, certificates, lesson_progress, task_submissions)

3. **Clear Cache**

    ```bash
    php artisan cache:clear
    php artisan route:cache
    php artisan config:cache
    ```

4. **Test Routes Are Registered**
    ```bash
    php artisan route:list | grep -E "my-courses|certificates"
    ```
    Should show:
    - GET /my-courses
    - GET /courses/{courseId}/detail
    - GET /my-certificates
    - GET /certificates/{certificateId}/download

### ✅ Database Requirements (verify they exist)

**enrollments table needs:**

- ✓ id (BIGINT PRIMARY KEY)
- ✓ user_id (BIGINT)
- ✓ course_id (BIGINT)
- ✓ progress_percentage (INT)
- ✓ status (ENUM or VARCHAR)
- ✓ enrolled_at (TIMESTAMP)
- ✓ timestamps

**certificates table needs:**

- ✓ id (BIGINT PRIMARY KEY)
- ✓ user_id (BIGINT)
- ✓ course_id (BIGINT)
- ✓ certificate_number (VARCHAR)
- ✓ issued_at (TIMESTAMP)
- ✓ timestamps

**lesson_progress table needs:**

- ✓ user_id (BIGINT)
- ✓ lesson_id (BIGINT)
- ✓ is_completed (BOOLEAN)
- ✓ completed_at (TIMESTAMP)
- ✓ timestamps

### ✅ Configuration Files

- [x] app/Providers/AppServiceProvider.php - Observer registered

### ✅ Route Middleware

- [x] StudentCourseController routes use auth:sanctum (already applied)
- [x] No role-specific middleware needed for students

## Post-Deployment Testing

### Manual Testing Steps

1. **Test List Courses Endpoint**

    ```bash
    curl -X GET http://localhost:8000/api/my-courses \
      -H "Authorization: Bearer YOUR_TOKEN"
    ```

    Expected: 200 OK with courses array

2. **Test Get Course Detail**

    ```bash
    curl -X GET http://localhost:8000/api/courses/5/detail \
      -H "Authorization: Bearer YOUR_TOKEN"
    ```

    Expected: 200 OK with course detail

3. **Test List Certificates**

    ```bash
    curl -X GET http://localhost:8000/api/my-certificates \
      -H "Authorization: Bearer YOUR_TOKEN"
    ```

    Expected: 200 OK with certificates array

4. **Test Download Certificate**

    ```bash
    curl -X GET http://localhost:8000/api/certificates/1/download \
      -H "Authorization: Bearer YOUR_TOKEN" \
      -o certificate.pdf
    ```

    Expected: PDF file downloaded

5. **Test Course Completion Flow**
    - Mark all lessons complete in course
    - Verify Enrollment.progress_percentage becomes 100
    - Verify Certificate record created
    - Verify /my-courses shows is_completed: true
    - Verify /my-certificates shows new certificate

### Automated Testing (if you use Laravel tests)

```php
// tests/Feature/StudentCourseTest.php
public function test_student_can_list_their_courses()
{
    $student = User::factory()->create(['role' => 'student']);
    $response = $this->actingAs($student)
                     ->getJson('/api/my-courses');
    $response->assertStatus(200);
}

public function test_certificate_created_on_100_percent()
{
    $enrollment = Enrollment::factory()->create(['progress_percentage' => 99]);
    $enrollment->load('course');

    // Simulate 100% progress
    $enrollment->progress_percentage = 100;
    $enrollment->save();

    // Certificate should be created
    $this->assertDatabaseHas('certificates', [
        'user_id' => $enrollment->user_id,
        'course_id' => $enrollment->course_id
    ]);
}

public function test_student_can_download_certificate()
{
    $cert = Certificate::factory()->create();
    $response = $this->actingAs($cert->user)
                     ->getJson("/api/certificates/{$cert->id}/download");
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
}
```

## Common Issues & Solutions

### Issue: "PDF engine error" or "mPDF error"

**Solution:** Install pdf library

```bash
composer require barryvdh/laravel-dompdf
```

### Issue: "Table certificates doesn't exist"

**Solution:** Run migrations

```bash
php artisan migrate
```

### Issue: "EnrollmentObserver not being triggered"

**Solution:** Verify in AppServiceProvider.boot()

```php
Enrollment::observe(EnrollmentObserver::class);
```

### Issue: "Certificate not creating on 100%"

**Solution:**

- Verify progress_percentage exactly equals 100
- Check isDirty('progress_percentage') returns true
- Verify no duplicate certificate already exists

### Issue: "Routes not found (404)"

**Solution:** Run route cache clear

```bash
php artisan route:clear
php artisan route:cache
```

## Performance Considerations

### Database Cleanup

- Deletes could be slow on large tables
- Consider running as queue job for high traffic:

```php
// In CourseCompletionService
class CleanupStudentDataJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue;

    public function handle() {
        // Move cleanup logic here
    }
}

// In CourseCompletionService
CourseCompletionService::dispatch($enrollment);
```

### PDF Generation

- Generated on-demand (not cached)
- For high traffic, consider Redis cache:

```php
$pdf = Cache::remember("cert_$certId", 3600, function() {
    return \PDF::loadView(...)->stream();
});
```

## Rollback Instructions

If you need to rollback:

1. **Remove Observer Registration**
    - Comment out in AppServiceProvider.boot()

2. **Keep Route Files**
    - Routes can stay (will just do nothing if observer disabled)

3. **Remove Certificates**
    - Database: DELETE FROM certificates
    - Or: Keep existing, stop generating new ones

4. **Restore Deleted Data**
    - Backup before deployment
    - Restore LessonProgress and TaskSubmission from backup if needed

## Documentation Files

For detailed docs, see:

- [app/Docs/STUDENT_COURSES_CERTIFICATE_GUIDE.md](app/Docs/STUDENT_COURSES_CERTIFICATE_GUIDE.md) - Complete system guide
- [app/Docs/DATABASE_SCHEMA_CERTIFICATES.php](app/Docs/DATABASE_SCHEMA_CERTIFICATES.php) - Database schema documentation

## Success Indicators

✅ System is working correctly if:

- Routes respond with 200/404 appropriately
- Certificates auto-create at 100% progress
- Students can download PDFs
- No errors in Laravel logs
- Database size doesn't balloon from old records

## Support

For issues or questions:

1. Check logs: `storage/logs/laravel.log`
2. Check this checklist
3. Review STUDENT_COURSES_CERTIFICATE_GUIDE.md
4. Test individual components in tinker:
    ```bash
    php artisan tinker
    > $enrollment = Enrollment::find(1);
    > $enrollment->progress_percentage = 100;
    > $enrollment->save();
    ```
