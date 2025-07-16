<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Albert;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings\ResponseConverter as EmbeddingsResponseConverter;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT\ResponseConverter as GPTResponseConverter;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter] string $apiKey,
        string $baseUrl,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        str_starts_with($baseUrl, 'https://') || throw new InvalidArgumentException('The Albert URL must start with "https://".');
        !str_ends_with($baseUrl, '/') || throw new InvalidArgumentException('The Albert URL must not end with a trailing slash.');
        preg_match('/\/v\d+$/', $baseUrl) || throw new InvalidArgumentException('The Albert URL must include an API version (e.g., /v1, /v2).');

        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        return new Platform(
            [
                new GPTModelClient($httpClient, $apiKey, $baseUrl),
                new EmbeddingsModelClient($httpClient, $apiKey, $baseUrl),
            ],
            [new GPTResponseConverter(), new EmbeddingsResponseConverter()],
            Contract::create(),
        );
    }
}
