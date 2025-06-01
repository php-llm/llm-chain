<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenRouter;

use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\AssistantMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\MessageBagNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\UserMessageNormalizer;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author rglozman
 */
final class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $handler = new Client($httpClient, $apiKey);

        return new Platform([$handler], [$handler], Contract::create(
            new AssistantMessageNormalizer(),
            new MessageBagNormalizer(),
            new UserMessageNormalizer(),
        ));
    }
}
