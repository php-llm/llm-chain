<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Albert;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings\ResponseConverter as EmbeddingsResponseConverter;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT\ResponseConverter as GPTResponseConverter;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Webmozart\Assert\Assert;

final class PlatformFactory
{
    public static function create(
        string $apiKey,
        string $albertUrl,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        Assert::startsWith($albertUrl, 'https://', 'The Albert URL must start with "https://".');

        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        // The base URL should include the full path to the API endpoint
        // Albert API expects the URL to end with /v1/
        $baseUrl = rtrim($albertUrl, '/');
        if (!str_ends_with($baseUrl, '/v1')) {
            $baseUrl .= '/v1';
        }
        $baseUrl .= '/';

        // Create Albert-specific model clients with custom base URL
        $gptClient = new GPTModelClient($httpClient, $apiKey, $baseUrl);
        $embeddingsClient = new EmbeddingsModelClient($httpClient, $apiKey, $baseUrl);

        return new Platform(
            [
                $gptClient,
                $embeddingsClient,
            ],
            [
                new GPTResponseConverter(),
                new EmbeddingsResponseConverter(),
            ],
            Contract::create(),
        );
    }
}
