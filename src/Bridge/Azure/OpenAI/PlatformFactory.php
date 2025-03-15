<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Azure\OpenAI;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT\ResponseConverter;
use PhpLlm\LlmChain\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PlatformFactory
{
    public static function create(
        string $baseUrl,
        string $deployment,
        string $apiVersion,
        #[\SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $embeddingsResponseFactory = new EmbeddingsModelClient($httpClient, $baseUrl, $deployment, $apiVersion, $apiKey);
        $GPTResponseFactory = new GPTModelClient($httpClient, $baseUrl, $deployment, $apiVersion, $apiKey);
        $whisperResponseFactory = new WhisperModelClient($httpClient, $baseUrl, $deployment, $apiVersion, $apiKey);

        return new Platform(
            [$GPTResponseFactory, $embeddingsResponseFactory, $whisperResponseFactory],
            [new ResponseConverter(), new Embeddings\ResponseConverter(), new Whisper\ResponseConverter()],
        );
    }
}
