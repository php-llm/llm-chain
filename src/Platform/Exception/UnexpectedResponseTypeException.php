<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Exception;

class UnexpectedResponseTypeException extends RuntimeException
{
    public function __construct(string $expectedType, string $actualType)
    {
        parent::__construct(\sprintf(
            'Unexpected response type: expected "%s", got "%s".',
            $expectedType,
            $actualType
        ));
    }
}
