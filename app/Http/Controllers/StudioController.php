<?php

namespace App\Http\Controllers;

use App\Models\StudioProject;
use App\Models\StudioPattern;
use App\Models\StudioTrack;
use App\Models\StudioAudioClip;
use App\Models\StudioInstrument;
use App\Models\StudioEffect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudioController extends Controller
{
    /**
     * Studio dashboard
     */
    public function index()
    {
        $projects = StudioProject::where('user_id', Auth::id())
            ->latest('last_opened_at')
            ->get();

        $instruments = StudioInstrument::where('is_active', true)->get();
        $effects = StudioEffect::where('is_active', true)->get();

        return view('studio.index', compact('projects', 'instruments', 'effects'));
    }

    /**
     * Create new project
     */
    public function createProject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bpm' => 'integer|min:40|max:300',
            'key' => 'string',
            'time_signature' => 'string',
        ]);

        $project = StudioProject::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'bpm' => $validated['bpm'] ?? 120,
            'key' => $validated['key'] ?? 'C',
            'time_signature' => $validated['time_signature'] ?? '4/4',
            'project_data' => json_encode([
                'version' => '1.0',
                'created_at' => now()->toIso8601String(),
            ]),
            'last_opened_at' => now(),
        ]);

        // Create master track
        StudioTrack::create([
            'project_id' => $project->id,
            'name' => 'Master',
            'type' => 'master',
            'volume' => 100,
            'pan' => 0,
            'order' => 0,
        ]);

        return redirect()->route('studio.edit', $project);
    }

    /**
     * Open project (main studio interface)
     */
    public function edit(StudioProject $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        // Update last opened
        $project->update(['last_opened_at' => now()]);

        $project->load(['patterns', 'tracks.audioClips']);

        $instruments = StudioInstrument::where('is_active', true)->get();
        $effects = StudioEffect::where('is_active', true)->get();

        return view('studio.editor', compact('project', 'instruments', 'effects'));
    }

    /**
     * Save project
     */
    public function saveProject(Request $request, StudioProject $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'bpm' => 'integer|min:40|max:300',
            'key' => 'string',
            'project_data' => 'required|json',
        ]);

        $project->update($validated);

        return response()->json(['success' => true, 'message' => 'Project saved!']);
    }

    /**
     * Delete project
     */
    public function deleteProject(StudioProject $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $project->delete();

        return redirect()->route('studio.index')
            ->with('success', 'Project deleted!');
    }

    /**
     * Create pattern (Piano Roll)
     */
    public function createPattern(Request $request, StudioProject $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'string',
            'length' => 'integer|min:1|max:64',
        ]);

        $pattern = StudioPattern::create([
            'project_id' => $project->id,
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#3B82F6',
            'length' => $validated['length'] ?? 16,
            'notes' => json_encode([]),
        ]);

        return response()->json(['success' => true, 'pattern' => $pattern]);
    }

    /**
     * Update pattern notes
     */
    public function updatePattern(Request $request, StudioPattern $pattern)
    {
        $validated = $request->validate([
            'notes' => 'required|json',
        ]);

        $pattern->update(['notes' => $validated['notes']]);

        return response()->json(['success' => true]);
    }

    /**
     * Create track (Mixer)
     */
    public function createTrack(Request $request, StudioProject $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:audio,midi,master',
            'color' => 'string',
        ]);

        $track = StudioTrack::create([
            'project_id' => $project->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'color' => $validated['color'] ?? '#3B82F6',
            'volume' => 100,
            'pan' => 0,
            'order' => $project->tracks()->count(),
        ]);

        return response()->json(['success' => true, 'track' => $track]);
    }

    /**
     * Update track settings
     */
    public function updateTrack(Request $request, StudioTrack $track)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'volume' => 'integer|min:0|max:200',
            'pan' => 'integer|min:-100|max:100',
            'muted' => 'boolean',
            'solo' => 'boolean',
            'effects' => 'json',
        ]);

        $track->update($validated);

        return response()->json(['success' => true]);
    }

    /**
     * Upload audio clip
     */
    public function uploadAudio(Request $request, StudioTrack $track)
    {
        $validated = $request->validate([
            'audio_file' => 'required|file|mimes:mp3,wav,ogg,flac|max:51200',
            'start_time' => 'integer|min:0',
        ]);

        $path = $request->file('audio_file')->store('studio/audio', 'public');

        // Get audio duration (you'd use FFmpeg here)
        $duration = 30000; // milliseconds (placeholder)

        $clip = StudioAudioClip::create([
            'track_id' => $track->id,
            'audio_file' => $path,
            'start_time' => $validated['start_time'] ?? 0,
            'duration' => $duration,
        ]);

        return response()->json(['success' => true, 'clip' => $clip]);
    }

    /**
     * Record audio
     */
    public function recordAudio(Request $request, StudioTrack $track)
    {
        $validated = $request->validate([
            'audio_blob' => 'required',
        ]);

        // Save recorded audio
        $audioData = base64_decode($validated['audio_blob']);
        $filename = 'recording-' . time() . '.wav';
        $path = 'studio/recordings/' . $filename;

        Storage::disk('public')->put($path, $audioData);

        $clip = StudioAudioClip::create([
            'track_id' => $track->id,
            'audio_file' => $path,
            'start_time' => 0,
            'duration' => 0, // Calculate from audio
        ]);

        return response()->json(['success' => true, 'clip' => $clip]);
    }

    /**
     * Export/Render project
     */
    public function exportProject(Request $request, StudioProject $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'format' => 'required|in:mp3,wav,flac',
            'quality' => 'required|in:low,medium,high',
        ]);

        // Queue export job
        // ExportProjectJob::dispatch($project, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Export started! You will be notified when ready.',
        ]);
    }

    /**
     * Get available instruments
     */
    public function getInstruments()
    {
        $instruments = StudioInstrument::where('is_active', true)->get();
        return response()->json($instruments);
    }

    /**
     * Get available effects
     */
    public function getEffects()
    {
        $effects = StudioEffect::where('is_active', true)->get();
        return response()->json($effects);
    }

    /**
     * Auto-save project
     */
    public function autoSave(Request $request, StudioProject $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'project_data' => 'required|json',
        ]);

        $project->update([
            'project_data' => $validated['project_data'],
            'last_opened_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

