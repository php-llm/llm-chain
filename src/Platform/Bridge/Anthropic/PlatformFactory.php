<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract\AssistantMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract\DocumentNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract\DocumentUrlNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract\ImageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract\ImageUrlNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract\MessageBagNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract\ToolCallMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract\ToolNormalizer;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        string $apiKey,
        string $version = '2023-06-01',
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        return new Platform(
            [new ModelClient($httpClient, $apiKey, $version)],
            [new ResponseConverter()],
            Contract::create(
                new AssistantMessageNormalizer(),
                new DocumentNormalizer(),
                new DocumentUrlNormalizer(),
                new ImageNormalizer(),
                new ImageUrlNormalizer(),
                new MessageBagNormalizer(),
                new ToolCallMessageNormalizer(),
                new ToolNormalizer(),
            )
        );
    }
}
