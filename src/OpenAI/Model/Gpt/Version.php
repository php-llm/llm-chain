<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model\Gpt;

enum Version: string
{
    case GPT_35_TURBO = 'gpt-3.5-turbo';
    case GPT_35_TURBO_INSTRUCT = 'gpt-3.5-turbo-instruct';
    case GPT_4 = 'gpt-4';
    case GPT_4_TURBO = 'gpt-4-turbo';
    case GPT_4o = 'gpt-4o';
    case GPT_4o_MINI = 'gpt-4o-mini';
    case o1_MINI = 'o1-mini';
    case o1_PREVIEW = 'o1-preview';

    public function hasStructuredOutputSupport(): bool
    {
        return self::GPT_4o === $this || self::GPT_4o_MINI === $this;
    }
}
