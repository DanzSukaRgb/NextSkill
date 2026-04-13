<?php

namespace App\Http\Controllers\Mentor;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class StudentManagementController extends Controller
{
    /**
     * Get all students enrolled in mentor's courses with filters
     */
    public function index(Request $request)
    {
        try {
            $mentorId = auth()->id();

            // Get all courses owned by mentor
            $courseIds = Course::where('user_id', $mentorId)->pluck('id');

            if ($courseIds->isEmpty()) {
                return BaseResponse::Success('Daftar siswa berhasil diambil', ['students' => []]);
            }

            // Build query
            $query = Enrollment::whereIn('course_id', $courseIds)
                ->with(['user', 'course.category']);

            // Filter by search (student name)
            if ($request->filled('search')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
            }

            // Filter by course
            if ($request->filled('course_id')) {
                $query->where('course_id', $request->course_id);
            }

            // Filter by category
            if ($request->filled('category_id')) {
                $query->whereHas('course', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            // Get results
            $enrollments = $query->orderBy('enrolled_at', 'desc')->get();

            // Format response
            $studentData = $enrollments->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'student_id' => $enrollment->user->id,
                    'student_name' => $enrollment->user->name,
                    'student_email' => $enrollment->user->email,
                    'student_avatar' => $enrollment->user->avatar,
                    'course_id' => $enrollment->course->id,
                    'course_title' => $enrollment->course->title,
                    'category_id' => $enrollment->course->category->id ?? null,
                    'category_name' => $enrollment->course->category->name ?? null,
                    'progress_percentage' => $enrollment->progress_percentage,
                    'enrolled_at' => $enrollment->enrolled_at,
                    'status' => $enrollment->status,
                ];
            });

            return BaseResponse::Success('Daftar siswa berhasil diambil', ['students' => $studentData]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil daftar siswa: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get available filters (courses and categories)
     */
    public function getFilters()
    {
        try {
            $mentorId = auth()->id();

            // Get all courses owned by mentor with categories
            $courses = Course::where('user_id', $mentorId)
                ->with('category')
                ->get();

            // Get unique categories from those courses
            $categories = $courses->pluck('category')->unique('id')->values();

            return BaseResponse::Success('Filter berhasil diambil', [
                'courses' => $courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'category_id' => $course->category->id ?? null,
                        'category_name' => $course->category->name ?? null,
                    ];
                }),
                'categories' => $categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil filter: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get student detail with progress per course
     */
    public function show($studentId)
    {
        try {
            $mentorId = auth()->id();

            // Get all course IDs owned by mentor
            $courseIds = Course::where('user_id', $mentorId)->pluck('id');

            // Get enrollments of this student in mentor's courses
            $enrollments = Enrollment::where('user_id', $studentId)
                ->whereIn('course_id', $courseIds)
                ->with(['user', 'course.category'])
                ->get();

            if ($enrollments->isEmpty()) {
                return BaseResponse::Error('Siswa tidak ditemukan di kursus Anda', 404);
            }

            $student = $enrollments->first()->user;

            $studentDetail = [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'student_email' => $student->email,
                'student_avatar' => $student->avatar,
                'student_bio' => $student->bio,
                'courses' => $enrollments->map(function ($enrollment) {
                    return [
                        'enrollment_id' => $enrollment->id,
                        'course_id' => $enrollment->course->id,
                        'course_title' => $enrollment->course->title,
                        'category_name' => $enrollment->course->category->name ?? null,
                        'progress_percentage' => $enrollment->progress_percentage,
                        'status' => $enrollment->status,
                        'enrolled_at' => $enrollment->enrolled_at,
                    ];
                }),
            ];

            return BaseResponse::Success('Detail siswa berhasil diambil', $studentDetail);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil detail siswa: ' . $e->getMessage(), 500);
        }
    }
}
