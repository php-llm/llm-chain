<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\CerebrasAI;

use PhpLlm\LlmChain\Bridge\OpenAI\GPT\ResponseConverter as OpenAIGPTResponseConverter;
use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        return new Platform([
            new Client($httpClient, $apiKey),
        ], [
            new OpenAIGPTResponseConverter(),
        ]);
    }
}
