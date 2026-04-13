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

    public function topLearners(Request $request)
    {
        $limit = $request->query('limit', 10);

        $topLearners = $this->leaderboardRepo->getTopLearners($limit);

        return BaseResponse::Success('Top learners retrieved', $topLearners);
    }

}
