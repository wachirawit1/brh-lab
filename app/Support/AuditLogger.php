<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

final class AuditLogger
{
    private const REDACTED = '[REDACTED]';

    private static ?bool $tableAvailable = null;

    public static function record(Request $request, string $event, string $action, array $context = []): void
    {
        $actorUsername = $context['actor_username'] ?? session('user.username');
        $actorName = $context['actor_name'] ?? session('user.fullname');
        $result = $context['result'] ?? 'success';

        $record = [
            'occurred_at' => now(),
            'request_id' => (string) Str::uuid(),
            'category' => $context['category'] ?? 'security',
            'event' => $event,
            'action' => $action,
            'result' => in_array($result, ['success', 'failed', 'denied'], true) ? $result : 'failed',
            'actor_username' => self::limit($actorUsername, 100),
            'actor_name' => self::limit($actorName, 255),
            'target_type' => self::limit($context['target_type'] ?? null, 80),
            'target_id' => self::limit($context['target_id'] ?? null, 191),
            'ip_address' => self::limit($request->ip(), 45),
            'user_agent' => self::limit($request->userAgent(), 2000),
            'old_values' => self::json($context['old_values'] ?? null),
            'new_values' => self::json($context['new_values'] ?? null),
            'metadata' => self::json(array_merge([
                'method' => $request->method(),
                'route' => $request->route()?->getName(),
            ], $context['metadata'] ?? [])),
        ];

        self::writeFileFallback($record);

        try {
            if (self::hasAuditTable()) {
                DB::connection('mysql')->table('system_audit_logs')->insert($record);
            }
        } catch (Throwable $exception) {
            Log::error('Unable to persist security audit record.', [
                'event' => $event,
                'request_id' => $record['request_id'],
                'exception' => $exception::class,
            ]);
        }
    }

    private static function hasAuditTable(): bool
    {
        return self::$tableAvailable ??= Schema::connection('mysql')->hasTable('system_audit_logs');
    }

    private static function writeFileFallback(array $record): void
    {
        try {
            $context = $record;
            $context['occurred_at'] = $record['occurred_at']->toIso8601String();
            Log::channel('audit')->info($record['action'], $context);
        } catch (Throwable) {
            // Audit database remains the primary store when the file channel is unavailable.
        }
    }

    private static function json(mixed $value): ?string
    {
        if ($value === null || $value === []) {
            return null;
        }

        $json = json_encode(
            self::redact($value),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        return $json === false ? null : $json;
    }

    private static function redact(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && preg_match('/password|passwd|secret|token|authorization|cookie|session/i', $key)) {
            return self::REDACTED;
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $itemKey => $itemValue) {
            $value[$itemKey] = self::redact($itemValue, (string) $itemKey);
        }

        return $value;
    }

    private static function limit(mixed $value, int $length): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_substr((string) $value, 0, $length);
    }
}
