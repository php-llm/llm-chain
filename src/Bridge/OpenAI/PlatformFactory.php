<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings\ResponseConverter;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings\ResponseFactory;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT\ResponseConverter as GPTResponseConverter;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT\ResponseFactory as GPTResponseFactory;
use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;

final readonly class PlatformFactory
{
    public static function create(#[\SensitiveParameter] string $apiKey): Platform
    {
        $httpClient = new EventSourceHttpClient();

        return new Platform(
            [new GPTResponseFactory($httpClient, $apiKey), new ResponseFactory($httpClient, $apiKey)],
            [new GPTResponseConverter(), new ResponseConverter()],
        );
    }
}
