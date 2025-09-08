<?php

declare(strict_types=1);

namespace Lythany\Support\Tests;

use Lythany\Support\Security;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use RuntimeException;

class SecurityTest extends TestCase
{
    public function testHashPassword(): void
    {
        $password = 'testpassword123';
        $hash = Security::hashPassword($password);
        
        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertTrue(Security::verifyPassword($password, $hash));
    }

    public function testVerifyPassword(): void
    {
        $password = 'testpassword123';
        $hash = Security::hashPassword($password);
        
        $this->assertTrue(Security::verifyPassword($password, $hash));
        $this->assertFalse(Security::verifyPassword('wrongpassword', $hash));
    }

    public function testRandomString(): void
    {
        $length = 16;
        $random = Security::randomString($length);
        
        $this->assertIsString($random);
        $this->assertEquals($length, strlen($random));
        
        // Should be different each time
        $random2 = Security::randomString($length);
        $this->assertNotEquals($random, $random2);
    }

    public function testRandomStringInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Security::randomString(0);
    }

    public function testRandomStringEmptyCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Security::randomString(10, '');
    }

    public function testRandomHex(): void
    {
        $length = 16;
        $hex = Security::randomHex($length);
        
        $this->assertIsString($hex);
        $this->assertEquals($length, strlen($hex));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $hex);
    }

    public function testCsrfToken(): void
    {
        $token = Security::csrfToken();
        
        $this->assertIsString($token);
        $this->assertEquals(40, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $token);
    }

    public function testSanitizeInput(): void
    {
        $input = '<script>alert("xss")</script>';
        $sanitized = Security::sanitizeInput($input);
        
        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $sanitized);
    }

    public function testSanitizeInputWithLineBreaks(): void
    {
        $input = "Line 1\nLine 2";
        $sanitized = Security::sanitizeInput($input, true);
        
        $this->assertStringContainsString('<br>', $sanitized);
    }

    public function testSanitizeEmail(): void
    {
        $this->assertEquals('test@example.com', Security::sanitizeEmail('test@example.com'));
        $this->assertNull(Security::sanitizeEmail('invalid-email'));
        $this->assertNull(Security::sanitizeEmail('test@'));
    }

    public function testSanitizeUrl(): void
    {
        $this->assertEquals('https://example.com', Security::sanitizeUrl('https://example.com'));
        $this->assertNull(Security::sanitizeUrl('invalid-url'));
        $this->assertNull(Security::sanitizeUrl('ftp://example.com')); // Not in allowed schemes
        $this->assertEquals('ftp://example.com', Security::sanitizeUrl('ftp://example.com', ['ftp']));
    }

    public function testHashData(): void
    {
        $data = 'test data';
        $hash = Security::hashData($data);
        
        $this->assertIsString($hash);
        $this->assertEquals(64, strlen($hash)); // SHA256 produces 64 char hex string
        
        // Same data should produce same hash
        $this->assertEquals($hash, Security::hashData($data));
    }

    public function testHashDataInvalidAlgorithm(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Security::hashData('data', 'invalid-algorithm');
    }

    public function testHmac(): void
    {
        $data = 'test data';
        $key = 'secret key';
        $hmac = Security::hmac($data, $key);
        
        $this->assertIsString($hmac);
        $this->assertEquals(64, strlen($hmac)); // SHA256 HMAC produces 64 char hex string
        
        // Same data and key should produce same HMAC
        $this->assertEquals($hmac, Security::hmac($data, $key));
    }

    public function testVerifyHmac(): void
    {
        $data = 'test data';
        $key = 'secret key';
        $hmac = Security::hmac($data, $key);
        
        $this->assertTrue(Security::verifyHmac($data, $key, $hmac));
        $this->assertFalse(Security::verifyHmac('different data', $key, $hmac));
        $this->assertFalse(Security::verifyHmac($data, 'different key', $hmac));
    }

    public function testIsSafeString(): void
    {
        $this->assertTrue(Security::isSafeString('test123'));
        $this->assertTrue(Security::isSafeString('test-123_test.txt'));
        $this->assertFalse(Security::isSafeString('test<script>'));
        $this->assertFalse(Security::isSafeString('test@example.com'));
    }

    public function testSanitizeFilename(): void
    {
        $this->assertEquals('test.txt', Security::sanitizeFilename('test.txt'));
        $this->assertEquals('test.txt', Security::sanitizeFilename('../test.txt'));
        $this->assertEquals('test.txt', Security::sanitizeFilename('test<>.txt'));
        $this->assertEquals('test.txt', Security::sanitizeFilename('  .test.txt..  '));
        $this->assertEquals('untitled', Security::sanitizeFilename(''));
        $this->assertEquals('untitled', Security::sanitizeFilename('...'));
    }

    public function testRateLimitCheck(): void
    {
        $storage = [];
        $key = 'test-key';
        
        // Should allow initial requests
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue(Security::rateLimitCheck($key, 5, 300, $storage));
        }
        
        // Should block after limit
        $this->assertFalse(Security::rateLimitCheck($key, 5, 300, $storage));
    }
}