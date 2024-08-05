<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Anthropic\Model\Claude;

enum Version: string
{
    case HAIKU_3 = 'claude-3-haiku-20240307';
    case SONNET_3 = 'claude-3-sonnet-20240229';
    case SONNET_35 = 'claude-3-5-sonnet-20240620';
    case OPUS = 'claude-3-opus-20240229';
}
