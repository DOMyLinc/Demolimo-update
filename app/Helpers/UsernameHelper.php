<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Str;

class UsernameHelper
{
    /**
     * Generate a unique username from a name or string.
     *
     * @param string $name
     * @return string
     */
    public static function generate(string $name): string
    {
        // Convert to slug (lowercase, hyphens removed)
        $slug = Str::slug($name, '');

        // If slug is empty (e.g. only special chars), use a default
        if (empty($slug)) {
            $slug = 'user';
        }

        // Check if username exists
        if (User::where('username', $slug)->exists()) {
            $originalSlug = $slug;
            $count = 1;

            while (User::where('username', $slug)->exists()) {
                $slug = $originalSlug . $count;
                $count++;
            }
        }

        return $slug;
    }
}
