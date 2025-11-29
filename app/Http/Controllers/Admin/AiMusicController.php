<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiMusicProvider;
use App\Models\AiMusicModel;
use App\Models\AiMusicGeneration;
use App\Models\StorageSetting;
use Illuminate\Http\Request;

class AiMusicController extends Controller
{
    /**
     * AI Music Dashboard
     */
    public function index()
    {
        if (!feature_enabled('ai_music_generation')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'AI Music Generation feature is currently disabled.');
        }

        $stats = [
            'total_providers' => AiMusicProvider::count(),
            'active_providers' => AiMusicProvider::where('is_active', true)->count(),
            'total_models' => AiMusicModel::count(),
            'total_generations' => AiMusicGeneration::count(),
            'generations_today' => AiMusicGeneration::whereDate('created_at', today())->count(),
            'total_cost' => AiMusicGeneration::where('status', 'completed')->sum('cost'),
        ];

        $recentGenerations = AiMusicGeneration::with(['user', 'model.provider'])
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.ai-music.index', compact('stats', 'recentGenerations'));
    }

    /**
     * Providers Management
     */
    public function providers()
    {
        $providers = AiMusicProvider::withCount('models')->orderBy('priority')->get();
        return view('admin.ai-music.providers', compact('providers'));
    }

    public function createProvider()
    {
        return view('admin.ai-music.provider-form');
    }

    public function storeProvider(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:ai_music_providers,slug',
            'api_endpoint' => 'required|url',
            'api_key' => 'nullable|string',
            'cost_per_generation' => 'required|numeric|min:0',
            'max_duration' => 'required|integer|min:1',
            'supported_styles' => 'nullable|json',
            'settings' => 'nullable|json',
            'is_active' => 'boolean',
            'priority' => 'required|integer',
        ]);

        if (isset($validated['supported_styles'])) {
            $validated['supported_styles'] = json_decode($validated['supported_styles'], true);
        }
        if (isset($validated['settings'])) {
            $validated['settings'] = json_decode($validated['settings'], true);
        }

        AiMusicProvider::create($validated);

        return redirect()->route('admin.ai-music.providers')
            ->with('success', 'AI provider created successfully!');
    }

    public function editProvider(AiMusicProvider $provider)
    {
        return view('admin.ai-music.provider-form', compact('provider'));
    }

    public function updateProvider(Request $request, AiMusicProvider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:ai_music_providers,slug,' . $provider->id,
            'api_endpoint' => 'required|url',
            'api_key' => 'nullable|string',
            'cost_per_generation' => 'required|numeric|min:0',
            'max_duration' => 'required|integer|min:1',
            'supported_styles' => 'nullable|json',
            'settings' => 'nullable|json',
            'is_active' => 'boolean',
            'priority' => 'required|integer',
        ]);

        if (isset($validated['supported_styles'])) {
            $validated['supported_styles'] = json_decode($validated['supported_styles'], true);
        }
        if (isset($validated['settings'])) {
            $validated['settings'] = json_decode($validated['settings'], true);
        }

        $provider->update($validated);

        return redirect()->route('admin.ai-music.providers')
            ->with('success', 'AI provider updated successfully!');
    }

    public function deleteProvider(AiMusicProvider $provider)
    {
        $provider->delete();
        return back()->with('success', 'AI provider deleted!');
    }

    /**
     * Models Management
     */
    public function models()
    {
        $models = AiMusicModel::with('provider')->orderBy('sort_order')->get();
        $providers = AiMusicProvider::where('is_active', true)->get();

        return view('admin.ai-music.models', compact('models', 'providers'));
    }

    public function createModel()
    {
        $providers = AiMusicProvider::where('is_active', true)->get();
        return view('admin.ai-music.model-form', compact('providers'));
    }

    public function storeModel(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:ai_music_providers,id',
            'name' => 'required|string|max:255',
            'model_id' => 'required|string',
            'description' => 'nullable|string',
            'price_per_generation' => 'required|numeric|min:0',
            'currency' => 'required|string|in:usd,points',
            'max_duration' => 'required|integer|min:1',
            'capabilities' => 'nullable|json',
            'is_active' => 'boolean',
            'sort_order' => 'required|integer',
        ]);

        if (isset($validated['capabilities'])) {
            $validated['capabilities'] = json_decode($validated['capabilities'], true);
        }

        AiMusicModel::create($validated);

        return redirect()->route('admin.ai-music.models')
            ->with('success', 'AI model created successfully!');
    }

    public function editModel(AiMusicModel $model)
    {
        $providers = AiMusicProvider::where('is_active', true)->get();
        return view('admin.ai-music.model-form', compact('model', 'providers'));
    }

    public function updateModel(Request $request, AiMusicModel $model)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:ai_music_providers,id',
            'name' => 'required|string|max:255',
            'model_id' => 'required|string',
            'description' => 'nullable|string',
            'price_per_generation' => 'required|numeric|min:0',
            'currency' => 'required|string|in:usd,points',
            'max_duration' => 'required|integer|min:1',
            'capabilities' => 'nullable|json',
            'is_active' => 'boolean',
            'sort_order' => 'required|integer',
        ]);

        if (isset($validated['capabilities'])) {
            $validated['capabilities'] = json_decode($validated['capabilities'], true);
        }

        $model->update($validated);

        return redirect()->route('admin.ai-music.models')
            ->with('success', 'AI model updated successfully!');
    }

    public function deleteModel(AiMusicModel $model)
    {
        $model->delete();
        return back()->with('success', 'AI model deleted!');
    }

    /**
     * Generations History
     */
    public function generations()
    {
        $generations = AiMusicGeneration::with(['user', 'model.provider'])
            ->latest()
            ->paginate(50);

        $stats = [
            'total' => AiMusicGeneration::count(),
            'completed' => AiMusicGeneration::where('status', 'completed')->count(),
            'pending' => AiMusicGeneration::where('status', 'pending')->count(),
            'failed' => AiMusicGeneration::where('status', 'failed')->count(),
        ];

        return view('admin.ai-music.generations', compact('generations', 'stats'));
    }

    /**
     * Storage Settings
     */
    public function storageSettings()
    {
        $settings = StorageSetting::orderBy('is_default', 'desc')->get();
        return view('admin.ai-music.storage', compact('settings'));
    }

    public function createStorage()
    {
        return view('admin.ai-music.storage-form');
    }

    public function storeStorage(Request $request)
    {
        $validated = $request->validate([
            'driver' => 'required|string',
            'name' => 'required|string|max:255',
            'credentials' => 'nullable|json',
            'bucket' => 'nullable|string',
            'region' => 'nullable|string',
            'endpoint' => 'nullable|url',
            'url' => 'nullable|url',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'use_for' => 'required|string',
        ]);

        if (isset($validated['credentials'])) {
            $validated['credentials'] = json_decode($validated['credentials'], true);
        }

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            StorageSetting::where('is_default', true)->update(['is_default' => false]);
        }

        StorageSetting::create($validated);

        return redirect()->route('admin.ai-music.storage')
            ->with('success', 'Storage setting created successfully!');
    }

    public function editStorage(StorageSetting $storage)
    {
        return view('admin.ai-music.storage-form', compact('storage'));
    }

    public function updateStorage(Request $request, StorageSetting $storage)
    {
        $validated = $request->validate([
            'driver' => 'required|string',
            'name' => 'required|string|max:255',
            'credentials' => 'nullable|json',
            'bucket' => 'nullable|string',
            'region' => 'nullable|string',
            'endpoint' => 'nullable|url',
            'url' => 'nullable|url',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'use_for' => 'required|string',
        ]);

        if (isset($validated['credentials'])) {
            $validated['credentials'] = json_decode($validated['credentials'], true);
        }

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            StorageSetting::where('is_default', true)
                ->where('id', '!=', $storage->id)
                ->update(['is_default' => false]);
        }

        $storage->update($validated);

        return redirect()->route('admin.ai-music.storage')
            ->with('success', 'Storage setting updated successfully!');
    }

    public function deleteStorage(StorageSetting $storage)
    {
        $storage->delete();
        return back()->with('success', 'Storage setting deleted!');
    }
}
