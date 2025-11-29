<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'queue_data',
        'current_index',
        'repeat_mode',
        'shuffle_enabled',
        'shuffle_order',
        'last_updated_at',
    ];

    protected $casts = [
        'queue_data' => 'array',
        'shuffle_order' => 'array',
        'shuffle_enabled' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Methods
     */
    public function addTrack($trackId, $position = null)
    {
        $queue = $this->queue_data ?? [];

        if ($position === null) {
            $queue[] = ['track_id' => $trackId, 'added_at' => now()->toIso8601String()];
        } else {
            array_splice($queue, $position, 0, [['track_id' => $trackId, 'added_at' => now()->toIso8601String()]]);
        }

        $this->update([
            'queue_data' => $queue,
            'last_updated_at' => now(),
        ]);

        return $this;
    }

    public function removeTrack($index)
    {
        $queue = $this->queue_data ?? [];

        if (isset($queue[$index])) {
            array_splice($queue, $index, 1);

            // Adjust current index if needed
            $currentIndex = $this->current_index;
            if ($index < $currentIndex) {
                $currentIndex--;
            } elseif ($index == $currentIndex && $currentIndex >= count($queue)) {
                $currentIndex = max(0, count($queue) - 1);
            }

            $this->update([
                'queue_data' => $queue,
                'current_index' => $currentIndex,
                'last_updated_at' => now(),
            ]);
        }

        return $this;
    }

    public function next()
    {
        $queue = $this->queue_data ?? [];
        $currentIndex = $this->current_index;

        if ($this->repeat_mode === 'one') {
            // Stay on current track
            return $this;
        }

        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= count($queue)) {
            if ($this->repeat_mode === 'all') {
                $nextIndex = 0;
            } else {
                // End of queue
                return $this;
            }
        }

        $this->update([
            'current_index' => $nextIndex,
            'last_updated_at' => now(),
        ]);

        return $this;
    }

    public function previous()
    {
        $currentIndex = $this->current_index;
        $prevIndex = max(0, $currentIndex - 1);

        $this->update([
            'current_index' => $prevIndex,
            'last_updated_at' => now(),
        ]);

        return $this;
    }

    public function toggleShuffle()
    {
        $shuffleEnabled = !$this->shuffle_enabled;

        if ($shuffleEnabled) {
            // Create shuffle order
            $queue = $this->queue_data ?? [];
            $indices = range(0, count($queue) - 1);
            shuffle($indices);

            $this->update([
                'shuffle_enabled' => true,
                'shuffle_order' => $indices,
                'last_updated_at' => now(),
            ]);
        } else {
            $this->update([
                'shuffle_enabled' => false,
                'shuffle_order' => null,
                'last_updated_at' => now(),
            ]);
        }

        return $this;
    }

    public function setRepeatMode($mode)
    {
        if (!in_array($mode, ['off', 'one', 'all'])) {
            throw new \InvalidArgumentException('Invalid repeat mode');
        }

        $this->update([
            'repeat_mode' => $mode,
            'last_updated_at' => now(),
        ]);

        return $this;
    }

    public function getCurrentTrack()
    {
        $queue = $this->queue_data ?? [];
        $index = $this->shuffle_enabled && $this->shuffle_order
            ? $this->shuffle_order[$this->current_index] ?? $this->current_index
            : $this->current_index;

        if (!isset($queue[$index])) {
            return null;
        }

        return Track::find($queue[$index]['track_id']);
    }

    public function clear()
    {
        $this->update([
            'queue_data' => [],
            'current_index' => 0,
            'shuffle_order' => null,
            'last_updated_at' => now(),
        ]);

        return $this;
    }

    public static function getOrCreateForUser($userId)
    {
        $queue = static::where('user_id', $userId)->first();

        if (!$queue) {
            $queue = static::create([
                'user_id' => $userId,
                'session_id' => \Illuminate\Support\Str::uuid()->toString(),
                'queue_data' => [],
                'current_index' => 0,
                'repeat_mode' => 'off',
                'shuffle_enabled' => false,
                'last_updated_at' => now(),
            ]);
        }

        return $queue;
    }
}
