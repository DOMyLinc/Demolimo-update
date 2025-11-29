<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Services\SecurityLogger;

class NoXss implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $xssPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',
            '/javascript:/i',
            '/on\w+\s*=/i', // onclick, onload, etc.
            '/<embed\b[^>]*>/i',
            '/<object\b[^>]*>/i',
            '/vbscript:/i',
            '/data:text\/html/i',
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                // Log the attempt
                app(SecurityLogger::class)->logXssAttempt($value, $attribute);

                $fail("The {$attribute} contains invalid content.");
                return;
            }
        }
    }
}
