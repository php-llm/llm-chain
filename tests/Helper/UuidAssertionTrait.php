<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Helper;

trait UuidAssertionTrait
{
    /**
     * Asserts that a value is a valid UUID v7 string.
     */
    public static function assertIsUuidV7(mixed $actual, string $message = ''): void
    {
        self::assertIsString($actual, $message ?: 'Failed asserting that value is a string.');
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $actual,
            $message ?: 'Failed asserting that value is a valid UUID v7.'
        );
    }
}
