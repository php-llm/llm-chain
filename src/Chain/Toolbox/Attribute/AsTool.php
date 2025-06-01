<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Attribute;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
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
