<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class StudentCourseController extends Controller
{
    /**
     * Get all courses enrolled by student
     */
    public function getMyCourses(Request $request)
    {
        try {
            $userId = auth()->id();

            $query = Enrollment::where('user_id', $userId)
                ->with(['course.category', 'course.user']);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $enrollments = $query->orderBy('enrolled_at', 'desc')->get();

            $courseData = $enrollments->map(function ($enrollment) {
                $isCompleted = $enrollment->progress_percentage >= 100;

                return [
                    'id' => $enrollment->id,
                    'course_id' => $enrollment->course->id,
                    'course_title' => $enrollment->course->title,
                    'mentor_name' => $enrollment->course->user->name,
                    'category_name' => $enrollment->course->category->name ?? null,
                    'thumbnail' => $enrollment->course->thumbnail,
                    'progress_percentage' => $enrollment->progress_percentage,
                    'status' => $enrollment->status,
                    'enrolled_at' => $enrollment->enrolled_at,
                    'is_completed' => $isCompleted,
                    'has_certificate' => $isCompleted,
                ];
            });

            return BaseResponse::Success('Daftar kursus berhasil diambil', ['courses' => $courseData]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil daftar kursus: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get course detail for student
     */
    public function getCourseDetail($courseId)
    {
        try {
            $userId = auth()->id();

            $enrollment = Enrollment::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->with(['course.category', 'course.user', 'course.lessons'])
                ->first();

            if (!$enrollment) {
                return BaseResponse::Error('Anda tidak terdaftar di kursus ini', 404);
            }

            $isCompleted = $enrollment->progress_percentage >= 100;

            $courseDetail = [
                'id' => $enrollment->course->id,
                'title' => $enrollment->course->title,
                'description' => $enrollment->course->description,
                'thumbnail' => $enrollment->course->thumbnail,
                'mentor_name' => $enrollment->course->user->name,
                'category_name' => $enrollment->course->category->name ?? null,
                'level' => $enrollment->course->level,
                'progress_percentage' => $enrollment->progress_percentage,
                'status' => $enrollment->status,
                'enrolled_at' => $enrollment->enrolled_at,
                'is_completed' => $isCompleted,
                'total_lessons' => $enrollment->course->lessons->count(),
                'lessons_completed' => intval(($enrollment->progress_percentage / 100) * $enrollment->course->lessons->count()),
            ];

            return BaseResponse::Success('Detail kursus berhasil diambil', $courseDetail);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil detail kursus: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all certificates of completed courses
     */
    public function getMyCertificates(Request $request)
    {
        try {
            $userId = auth()->id();

            $query = Certificate::where('user_id', $userId)
                ->with(['course.category', 'course.user']);

            $certificates = $query->orderBy('issued_at', 'desc')->get();

            $certData = $certificates->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'certificate_number' => $cert->certificate_number,
                    'course_id' => $cert->course->id,
                    'course_title' => $cert->course->title,
                    'mentor_name' => $cert->course->user->name,
                    'category_name' => $cert->course->category->name ?? null,
                    'issued_at' => $cert->issued_at,
                    'issued_at_formatted' => $cert->issued_at->format('d M Y'),
                ];
            });

            return BaseResponse::Success('Daftar sertifikat berhasil diambil', ['certificates' => $certData]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil daftar sertifikat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Download certificate as PDF
     */
    public function downloadCertificatePDF($certificateId)
    {
        try {
            $userId = auth()->id();

            $certificate = Certificate::where('user_id', $userId)
                ->where('id', $certificateId)
                ->with(['course.user', 'user'])
                ->first();

            if (!$certificate) {
                return BaseResponse::Error('Sertifikat tidak ditemukan', 404);
            }

            // Generate PDF using template
            $pdf = \PDF::loadView('certificates.template', [
                'certificate' => $certificate,
                'student_name' => $certificate->user->name,
                'course_title' => $certificate->course->title,
                'mentor_name' => $certificate->course->user->name,
                'issued_at' => $certificate->issued_at->format('d M Y'),
                'certificate_number' => $certificate->certificate_number,
            ]);

            $filename = 'Certificate_' . $certificate->certificate_number . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal download sertifikat: ' . $e->getMessage(), 500);
        }
    }
}
