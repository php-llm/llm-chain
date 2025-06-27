<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface Task
{
    public const TRANSCRIPTION = 'transcription';
    public const TRANSLATION = 'translation';
}