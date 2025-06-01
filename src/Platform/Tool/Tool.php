<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Tool;

use PhpLlm\LlmChain\Platform\Contract\JsonSchema\Factory;

/**
 * @phpstan-import-type JsonSchema from Factory
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class Tool
{
    /**
     * @param JsonSchema|null $parameters
     */
    public function __construct(
        public ExecutionReference $reference,
        public string $name,
        public string $description,
        public ?array $parameters = null,
    ) {
    }
}
