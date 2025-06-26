<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Google;

use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\AssistantMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\MessageBagNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\ToolCallMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\ToolNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\UserMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Embeddings\ModelClient;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Roy Garrido
 */
final readonly class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $responseHandler = new ModelHandler($httpClient, $apiKey);
        $embeddings = new ModelClient($httpClient, $apiKey);

        return new Platform([$responseHandler, $embeddings], [$responseHandler, $embeddings], Contract::create(
            new AssistantMessageNormalizer(),
            new MessageBagNormalizer(),
            new ToolNormalizer(),
            new ToolCallMessageNormalizer(),
            new UserMessageNormalizer(),
        ));
    }
}
