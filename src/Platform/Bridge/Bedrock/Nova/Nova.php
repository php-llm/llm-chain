<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova;

use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;

final class Nova extends Model
{
    public const MICRO = 'nova-micro';
    public const LITE = 'nova-lite';
    public const PRO = 'nova-pro';
    public const PREMIER = 'nova-premier';

    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(
        string $name = self::PRO,
        array $options = ['temperature' => 1.0, 'max_tokens' => 1000],
    ) {
        $capabilities = [
            Capability::INPUT_MESSAGES,
            Capability::OUTPUT_TEXT,
            Capability::TOOL_CALLING,
        ];

        if (self::MICRO !== $name) {
            $capabilities[] = Capability::INPUT_IMAGE;
        }

        parent::__construct($name, $capabilities, $options);
    }
}
