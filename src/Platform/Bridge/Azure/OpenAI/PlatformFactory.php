<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Azure\OpenAI;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT\ResponseConverter;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper\AudioNormalizer;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
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
            [new ResponseConverter(), new Embeddings\ResponseConverter(), new \PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper\ResponseConverter()],
            Contract::create(new AudioNormalizer()),
        );
    }
}
