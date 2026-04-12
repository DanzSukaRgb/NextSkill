<?php

namespace App\Repositories\Leaderboard;

use App\Models\Course;
use App\Models\StudentPoint;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Support\Facades\DB;

class LeaderboardRepository
{
    /**
     * Get top learners for current month
     */
    public function getTopLearners($limit = 10, $month = null, $year = null){
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $topLearners = StudentPoint::selectRaw('student_id, SUM(points) as total_points')
        ->whereYear('gained_at', $year)
        ->whereMonth('gained_at', $month)
        ->groupBy('student_id')
        ->orderByDesc('total_points')
        ->limit($limit)
        ->with('student:id,name')
        ->get();

        $leaderboard = [];
        $rank = 1;
        foreach ($topLearners as $learner) {
            $student = $learner->student;
            $userLevel = UserLevel::where('user_id', $learner->student_id)->first();
            $enrollments = $student->enrollments()->distinct('course_id')->pluck('course_id');
            $courses = Course::whereIn('id', $enrollments)->pluck('title');

            $leaderboard[] = [
                'rank' => $rank++,
                'student_name' => $student->name,
                'username' => $student->username,
                'level' => $userLevel?->level ?? 1,
                'total_points' => $learner->total_points,
                'active_courses' => $courses,
                'avatar' => $student->avatar,
                ''
            ];
        }

        return $topLearners;

    }

    public function addPoints($studentId, $points, $pointsSource, $sourceId = null)
    {
        StudentPoint::create([
            'student_id' => $studentId,
            'points_source' => $pointsSource,
            'source_id' => $sourceId,
            'points' => $points,
        ]);

        $userLevel = UserLevel::where('user_id', $studentId)->first();

        if (!$userLevel) {
            $userLevel = UserLevel::create([
                'user_id' => $studentId,
                'level' => 1,
                'total_xp' => 0,
            ]);
        }

        $newXp = $userLevel->total_xp + $points;
        $newLevel = $userLevel->level;
        $xpForNextLevel = $userLevel->xp_for_next_level;

        while ($newXp >= $xpForNextLevel) {
            $newXp -= $xpForNextLevel;
            $newLevel++;
            $xpForNextLevel = $this->calculateXpForNextLevel($newLevel);
        }

        $userLevel->update([
            'level' => $newLevel,
            'total_xp' => $userLevel->total_xp + $points,
            'xp_for_next_level' => $xpForNextLevel,
        ]);

        return $userLevel;
    }

    /**
     * Calculate XP required for next level
     * Formula: Base XP (1000) * level multiplier
     */
    private function calculateXpForNextLevel($level)
    {
        return 1000 * $level;
    }
}
