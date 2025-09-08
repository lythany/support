<?php

declare(strict_types=1);

namespace Lythany\Support\Tests;

use Lythany\Support\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testEmail(): void
    {
        $this->assertTrue(Validator::email('test@example.com'));
        $this->assertTrue(Validator::email('user.name+tag@example.com'));
        $this->assertFalse(Validator::email('invalid-email'));
        $this->assertFalse(Validator::email('test@'));
        $this->assertFalse(Validator::email('@example.com'));
        
        // Test length limits
        $longLocal = str_repeat('a', 65) . '@example.com';
        $this->assertFalse(Validator::email($longLocal));
    }

    public function testUrl(): void
    {
        $this->assertTrue(Validator::url('https://example.com'));
        $this->assertTrue(Validator::url('http://example.com/path?query=1'));
        $this->assertFalse(Validator::url('ftp://example.com')); // Not in allowed schemes
        $this->assertTrue(Validator::url('ftp://example.com', ['ftp']));
        $this->assertFalse(Validator::url('invalid-url'));
        $this->assertFalse(Validator::url('http://localhost')); // Private IP range
    }

    public function testJson(): void
    {
        $this->assertTrue(Validator::json('{"key": "value"}'));
        $this->assertTrue(Validator::json('[1, 2, 3]'));
        $this->assertFalse(Validator::json('invalid json'));
        $this->assertFalse(Validator::json(''));
        $this->assertFalse(Validator::json('{key: value}')); // Invalid JSON syntax
    }

    public function testUuid(): void
    {
        $this->assertTrue(Validator::uuid('550e8400-e29b-41d4-a716-446655440000'));
        $this->assertTrue(Validator::uuid('6ba7b810-9dad-11d1-80b4-00c04fd430c8'));
        $this->assertFalse(Validator::uuid('invalid-uuid'));
        $this->assertFalse(Validator::uuid('550e8400-e29b-41d4-a716-44665544000')); // Too short
        
        // Test version-specific validation
        $this->assertTrue(Validator::uuid('550e8400-e29b-41d4-a716-446655440000', 4));
        $this->assertFalse(Validator::uuid('550e8400-e29b-11d4-a716-446655440000', 4));
    }

    public function testIp(): void
    {
        $this->assertTrue(Validator::ip('192.168.1.1'));
        $this->assertTrue(Validator::ip('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertFalse(Validator::ip('256.256.256.256'));
        $this->assertFalse(Validator::ip('invalid-ip'));
        
        // Test IPv4 only
        $this->assertTrue(Validator::ip('192.168.1.1', FILTER_FLAG_IPV4));
        $this->assertFalse(Validator::ip('2001:0db8:85a3:0000:0000:8a2e:0370:7334', FILTER_FLAG_IPV4));
    }

    public function testMac(): void
    {
        $this->assertTrue(Validator::mac('00:1B:44:11:3A:B7'));
        $this->assertTrue(Validator::mac('00-1B-44-11-3A-B7'));
        $this->assertFalse(Validator::mac('invalid-mac'));
        $this->assertFalse(Validator::mac('00:1B:44:11:3A'));
    }

    public function testCreditCard(): void
    {
        // Valid test credit card numbers (Luhn algorithm)
        $this->assertTrue(Validator::creditCard('4532015112830366')); // Visa
        $this->assertTrue(Validator::creditCard('5555 5555 5555 4444')); // MasterCard with spaces
        $this->assertFalse(Validator::creditCard('1234567890123456')); // Invalid Luhn
        $this->assertFalse(Validator::creditCard('123')); // Too short
        $this->assertFalse(Validator::creditCard('12345678901234567890')); // Too long
    }

    public function testPhone(): void
    {
        $this->assertTrue(Validator::phone('+1234567890'));
        $this->assertTrue(Validator::phone('1-234-567-8901'));
        $this->assertTrue(Validator::phone('(123) 456-7890'));
        $this->assertFalse(Validator::phone('123')); // Too short
        $this->assertFalse(Validator::phone('01234567890')); // Starts with 0
        $this->assertFalse(Validator::phone('invalid-phone'));
    }

    public function testPassword(): void
    {
        $this->assertTrue(Validator::password('Password123!'));
        $this->assertFalse(Validator::password('password')); // No uppercase, numbers, special chars
        $this->assertFalse(Validator::password('PASSWORD')); // No lowercase, numbers, special chars
        $this->assertFalse(Validator::password('Password')); // No numbers, special chars
        $this->assertFalse(Validator::password('Pass12!')); // Too short (7 chars < 8)
        
        // Test custom requirements
        $this->assertTrue(Validator::password('password', 6, false, true, false, false));
    }

    public function testDate(): void
    {
        $this->assertTrue(Validator::date('2023-12-25'));
        $this->assertTrue(Validator::date('25/12/2023', 'd/m/Y'));
        $this->assertFalse(Validator::date('2023-13-25')); // Invalid month
        $this->assertFalse(Validator::date('25-12-2023')); // Wrong format
        $this->assertFalse(Validator::date('invalid-date'));
    }

    public function testRange(): void
    {
        $this->assertTrue(Validator::range(5, 1, 10));
        $this->assertTrue(Validator::range('5.5', 1, 10));
        $this->assertFalse(Validator::range(15, 1, 10));
        $this->assertFalse(Validator::range('not-a-number', 1, 10));
    }

    public function testLength(): void
    {
        $this->assertTrue(Validator::length('hello', 3, 10));
        $this->assertFalse(Validator::length('hi', 3, 10)); // Too short
        $this->assertFalse(Validator::length('this is too long', 3, 10)); // Too long
        $this->assertTrue(Validator::length('hello', null, 10)); // No min
        $this->assertTrue(Validator::length('hello', 3, null)); // No max
    }

    public function testPattern(): void
    {
        $this->assertTrue(Validator::pattern('abc123', '/^[a-z0-9]+$/'));
        $this->assertFalse(Validator::pattern('ABC123', '/^[a-z0-9]+$/'));
        $this->assertTrue(Validator::pattern('test@example.com', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'));
    }

    public function testIn(): void
    {
        $allowed = ['red', 'green', 'blue'];
        
        $this->assertTrue(Validator::in('red', $allowed));
        $this->assertFalse(Validator::in('yellow', $allowed));
        $this->assertTrue(Validator::in('1', ['1', '2', '3']));
        $this->assertFalse(Validator::in(1, ['1', '2', '3'])); // Strict comparison by default
        $this->assertTrue(Validator::in(1, ['1', '2', '3'], false)); // Non-strict comparison
    }
}