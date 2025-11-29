<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Track;

class MusicPlayer extends Component
{
    public $currentTrack = null;
    public $isPlaying = false;
    public $volume = 80;
    public $progress = 0;
    public $queue = [];
    public $originalQueue = [];
    public $currentIndex = 0;
    public $showQueue = false;
    public $isMinimized = false;
    public $shuffle = false;
    public $repeat = false;

    protected $listeners = [
        'playTrack' => 'play',
        'addToQueue' => 'addToQueue',
        'playNext' => 'playNext',
        'playPrevious' => 'playPrevious'
    ];

    public function play($trackId)
    {
        $track = Track::with('artist')->find($trackId);

        if ($track) {
            $this->currentTrack = $track->toArray();
            $this->isPlaying = true;

            // Add to queue if not already there
            if (empty($this->queue)) {
                $this->queue = [$track->toArray()];
                $this->originalQueue = [$track->toArray()];
                $this->currentIndex = 0;
            }

            $this->dispatch('audio-play', url: $track->audio_url);
        }
    }

    public function togglePlay()
    {
        $this->isPlaying = !$this->isPlaying;
        $this->dispatch('audio-toggle');
    }

    public function toggleShuffle()
    {
        $this->shuffle = !$this->shuffle;

        if ($this->shuffle) {
            // Save original queue if not already saved
            if (empty($this->originalQueue)) {
                $this->originalQueue = $this->queue;
            }

            // Shuffle the queue
            $currentTrack = $this->currentTrack;
            $shuffled = $this->queue;
            shuffle($shuffled);

            // Make sure current track is first
            $this->queue = array_filter($shuffled, function ($track) use ($currentTrack) {
                return $track['id'] !== $currentTrack['id'];
            });
            array_unshift($this->queue, $currentTrack);
            $this->currentIndex = 0;

            $this->dispatch('notify', message: 'Shuffle enabled');
        } else {
            // Restore original queue
            if (!empty($this->originalQueue)) {
                $this->queue = $this->originalQueue;
                // Find current track index in original queue
                $currentId = $this->currentTrack['id'];
                foreach ($this->queue as $index => $track) {
                    if ($track['id'] === $currentId) {
                        $this->currentIndex = $index;
                        break;
                    }
                }
            }
            $this->dispatch('notify', message: 'Shuffle disabled');
        }
    }

    public function toggleRepeat()
    {
        $this->repeat = !$this->repeat;
        $this->dispatch('notify', message: $this->repeat ? 'Repeat enabled' : 'Repeat disabled');
    }

    public function next()
    {
        if (empty($this->queue)) {
            return;
        }

        if ($this->repeat) {
            // Replay current track
            $this->dispatch('audio-play', url: $this->currentTrack['audio_url']);
            return;
        }

        $this->currentIndex++;

        if ($this->currentIndex >= count($this->queue)) {
            // End of queue
            $this->currentIndex = 0;
            $this->isPlaying = false;
            return;
        }

        $nextTrack = $this->queue[$this->currentIndex];
        $this->currentTrack = $nextTrack;
        $this->isPlaying = true;
        $this->dispatch('audio-play', url: $nextTrack['audio_url']);
    }

    public function previous()
    {
        if (empty($this->queue)) {
            return;
        }

        $this->currentIndex--;

        if ($this->currentIndex < 0) {
            $this->currentIndex = 0;
            // Restart current track
            $this->dispatch('audio-play', url: $this->currentTrack['audio_url']);
            return;
        }

        $prevTrack = $this->queue[$this->currentIndex];
        $this->currentTrack = $prevTrack;
        $this->isPlaying = true;
        $this->dispatch('audio-play', url: $prevTrack['audio_url']);
    }

    public function playFromQueue($index)
    {
        if (isset($this->queue[$index])) {
            $this->currentIndex = $index;
            $this->currentTrack = $this->queue[$index];
            $this->isPlaying = true;
            $this->dispatch('audio-play', url: $this->currentTrack['audio_url']);
        }
    }

    public function addToQueue($trackId)
    {
        $track = Track::with('artist')->find($trackId);

        if ($track) {
            $this->queue[] = $track->toArray();
            if (!$this->shuffle) {
                $this->originalQueue[] = $track->toArray();
            }
            $this->dispatch('notify', message: 'Added to queue');
        }
    }

    public function removeFromQueue($index)
    {
        if (isset($this->queue[$index])) {
            unset($this->queue[$index]);
            $this->queue = array_values($this->queue); // Re-index array

            // Adjust current index if needed
            if ($this->currentIndex >= count($this->queue)) {
                $this->currentIndex = max(0, count($this->queue) - 1);
            }
        }
    }

    public function clearQueue()
    {
        $this->queue = $this->currentTrack ? [$this->currentTrack] : [];
        $this->originalQueue = $this->queue;
        $this->currentIndex = 0;
        $this->dispatch('notify', message: 'Queue cleared');
    }

    public function render()
    {
        return view('livewire.music-player');
    }
}
