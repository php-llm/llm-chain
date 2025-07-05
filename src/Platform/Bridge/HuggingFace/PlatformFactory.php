<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace;

use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Contract\FileNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Contract\MessageBagNormalizer;
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
        #[\SensitiveParameter]
        string $apiKey,
        string $provider = Provider::HF_INFERENCE,
        ?HttpClientInterface $httpClient = null,
        ?Contract $contract = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        return new Platform(
            [new ModelClient($httpClient, $provider, $apiKey)],
            [new ResponseConverter()],
            $contract ?? Contract::create(
                new FileNormalizer(),
                new MessageBagNormalizer(),
            ),
        );
    }
}
