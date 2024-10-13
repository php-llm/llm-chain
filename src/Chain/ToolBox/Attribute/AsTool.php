<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\Attribute;

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
