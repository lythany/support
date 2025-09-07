<?php

declare(strict_types=1);

namespace Lythany\Support;

use Closure;
use Countable;
use InvalidArgumentException;
use JsonException;

/**
 * String manipulation utilities
 * 
 * Provides comprehensive string manipulation methods with a focus on performance,
 * Unicode support, and developer experience.
 *
 * @package Lythany\Support
 * @author Lythany Framework Team
 * @since 1.0.0
 */
class Str
{
    /**
     * The cache of snake-cased words
     *
     * @var array<string, string>
     */
    protected static array $snakeCache = [];

    /**
     * The cache of camel-cased words
     *
     * @var array<string, string>
     */
    protected static array $camelCache = [];

    /**
     * The cache of studly-cased words
     *
     * @var array<string, string>
     */
    protected static array $studlyCache = [];

    /**
     * The cache of plural words
     *
     * @var array<string, string>
     */
    protected static array $pluralCache = [];

    /**
     * The cache of singular words
     *
     * @var array<string, string>
     */
    protected static array $singularCache = [];

    /**
     * Generate a URL friendly "slug" from a given string
     *
     * @param string $title
     * @param string $separator
     * @param string|null $language
     * @return string
     */
    public static function slug(string $title, string $separator = '-', ?string $language = 'en'): string
    {
        $title = $language ? static::ascii($title, $language) : $title;

        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';
        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

        // Replace @ with the word 'at'
        $title = str_replace('@', $separator.'at'.$separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title, 'UTF-8'));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * Transliterate a UTF-8 value to ASCII
     *
     * @param string $value
     * @param string $language
     * @return string
     */
    public static function ascii(string $value, string $language = 'en'): string
    {
        if (extension_loaded('iconv')) {
            $result = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            return $result !== false ? $result : $value;
        }

        return static::transliterate($value, $language);
    }

    /**
     * Transliterate characters to closest ASCII equivalents
     *
     * @param string $string
     * @param string $language
     * @return string
     */
    protected static function transliterate(string $string, string $language = 'en'): string
    {
        $map = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE',
            'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I',
            'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
            'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Þ' => 'TH', 'ß' => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
            'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
            'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u',
            'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y',
        ];

        return strtr($string, $map);
    }

    /**
     * Convert a string to camel case
     *
     * @param string $value
     * @return string
     */
    public static function camel(string $value): string
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Determine if a given string contains a given substring
     *
     * @param string $haystack
     * @param string|array $needles
     * @param bool $ignoreCase
     * @return bool
     */
    public static function contains(string $haystack, string|array $needles, bool $ignoreCase = false): bool
    {
        $needles = (array) $needles;

        foreach ($needles as $needle) {
            if ($needle === '') {
                continue;
            }
            
            if ($ignoreCase ? 
                mb_stripos($haystack, $needle, 0, 'UTF-8') !== false : 
                mb_strpos($haystack, $needle, 0, 'UTF-8') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string contains all array values
     *
     * @param string $haystack
     * @param array $needles
     * @param bool $ignoreCase
     * @return bool
     */
    public static function containsAll(string $haystack, array $needles, bool $ignoreCase = false): bool
    {
        foreach ($needles as $needle) {
            if (!static::contains($haystack, $needle, $ignoreCase)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if a given string ends with a given substring
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function endsWith(string $haystack, string|array $needles): bool
    {
        $needles = (array) $needles;

        foreach ($needles as $needle) {
            if ($needle !== '' && str_ends_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cap a string with a single instance of a given value
     *
     * @param string $value
     * @param string $cap
     * @return string
     */
    public static function finish(string $value, string $cap): string
    {
        if (!static::endsWith($value, $cap)) {
            return $value.$cap;
        }

        return $value;
    }

    /**
     * Determine if a given string is 7 bit ASCII
     *
     * @param string $value
     * @return bool
     */
    public static function isAscii(string $value): bool
    {
        return mb_check_encoding($value, 'ASCII');
    }

    /**
     * Determine if a given string is valid JSON
     *
     * @param string $value
     * @return bool
     */
    public static function isJson(string $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        try {
            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            return true;
        } catch (JsonException) {
            return false;
        }
    }

    /**
     * Determine if a given string is a valid UUID
     *
     * @param string $value
     * @return bool
     */
    public static function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }

    /**
     * Determine if a given string is a valid ULID
     *
     * @param string $value
     * @return bool
     */
    public static function isUlid(string $value): bool
    {
        return preg_match('/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/', $value) === 1;
    }

    /**
     * Convert a string to kebab case
     *
     * @param string $value
     * @return string
     */
    public static function kebab(string $value): string
    {
        return static::snake($value, '-');
    }

    /**
     * Return the length of the given string
     *
     * @param string $value
     * @param string|null $encoding
     * @return int
     */
    public static function length(string $value, ?string $encoding = null): int
    {
        return mb_strlen($value, $encoding ?: 'UTF-8');
    }

    /**
     * Limit the number of characters in a string
     *
     * @param string $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }

    /**
     * Convert the given string to lower-case
     *
     * @param string $value
     * @return string
     */
    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Limit the number of words in a string
     *
     * @param string $value
     * @param int $words
     * @param string $end
     * @return string
     */
    public static function words(string $value, int $words = 100, string $end = '...'): string
    {
        preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

        if (!isset($matches[0]) || static::length($value) === static::length($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]).$end;
    }

    /**
     * Masks a portion of a string with a repeated character
     *
     * @param string $string
     * @param string $character
     * @param int $index
     * @param int|null $length
     * @param string $encoding
     * @return string
     */
    public static function mask(string $string, string $character, int $index, ?int $length = null, string $encoding = 'UTF-8'): string
    {
        if ($character === '') {
            return $string;
        }

        $segment = mb_substr($string, $index, $length, $encoding);

        if ($segment === '') {
            return $string;
        }

        $strlen = mb_strlen($string, $encoding);
        $startIndex = $index;

        if ($index < 0) {
            $startIndex = $index < -$strlen ? 0 : $strlen + $index;
        }

        $start = mb_substr($string, 0, $startIndex, $encoding);
        $segmentLen = mb_strlen($segment, $encoding);
        $end = mb_substr($string, $startIndex + $segmentLen);

        return $start.str_repeat(mb_substr($character, 0, 1, $encoding), $segmentLen).$end;
    }

    /**
     * Get the string matching the given pattern
     *
     * @param string $pattern
     * @param string $subject
     * @return string
     */
    public static function match(string $pattern, string $subject): string
    {
        preg_match($pattern, $subject, $matches);

        if (!$matches) {
            return '';
        }

        return $matches[1] ?? $matches[0];
    }

    /**
     * Determine if a string matches a given pattern
     *
     * @param string|array $pattern
     * @param string $value
     * @return bool
     */
    public static function is(string|array $pattern, string $value): bool
    {
        $patterns = is_array($pattern) ? $pattern : [$pattern];

        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            $pattern = (string) $pattern;

            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern === $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^'.$pattern.'\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert a string to PascalCase
     *
     * @param string $value
     * @return string
     */
    public static function studly(string $value): string
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $words = explode(' ', static::replace(['-', '_'], ' ', $value));

        $studlyWords = array_map([static::class, 'ucfirst'], $words);

        return static::$studlyCache[$key] = implode($studlyWords);
    }

    /**
     * Convert a string to snake_case
     *
     * @param string $value
     * @param string $delimiter
     * @return string
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        $value = preg_replace('/[_-]+/', $delimiter, $value);

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * Determine if a given string starts with a given substring
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function startsWith(string $haystack, string|array $needles): bool
    {
        $needles = (array) $needles;

        foreach ($needles as $needle) {
            if ($needle !== '' && str_starts_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the given string to upper-case
     *
     * @param string $value
     * @return string
     */
    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Convert the given string to title case
     *
     * @param string $value
     * @return string
     */
    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Make a string's first character uppercase
     *
     * @param string $string
     * @return string
     */
    public static function ucfirst(string $string): string
    {
        return static::upper(static::substr($string, 0, 1)).static::substr($string, 1);
    }

    /**
     * Returns the portion of string specified by the start and length parameters
     *
     * @param string $string
     * @param int $start
     * @param int|null $length
     * @param string $encoding
     * @return string
     */
    public static function substr(string $string, int $start, ?int $length = null, string $encoding = 'UTF-8'): string
    {
        return mb_substr($string, $start, $length, $encoding);
    }

    /**
     * Returns the number of substring occurrences
     *
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @param int|null $length
     * @return int
     */
    public static function substrCount(string $haystack, string $needle, int $offset = 0, ?int $length = null): int
    {
        if ($length !== null) {
            return substr_count($haystack, $needle, $offset, $length);
        }

        return substr_count($haystack, $needle, $offset);
    }

    /**
     * Replace text within a portion of a string
     *
     * @param string|array $string
     * @param string|array $replace
     * @param int|array $offset
     * @param int|array|null $length
     * @return string|array
     */
    public static function substrReplace(string|array $string, string|array $replace, int|array $offset = 0, int|array|null $length = null): string|array
    {
        if ($length === null) {
            $length = strlen($string);
        }

        return substr_replace($string, $replace, $offset, $length);
    }

    /**
     * Swap multiple keywords in a string with other keywords
     *
     * @param array $map
     * @param string $subject
     * @return string
     */
    public static function swap(array $map, string $subject): string
    {
        return strtr($subject, $map);
    }

    /**
     * Remove all "extra" blank space from the given string
     *
     * @param string $value
     * @return string
     */
    public static function squish(string $value): string
    {
        return preg_replace('~(\v|\h)+~', ' ', trim($value));
    }

    /**
     * Begin a string with a single instance of a given value
     *
     * @param string $value
     * @param string $prefix
     * @return string
     */
    public static function start(string $value, string $prefix): string
    {
        if (!static::startsWith($value, $prefix)) {
            return $prefix.$value;
        }

        return $value;
    }

    /**
     * Replace a given value in the string sequentially with an array
     *
     * @param string $search
     * @param array $replace
     * @param string $subject
     * @return string
     */
    public static function replaceArray(string $search, array $replace, string $subject): string
    {
        $segments = explode($search, $subject);

        $result = array_shift($segments);

        foreach ($segments as $segment) {
            $result .= (array_shift($replace) ?? $search).$segment;
        }

        return $result;
    }

    /**
     * Replace the first occurrence of a given value in the string
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceFirst(string $search, string $replace, string $subject): string
    {
        if ($search === '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Replace the last occurrence of a given value in the string
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceLast(string $search, string $replace, string $subject): string
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Replace the given value in the given string
     *
     * @param string|array $search
     * @param string|array $replace
     * @param string|array $subject
     * @param bool $caseSensitive
     * @return string|array
     */
    public static function replace(string|array $search, string|array $replace, string|array $subject, bool $caseSensitive = true): string|array
    {
        if ($caseSensitive) {
            return str_replace($search, $replace, $subject);
        }

        return str_ireplace($search, $replace, $subject);
    }

    /**
     * Remove any occurrence of the given string in the subject
     *
     * @param string|array $search
     * @param string $subject
     * @param bool $caseSensitive
     * @return string
     */
    public static function remove(string|array $search, string $subject, bool $caseSensitive = true): string
    {
        return static::replace($search, '', $subject, $caseSensitive);
    }

    /**
     * Reverse the given string
     *
     * @param string $value
     * @return string
     */
    public static function reverse(string $value): string
    {
        return implode(array_reverse(mb_str_split($value, 1, 'UTF-8')));
    }

    /**
     * Generate a random, secure password
     *
     * @param int $length
     * @param bool $letters
     * @param bool $numbers
     * @param bool $symbols
     * @param bool $spaces
     * @return string
     */
    public static function password(int $length = 32, bool $letters = true, bool $numbers = true, bool $symbols = true, bool $spaces = false): string
    {
        $pool = '';

        if ($letters) {
            $pool .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($numbers) {
            $pool .= '0123456789';
        }

        if ($symbols) {
            $pool .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
        }

        if ($spaces) {
            $pool .= ' ';
        }

        if ($pool === '') {
            return '';
        }

        return static::random($length, $pool);
    }

    /**
     * Generate a more truly "random" alpha-numeric string
     *
     * @param int $length
     * @param string|null $pool
     * @return string
     */
    public static function random(int $length = 16, ?string $pool = null): string
    {
        $pool = $pool ?: '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $pool[random_int(0, strlen($pool) - 1)];
        }

        return $string;
    }

    /**
     * Pad both sides of a string with another
     *
     * @param string $value
     * @param int $length
     * @param string $pad
     * @return string
     */
    public static function padBoth(string $value, int $length, string $pad = ' '): string
    {
        $short = max(0, $length - mb_strlen($value));
        $shortLeft = floor($short / 2);
        $shortRight = ceil($short / 2);

        return mb_substr(str_repeat($pad, (int) $shortLeft), 0, (int) $shortLeft).
               $value.
               mb_substr(str_repeat($pad, (int) $shortRight), 0, (int) $shortRight);
    }

    /**
     * Pad the left side of a string with another
     *
     * @param string $value
     * @param int $length
     * @param string $pad
     * @return string
     */
    public static function padLeft(string $value, int $length, string $pad = ' '): string
    {
        $short = max(0, $length - mb_strlen($value));

        return mb_substr(str_repeat($pad, $short), 0, $short).$value;
    }

    /**
     * Pad the right side of a string with another
     *
     * @param string $value
     * @param int $length
     * @param string $pad
     * @return string
     */
    public static function padRight(string $value, int $length, string $pad = ' '): string
    {
        $short = max(0, $length - mb_strlen($value));

        return $value.mb_substr(str_repeat($pad, $short), 0, $short);
    }

    /**
     * Get the plural form of an English word
     *
     * @param string $value
     * @param int|Countable $count
     * @return string
     */
    public static function plural(string $value, int|Countable $count = 2): string
    {
        if (is_countable($count)) {
            $count = count($count);
        }

        if ((int) abs($count) === 1 || static::uncountable($value)) {
            return $value;
        }

        $plural = static::$pluralCache[$value] ?? null;

        if ($plural !== null) {
            return $plural;
        }

        $plural = static::pluralize($value);

        return static::$pluralCache[$value] = $plural;
    }

    /**
     * Get the singular form of an English word
     *
     * @param string $value
     * @return string
     */
    public static function singular(string $value): string
    {
        $singular = static::$singularCache[$value] ?? null;

        if ($singular !== null) {
            return $singular;
        }

        $singular = static::singularize($value);

        return static::$singularCache[$value] = $singular;
    }

    /**
     * Pluralize the last word of an English, studly caps case string
     *
     * @param string $value
     * @param int|Countable $count
     * @return string
     */
    public static function pluralStudly(string $value, int|Countable $count = 2): string
    {
        $parts = preg_split('/(.)(?=[A-Z])/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE);

        $lastWord = array_pop($parts);

        return implode('', $parts).static::plural($lastWord, $count);
    }

    /**
     * Convert string to pluralized form (basic English rules)
     *
     * @param string $word
     * @return string
     */
    protected static function pluralize(string $word): string
    {
        // Handle some irregular plurals
        $irregulars = [
            'man' => 'men',
            'woman' => 'women',
            'child' => 'children',
            'tooth' => 'teeth',
            'foot' => 'feet',
            'mouse' => 'mice',
            'person' => 'people',
        ];

        $lowerWord = static::lower($word);

        if (isset($irregulars[$lowerWord])) {
            return static::matchCase($irregulars[$lowerWord], $word);
        }

        // Words ending in 's', 'sh', 'ch', 'x', 'z' add 'es'
        if (preg_match('/(s|sh|ch|x|z)$/i', $word)) {
            return $word . 'es';
        }

        // Words ending in consonant + 'y', change 'y' to 'ies'
        if (preg_match('/[bcdfghjklmnpqrstvwxz]y$/i', $word)) {
            return substr($word, 0, -1) . 'ies';
        }

        // Words ending in 'f' or 'fe', change to 'ves'
        if (preg_match('/fe?$/i', $word)) {
            return preg_replace('/fe?$/i', 'ves', $word);
        }

        // Default: add 's'
        return $word . 's';
    }

    /**
     * Convert string to singular form (basic English rules)
     *
     * @param string $word
     * @return string
     */
    protected static function singularize(string $word): string
    {
        // Handle some irregular singulars
        $irregulars = [
            'men' => 'man',
            'women' => 'woman',
            'children' => 'child',
            'teeth' => 'tooth',
            'feet' => 'foot',
            'mice' => 'mouse',
            'people' => 'person',
        ];

        $lowerWord = static::lower($word);

        if (isset($irregulars[$lowerWord])) {
            return static::matchCase($irregulars[$lowerWord], $word);
        }

        // Words ending in 'ies', change to 'y'
        if (preg_match('/ies$/i', $word)) {
            return substr($word, 0, -3) . 'y';
        }

        // Words ending in 'ves', change to 'f'
        if (preg_match('/ves$/i', $word)) {
            return substr($word, 0, -3) . 'f';
        }

        // Words ending in 'ses', 'shes', 'ches', 'xes', 'zes', remove 'es'
        if (preg_match('/(s|sh|ch|x|z)es$/i', $word)) {
            return substr($word, 0, -2);
        }

        // Words ending in 's' (but not 'ss'), remove 's'
        if (preg_match('/[^s]s$/i', $word)) {
            return substr($word, 0, -1);
        }

        return $word;
    }

    /**
     * Match the case of the pattern to the subject
     *
     * @param string $pattern
     * @param string $subject
     * @return string
     */
    protected static function matchCase(string $pattern, string $subject): string
    {
        if (ctype_upper($subject)) {
            return static::upper($pattern);
        }

        if (ctype_upper($subject[0])) {
            return static::ucfirst($pattern);
        }

        return $pattern;
    }

    /**
     * Determine if the given value is uncountable
     *
     * @param string $value
     * @return bool
     */
    protected static function uncountable(string $value): bool
    {
        $uncountables = [
            'advice', 'air', 'aircraft', 'artwork', 'baggage', 'butter', 'cash', 'cattle', 'deer',
            'equipment', 'fish', 'furniture', 'gold', 'homework', 'impatience', 'information',
            'knowledge', 'love', 'luggage', 'money', 'music', 'oil', 'patience', 'police',
            'pollution', 'research', 'rice', 'sand', 'scissors', 'series', 'sheep', 'species',
            'sugar', 'traffic', 'travel', 'trouble', 'water', 'work',
        ];

        return in_array(static::lower($value), $uncountables, true);
    }

    /**
     * Parse a Class@method style callback into class and method
     *
     * @param string $callback
     * @param string|null $default
     * @return array<int, string|null>
     */
    public static function parseCallback(string $callback, ?string $default = null): array
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }

    /**
     * Get the portion of a string before the first occurrence of a given value
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function before(string $subject, string $search): string
    {
        if ($search === '') {
            return $subject;
        }

        $result = strstr($subject, $search, true);

        return $result === false ? $subject : $result;
    }

    /**
     * Get the portion of a string before the last occurrence of a given value
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function beforeLast(string $subject, string $search): string
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strrpos($subject, $search, 0, 'UTF-8');

        if ($pos === false) {
            return $subject;
        }

        return static::substr($subject, 0, $pos);
    }

    /**
     * Get the portion of a string between two given values
     *
     * @param string $subject
     * @param string $from
     * @param string $to
     * @return string
     */
    public static function between(string $subject, string $from, string $to): string
    {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return static::beforeLast(static::after($subject, $from), $to);
    }

    /**
     * Get the portion of a string after the first occurrence of a given value
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function after(string $subject, string $search): string
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Get the portion of a string after the last occurrence of a given value
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function afterLast(string $subject, string $search): string
    {
        if ($search === '') {
            return $subject;
        }

        $position = mb_strrpos($subject, $search, 0, 'UTF-8');

        if ($position === false) {
            return $subject;
        }

        return mb_substr($subject, $position + mb_strlen($search, 'UTF-8'));
    }

    /**
     * Transliterate a string to its closest ASCII representation
     *
     * @param string $value
     * @param string $language
     * @param bool $removeUnsupported
     * @return string
     */
    public static function toAscii(string $value, string $language = 'en', bool $removeUnsupported = true): string
    {
        $value = static::ascii($value, $language);

        if ($removeUnsupported) {
            $value = preg_replace('/[^\x00-\x7F]/', '', $value);
        }

        return $value;
    }

    /**
     * Convert a string to a base64 encoded string
     *
     * @param string $value
     * @return string
     */
    public static function toBase64(string $value): string
    {
        return base64_encode($value);
    }

    /**
     * Convert a base64 encoded string back to its original form
     *
     * @param string $value
     * @return string|false
     */
    public static function fromBase64(string $value): string|false
    {
        return base64_decode($value, true);
    }

    /**
     * Generate a UUID v4
     *
     * @return string
     */
    public static function uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

    /**
     * Generate an ordered UUID v4 (time-based)
     *
     * @param int|null $timestamp
     * @return string
     */
    public static function orderedUuid(?int $timestamp = null): string
    {
        $timestamp = $timestamp ?: time();

        return sprintf(
            '%08x-%04x-%04x-%04x-%04x%08x',
            $timestamp,
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffffffff)
        );
    }

    /**
     * Convert the string into an HTML string instance
     *
     * @param string $html
     * @return object
     */
    public static function toHtmlString(string $html): object
    {
        return new class($html) {
            public function __construct(public readonly string $html) {}
            public function __toString(): string { return $this->html; }
            public function toHtml(): string { return $this->html; }
        };
    }

    /**
     * Repeat the given string
     *
     * @param string $string
     * @param int $times
     * @return string
     */
    public static function repeat(string $string, int $times): string
    {
        return str_repeat($string, $times);
    }

    /**
     * Strip HTML tags from the given string
     *
     * @param string $string
     * @param array|string|null $allowedTags
     * @return string
     */
    public static function stripTags(string $string, array|string|null $allowedTags = null): string
    {
        if (is_array($allowedTags)) {
            $allowedTags = '<' . implode('><', $allowedTags) . '>';
        }

        return strip_tags($string, $allowedTags);
    }

    /**
     * Wrap the string with the given strings
     *
     * @param string $value
     * @param string $before
     * @param string|null $after
     * @return string
     */
    public static function wrap(string $value, string $before, ?string $after = null): string
    {
        return $before . $value . ($after ?: $before);
    }

    /**
     * Unwrap the string with the given strings
     *
     * @param string $value
     * @param string $before
     * @param string|null $after
     * @return string
     */
    public static function unwrap(string $value, string $before, ?string $after = null): string
    {
        if (static::startsWith($value, $before)) {
            $value = static::substr($value, static::length($before));
        }

        if (static::endsWith($value, $after ?: $before)) {
            $value = static::substr($value, 0, -static::length($after ?: $before));
        }

        return $value;
    }

    /**
     * Convert special characters to HTML entities
     *
     * @param string $value
     * @param bool $doubleEncode
     * @return string
     */
    public static function e(string $value, bool $doubleEncode = true): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }

    /**
     * Decode HTML entities
     *
     * @param string $value
     * @return string
     */
    public static function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Convert tabs to spaces
     *
     * @param string $value
     * @param int $tabSize
     * @return string
     */
    public static function tabsToSpaces(string $value, int $tabSize = 4): string
    {
        return str_replace("\t", str_repeat(' ', $tabSize), $value);
    }

    /**
     * Convert spaces to tabs
     *
     * @param string $value
     * @param int $tabSize
     * @return string
     */
    public static function spacesToTabs(string $value, int $tabSize = 4): string
    {
        return str_replace(str_repeat(' ', $tabSize), "\t", $value);
    }

    /**
     * Count the number of words in a string
     *
     * @param string $string
     * @param string|null $charlist
     * @return int
     */
    public static function wordCount(string $string, ?string $charlist = null): int
    {
        return str_word_count($string, 0, $charlist);
    }

    /**
     * Get the words from a string
     *
     * @param string $string
     * @param string|null $charlist
     * @return array
     */
    public static function wordSplit(string $string, ?string $charlist = null): array
    {
        return str_word_count($string, 1, $charlist);
    }

    /**
     * Trim whitespace from the beginning and end of a string
     *
     * @param string $value
     * @param string $charlist
     * @return string
     */
    public static function trim(string $value, string $charlist = " \t\n\r\0\x0B"): string
    {
        return trim($value, $charlist);
    }

    /**
     * Trim whitespace from the beginning of a string
     *
     * @param string $value
     * @param string $charlist
     * @return string
     */
    public static function ltrim(string $value, string $charlist = " \t\n\r\0\x0B"): string
    {
        return ltrim($value, $charlist);
    }

    /**
     * Trim whitespace from the end of a string
     *
     * @param string $value
     * @param string $charlist
     * @return string
     */
    public static function rtrim(string $value, string $charlist = " \t\n\r\0\x0B"): string
    {
        return rtrim($value, $charlist);
    }
}