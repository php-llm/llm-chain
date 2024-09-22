<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final readonly class AsTool
{
    public function __construct(
        public string $name,
        public string $description,
        public string $method = '__invoke',
    ) {
    }
}
