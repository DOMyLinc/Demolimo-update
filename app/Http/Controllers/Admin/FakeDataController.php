<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class FakeDataController extends Controller
{
    protected $firstNames = [
        'James',
        'John',
        'Robert',
        'Michael',
        'William',
        'David',
        'Richard',
        'Joseph',
        'Mary',
        'Patricia',
        'Jennifer',
        'Linda',
        'Elizabeth',
        'Barbara',
        'Susan',
        'Jessica',
        'Alex',
        'Sam',
        'Jordan',
        'Taylor',
        'Morgan',
        'Casey',
        'Riley',
        'Avery'
    ];

    protected $lastNames = [
        'Smith',
        'Johnson',
        'Williams',
        'Brown',
        'Jones',
        'Garcia',
        'Miller',
        'Davis',
        'Rodriguez',
        'Martinez',
        'Hernandez',
        'Lopez',
        'Gonzalez',
        'Wilson',
        'Anderson',
        'Thomas',
        'Taylor',
        'Moore',
        'Jackson',
        'Martin',
        'Lee',
        'Walker',
        'Hall'
    ];

    protected $musicGenres = [
        'Pop',
        'Rock',
        'Hip Hop',
        'Electronic',
        'Jazz',
        'Classical',
        'R&B',
        'Country',
        'Indie',
        'Alternative',
        'Metal',
        'Reggae',
        'Blues',
        'Folk',
        'Soul',
        'Funk'
    ];

    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'fake_users' => User::where('is_fake', true)->count(),
            'total_tracks' => Track::count(),
        ];

        return view('admin.fake-data.index', compact('stats'));
    }

    /**
     * Generate fake users
     */
    public function generateUsers(Request $request)
    {
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:1000',
            'role' => 'required|in:user,artist',
            'verified' => 'boolean',
            'with_avatar' => 'boolean',
            'with_tracks' => 'boolean',
            'tracks_per_user' => 'nullable|integer|min:1|max:50',
        ]);

        $count = $validated['count'];
        $generated = 0;

        for ($i = 0; $i < $count; $i++) {
            $firstName = $this->firstNames[array_rand($this->firstNames)];
            $lastName = $this->lastNames[array_rand($this->lastNames)];
            $name = $firstName . ' ' . $lastName;
            $username = strtolower($firstName . $lastName . rand(100, 999));
            $email = $username . '@fakeuser.com';

            // Check if email exists
            if (User::where('email', $email)->exists()) {
                continue;
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => $validated['role'],
                'is_verified' => $validated['verified'] ?? true,
                'is_fake' => true,
                'points' => rand(0, 1000),
                'avatar' => $validated['with_avatar'] ? $this->generateAvatar($name) : null,
                'bio' => $this->generateBio(),
            ]);

            // Generate tracks for this user
            if ($validated['with_tracks'] ?? false) {
                $trackCount = $validated['tracks_per_user'] ?? rand(1, 5);
                $this->generateTracksForUser($user, $trackCount);
            }

            $generated++;
        }

        return back()->with('success', "Generated {$generated} fake users successfully!");
    }

    /**
     * Generate fake tracks
     */
    public function generateTracks(Request $request)
    {
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:500',
            'user_id' => 'nullable|exists:users,id',
            'with_metrics' => 'boolean',
        ]);

        $count = $validated['count'];
        $userId = $validated['user_id'];

        if (!$userId) {
            // Get random users
            $users = User::where('role', '!=', 'admin')->pluck('id')->toArray();
            if (empty($users)) {
                return back()->with('error', 'No users available. Create users first.');
            }
        } else {
            $users = [$userId];
        }

        $generated = 0;

        for ($i = 0; $i < $count; $i++) {
            $userId = $users[array_rand($users)];
            $this->generateTracksForUser(User::find($userId), 1, $validated['with_metrics'] ?? false);
            $generated++;
        }

        return back()->with('success', "Generated {$generated} fake tracks successfully!");
    }

    /**
     * Generate tracks for a specific user
     */
    protected function generateTracksForUser(User $user, int $count, bool $withMetrics = false)
    {
        for ($i = 0; $i < $count; $i++) {
            $genre = $this->musicGenres[array_rand($this->musicGenres)];
            $title = $this->generateTrackTitle($genre);

            $track = Track::create([
                'user_id' => $user->id,
                'title' => $title,
                'slug' => Str::slug($title) . '-' . rand(1000, 9999),
                'description' => $this->generateTrackDescription($genre),
                'audio_path' => 'fake/audio/' . Str::slug($title) . '.mp3',
                'image_path' => $this->generateTrackCover(),
                'duration' => rand(120, 300),
                'file_size' => rand(3000000, 10000000),
                'bitrate' => 320,
                'plays' => $withMetrics ? rand(0, 10000) : 0,
                'views' => $withMetrics ? rand(0, 15000) : 0,
                'likes' => $withMetrics ? rand(0, 5000) : 0,
                'shares' => $withMetrics ? rand(0, 1000) : 0,
                'downloads' => $withMetrics ? rand(0, 2000) : 0,
                'is_public' => true,
                'is_downloadable' => rand(0, 1),
                'price' => rand(0, 1) ? 0 : rand(1, 10),
                'tags' => [$genre, 'fake', 'demo'],
            ]);

            // Calculate blockchain value if enabled
            if ($withMetrics && \App\Models\PlatformSetting::get('blockchain_enabled', true)) {
                app(\App\Services\BlockchainValuationService::class)->calculateTrackValue($track);
            }
        }
    }

    /**
     * Generate realistic track title
     */
    protected function generateTrackTitle(string $genre): string
    {
        $templates = [
            'Midnight {word}',
            '{word} Dreams',
            'Lost in {word}',
            'The {word} Song',
            '{word} Nights',
            'Dancing {word}',
            'Summer {word}',
            '{word} Vibes',
            'Electric {word}',
            '{word} Paradise',
        ];

        $words = [
            'Love',
            'Fire',
            'Ocean',
            'Sky',
            'Heart',
            'Soul',
            'Light',
            'Shadow',
            'Rain',
            'Storm',
            'Star',
            'Moon',
            'Sun',
            'Wind',
            'Wave',
            'Echo'
        ];

        $template = $templates[array_rand($templates)];
        $word = $words[array_rand($words)];

        return str_replace('{word}', $word, $template);
    }

    /**
     * Generate track description
     */
    protected function generateTrackDescription(string $genre): string
    {
        $descriptions = [
            "A beautiful {genre} track that captures the essence of modern music.",
            "Experience the perfect blend of {genre} with this amazing composition.",
            "This {genre} masterpiece will take you on an unforgettable journey.",
            "Feel the energy of {genre} in this incredible production.",
            "A stunning {genre} track featuring amazing melodies and rhythms.",
        ];

        $description = $descriptions[array_rand($descriptions)];
        return str_replace('{genre}', $genre, $description);
    }

    /**
     * Generate user bio
     */
    protected function generateBio(): string
    {
        $bios = [
            "Music producer and artist passionate about creating unique sounds.",
            "Independent musician exploring new genres and styles.",
            "Creating music that touches the soul and moves the body.",
            "Professional artist with a love for experimental sounds.",
            "Bringing fresh vibes to the music scene.",
            "Music is my passion, creating is my purpose.",
        ];

        return $bios[array_rand($bios)];
    }

    /**
     * Generate avatar URL (using placeholder service)
     */
    protected function generateAvatar(string $name): string
    {
        $initial = substr($name, 0, 1);
        return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&size=200&background=random";
    }

    /**
     * Generate track cover URL
     */
    protected function generateTrackCover(): string
    {
        $colors = ['667eea', '764ba2', 'f093fb', '4facfe', 'fa709a', 'fee140'];
        $color = $colors[array_rand($colors)];
        return "https://via.placeholder.com/500x500/{$color}/ffffff?text=Track+Cover";
    }

    /**
     * Delete all fake data
     */
    public function deleteFakeData(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:users,tracks,all',
        ]);

        switch ($validated['type']) {
            case 'users':
                $count = User::where('is_fake', true)->count();
                User::where('is_fake', true)->delete();
                return back()->with('success', "Deleted {$count} fake users!");

            case 'tracks':
                $count = Track::whereHas('user', function ($q) {
                    $q->where('is_fake', true);
                })->count();
                Track::whereHas('user', function ($q) {
                    $q->where('is_fake', true);
                })->delete();
                return back()->with('success', "Deleted {$count} fake tracks!");

            case 'all':
                $userCount = User::where('is_fake', true)->count();
                $trackCount = Track::whereHas('user', function ($q) {
                    $q->where('is_fake', true);
                })->count();

                Track::whereHas('user', function ($q) {
                    $q->where('is_fake', true);
                })->delete();
                User::where('is_fake', true)->delete();

                return back()->with('success', "Deleted {$userCount} fake users and {$trackCount} fake tracks!");
        }
    }

    /**
     * Generate fake interactions (likes, plays, etc.)
     */
    public function generateInteractions(Request $request)
    {
        $validated = $request->validate([
            'track_id' => 'nullable|exists:tracks,id',
            'plays' => 'nullable|integer|min:0|max:100000',
            'likes' => 'nullable|integer|min:0|max:50000',
            'views' => 'nullable|integer|min:0|max:100000',
            'shares' => 'nullable|integer|min:0|max:10000',
        ]);

        if ($validated['track_id']) {
            $tracks = [Track::find($validated['track_id'])];
        } else {
            $tracks = Track::where('is_public', true)->get();
        }

        $updated = 0;

        foreach ($tracks as $track) {
            if (isset($validated['plays'])) {
                $track->plays += rand(0, $validated['plays']);
            }
            if (isset($validated['likes'])) {
                $track->likes += rand(0, $validated['likes']);
            }
            if (isset($validated['views'])) {
                $track->views += rand(0, $validated['views']);
            }
            if (isset($validated['shares'])) {
                $track->shares += rand(0, $validated['shares']);
            }

            $track->save();

            // Recalculate blockchain value
            if (\App\Models\PlatformSetting::get('blockchain_enabled', true)) {
                app(\App\Services\BlockchainValuationService::class)->calculateTrackValue($track);
            }

            $updated++;
        }

        return back()->with('success', "Generated interactions for {$updated} tracks!");
    }
}
