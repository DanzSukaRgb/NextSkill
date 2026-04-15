<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Helpers\ImageHelper;
use App\Models\Course;
use App\Models\User;
use App\Models\Transaction;
use App\Models\RevenueShare;
use App\Models\Enrollment;
use App\Models\Task;
use App\Models\StudentPoint;
use App\Models\MentorBalance;
use App\Models\PlatformBalance;
use App\Models\CourseMentorApplication;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard based on user role
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $role = $user->role;

            if ($role === 'admin') {
                return $this->adminDashboard();
            } elseif ($role === 'mentor') {
                return $this->mentorDashboard();
            } elseif ($role === 'student') {
                return $this->studentDashboard();
            }

            return BaseResponse::Error('Role tidak dikenali', 400);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil dashboard: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin Dashboard
     */
    private function adminDashboard()
    {
        // Total transaction volume
        $totalTransaksi = Transaction::sum('gross_amount');

        // Get revenue share config
        $revenueShare = RevenueShare::first();
        $platformPercentage = $revenueShare?->platform_revenue_share ?? 20;
        $mentorPercentage = $revenueShare?->mentor_revenue_share ?? 80;

        // Platform balance
        $platformBalance = PlatformBalance::first();
        $komisiPlatform = $platformBalance?->balance ?? 0;

        // Total mentor balance
        $hakMentor = MentorBalance::sum('balance') ?? 0;

        // Active students
        $siswaAktif = User::where('role', 'student')->count();

        // Transaction volume last 6 months
        $transaksiChart = Transaction::selectRaw('MONTH(created_at) as bulan, SUM(gross_amount) as total')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->map(function ($item) {
                $bulan = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AUG', 'SEP', 'OKT', 'NOV', 'DES'];
                return [
                    'bulan' => $bulan[$item->bulan - 1],
                    'total' => $item->total,
                ];
            });

        // Pending mentor course applications
        $courseApplications = CourseMentorApplication::where('status', 'pending')
            ->with(['course:id,title', 'user:id,name'])
            ->limit(5)
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'course_title' => $app->course->title,
                    'mentor_name' => $app->user->name,
                    'applied_at' => $app->created_at->diffForHumans(),
                ];
            });

        $data = [
            'total_transaksi' => (int) $totalTransaksi,
            'komisi_platform' => (int) $komisiPlatform,
            'hak_mentor' => (int) $hakMentor,
            'platform_percentage' => $platformPercentage,
            'mentor_percentage' => $mentorPercentage,
            'siswa_aktif' => $siswaAktif,
            'grafik_transaksi' => $transaksiChart,
            'pending_course_applications' => $courseApplications,
        ];

        return BaseResponse::Success('Dashboard Admin', $data);
    }

    /**
     * Mentor Dashboard
     */
    private function mentorDashboard()
    {
        $mentorId = auth()->id();

        // Total registered students
        $siswaGabung = Enrollment::whereHas('course', function ($query) use ($mentorId) {
            $query->where('user_id', $mentorId);
        })->distinct('user_id')->count();

        // Total courses
        $totalKursus = Course::where('user_id', $mentorId)->count();

        // Average course rating (akan diimplementasikan saat ada review system)
        $rataRating = 4.8; // Default/placeholder sampai review system ada

        // Total earnings
        $pendapatan = Transaction::whereHas('course', function ($query) use ($mentorId) {
            $query->where('user_id', $mentorId);
        })->sum('mentor_revenue');

        // Monthly student registration
        $pendaftaranBulanan = Enrollment::whereHas('course', function ($query) use ($mentorId) {
            $query->where('user_id', $mentorId);
        })
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->map(function ($item) {
                return [
                    'bulan' => $item->bulan,
                    'total' => $item->total,
                ];
            });

        // Newest students
        $siswaBaru = Enrollment::whereHas('course', function ($query) use ($mentorId) {
            $query->where('user_id', $mentorId);
        })
            ->with('user:id,name')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->user->id,
                    'name' => $enrollment->user->name,
                    'joined_at' => $enrollment->created_at->diffForHumans(),
                ];
            });

        // Popular categories bought by students
        $topCategories = Enrollment::whereHas('course', function ($query) use ($mentorId) {
            $query->where('user_id', $mentorId);
        })
            ->with('course.category')
            ->get()
            ->groupBy('course.category.name')
            ->map(function ($enrollments) {
                return $enrollments->count();
            })
            ->sortDesc()
            ->take(5)
            ->map(function ($count, $category) {
                return [
                    'category' => $category,
                    'jumlah_siswa' => $count,
                ];
            })
            ->values();

        $data = [
            'siswa_terdaftar' => $siswaGabung,
            'kursus_saya' => $totalKursus,
            'rata_rating' => round($rataRating, 1),
            'pendapatan' => (int) $pendapatan,
            'pendaftaran_bulanan' => $pendaftaranBulanan,
            'siswa_baru' => $siswaBaru,
            'kategori_populer' => $topCategories,
        ];

        return BaseResponse::Success('Dashboard Mentor', $data);
    }

    /**
     * Student Dashboard
     */
    private function studentDashboard()
    {
        $studentId = auth()->id();

        // Total XP
        $totalXP = StudentPoint::where('student_id', $studentId)->sum('points') ?? 0;

        // My courses
        $myCourses = Enrollment::where('user_id', $studentId)
            ->with([
                'course' => function ($query) {
                    $query->with('user:id,name')->select('id', 'title', 'thumbnail', 'user_id');
                }
            ])
            ->get();

        // Learning stats - from real database
        // 1. Count videos completed
        $videosCompleted = LessonProgress::where('user_id', $studentId)
            ->where('is_completed', true)
            ->count();

        // 2. Calculate total learning time (in minutes)
        $learningTimeMinutes = LessonProgress::where('user_id', $studentId)
            ->where('is_completed', true)
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->sum('lessons.duration_in_minutes') ?? 0;

        // Convert minutes to hours and minutes format
        $hours = intdiv($learningTimeMinutes, 60);
        $minutes = $learningTimeMinutes % 60;
        $learningTimeFormatted = "{$hours}j {$minutes}m";

        // 3. Calculate learning streak (consecutive days of learning)
        $completedDates = LessonProgress::where('user_id', $studentId)
            ->where('is_completed', true)
            ->selectRaw('DATE(completed_at) as date')
            ->distinct()
            ->orderByDesc('date')
            ->pluck('date')
            ->toArray();

        $streakDays = 0;
        if (!empty($completedDates)) {
            $streakDays = 1;
            $currentDate = \Carbon\Carbon::parse($completedDates[0]);
            for ($i = 1; $i < count($completedDates); $i++) {
                $nextDate = \Carbon\Carbon::parse($completedDates[$i]);
                if ($currentDate->diffInDays($nextDate) == 1) {
                    $streakDays++;
                    $currentDate = $nextDate;
                } else {
                    break;
                }
            }
        }

        $learningStats = [
            'streak_days' => $streakDays,
            'learning_time' => $learningTimeFormatted,
            'videos_completed' => $videosCompleted,
        ];

        // Incomplete quizzes
        $incompleteQuiz = $myCourses->first(); // Dummy, bisa disesuaikan

        // Continue learning - courses with progress
        $lanjutkanBelajar = $myCourses->map(function ($enrollment) {
            $course = $enrollment->course;
            $progress = rand(30, 80); // Dummy progress percentage
            return [
                'id' => $course->id,
                'title' => $course->title,
                'mentor_name' => $course->user->name ?? 'Unknown',
                'progress' => $progress,
                'thumbnail' => ImageHelper::getImageUrl($course->thumbnail),
                'status' => $progress === 100 ? 'Completed' : 'In Progress',
            ];
        })->take(5);

        // Recommended courses (courses not enrolled)
        $recommendedCourses = Course::whereNotIn('id', $myCourses->pluck('course_id'))
            ->with('category:id,name')
            ->where('status', 'published')
            ->limit(5)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'category' => $course->category->name ?? 'General',
                    'price' => (int) $course->price,
                    'rating' => 4.5, // Placeholder sampai review system ada
                    'thumbnail' => ImageHelper::getImageUrl($course->thumbnail),
                ];
            });

        $data = [
            'total_xp' => $totalXP,
            'learning_stats' => $learningStats,
            'ada_kuis_belum_selesai' => $incompleteQuiz ? true : false,
            'lanjutkan_belajar' => $lanjutkanBelajar,
            'recommended_courses' => $recommendedCourses,
        ];

        return BaseResponse::Success('Dashboard Student', $data);
    }
}
