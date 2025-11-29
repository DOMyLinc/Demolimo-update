<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $minLength = config('security.password.min_length', 8);
        $requireUppercase = config('security.password.require_uppercase', true);
        $requireLowercase = config('security.password.require_lowercase', true);
        $requireNumbers = config('security.password.require_numbers', true);
        $requireSpecialChars = config('security.password.require_special_chars', true);

        // Check minimum length
        if (strlen($value) < $minLength) {
            $fail("The {$attribute} must be at least {$minLength} characters.");
            return;
        }

        // Check for uppercase letter
        if ($requireUppercase && !preg_match('/[A-Z]/', $value)) {
            $fail("The {$attribute} must contain at least one uppercase letter.");
            return;
        }

        // Check for lowercase letter
        if ($requireLowercase && !preg_match('/[a-z]/', $value)) {
            $fail("The {$attribute} must contain at least one lowercase letter.");
            return;
        }

        // Check for number
        if ($requireNumbers && !preg_match('/[0-9]/', $value)) {
            $fail("The {$attribute} must contain at least one number.");
            return;
        }

        // Check for special character
        if ($requireSpecialChars && !preg_match('/[^A-Za-z0-9]/', $value)) {
            $fail("The {$attribute} must contain at least one special character.");
            return;
        }

        // Check against common passwords
        if ($this->isCommonPassword($value)) {
            $fail("The {$attribute} is too common. Please choose a stronger password.");
            return;
        }
    }

    /**
     * Check if password is in common password list
     */
    protected function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password',
            'password123',
            '12345678',
            'qwerty',
            'abc123',
            'monkey',
            '1234567',
            'letmein',
            'trustno1',
            'dragon',
            'baseball',
            'iloveyou',
            'master',
            'sunshine',
            'ashley',
            'bailey',
            'passw0rd',
            'shadow',
            '123123',
            '654321',
            'superman',
            'qazwsx',
            'michael',
            'football',
            'welcome'
        ];

        return in_array(strtolower($password), $commonPasswords);
    }
}
