<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenAI;

use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;

class Whisper extends Model
{
    public const WHISPER_1 = 'whisper-1';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(string $name = self::WHISPER_1, array $options = [])
    {
        $capabilities = [
            Capability::INPUT_AUDIO,
            Capability::OUTPUT_TEXT,
        ];

        parent::__construct($name, $capabilities, $options);
    }
}
