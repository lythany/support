<?php

declare(strict_types=1);

namespace Lythany\Support;

use InvalidArgumentException;
use RuntimeException;

/**
 * Security utility functions
 * 
 * Provides comprehensive security utilities including password hashing,
 * secure random generation, input sanitization, and validation.
 *
 * @package Lythany\Support
 * @author Lythany Framework Team
 * @since 1.0.1
 */
class Security
{
    /**
     * Default password hashing options
     *
     * @var array<string, mixed>
     */
    protected static array $defaultHashOptions = [
        'cost' => 12,
    ];

    /**
     * Create a secure hash of the given password
     *
     * @param string $password
     * @param array<string, mixed> $options
     * @return string
     * @throws RuntimeException
     */
    public static function hashPassword(string $password, array $options = []): string
    {
        $options = array_merge(static::$defaultHashOptions, $options);
        
        $hash = password_hash($password, PASSWORD_ARGON2ID, $options);
        
        if ($hash === false) {
            throw new RuntimeException('Failed to hash password');
        }
        
        return $hash;
    }

    /**
     * Verify a password against its hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if a password hash needs to be rehashed
     *
     * @param string $hash
     * @param array<string, mixed> $options
     * @return bool
     */
    public static function needsRehash(string $hash, array $options = []): bool
    {
        $options = array_merge(static::$defaultHashOptions, $options);
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, $options);
    }

    /**
     * Generate a cryptographically secure random string
     *
     * @param int $length
     * @param string $characters
     * @return string
     * @throws InvalidArgumentException
     */
    public static function randomString(int $length = 32, string $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string
    {
        if ($length <= 0) {
            throw new InvalidArgumentException('Length must be positive');
        }

        if (empty($characters)) {
            throw new InvalidArgumentException('Characters string cannot be empty');
        }

        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Generate a cryptographically secure random hex string
     *
     * @param int $length
     * @return string
     * @throws InvalidArgumentException
     */
    public static function randomHex(int $length = 32): string
    {
        if ($length <= 0) {
            throw new InvalidArgumentException('Length must be positive');
        }

        $bytes = random_bytes((int) ceil($length / 2));
        return substr(bin2hex($bytes), 0, $length);
    }

    /**
     * Generate a secure random token suitable for CSRF protection
     *
     * @param int $length
     * @return string
     */
    public static function csrfToken(int $length = 40): string
    {
        return static::randomString($length, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
    }

    /**
     * Sanitize user input to prevent XSS attacks
     *
     * @param string $input
     * @param bool $preserveLineBreaks
     * @return string
     */
    public static function sanitizeInput(string $input, bool $preserveLineBreaks = false): string
    {
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // HTML entity encoding
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        
        if ($preserveLineBreaks) {
            // Convert line breaks to <br> tags if preserving them
            $input = nl2br($input, false);
        }
        
        return $input;
    }

    /**
     * Validate and sanitize email address
     *
     * @param string $email
     * @return string|null Returns sanitized email or null if invalid
     */
    public static function sanitizeEmail(string $email): ?string
    {
        $email = trim($email);
        $sanitized = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if ($sanitized === false || !filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        
        return $sanitized;
    }

    /**
     * Validate and sanitize URL
     *
     * @param string $url
     * @param array<string> $allowedSchemes
     * @return string|null Returns sanitized URL or null if invalid
     */
    public static function sanitizeUrl(string $url, array $allowedSchemes = ['http', 'https']): ?string
    {
        $url = trim($url);
        $sanitized = filter_var($url, FILTER_SANITIZE_URL);
        
        if ($sanitized === false || !filter_var($sanitized, FILTER_VALIDATE_URL)) {
            return null;
        }
        
        $parsed = parse_url($sanitized);
        
        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], $allowedSchemes, true)) {
            return null;
        }
        
        return $sanitized;
    }

    /**
     * Generate a secure hash for data integrity verification
     *
     * @param string $data
     * @param string $algorithm
     * @return string
     * @throws InvalidArgumentException
     */
    public static function hashData(string $data, string $algorithm = 'sha256'): string
    {
        if (!in_array($algorithm, hash_algos(), true)) {
            throw new InvalidArgumentException("Unsupported hash algorithm: {$algorithm}");
        }
        
        return hash($algorithm, $data);
    }

    /**
     * Create HMAC for data authentication
     *
     * @param string $data
     * @param string $key
     * @param string $algorithm
     * @return string
     * @throws InvalidArgumentException
     */
    public static function hmac(string $data, string $key, string $algorithm = 'sha256'): string
    {
        if (!in_array($algorithm, hash_algos(), true)) {
            throw new InvalidArgumentException("Unsupported hash algorithm: {$algorithm}");
        }
        
        return hash_hmac($algorithm, $data, $key);
    }

    /**
     * Verify HMAC
     *
     * @param string $data
     * @param string $key
     * @param string $expectedHmac
     * @param string $algorithm
     * @return bool
     */
    public static function verifyHmac(string $data, string $key, string $expectedHmac, string $algorithm = 'sha256'): bool
    {
        $computedHmac = static::hmac($data, $key, $algorithm);
        return hash_equals($expectedHmac, $computedHmac);
    }

    /**
     * Check if a string contains only safe characters (alphanumeric + safe symbols)
     *
     * @param string $input
     * @param string $additionalSafeChars
     * @return bool
     */
    public static function isSafeString(string $input, string $additionalSafeChars = '-_.'): bool
    {
        $pattern = '/^[a-zA-Z0-9' . preg_quote($additionalSafeChars, '/') . ']+$/';
        return preg_match($pattern, $input) === 1;
    }

    /**
     * Remove potentially dangerous file path characters
     *
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove path separators and null bytes
        $filename = str_replace(['/', '\\', "\0"], '', $filename);
        
        // Remove or replace dangerous characters
        $filename = preg_replace('/[<>:"|*?]/', '', $filename);
        
        // Remove control characters
        $filename = preg_replace('/[\x00-\x1f\x7f]/', '', $filename);
        
        // Trim dots and spaces from start/end to prevent hidden files or trailing spaces
        $filename = trim($filename, '. ');
        
        // Ensure it's not empty after sanitization
        if (empty($filename)) {
            $filename = 'untitled';
        }
        
        return $filename;
    }

    /**
     * Rate limit check based on key and time window
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int $windowSeconds
     * @param array<string, array<string, mixed>> $storage In-memory storage (in production, use Redis/database)
     * @return bool Returns true if within rate limit, false if exceeded
     */
    public static function rateLimitCheck(string $key, int $maxAttempts = 5, int $windowSeconds = 300, array &$storage = []): bool
    {
        $now = time();
        
        if (!isset($storage[$key])) {
            $storage[$key] = ['attempts' => 0, 'reset_time' => $now + $windowSeconds];
        }
        
        $data = &$storage[$key];
        
        // Reset if window has passed
        if ($now >= $data['reset_time']) {
            $data['attempts'] = 0;
            $data['reset_time'] = $now + $windowSeconds;
        }
        
        // Check if within limit
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }
        
        // Increment attempts
        $data['attempts']++;
        
        return true;
    }
}