<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\BaseResponse;
use App\Repositories\Leaderboard\LeaderboardRepository;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    private $leaderboardRepo;

    public function __construct(LeaderboardRepository $leaderboardRepo)
    {
        $this->leaderboardRepo = $leaderboardRepo;
    }

    /**
     * Get top learners leaderboard
     */
    public function topLearners(Request $request)
    {
        $limit = $request->query('limit', 10);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $topLearners = $this->leaderboardRepo->getTopLearners($limit, $month, $year);

        return BaseResponse::Success('Top learners retrieved', $topLearners);
    }


    /**
     * Get top learners this month (for dashboard)
     */
    public function thisMonthTop($limit = 5)
    {
        $topLearners = $this->leaderboardRepo->getTopLearners($limit, now()->month, now()->year);

        return BaseResponse::Success('This month top learners', $topLearners);
    }
}
