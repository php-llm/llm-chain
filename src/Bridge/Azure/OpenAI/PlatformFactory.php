<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Azure\OpenAI;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT\ResponseConverter;
use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;

final readonly class PlatformFactory
{
    public static function create(
        string $baseUrl,
        string $deployment,
        string $apiVersion,
        #[\SensitiveParameter] string $apiKey,
    ): Platform {
        $httpClient = new EventSourceHttpClient();
        $embeddingsResponseFactory = new EmbeddingsModelClient($httpClient, $baseUrl, $deployment, $apiVersion, $apiKey);
        $GPTResponseFactory = new GPTModelClient($httpClient, $baseUrl, $deployment, $apiVersion, $apiKey);

        return new Platform(
            [$GPTResponseFactory, $embeddingsResponseFactory],
            [new ResponseConverter(), new Embeddings\ResponseConverter()],
        );
    }
}
