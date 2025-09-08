<?php

declare(strict_types=1);

namespace Lythany\Support;

/**
 * Input validation utilities
 * 
 * Provides comprehensive input validation methods with security considerations.
 *
 * @package Lythany\Support
 * @author Lythany Framework Team
 * @since 1.0.1
 */
class Validator
{
    /**
     * Validate email address with comprehensive checks
     *
     * @param string $email
     * @param bool $checkMxRecord
     * @return bool
     */
    public static function email(string $email, bool $checkMxRecord = false): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Additional security checks
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return false;
        }
        
        [$local, $domain] = $parts;
        
        // Check for reasonable length limits
        if (strlen($local) > 64 || strlen($domain) > 253) {
            return false;
        }
        
        // Check MX record if requested
        if ($checkMxRecord && function_exists('checkdnsrr')) {
            return checkdnsrr($domain, 'MX');
        }
        
        return true;
    }

    /**
     * Validate URL with security considerations
     *
     * @param string $url
     * @param array<string> $allowedSchemes
     * @param bool $requireTld
     * @return bool
     */
    public static function url(string $url, array $allowedSchemes = ['http', 'https'], bool $requireTld = true): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $parsed = parse_url($url);
        
        if (!isset($parsed['scheme'], $parsed['host'])) {
            return false;
        }
        
        // Check allowed schemes
        if (!in_array($parsed['scheme'], $allowedSchemes, true)) {
            return false;
        }
        
        // Prevent localhost/private IP access in URLs if needed
        $host = $parsed['host'];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            // It's an IP address - check if it's private/localhost
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        } elseif ($requireTld) {
            // It's a domain - check for TLD
            if (!str_contains($host, '.')) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validate JSON string
     *
     * @param string $json
     * @param int $maxDepth
     * @return bool
     */
    public static function json(string $json, int $maxDepth = 512): bool
    {
        if (empty($json)) {
            return false;
        }
        
        json_decode($json, true, $maxDepth);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Validate UUID (versions 1-5)
     *
     * @param string $uuid
     * @param int|null $version Specific version to validate (1-5) or null for any
     * @return bool
     */
    public static function uuid(string $uuid, ?int $version = null): bool
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        
        if (!preg_match($pattern, $uuid)) {
            return false;
        }
        
        if ($version !== null) {
            $versionDigit = $uuid[14];
            return (string) $version === $versionDigit;
        }
        
        return true;
    }

    /**
     * Validate IP address (v4 or v6)
     *
     * @param string $ip
     * @param int $flags FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, etc.
     * @return bool
     */
    public static function ip(string $ip, int $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
    }

    /**
     * Validate MAC address
     *
     * @param string $mac
     * @return bool
     */
    public static function mac(string $mac): bool
    {
        return filter_var($mac, FILTER_VALIDATE_MAC) !== false;
    }

    /**
     * Validate credit card number using Luhn algorithm
     *
     * @param string $number
     * @return bool
     */
    public static function creditCard(string $number): bool
    {
        // Remove non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);
        
        if (!$number || strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }
        
        // Luhn algorithm
        $sum = 0;
        $alternate = false;
        
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            
            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $alternate = !$alternate;
        }
        
        return $sum % 10 === 0;
    }

    /**
     * Validate phone number (basic international format)
     *
     * @param string $phone
     * @return bool
     */
    public static function phone(string $phone): bool
    {
        // Remove common formatting characters
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        
        if (!$cleaned) {
            return false;
        }
        
        // Basic validation: 7-15 digits, optionally starting with +
        return preg_match('/^\+?[1-9]\d{6,14}$/', $cleaned) === 1;
    }

    /**
     * Validate password strength
     *
     * @param string $password
     * @param int $minLength
     * @param bool $requireUppercase
     * @param bool $requireLowercase
     * @param bool $requireNumbers
     * @param bool $requireSpecialChars
     * @return bool
     */
    public static function password(
        string $password,
        int $minLength = 8,
        bool $requireUppercase = true,
        bool $requireLowercase = true,
        bool $requireNumbers = true,
        bool $requireSpecialChars = true
    ): bool {
        if (strlen($password) < $minLength) {
            return false;
        }
        
        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        if ($requireNumbers && !preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        if ($requireSpecialChars && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate date string
     *
     * @param string $date
     * @param string $format
     * @return bool
     */
    public static function date(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Validate that a value is within a numeric range
     *
     * @param mixed $value
     * @param int|float $min
     * @param int|float $max
     * @return bool
     */
    public static function range(mixed $value, int|float $min, int|float $max): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        $numericValue = is_string($value) ? (float) $value : $value;
        
        return $numericValue >= $min && $numericValue <= $max;
    }

    /**
     * Validate string length
     *
     * @param string $value
     * @param int|null $min
     * @param int|null $max
     * @return bool
     */
    public static function length(string $value, ?int $min = null, ?int $max = null): bool
    {
        $length = mb_strlen($value, 'UTF-8');
        
        if ($min !== null && $length < $min) {
            return false;
        }
        
        if ($max !== null && $length > $max) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate that value matches regex pattern
     *
     * @param string $value
     * @param string $pattern
     * @return bool
     */
    public static function pattern(string $value, string $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Validate that value is in allowed list
     *
     * @param mixed $value
     * @param array<mixed> $allowed
     * @param bool $strict
     * @return bool
     */
    public static function in(mixed $value, array $allowed, bool $strict = true): bool
    {
        return in_array($value, $allowed, $strict);
    }
}