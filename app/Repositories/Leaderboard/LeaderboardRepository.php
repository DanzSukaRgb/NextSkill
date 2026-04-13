<?php

namespace App\Repositories\Leaderboard;

use App\Models\Course;
use App\Models\StudentPoint;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Support\Facades\DB;

class LeaderboardRepository
{

    private $studentPointModel;
    private $userLevelModel;

    public function __construct(StudentPoint $studentPointModel, UserLevel $userLevelModel)
    {
        $this->studentPointModel = $studentPointModel;
        $this->userLevelModel = $userLevelModel;
    }
    /**
     * Get top learners for current month
     */
    public function getTopLearners($limit = 10){

        $topLearners = $this->studentPointModel->selectRaw('student_id, SUM(points) as total_points')
        ->groupBy('student_id')
        ->orderByDesc('total_points')
        ->limit($limit)
        ->with('student:id,name,avatar')
        ->get();

        $leaderboard = [];
        $rank = 1;
        foreach ($topLearners as $learner) {
            $student = $learner->student;
            $userLevel = $this->userLevelModel->where('user_id', $learner->student_id)->first();
            $enrollments = $student->enrollments()->distinct('course_id')->pluck('course_id');
            $courses = Course::whereIn('id', $enrollments)->pluck('title');

            $leaderboard[] = [
                'rank' => $rank++,
                'student_name' => $student->name,
                'level' => $userLevel?->level ?? 1,
                'total_points' => $learner->total_points,
                'active_courses' => $courses,
                'avatar' => $student->avatar,
                'total_xp' => $userLevel?->total_xp ?? 0
            ];
        }

        return $leaderboard;

    }

    public function addPoints($studentId, $points, $poinstSource, $sourceId = null) {
        $this->studentPointModel->create([
            'student_id' => $studentId,
            'points_source' => $poinstSource,
            'source_id' => $sourceId,
            'points' => $points,
        ]);

        $userLevel =$this->userLevelModel->first();
        if(!$userLevel){
            $userLevel = $this->userLevelModel->create([
                'user_id' => $studentId,
                'level' => 1,
                'total_xp' => 0,
            ]);
        }

        $newXp = $userLevel->total_xp + $points;
        $newLevel = $userLevel->level;
        $xpForNextLevel = $userLevel->xp_for_next_level;
        while($newXp >= $xpForNextLevel){
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
    private function calculateXpForNextLevel($level)
    {
        return 1000 * $level;
    }
}
