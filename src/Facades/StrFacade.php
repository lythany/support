<?php

declare(strict_types=1);

namespace Lythany\Support\Facades;

use Lythany\Support\Facade;

/**
 * String Helper Facade
 * 
 * @method static string slug(string $title, string $separator = '-', string|null $language = 'en')
 * @method static string ascii(string $value, string $language = 'en')
 * @method static string camel(string $value)
 * @method static bool contains(string $haystack, string|array $needles, bool $ignoreCase = false)
 * @method static bool containsAll(string $haystack, array $needles, bool $ignoreCase = false)
 * @method static bool endsWith(string $haystack, string|array $needles)
 * @method static string finish(string $value, string $cap)
 * @method static bool isAscii(string $value)
 * @method static bool isJson(string $value)
 * @method static bool isUuid(string $value)
 * @method static bool isUlid(string $value)
 * @method static string kebab(string $value)
 * @method static int length(string $value, string|null $encoding = null)
 * @method static string limit(string $value, int $limit = 100, string $end = '...')
 * @method static string lower(string $value)
 * @method static string words(string $value, int $words = 100, string $end = '...')
 * @method static string mask(string $string, string $character, int $index, int|null $length = null, string $encoding = 'UTF-8')
 * @method static string match(string $pattern, string $subject)
 * @method static bool is(string|array $pattern, string $value)
 * @method static string studly(string $value)
 * @method static string snake(string $value, string $delimiter = '_')
 * @method static bool startsWith(string $haystack, string|array $needles)
 * @method static string upper(string $value)
 * @method static string title(string $value)
 * @method static string ucfirst(string $string)
 * @method static string substr(string $string, int $start, int|null $length = null, string $encoding = 'UTF-8')
 * @method static string random(int $length = 16, string|null $pool = null)
 * @method static string uuid()
 * @method static string orderedUuid(int|null $timestamp = null)
 * @method static string plural(string $value, int|\Countable $count = 2)
 * @method static string singular(string $value)
 * @method static string replace(string|array $search, string|array $replace, string|array $subject, bool $caseSensitive = true)
 * @method static string remove(string|array $search, string $subject, bool $caseSensitive = true)
 * @method static string reverse(string $value)
 * @method static string trim(string $value, string $charlist = " \t\n\r\0\x0B")
 * @method static string e(string $value, bool $doubleEncode = true)
 * @method static string decode(string $value)
 * @method static string wrap(string $value, string $before, string|null $after = null)
 * @method static string unwrap(string $value, string $before, string|null $after = null)
 * 
 * @package Lythany\Support\Facades
 */
class StrFacade extends Facade
{
    /**
     * Get the registered name of the component
     * 
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'str';
    }
}