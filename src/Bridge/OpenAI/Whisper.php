<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI;

use PhpLlm\LlmChain\Model\Capability;
use PhpLlm\LlmChain\Model\Model;

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
