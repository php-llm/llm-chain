<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock;

use AsyncAws\BedrockRuntime\BedrockRuntimeClient;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Anthropic\ClaudeHandler;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Meta\LlamaModelClient;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\NovaHandler;

/**
 * @author Björn Altmann
 */
final readonly class PlatformFactory
{
    public static function create(
        BedrockRuntimeClient $bedrockRuntimeClient = new BedrockRuntimeClient(),
    ): Platform {
        $modelClient[] = new ClaudeHandler($bedrockRuntimeClient);
        $modelClient[] = new NovaHandler($bedrockRuntimeClient);
        $modelClient[] = new LlamaModelClient($bedrockRuntimeClient);

        return new Platform($modelClient);
    }
}
