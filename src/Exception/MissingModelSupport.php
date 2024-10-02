<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Exception;

final class MissingModelSupport extends RuntimeException
{
    private function __construct(string $model, string $support)
    {
        parent::__construct(sprintf('Model "%s" does not support "%s".', $model, $support));
    }

    public static function forToolCalling(string $model): self
    {
        return new self($model, 'tool calling');
    }

    public static function forImageInput(string $model): self
    {
        return new self($model, 'image input');
    }

    public static function forStructuredOutput(string $model): self
    {
        return new self($model, 'structured output');
    }
}
