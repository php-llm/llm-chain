<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Replicate;

use PhpLlm\LlmChain\Platform\Bridge\Replicate\Contract\LlamaMessageBagNormalizer;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\Clock\Clock;
use Symfony\Component\HttpClient\HttpClient;
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
        ?Contract $contract = null,
    ): Platform {
        return new Platform(
            [new LlamaModelClient(new Client($httpClient ?? HttpClient::create(), new Clock(), $apiKey))],
            [new LlamaResponseConverter()],
            $contract ?? Contract::create(new LlamaMessageBagNormalizer()),
        );
    }
}
