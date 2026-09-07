<?php

// Never consume deployment config cache or credentials in the test runner.
foreach ([
    'APP_ENV' => 'testing',
    'APP_CONFIG_CACHE' => __DIR__.'/nonexistent-testing-config.php',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
] as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $_SERVER[$key] = $value;
}
if (is_file(__DIR__.'/nonexistent-testing-config.php')) {
    throw new RuntimeException('Unexpected test config cache; refusing to run.');
}
require __DIR__.'/../vendor/autoload.php';
