<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRecommendation;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    /**
     * Get personalized recommendations for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $recommendations = UserRecommendation::with(['track.user', 'track.album'])
            ->where('user_id', $user->id)
            ->orderByDesc('score')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $recommendations
        ]);
    }
}
