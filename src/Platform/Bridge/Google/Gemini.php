<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Google;

use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;

/**
 * @author Roy Garrido
 */
class Gemini extends Model
{
    public const GEMINI_2_FLASH = 'gemini-2.0-flash';
    public const GEMINI_2_PRO = 'gemini-2.0-pro-exp-02-05';
    public const GEMINI_2_FLASH_LITE = 'gemini-2.0-flash-lite-preview-02-05';
    public const GEMINI_2_FLASH_THINKING = 'gemini-2.0-flash-thinking-exp-01-21';
    public const GEMINI_1_5_FLASH = 'gemini-1.5-flash';

    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(string $name = self::GEMINI_2_PRO, array $options = ['temperature' => 1.0])
    {
        $capabilities = [
            Capability::INPUT_MESSAGES,
            Capability::INPUT_IMAGE,
            Capability::INPUT_AUDIO,
            Capability::INPUT_PDF,
            Capability::OUTPUT_STREAMING,
            Capability::STRUCTURED_OUTPUT,
            Capability::TOOL_CALLING,
        ];

        parent::__construct($name, $capabilities, $options);
    }
}
