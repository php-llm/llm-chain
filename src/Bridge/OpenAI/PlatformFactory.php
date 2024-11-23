<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings\ModelClient as EmbeddingsModelClient;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings\ResponseConverter as EmbeddingsResponseConverter;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT\ModelClient as GPTModelClient;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT\ResponseConverter as GPTResponseConverter;
use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;

final readonly class PlatformFactory
{
    public static function create(#[\SensitiveParameter] string $apiKey): Platform
    {
        $httpClient = new EventSourceHttpClient();

        return new Platform(
            [new GPTModelClient($httpClient, $apiKey), new EmbeddingsModelClient($httpClient, $apiKey)],
            [new GPTResponseConverter(), new EmbeddingsResponseConverter()],
        );
    }
}
