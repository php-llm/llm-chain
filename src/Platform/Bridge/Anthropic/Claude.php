<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic;

use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;

class Claude extends Model
{
    public const HAIKU_3 = 'claude-3-haiku-20240307';
    public const HAIKU_35 = 'claude-3-5-haiku-20241022';
    public const SONNET_3 = 'claude-3-sonnet-20240229';
    public const SONNET_35 = 'claude-3-5-sonnet-20240620';
    public const SONNET_35_V2 = 'claude-3-5-sonnet-20241022';
    public const SONNET_37 = 'claude-3-7-sonnet-20250219';
    public const OPUS_3 = 'claude-3-opus-20240229';

    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(
        string $name = self::SONNET_37,
        array $options = ['temperature' => 1.0, 'max_tokens' => 1000],
    ) {
        $capabilities = [
            Capability::INPUT_MESSAGES,
            Capability::INPUT_IMAGE,
            Capability::OUTPUT_TEXT,
            Capability::OUTPUT_STREAMING,
            Capability::TOOL_CALLING,
        ];

        parent::__construct($name, $capabilities, $options);
    }
}
