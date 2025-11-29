<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AiMusicGeneration;
use App\Models\AiMusicModel;
use App\Models\AiMusicProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AiMusicController extends Controller
{
    public function index()
    {
        $providers = AiMusicProvider::where('is_active', true)
            ->with([
                'models' => function ($q) {
                    $q->where('is_active', true)->orderBy('sort_order');
                }
            ])
            ->orderBy('priority')
            ->get();

        return view('user.ai-music.index', compact('providers'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:ai_music_models,id',
            'prompt' => 'required|string|max:1000',
            'duration' => 'required|integer|min:10|max:300',
        ]);

        $model = AiMusicModel::findOrFail($request->model_id);
        $user = Auth::user();

        // Check if user has enough credits/points (Mock logic)
        // if ($user->credits < $model->price_per_generation) { ... }

        // Create Generation Record
        $generation = AiMusicGeneration::create([
            'user_id' => $user->id,
            'ai_music_model_id' => $model->id,
            'prompt' => $request->prompt,
            'duration' => $request->duration,
            'status' => 'pending',
            'cost' => $model->price_per_generation,
            'currency' => $model->currency,
        ]);

        // Mocking the API call and completion
        // In a real app, this would be a Job dispatched to a queue
        $this->mockGeneration($generation);

        return redirect()->route('ai-music.history')
            ->with('success', 'Music generation started! It should be ready shortly.');
    }

    public function history()
    {
        $generations = AiMusicGeneration::where('user_id', Auth::id())
            ->with('model.provider')
            ->latest()
            ->paginate(20);

        return view('user.ai-music.history', compact('generations'));
    }

    private function mockGeneration(AiMusicGeneration $generation)
    {
        // Simulate processing
        $generation->update([
            'status' => 'completed',
            'file_path' => 'ai-generated/demo-track-' . Str::random(10) . '.mp3',
            'completed_at' => now(),
        ]);
    }
}
