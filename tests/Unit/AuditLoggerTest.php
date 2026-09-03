<?php

namespace Tests\Unit;

use App\Support\AuditLogger;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AuditLoggerTest extends TestCase
{
    public function test_sensitive_values_are_redacted_recursively(): void
    {
        $method = new ReflectionMethod(AuditLogger::class, 'json');
        $json = $method->invoke(null, [
            'username' => 'demo.user',
            'password' => 'must-not-be-logged',
            'nested' => [
                'api_token' => 'must-not-be-logged',
                'role' => 'Admin',
            ],
        ]);

        $this->assertIsString($json);
        $this->assertStringContainsString('demo.user', $json);
        $this->assertStringContainsString('Admin', $json);
        $this->assertSame(2, substr_count($json, '[REDACTED]'));
        $this->assertStringNotContainsString('must-not-be-logged', $json);
    }
}
