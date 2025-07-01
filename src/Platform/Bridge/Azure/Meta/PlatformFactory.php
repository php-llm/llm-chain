<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Azure\Meta;

use PhpLlm\LlmChain\Platform\ContractInterface;
use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class PlatformFactory
{
    public static function create(
        string $baseUrl,
        #[\SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
        ?ContractInterface $contract = null,
    ): Platform {
        $modelClient = new LlamaHandler($httpClient ?? HttpClient::create(), $baseUrl, $apiKey);

        return new Platform([$modelClient], [$modelClient], $contract);
    }
}
