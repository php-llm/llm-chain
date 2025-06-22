<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Mistral;

use PhpLlm\LlmChain\Platform\Bridge\Mistral\Contract\ToolNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\Embeddings\ModelClient as EmbeddingsModelClient;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\Embeddings\ResponseConverter as EmbeddingsResponseConverter;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\Llm\ModelClient as MistralModelClient;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\ResponseContract\MistralResponseParser;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\ResponseContract\MistralStreamParser;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Contract\ResponseDenormalizer;
use PhpLlm\LlmChain\Platform\Platform;
use PhpLlm\LlmChain\Platform\ResponseContract;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
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
            [
                new EmbeddingsResponseConverter(),
                (new ResponseContract(
                    new ResponseDenormalizer(
                        new MistralResponseParser(),
                        new MistralStreamParser(),
                    )
                ))->asConverter(),
            ],
            Contract::create(new ToolNormalizer()),
        );
    }
}
