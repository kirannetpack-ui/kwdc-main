<?php

namespace App\Services;

class SecurityService
{
    /**
     * Sanitize input data
     */
    public static function sanitize($data)
    {
        if (is_string($data)) {
            return strip_tags(trim($data));
        }
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return $data;
    }

    /**
     * Validate email domain
     */
    public static function validateEmailDomain($email)
    {
        $domain = substr(strrchr($email, "@"), 1);
        return checkdnsrr($domain, 'MX');
    }

    /**
     * Generate secure random string
     */
    public static function secureRandom($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Check if password is strong
     */
    public static function isStrongPassword($password)
    {
        $patterns = [
            '/[a-z]/',      // lowercase
            '/[A-Z]/',      // uppercase
            '/[0-9]/',      // digit
            '/[^a-zA-Z0-9]/' // special character
        ];
        
        $score = 0;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $password)) {
                $score++;
            }
        }
        
        return $score >= 4 && strlen($password) >= 8;
    }
}