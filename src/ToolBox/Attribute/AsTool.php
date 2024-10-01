<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox\Attribute;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final readonly class AsTool
{
    /**
     * @param class-string|array<mixed>|null $responseFormat
     */
    public function __construct(
        public string $name,
        public string $description,
        public string $method = '__invoke',
        public string|array|null $responseFormat = null,
    ) {
        if (is_string($responseFormat) && !class_exists($responseFormat)) {
            throw new InvalidArgumentException('The response format must be a class name.');
        }

        if (is_array($responseFormat) && [] === $responseFormat) {
            throw new InvalidArgumentException('The response format must not be an empty array.');
        }
    }
}
