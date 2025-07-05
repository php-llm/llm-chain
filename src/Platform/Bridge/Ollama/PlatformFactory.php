<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Ollama;

use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class PlatformFactory
{
    public static function create(
        string $hostUrl = 'http://localhost:11434',
        ?HttpClientInterface $httpClient = null,
        ?Contract $contract = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $handler = new LlamaModelHandler($httpClient, $hostUrl);

        return new Platform([$handler], [$handler], $contract);
    }
}
