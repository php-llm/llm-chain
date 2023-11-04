<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain\ToolBox;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class AsTool
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $method = '__invoke',
    ) {
    }
}
