<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Mistral;

use PhpLlm\LlmChain\Bridge\Mistral\Embeddings\ModelClient as EmbeddingsModelClient;
use PhpLlm\LlmChain\Bridge\Mistral\Embeddings\ResponseConverter as EmbeddingsResponseConverter;
use PhpLlm\LlmChain\Bridge\Mistral\Llm\ModelClient as MistralModelClient;
use PhpLlm\LlmChain\Bridge\Mistral\Llm\ResponseConverter as MistralResponseConverter;
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

        return new Platform(
            [new EmbeddingsModelClient($httpClient, $apiKey), new MistralModelClient($httpClient, $apiKey)],
            [new EmbeddingsResponseConverter(), new MistralResponseConverter()],
        );
    }
}
