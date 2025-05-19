<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Bedrock;

use AsyncAws\BedrockRuntime\BedrockRuntimeClient;
use PhpLlm\LlmChain\Bridge\Bedrock\Anthropic\ClaudeHandler;
use PhpLlm\LlmChain\Bridge\Bedrock\Meta\LlamaModelClient;
use PhpLlm\LlmChain\Bridge\Bedrock\Nova\NovaHandler;

final readonly class PlatformFactory
{
    public static function create(
        ?BedrockRuntimeClient $bedrockRuntimeClient = null,
    ): Platform {
        $modelClient[] = new ClaudeHandler($bedrockRuntimeClient);
        $modelClient[] = new NovaHandler($bedrockRuntimeClient);
        $modelClient[] = new LlamaModelClient($bedrockRuntimeClient);

        return new Platform($modelClient);
    }
}
