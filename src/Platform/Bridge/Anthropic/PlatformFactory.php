<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract\AnthropicSet;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\ContractInterface;
use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        string $apiKey,
        string $version = '2023-06-01',
        ?HttpClientInterface $httpClient = null,
        ?ContractInterface $contract = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        return new Platform(
            [new ModelClient($httpClient, $apiKey, $version)],
            [new ResponseConverter()],
            $contract ?? Contract::create(...AnthropicSet::get()),
        );
    }
}
