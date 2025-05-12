<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Bedrock\Nova;

use PhpLlm\LlmChain\Model\Capability;
use PhpLlm\LlmChain\Model\Model;

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
            // Tool calling is supported, but:
            // Invoke currently has some validation errors on the bedrock api side when returning tool calling results.
            // It's encouraged to use the converse api instead.
            // Capability::TOOL_CALLING,
        ];

        if (self::MICRO !== $name) {
            $capabilities[] = Capability::INPUT_IMAGE;
        }

        parent::__construct($name, $capabilities, $options);
    }
}
