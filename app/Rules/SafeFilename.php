<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeFilename implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        // Check for directory traversal
        if (str_contains($value, '..') || str_contains($value, '/') || str_contains($value, '\\')) {
            $fail("The {$attribute} contains invalid characters.");
            return;
        }

        // Check for null bytes
        if (str_contains($value, "\0")) {
            $fail("The {$attribute} contains invalid characters.");
            return;
        }

        // Check for dangerous extensions
        $dangerousExtensions = [
            'php',
            'phtml',
            'php3',
            'php4',
            'php5',
            'phps',
            'pht',
            'exe',
            'bat',
            'cmd',
            'com',
            'sh',
            'bash',
            'js',
            'vbs',
            'jar',
            'app',
        ];

        $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));
        if (in_array($extension, $dangerousExtensions)) {
            $fail("The {$attribute} has an invalid file type.");
            return;
        }

        // Check for special characters
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $value)) {
            $fail("The {$attribute} contains invalid characters.");
            return;
        }
    }
}
