<?php

declare(strict_types=1);

namespace Lythany\Support\Facades;

/**
 * Security Facade
 *
 * @method static string hashPassword(string $password, array $options = [])
 * @method static bool verifyPassword(string $password, string $hash)
 * @method static bool needsRehash(string $hash, array $options = [])
 * @method static string randomString(int $length = 32, string $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
 * @method static string randomHex(int $length = 32)
 * @method static string csrfToken(int $length = 40)
 * @method static string sanitizeInput(string $input, bool $preserveLineBreaks = false)
 * @method static string|null sanitizeEmail(string $email)
 * @method static string|null sanitizeUrl(string $url, array $allowedSchemes = ['http', 'https'])
 * @method static string hashData(string $data, string $algorithm = 'sha256')
 * @method static string hmac(string $data, string $key, string $algorithm = 'sha256')
 * @method static bool verifyHmac(string $data, string $key, string $expectedHmac, string $algorithm = 'sha256')
 * @method static bool isSafeString(string $input, string $additionalSafeChars = '-_.')
 * @method static string sanitizeFilename(string $filename)
 * @method static bool rateLimitCheck(string $key, int $maxAttempts = 5, int $windowSeconds = 300, array &$storage = [])
 *
 * @package Lythany\Support\Facades
 * @author Lythany Framework Team
 * @since 1.0.1
 */
class SecurityFacade extends Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'security';
    }

    /**
     * Get the facade root object from the service container
     *
     * @return string
     */
    protected static function getFacadeClass(): string
    {
        return \Lythany\Support\Security::class;
    }
}