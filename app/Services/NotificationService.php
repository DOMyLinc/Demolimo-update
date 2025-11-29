<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;
use App\Mail\WeeklyDigestMail;

class NotificationService
{
    /**
     * Send notification to user
     */
    public function send($userId, $type, $title, $message, $data = [], $actionUrl = null)
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $preferences = $user->notificationPreferences ?? new NotificationPreference();

        // Create in-app notification
        if ($this->shouldSendApp($preferences, $type)) {
            Notification::createNotification($userId, $type, $title, $message, $data, $actionUrl);
        }

        // Send email notification
        if ($this->shouldSendEmail($preferences, $type)) {
            $this->sendEmail($user, $type, $title, $message, $actionUrl);
        }

        // Send push notification
        if ($this->shouldSendPush($preferences, $type)) {
            $this->sendPush($user, $title, $message, $actionUrl);
        }

        return true;
    }

    /**
     * Notify about new follower
     */
    public function notifyNewFollower($userId, $followerId)
    {
        $follower = User::find($followerId);

        $this->send(
            $userId,
            'new_follower',
            'New Follower',
            "{$follower->name} started following you",
            ['follower_id' => $followerId],
            route('user.profile', $follower->id)
        );
    }

    /**
     * Notify about new comment
     */
    public function notifyNewComment($userId, $commentId, $trackId)
    {
        $this->send(
            $userId,
            'new_comment',
            'New Comment',
            "Someone commented on your track",
            ['comment_id' => $commentId, 'track_id' => $trackId],
            route('tracks.show', $trackId)
        );
    }

    /**
     * Notify about new like
     */
    public function notifyNewLike($userId, $trackId, $likerId)
    {
        $liker = User::find($likerId);

        $this->send(
            $userId,
            'new_like',
            'New Like',
            "{$liker->name} liked your track",
            ['track_id' => $trackId, 'liker_id' => $likerId],
            route('tracks.show', $trackId)
        );
    }

    /**
     * Notify about new message
     */
    public function notifyNewMessage($userId, $senderId, $messageId)
    {
        $sender = User::find($senderId);

        $this->send(
            $userId,
            'new_message',
            'New Message',
            "New message from {$sender->name}",
            ['sender_id' => $senderId, 'message_id' => $messageId],
            route('messages.show', $messageId)
        );
    }

    /**
     * Notify about track upload
     */
    public function notifyTrackUploaded($followerId, $artistId, $trackId)
    {
        $artist = User::find($artistId);

        $this->send(
            $followerId,
            'track_uploaded',
            'New Track',
            "{$artist->name} uploaded a new track",
            ['artist_id' => $artistId, 'track_id' => $trackId],
            route('tracks.show', $trackId)
        );
    }

    /**
     * Notify about event reminder
     */
    public function notifyEventReminder($userId, $eventId)
    {
        $event = \App\Models\Event::find($eventId);

        $this->send(
            $userId,
            'event_reminder',
            'Event Reminder',
            "Reminder: {$event->name} starts soon!",
            ['event_id' => $eventId],
            route('events.show', $eventId)
        );
    }

    /**
     * Check if should send app notification
     */
    protected function shouldSendApp($preferences, $type)
    {
        $key = 'app_' . str_replace('new_', '', $type);
        return $preferences->$key ?? true;
    }

    /**
     * Check if should send email notification
     */
    protected function shouldSendEmail($preferences, $type)
    {
        $key = 'email_' . str_replace('new_', '', $type);
        return $preferences->$key ?? true;
    }

    /**
     * Check if should send push notification
     */
    protected function shouldSendPush($preferences, $type)
    {
        $key = 'push_' . str_replace('new_', '', $type);
        return $preferences->$key ?? true;
    }

    /**
     * Send email notification
     */
    protected function sendEmail($user, $type, $title, $message, $actionUrl)
    {
        try {
            Mail::to($user->email)->send(new NotificationMail($type, $title, $message, $actionUrl));
        } catch (\Exception $e) {
            \Log::error('Failed to send email notification: ' . $e->getMessage());
        }
    }

    /**
     * Send push notification
     */
    protected function sendPush($user, $title, $message, $actionUrl)
    {
        // Implement push notification via service worker
        // This would use Web Push API or Firebase Cloud Messaging
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all as read for user
     */
    public function markAllAsRead($userId)
    {
        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get unread count
     */
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Send weekly digest
     */
    public function sendWeeklyDigest($userId)
    {
        $user = User::find($userId);

        // Get weekly stats
        $stats = [
            'new_followers' => $user->followers()->wherePivot('created_at', '>=', now()->subWeek())->count(),
            'new_likes' => $user->tracks()->withCount([
                'likes' => function ($q) {
                    $q->where('created_at', '>=', now()->subWeek());
                }
            ])->get()->sum('likes_count'),
            'new_plays' => \App\Models\Listener::where('user_id', $userId)
                ->where('started_at', '>=', now()->subWeek())
                ->count(),
        ];

        try {
            Mail::to($user->email)->send(new WeeklyDigestMail($stats));
        } catch (\Exception $e) {
            \Log::error('Failed to send weekly digest: ' . $e->getMessage());
        }
    }
}
