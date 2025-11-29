<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function index()
    {
        return view('user.rewards.index');
    }

    public function claim($id)
    {
        // Logic to claim reward
        return back()->with('success', 'Reward claimed successfully!');
    }
}
