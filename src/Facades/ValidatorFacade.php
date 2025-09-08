<?php

declare(strict_types=1);

namespace Lythany\Support\Facades;

/**
 * Validator Facade
 *
 * @method static bool email(string $email, bool $checkMxRecord = false)
 * @method static bool url(string $url, array $allowedSchemes = ['http', 'https'], bool $requireTld = true)
 * @method static bool json(string $json, int $maxDepth = 512)
 * @method static bool uuid(string $uuid, int|null $version = null)
 * @method static bool ip(string $ip, int $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)
 * @method static bool mac(string $mac)
 * @method static bool creditCard(string $number)
 * @method static bool phone(string $phone)
 * @method static bool password(string $password, int $minLength = 8, bool $requireUppercase = true, bool $requireLowercase = true, bool $requireNumbers = true, bool $requireSpecialChars = true)
 * @method static bool date(string $date, string $format = 'Y-m-d')
 * @method static bool range(mixed $value, int|float $min, int|float $max)
 * @method static bool length(string $value, int|null $min = null, int|null $max = null)
 * @method static bool pattern(string $value, string $pattern)
 * @method static bool in(mixed $value, array $allowed, bool $strict = true)
 *
 * @package Lythany\Support\Facades
 * @author Lythany Framework Team
 * @since 1.0.1
 */
class ValidatorFacade extends Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'validator';
    }

    /**
     * Get the facade root object from the service container
     *
     * @return string
     */
    protected static function getFacadeClass(): string
    {
        return \Lythany\Support\Validator::class;
    }
}