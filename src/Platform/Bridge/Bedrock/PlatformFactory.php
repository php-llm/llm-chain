<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock;

use AsyncAws\BedrockRuntime\BedrockRuntimeClient;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Anthropic\ClaudeHandler;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Meta\LlamaModelClient;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\NovaHandler;
use PhpLlm\LlmChain\Platform\Contract;

/**
 * @author Björn Altmann
 */
final readonly class PlatformFactory
{
    public static function create(
        BedrockRuntimeClient $bedrockRuntimeClient = new BedrockRuntimeClient(),
        ?Contract $contract = null,
    ): Platform {
        if (!class_exists(BedrockRuntimeClient::class)) {
            throw new \RuntimeException('For using the Bedrock platform, the async-aws/bedrock-runtime package is required. Try running "composer require async-aws/bedrock-runtime".');
        }

        $modelClient[] = new ClaudeHandler($bedrockRuntimeClient);
        $modelClient[] = new NovaHandler($bedrockRuntimeClient);
        $modelClient[] = new LlamaModelClient($bedrockRuntimeClient);

        return new Platform($modelClient, $contract);
    }
}
