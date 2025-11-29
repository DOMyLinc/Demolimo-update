<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PostReaction;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    /**
     * Toggle reaction on a post
     */
    public function togglePostReaction(Request $request, Post $post)
    {
        $request->validate([
            'reaction_type' => 'required|in:like,love,haha,wow,sad,angry',
        ]);

        $userId = Auth::id();
        $reactionType = $request->reaction_type;

        // Check if user already reacted
        $existingReaction = PostReaction::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingReaction) {
            // If same reaction, remove it
            if ($existingReaction->reaction_type === $reactionType) {
                $existingReaction->delete();

                return response()->json([
                    'success' => true,
                    'action' => 'removed',
                    'message' => 'Reaction removed',
                    'reaction_counts' => $post->fresh()->reaction_summary ?? [],
                ]);
            }

            // Update to new reaction type
            $existingReaction->update(['reaction_type' => $reactionType]);

            return response()->json([
                'success' => true,
                'action' => 'updated',
                'message' => 'Reaction updated',
                'reaction_type' => $reactionType,
                'emoji' => PostReaction::getEmoji($reactionType),
                'reaction_counts' => $post->fresh()->reaction_summary ?? [],
            ]);
        }

        // Create new reaction
        PostReaction::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'reaction_type' => $reactionType,
        ]);

        return response()->json([
            'success' => true,
            'action' => 'added',
            'message' => 'Reaction added',
            'reaction_type' => $reactionType,
            'emoji' => PostReaction::getEmoji($reactionType),
            'reaction_counts' => $post->fresh()->reaction_summary ?? [],
        ]);
    }

    /**
     * Toggle reaction on a comment
     */
    public function toggleCommentReaction(Request $request, Comment $comment)
    {
        $request->validate([
            'reaction_type' => 'required|in:like,love,haha,wow,sad,angry',
        ]);

        $userId = Auth::id();
        $reactionType = $request->reaction_type;

        // Check if user already reacted
        $existingReaction = CommentReaction::where('comment_id', $comment->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingReaction) {
            // If same reaction, remove it
            if ($existingReaction->reaction_type === $reactionType) {
                $existingReaction->delete();

                return response()->json([
                    'success' => true,
                    'action' => 'removed',
                    'message' => 'Reaction removed',
                    'reactions_count' => $comment->fresh()->reactions_count ?? 0,
                ]);
            }

            // Update to new reaction type
            $existingReaction->update(['reaction_type' => $reactionType]);

            return response()->json([
                'success' => true,
                'action' => 'updated',
                'message' => 'Reaction updated',
                'reaction_type' => $reactionType,
                'emoji' => CommentReaction::getEmoji($reactionType),
                'reactions_count' => $comment->fresh()->reactions_count ?? 0,
            ]);
        }

        // Create new reaction
        CommentReaction::create([
            'comment_id' => $comment->id,
            'user_id' => $userId,
            'reaction_type' => $reactionType,
        ]);

        return response()->json([
            'success' => true,
            'action' => 'added',
            'message' => 'Reaction added',
            'reaction_type' => $reactionType,
            'emoji' => CommentReaction::getEmoji($reactionType),
            'reactions_count' => $comment->fresh()->reactions_count ?? 0,
        ]);
    }

    /**
     * Get reactions for a post
     */
    public function getPostReactions(Post $post)
    {
        $reactions = PostReaction::where('post_id', $post->id)
            ->with('user:id,name,avatar')
            ->get()
            ->groupBy('reaction_type')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'users' => $group->map(fn($r) => $r->user)->take(10),
                ];
            });

        $userReaction = PostReaction::where('post_id', $post->id)
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'success' => true,
            'reactions' => $reactions,
            'user_reaction' => $userReaction ? [
                'type' => $userReaction->reaction_type,
                'emoji' => PostReaction::getEmoji($userReaction->reaction_type),
            ] : null,
            'total_count' => $post->reactions_count,
        ]);
    }

    /**
     * Get reactions for a comment
     */
    public function getCommentReactions(Comment $comment)
    {
        $reactions = CommentReaction::where('comment_id', $comment->id)
            ->with('user:id,name,avatar')
            ->get()
            ->groupBy('reaction_type')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'users' => $group->map(fn($r) => $r->user)->take(10),
                ];
            });

        $userReaction = CommentReaction::where('comment_id', $comment->id)
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'success' => true,
            'reactions' => $reactions,
            'user_reaction' => $userReaction ? [
                'type' => $userReaction->reaction_type,
                'emoji' => CommentReaction::getEmoji($userReaction->reaction_type),
            ] : null,
            'total_count' => $comment->reactions_count,
        ]);
    }
}
